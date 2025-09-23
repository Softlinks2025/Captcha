<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    // Create a Razorpay order for any payment purpose
    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1', // in rupees
            'purpose' => 'required|string', // e.g. 'agent_joining_fee', 'plan_purchase'
            'receipt' => 'nullable|string', // optional custom receipt
        ]);
        $user = Auth::guard('agent')->user() ?? Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Authentication required.'], 401);
        }
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $receipt = $request->receipt ?? ($request->purpose . '_' . $user->id . '_' . time());
        $order = $api->order->create([
            'receipt' => $receipt,
            'amount' => $request->amount * 100, // Amount in paise
            'currency' => 'INR',
            'payment_capture' => 1
        ]);
        $orderArr = $order->toArray(); // Use Razorpay Entity toArray method
        // Store the order in the payments table
        \App\Models\Payment::create([
            'user_id' => $user instanceof \App\Models\User ? $user->id : null,
            'agent_id' => $user instanceof \App\Models\Agent ? $user->id : null,
            'order_id' => $orderArr['id'] ?? null,
            'purpose' => $request->purpose,
            'amount' => $request->amount,
            'currency' => 'INR',
            'status' => 'created',
            'meta' => [
                'razorpay_order' => $orderArr,
                'receipt' => $receipt,
            ],
        ]);
        return response()->json([
            'status' => 'success',
            'order' => $orderArr
        ]);
    }

    // Verify Razorpay payment for any purpose
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'purpose' => 'required|string', // e.g. 'agent_joining_fee', 'plan_purchase'
        ]);

        $user = Auth::guard('agent')->user() ?? Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Authentication required.'], 401);
        }

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        
        $attributes = [
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature
        ];

        try {
            // Verify the payment signature
            $api->utility->verifyPaymentSignature($attributes);
            
            // Update the payment record
            $payment = \App\Models\Payment::where('order_id', $request->razorpay_order_id)->first();
            if ($payment) {
                // Update payment status
                $payment->update([
                    'status' => 'completed',
                    'payment_id' => $request->razorpay_payment_id,
                    'paid_at' => now(),
                    'meta->razorpay_payment_id' => $request->razorpay_payment_id,
                    'meta->razorpay_signature' => $request->razorpay_signature,
                ]);
                
                // Refresh the payment model to get updated attributes
                $payment->refresh();
                
                // Log the payment update
                \Log::info('Payment verified and processed', [
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                    'user_type' => get_class($user),
                    'purpose' => $request->purpose,
                    'amount' => $payment->amount, // Use the amount from payment record
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'updated_at' => $payment->updated_at,
                ]);
            } else {
                \Log::error('Payment record not found', [
                    'order_id' => $request->razorpay_order_id,
                    'payment_id' => $request->razorpay_payment_id,
                    'purpose' => $request->purpose
                ]);
                throw new \Exception('Payment record not found');
            }

            // Handle post-payment logic based on purpose
            if ($request->purpose === 'agent_joining_fee') {
                // Log the start of joining fee processing
                \Log::info('Starting agent joining fee processing', [
                    'user_id' => $user->id,
                    'user_type' => get_class($user),
                    'payment_id' => $request->razorpay_payment_id,
                    'payment_amount' => $request->amount
                ]);
                // Log the start of joining fee processing
                \Log::info('Starting agent joining fee processing', [
                    'user_id' => $user->id,
                    'user_type' => get_class($user),
                    'payment_id' => $request->razorpay_payment_id
                ]);
                
                // Get the agent model (in case user is logged in as agent or user)
                $agent = $user instanceof \App\Models\Agent ? $user : $user->agent;
                
                // Log agent retrieval
                if (!$agent) {
                    \Log::error('Agent not found for user', [
                        'user_id' => $user->id,
                        'user_type' => get_class($user)
                    ]);
                    throw new \Exception('Agent record not found for this user');
                }
                
                if ($agent) {
                    // Log before update
                    \Log::info('Attempting to update agent joining fee status', [
                        'agent_id' => $agent->id,
                        'current_joining_fee_paid' => $agent->joining_fee_paid,
                        'current_joining_fee_paid_at' => $agent->joining_fee_paid_at,
                        'payment_id' => $request->razorpay_payment_id
                    ]);
                    
                    // Update agent's joining fee status using direct DB update to bypass any model events
                    $updated = DB::table('agents')
                        ->where('id', $agent->id)
                        ->update([
                            'joining_fee_paid' => true,
                            'joining_fee_paid_at' => now(),
                            'updated_at' => now()
                        ]);
                    
                    // Log the raw update result
                    \Log::info('Agent joining fee update result', [
                        'agent_id' => $agent->id,
                        'rows_affected' => $updated,
                        'payment_id' => $request->razorpay_payment_id
                    ]);
                    
                    // Refresh the agent model to verify the update
                    $agent = $agent->fresh();
                    
                    if ($updated === 0) {
                        throw new \Exception('No rows were updated when setting joining fee status');
                    }
                    
                    // Log the final state
                    \Log::info('Agent joining fee status after update', [
                        'agent_id' => $agent->id,
                        'joining_fee_paid' => $agent->joining_fee_paid,
                        'joining_fee_paid_at' => $agent->joining_fee_paid_at,
                        'payment_id' => $request->razorpay_payment_id,
                        'database_updated_at' => $agent->updated_at
                    ]);
                    
                    // Also update the agent's joining_fee_plan_id if not set
                    if (empty($agent->joining_fee_plan_id)) {
                        $joiningFee = \App\Models\AgentJoiningFee::where('amount', $request->amount)->first();
                        if ($joiningFee) {
                            $agent->joining_fee_plan_id = $joiningFee->id;
                            $agent->save();
                            \Log::info('Updated agent joining_fee_plan_id', [
                                'agent_id' => $agent->id,
                                'joining_fee_plan_id' => $joiningFee->id
                            ]);
                        }
                    }
                } else {
                    \Log::error('Agent not found for joining fee payment', [
                        'user_id' => $user->id,
                        'user_type' => get_class($user)
                    ]);
                }
            }
            
            // Add more cases for other purposes (e.g., plan purchase)
            return response()->json([
                'status' => 'success', 
                'message' => 'Payment verified and processed.', 
                'purpose' => $request->purpose
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Payment verification failed', [
                'error' => $e->getMessage(),
                'order_id' => $request->razorpay_order_id,
                'payment_id' => $request->razorpay_payment_id,
                'purpose' => $request->purpose
            ]);
            
            return response()->json([
                'status' => 'error', 
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 400);
        }
    }
} 