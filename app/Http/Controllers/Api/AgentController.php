<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Razorpay\Api\Api;

class AgentController extends Controller
{
    public function __construct()
    {
        // Remove admin-only middleware so agent APIs are accessible to agents
    }

    public function index()
    {
        return Agent::paginate(10);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|string',
            'referral_code' => 'required|string|unique:agents',
        ]);

        $agent = Agent::create($request->only(['name', 'phone_number', 'referral_code']));
        return response()->json($agent, 201);
    }
}