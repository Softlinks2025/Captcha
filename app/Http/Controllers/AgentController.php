<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class AgentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    private function checkAdmin()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    public function index(Request $request)
    {
        $this->checkAdmin();
        try {
            // Log the start of the operation
            \Log::info('Fetching agents list');
            
            // Check if the agents table exists
            if (!\Schema::hasTable('agents')) {
                \Log::error('Agents table does not exist');
                abort(500, 'The agents table does not exist. Please run migrations.');
            }
            
            // Get filter parameters
            $status = $request->query('status');
            $name = $request->query('name');
            $phone = $request->query('phone');
            
            // Build the query
            $query = Agent::select('id', 'name', 'phone_number', 'created_at', 'wallet_balance', 'total_earnings', 'total_withdrawals', 'referral_code', 'last_login_at', 'joining_fee_paid', 'address')
                ->when($name, function($q) use ($name) {
                    $q->where('name', 'like', '%' . $name . '%');
                })
                ->when($phone, function($q) use ($phone) {
                    $q->where('phone_number', 'like', '%' . $phone . '%');
                })
                ->orderBy('created_at', 'desc');
                
            // Apply status filter if provided
            if ($status === 'active') {
                $query->whereNotNull('last_login_at')
                      ->where('last_login_at', '>=', Carbon::now()->subDays(3));
            } elseif ($status === 'inactive') {
                $query->where(function($q) {
                    $q->whereNull('last_login_at')
                      ->orWhere('last_login_at', '<', Carbon::now()->subDays(3));
                });
            }
            
            // Paginate with query string for maintaining filters
            $agents = $query->paginate(100)->withQueryString();
            \Log::info('Successfully fetched ' . $agents->count() . ' agents');

            // Get counts for the dashboard cards
            $totalAgents = Agent::count();
            $activeAgents = Agent::whereNotNull('last_login_at')
                ->where('last_login_at', '>=', Carbon::now()->subDays(3))
                ->count();
            $inactiveAgents = Agent::whereNull('last_login_at')
                ->orWhere('last_login_at', '<', Carbon::now()->subDays(3))
                ->count();

            return view('admin.agents.index', compact(
                'agents', 
                'totalAgents', 
                'activeAgents', 
                'inactiveAgents', 
                'status',
                'name',
                'phone'
            ));
            
        } catch (\Exception $e) {
            // Log the full error with trace
            \Log::error('Error in AgentController@index: ' . $e->getMessage() . 
                       ' in ' . $e->getFile() . ':' . $e->getLine() . 
                       '\n' . $e->getTraceAsString());
            
            // Return a more helpful error message in development
            if (config('app.debug')) {
                return response()->view('errors.500', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
            
            // Generic error message in production
            return response()->view('errors.500', [
                'message' => 'An error occurred while loading the agents page.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'string',
                'regex:/^(\+91|0)?[6-9]\d{9}$/'
            ],
            'date_of_birth' => 'nullable|date|before:today',
            'email' => 'nullable|email',
            'upi_id' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        // Normalize phone number to include +91 if not present
        $phone = $validated['phone_number'];
        if (!str_starts_with($phone, '+91')) {
            $phone = '+91' . ltrim($phone, '0');
        }
        $validated['phone_number'] = $phone;

        $data = $validated;
        $data['referral_code'] = \App\Models\Agent::generateReferralCode();
        $data['profile_completed'] = true;

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        } else if ($request->filled('profile_image_url') && filter_var($request->input('profile_image_url'), FILTER_VALIDATE_URL)) {
            $url = $request->input('profile_image_url');
            $imageContents = @file_get_contents($url);
            if ($imageContents !== false) {
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename = 'profile-images/' . uniqid('agent_admin_' . time() . '_') . '.' . $extension;
                \Storage::disk('public')->put($filename, $imageContents);
                $data['profile_image'] = $filename;
            }
        }

        \App\Models\Agent::create($data);
        return redirect()->route('admin.agents.index')->with('success', 'Agent created.');
    }

    public function edit(Agent $agent)
    {
        $this->checkAdmin();
        return view('admin.agents.edit', compact('agent'));
    }

    public function update(Request $request, Agent $agent)
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'string',
                'regex:/^(\+91|0)?[6-9]\d{9}$/'
            ],
            'date_of_birth' => 'nullable|date|before:today',
            'email' => 'nullable|email',
            'upi_id' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'pincode' => 'nullable|string',
            'aadhar_number' => 'nullable|string',
            'pan_number' => 'nullable|string',
            'gst_number' => 'nullable|string',
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
            'bank_account_number' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'account_holder_name' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        // Normalize phone number to include +91 if not present
        $phone = $validated['phone_number'];
        if (!str_starts_with($phone, '+91')) {
            $phone = '+91' . ltrim($phone, '0');
        }
        $validated['phone_number'] = $phone;

        $data = $validated;

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        } else if ($request->filled('profile_image_url') && filter_var($request->input('profile_image_url'), FILTER_VALIDATE_URL)) {
            $url = $request->input('profile_image_url');
            $imageContents = @file_get_contents($url);
            if ($imageContents !== false) {
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename = 'profile-images/' . uniqid('agent_admin_' . time() . '_') . '.' . $extension;
                \Storage::disk('public')->put($filename, $imageContents);
                $data['profile_image'] = $filename;
            }
        }

        $agent->update($data);
        return redirect()->route('admin.agents.index')->with('success', 'Agent updated.');
    }

    public function destroy(Agent $agent)
    {
        $this->checkAdmin();
        $agent->delete();
        return redirect()->route('admin.agents.index')->with('success', 'Agent deleted.');
    }

    public function show($id)
    {
        $this->checkAdmin();
        $agent = Agent::with(['referredUsers', 'walletTransactions', 'withdrawalRequests'])->findOrFail($id);
        return view('admin.agents.show', compact('agent'));
    }

    public function create()
    {
        $this->checkAdmin();
        return view('admin.agents.create');
    }

    /**
     * Get a list of all agents (for contact matching)
     * GET /api/v1/agents/list
     */
    public function list(Request $request)
    {
        $this->checkAdmin();
        $agents = \App\Models\Agent::select('id', 'name', 'phone_number', 'profile_image')->get();
        $agents = $agents->map(function($agent) {
            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'phone_number' => $agent->phone_number,
                'profile_image_url' => $agent->profile_image ? asset('storage/' . $agent->profile_image) : null,
            ];
        });
        return response()->json(['status' => 'success', 'agents' => $agents]);
    }

    /**
     * Export agent list as CSV (with status filter and correct columns)
     */
    
    public function exportCsv(Request $request)
    {
        $this->checkAdmin();
        $status = $request->query('status');
        $query = Agent::select('id', 'name', 'phone_number', 'email', 'address', 'referral_code', 'wallet_balance', 'total_earnings', 'total_withdrawals', 'last_login_at', 'created_at');
        if ($status === 'active') {
            $query->whereNotNull('last_login_at')->where('last_login_at', '>=', \Carbon\Carbon::now()->subDays(3));
        } elseif ($status === 'inactive') {
            $query->where(function($q) {
                $q->whereNull('last_login_at')->orWhere('last_login_at', '<', \Carbon\Carbon::now()->subDays(3));
            });
        }
        $agents = $query->latest()->get();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="agents.csv"',
        ];
        $columns = ['ID', 'Name', 'Phone', 'Email', 'Address', 'Referral Code', 'Status', 'Wallet Balance', 'Total Earnings', 'Total Withdrawals', 'Created At'];
        $callback = function() use ($agents, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($agents as $agent) {
                $inactive = is_null($agent->last_login_at) || \Carbon\Carbon::parse($agent->last_login_at)->lt(\Carbon\Carbon::now()->subDays(3));
                $status = $inactive ? 'Inactive' : 'Active';
                fputcsv($file, [
                    $agent->id,
                    $agent->name,
                    $agent->phone_number,
                    $agent->email,
                    $agent->address,
                    $agent->referral_code,
                    $status,
                    $agent->wallet_balance,
                    $agent->total_earnings,
                    $agent->total_withdrawals,
                    $agent->created_at,
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}