<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WithdrawalRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'withdrawal-subadmin'])) {
            abort(403, 'Unauthorized');
        }
        
        // Get user withdrawal requests from withdrawal_requests table
        $userQuery = \App\Models\WithdrawalRequest::with(['user'])
            ->orderByDesc('created_at');
            
        // Get agent withdrawal requests from agent_withdrawal_requests table
        $agentQuery = \App\Models\AgentWithdrawalRequest::with(['agent'])
            ->orderByDesc('created_at');
            
        // Apply date filter if provided
        if ($request->has('start_date') && $request->start_date) {
            $userQuery->whereDate('created_at', '>=', $request->start_date);
            $agentQuery->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $userQuery->whereDate('created_at', '<=', $request->end_date);
            $agentQuery->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Get all records without pagination
        $userWithdrawalRequests = $userQuery->get();
        $agentWithdrawalRequests = $agentQuery->get();
        $allWithdrawalRequests = $userWithdrawalRequests->concat($agentWithdrawalRequests);
            
        return view('admin.withdrawals_requests.index', [
            'withdrawalRequests' => $allWithdrawalRequests,
            'userWithdrawalRequests' => $userWithdrawalRequests,
            'agentWithdrawalRequests' => $agentWithdrawalRequests,
            'request' => $request
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasAnyRole(['admin', 'withdrawal-subadmin'])) {
            abort(403, 'Unauthorized');
        }
        return view('admin.withdrawals_requests.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'withdrawal-subadmin'])) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'subscription_name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $validated['user_id'] = auth()->id();
        
        WithdrawalRequest::create($validated);

        return redirect()->route('withdrawal-requests.index')
            ->with('success', 'Withdrawal request created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WithdrawalRequest $withdrawalRequest)
    {
        return view('admin.withdrawals_requests.show', compact('withdrawalRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WithdrawalRequest $withdrawalRequest)
    {
        return view('admin.withdrawals_requests.edit', compact('withdrawalRequest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        $action = $request->input('action');
        
        if ($action === 'approve' && $withdrawalRequest->status === 'pending') {
            // Start database transaction
            \DB::beginTransaction();
            
            try {
                $user = $withdrawalRequest->user;
                
                // Check if user has sufficient balance
                if ($user->wallet_balance < $withdrawalRequest->amount) {
                    return redirect()->back()->with('error', 'Insufficient wallet balance.');
                }
                
                // Deduct amount from user's wallet
                $user->wallet_balance -= $withdrawalRequest->amount;
                $user->save();
                
                // Update withdrawal request status
                $withdrawalRequest->status = 'approved';
                $withdrawalRequest->approved_at = now();
                $withdrawalRequest->admin_id = auth()->id();
                $withdrawalRequest->save();
                
                // Create wallet transaction record
                \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $withdrawalRequest->amount,
                    'type' => 'debit',
                    'description' => 'Withdrawal approved',
                    'balance_after' => $user->wallet_balance,
                ]);
                
                // Commit transaction
                \DB::commit();
                
                // Notify user
                if ($withdrawalRequest->user) {
                    $withdrawalRequest->user->notify(new \App\Notifications\WithdrawalRequestStatusNotification('approved', $withdrawalRequest));
                }
                
                return redirect()->back()->with('success', 'Withdrawal request approved and amount deducted from user\'s wallet.');
                
            } catch (\Exception $e) {
                // Rollback transaction on error
                \DB::rollBack();
                \Log::error('Error approving withdrawal: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to process withdrawal approval: ' . $e->getMessage());
            }
            
        } elseif ($action === 'decline' && $withdrawalRequest->status === 'pending') {
            // Decline logic
            $withdrawalRequest->status = 'declined';
            $withdrawalRequest->approved_at = now();
            $withdrawalRequest->admin_id = auth()->id();
            $withdrawalRequest->remarks = $request->input('remarks');
            $withdrawalRequest->save();
            
            // Notify user
            if ($withdrawalRequest->user) {
                $withdrawalRequest->user->notify(new \App\Notifications\WithdrawalRequestStatusNotification('declined', $withdrawalRequest));
            }
            return redirect()->back()->with('success', 'Withdrawal request declined and user notified.');
        }
        return redirect()->back()->with('error', 'Invalid action or request already processed.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WithdrawalRequest $withdrawalRequest)
    {
        $withdrawalRequest->delete();

        return redirect()->route('withdrawal-requests.index')
            ->with('success', 'Withdrawal request deleted successfully');
    }

    /**
     * Export withdrawal requests to CSV
     */
    public function exportToCsv()
    {
        if (!auth()->user()->hasAnyRole(['admin', 'withdrawal-subadmin'])) {
            abort(403, 'Unauthorized');
        }

        // Get user withdrawal requests
        $userWithdrawalRequests = \App\Models\WithdrawalRequest::with(['user'])->get();
        // Get agent withdrawal requests
        $agentWithdrawalRequests = \App\Models\AgentWithdrawalRequest::with(['agent'])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="withdrawal_requests_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($userWithdrawalRequests, $agentWithdrawalRequests) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Request Type',
                'ID',
                'Name',
                'Phone Number',
                'Amount',
                'Status',
                'Payment Method',
                'UPI ID',
                'Bank Account Number',
                'IFSC Code',
                'Bank Name',
                'Requested At',
                'Processed At',
                'Remarks'
            ]);

            // Add user withdrawal requests
            foreach ($userWithdrawalRequests as $request) {
                fputcsv($file, [
                    'User Withdrawal',
                    $request->id,
                    $request->user ? $request->user->name : 'N/A',
                    $request->user ? $request->user->phone : 'N/A',
                    '₹' . number_format($request->amount, 2),
                    ucfirst($request->status),
                    $request->payment_method ?? 'N/A',
                    $request->upi_id ?? 'Not provided',
                    $request->account_number ?? 'Not provided',
                    $request->ifsc_code ?? 'Not provided',
                    $request->bank_name ?? 'Not provided',
                    $request->created_at->format('Y-m-d H:i:s'),
                    $request->processed_at ? $request->processed_at->format('Y-m-d H:i:s') : 'N/A',
                    $request->remarks ?? 'N/A'
                ]);
            }

            // Add agent withdrawal requests
            foreach ($agentWithdrawalRequests as $request) {
                fputcsv($file, [
                    'Agent Withdrawal',
                    $request->id,
                    $request->agent ? $request->agent->name : 'N/A',
                    $request->agent ? $request->agent->phone_number : 'N/A',
                    '₹' . number_format($request->amount, 2),
                    ucfirst($request->status),
                    $request->payment_method ?? 'N/A',
                    $request->upi_id ?? 'Not provided',
                    $request->account_number ?? 'Not provided',
                    $request->ifsc_code ?? 'Not provided',
                    $request->bank_name ?? 'Not provided',
                    $request->created_at->format('Y-m-d H:i:s'),
                    $request->processed_at ? $request->processed_at->format('Y-m-d H:i:s') : 'N/A',
                    $request->remarks ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
