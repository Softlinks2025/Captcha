<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    // List tickets for the authenticated agent
    public function index(Request $request)
    {
        $agent = Auth::guard('agent')->user();
        $tickets = Ticket::where('agent_id', $agent->id)->latest()->get();
        return response()->json(['tickets' => $tickets]);
    }

    // Create a new ticket for the agent
    public function store(Request $request)
    {
        $agent = Auth::guard('agent')->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        $ticket = new Ticket([
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'pending',
            'agent_id' => $agent->id,
        ]);
        $ticket->save();
        return response()->json(['success' => true, 'ticket' => $ticket]);
    }
} 