<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PushNotification;
use App\Models\User;
use App\Models\Agent;
use Illuminate\Support\Facades\Storage;

class PushNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (!auth()->user()->hasAnyRole(['admin', 'push-subadmin'])) {
            abort(403, 'Unauthorized');
        }
        return view('admin.push_notification.index');
    }

    public function send(\Illuminate\Http\Request $request)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'push-subadmin'])) {
            abort(403, 'Unauthorized');
        }
        $rules = [
            'recipient_type' => 'required|in:user,agent,both,individual',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'image' => 'nullable|image|max:2048',
        ];
        if ($request->input('recipient_type') === 'individual') {
            $rules['individual_type'] = 'required|in:user,agent';
            if ($request->input('individual_type') === 'user') {
                $rules['individual_user_id'] = 'required|exists:users,id';
            } elseif ($request->input('individual_type') === 'agent') {
                $rules['individual_agent_id'] = 'required|exists:agents,id';
            }
        }
        $request->validate($rules);

        $title = $request->input('title');
        $message = $request->input('message');
        $recipientType = $request->input('recipient_type');
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('push_notifications', 'public');
        }
        $results = [];
        if ($recipientType === 'individual') {
            $individualType = $request->input('individual_type');
            if ($individualType === 'user') {
                $user = User::findOrFail($request->input('individual_user_id'));
                $token = $user->fcm_token;
                $response = \App\Helpers\FcmHelper::sendV1($token, $title, $message, $imagePath ? Storage::url($imagePath) : null);
                PushNotification::create([
                    'title' => $title,
                    'message' => $message,
                    'image_path' => $imagePath,
                    'recipient_type' => 'user',
                    'recipient_id' => $user->id,
                    'sent_at' => now(),
                    'status' => 'sent',
                ]);
                $results[] = $response;
            } elseif ($individualType === 'agent') {
                $agent = Agent::findOrFail($request->input('individual_agent_id'));
                $token = $agent->fcm_token;
                $response = \App\Helpers\FcmHelper::sendV1($token, $title, $message, $imagePath ? Storage::url($imagePath) : null);
                PushNotification::create([
                    'title' => $title,
                    'message' => $message,
                    'image_path' => $imagePath,
                    'recipient_type' => 'agent',
                    'recipient_id' => $agent->id,
                    'sent_at' => now(),
                    'status' => 'sent',
                ]);
                $results[] = $response;
            }
        } else {
            if ($recipientType === 'user' || $recipientType === 'both') {
                $users = User::whereNotNull('fcm_token')->get();
                foreach ($users as $user) {
                    $token = $user->fcm_token;
                    $response = \App\Helpers\FcmHelper::sendV1($token, $title, $message, $imagePath ? Storage::url($imagePath) : null);
                    PushNotification::create([
                        'title' => $title,
                        'message' => $message,
                        'image_path' => $imagePath,
                        'recipient_type' => 'user',
                        'recipient_id' => $user->id,
                        'sent_at' => now(),
                        'status' => 'sent',
                    ]);
                    $results[] = $response;
                }
            }
            if ($recipientType === 'agent' || $recipientType === 'both') {
                $agents = Agent::whereNotNull('fcm_token')->get();
                foreach ($agents as $agent) {
                    $token = $agent->fcm_token;
                    $response = \App\Helpers\FcmHelper::sendV1($token, $title, $message, $imagePath ? Storage::url($imagePath) : null);
                    PushNotification::create([
                        'title' => $title,
                        'message' => $message,
                        'image_path' => $imagePath,
                        'recipient_type' => 'agent',
                        'recipient_id' => $agent->id,
                        'sent_at' => now(),
                        'status' => 'sent',
                    ]);
                    $results[] = $response;
                }
            }
        }
        return back()->with('success', 'Push notification sent to selected recipients!');
    }

    public function searchUsers(Request $request)
    {
        $q = $request->input('q');
        $users = User::where(function($query) use ($q) {
            $query->where('name', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%")
                  ->orWhere('phone', 'like', "%$q%")
                  ->orWhere('id', $q);
        })
        ->limit(20)
        ->get(['id', 'name', 'email', 'phone']);
        $results = $users->map(function($user) {
            return [
                'id' => $user->id,
                'text' => $user->name . ' (' . $user->email . ', ' . $user->phone . ')',
            ];
        });
        return response()->json($results);
    }

    public function searchAgents(Request $request)
    {
        $q = $request->input('q');
        $agents = Agent::where(function($query) use ($q) {
            $query->where('name', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%")
                  ->orWhere('phone_number', 'like', "%$q%")
                  ->orWhere('id', $q);
        })
        ->limit(20)
        ->get(['id', 'name', 'email', 'phone_number']);
        $results = $agents->map(function($agent) {
            return [
                'id' => $agent->id,
                'text' => $agent->name . ' (' . $agent->email . ', ' . $agent->phone_number . ')',
            ];
        });
        return response()->json($results);
    }
} 