<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentPlan;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|push-subadmin|withdrawal-subadmin|ticket-manager');
    }

    private function checkAdmin()
    {
        if (!auth()->user()->hasAnyRole(['admin', 'ticket-manager'])) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        try {
            $start = $request->input('start_date') ?? now()->startOfMonth()->toDateString();
            $end = $request->input('end_date') ?? now()->endOfDay()->toDateString();

            // Agent revenue - Sum of all joining fees paid by agents
            $agentRevenue = \App\Models\Agent::where('joining_fee_paid', true)
                ->whereDate('joining_fee_paid_at', '>=', $start)
                ->whereDate('joining_fee_paid_at', '<=', $end)
                ->sum('joining_fee_amount');

            // User revenue (only users who have purchased a plan)
            $userRevenue = User::whereNotNull('purchased_date')
                ->whereBetween('purchased_date', [$start, $end])
                ->sum('total_amount_paid');

            $totalRevenue = $agentRevenue + $userRevenue;

            $totalAgents = Agent::count();
            $totalUsers = User::count();
            $totalUserSubscriptions = SubscriptionPlan::count();
            $totalAgentSubscriptions = AgentPlan::count();
            $totalAgentsPaid = Agent::where('joining_fee_paid', 1)->count();
            $totalUserSubscribed = User::whereNotNull('subscription_name')->count();
            $totalReferrals = User::whereNotNull('agent_id')->count();
            $latestUser = User::latest()->first();
            $latestAgent = Agent::latest()->first();
            $userWithdrawalRequestCount = \App\Models\WithdrawalRequest::count();
            $agentWithdrawalRequestCount = \App\Models\AgentWithdrawalRequest::count();
            $withdrawalRequestCount = $userWithdrawalRequestCount + $agentWithdrawalRequestCount;

            // $totalRevenue = User::sum('total_amount_paid');
            // $recentWithdrawals = WithdrawalRequest::with('user')->latest()->take(5)->get();
            // $recentUsers = User::latest()->take(5)->get();
            // $recentAgents = Agent::latest()->take(5)->get();

            return view('admin.dashboard', [
                'totalAgents' => $totalAgents,
                'totalAgentsPaid' => $totalAgentsPaid,
                'totalUsers' => $totalUsers,
                'totalUserSubscriptions' => $totalUserSubscriptions,
                'totalAgentSubscriptions' => $totalAgentSubscriptions,
                'totalUserSubscribed' => $totalUserSubscribed,
                'totalReferrals' => $totalReferrals,
                'latestUser' => $latestUser,
                'latestAgent' => $latestAgent,
                'withdrawalRequestCount' => $withdrawalRequestCount,
                'agentWithdrawalRequestCount' => $agentWithdrawalRequestCount,
                'userWithdrawalRequestCount' => $userWithdrawalRequestCount,
                'agentRevenue' => $agentRevenue,
                'userRevenue' => $userRevenue,
                'totalRevenue' => $totalRevenue,
                'start' => $start,
                'end' => $end,
            ]);
        } catch (\Exception $e) {
            // Log the error and redirect back with error message
            \Log::error('Dashboard Error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'An error occurred while loading the dashboard. Please try again.');
        }
    }

    /**
     * Create a new agent
     */
    public function createAgent(Request $request)
    {
        $this->checkAdmin();
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone_number' => 'required|string|regex:/^[0-9]{10}$/|unique:agents,phone_number',
                'email' => 'nullable|email|unique:agents,email',
                'password' => 'required|string|min:6',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'pincode' => 'nullable|string|regex:/^[0-9]{6}$/',
                'aadhar_number' => 'nullable|string|regex:/^[0-9]{12}$/',
                'pan_number' => 'nullable|string|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
                'gst_number' => 'nullable|string|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                'bio' => 'nullable|string|max:1000'
            ]);

            // Generate unique referral code
            $referralCode = Agent::generateReferralCode();

            // Create the agent
            $agent = Agent::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'referral_code' => $referralCode,
                'is_verified' => true,
                'profile_completed' => true,
                'status' => 'active',
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'aadhar_number' => $request->aadhar_number,
                'pan_number' => $request->pan_number,
                'gst_number' => $request->gst_number,
                'bio' => $request->bio
            ]);

            // Assign agent role
            $agent->assignRole('agent');

            Log::info('Agent created by admin', [
                'admin_id' => Auth::id(),
                'agent_id' => $agent->id,
                'phone_number' => $agent->phone_number,
                'referral_code' => $agent->referral_code
            ]);

            return redirect()->route('admin.agents.index')
                ->with('success', 'Agent created successfully with referral code: ' . $agent->referral_code);

        } catch (\Exception $e) {
            Log::error('Agent creation error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create agent: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update agent
     */
    public function updateAgent(Request $request, $id)
    {
        $this->checkAdmin();
        try {
            $agent = Agent::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'phone_number' => 'required|string|regex:/^[0-9]{10}$/|unique:agents,phone_number,' . $id,
                'email' => 'nullable|email|unique:agents,email,' . $id,
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'pincode' => 'nullable|string|regex:/^[0-9]{6}$/',
                'aadhar_number' => 'nullable|string|regex:/^[0-9]{12}$/',
                'pan_number' => 'nullable|string|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
                'gst_number' => 'nullable|string|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                'bio' => 'nullable|string|max:1000',
                'status' => 'required|in:active,inactive,suspended'
            ]);

            $agent->update($request->only([
                'name', 'phone_number', 'email', 'address', 'city', 'state', 'pincode',
                'aadhar_number', 'pan_number', 'gst_number', 'bio', 'status'
            ]));

            Log::info('Agent updated by admin', [
                'admin_id' => Auth::id(),
                'agent_id' => $agent->id,
                'phone_number' => $agent->phone_number
            ]);

            return redirect()->route('admin.agents.index')
                ->with('success', 'Agent updated successfully');

        } catch (\Exception $e) {
            Log::error('Agent update error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update agent: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete agent
     */
    public function deleteAgent($id)
    {
        $this->checkAdmin();
        try {
            $agent = Agent::findOrFail($id);
            $agentName = $agent->name;
            $agent->delete();

            Log::info('Agent deleted by admin', [
                'admin_id' => Auth::id(),
                'agent_id' => $id,
                'agent_name' => $agentName
            ]);

            return redirect()->route('admin.agents.index')
                ->with('success', 'Agent deleted successfully');

        } catch (\Exception $e) {
            Log::error('Agent deletion error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete agent: ' . $e->getMessage());
        }
    }

    public function allWithdrawalRequests()
    {
        $userWithdrawalRequests = \App\Models\WithdrawalRequest::with('user')
            ->select('id', 'user_id', 'amount', 'upi_id', 'account_number', 'ifsc_code', 'bank_name', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->get();

        $agentWithdrawalRequests = \App\Models\AgentWithdrawalRequest::with('agent')
            ->select('id', 'agent_id', 'amount', 'upi_id', 'account_number', 'ifsc_code', 'bank_name', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->get();
        $showAll = true;
        return view('admin.withdrawals_requests.index', [
            'userWithdrawalRequests' => $userWithdrawalRequests,
            'agentWithdrawalRequests' => $agentWithdrawalRequests,
            'showAll' => $showAll
        ]);
    }
}