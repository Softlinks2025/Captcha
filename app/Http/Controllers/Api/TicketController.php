<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    // List tickets for the authenticated user/agent
    public function index(Request $request)
    {
       
        $user = Auth::user();
        $tickets = Ticket::query();
        if ($user && $user instanceof \App\Models\User) {
            $tickets->where('user_id', $user->id);
        } elseif ($user && $user instanceof \App\Models\Agent) {
            $tickets->where('agent_id', $user->id);
        }
        return response()->json(['tickets' => $tickets->latest()->get()]);
    }

    // Raise a new ticket
    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        $ticket = new Ticket([
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'pending',
        ]);
        if ($user && $user instanceof \App\Models\User) {
            $ticket->user_id = $user->id;
        } elseif ($user && $user instanceof \App\Models\Agent) {
            $ticket->agent_id = $user->id;
        }
        $ticket->save();
        return response()->json(['success' => true, 'ticket' => $ticket]);
    }
} 