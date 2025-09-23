<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SubadminController extends Controller
{
    private function checkSubadminAccess()
    {
        if (!auth()->user()->hasAnyRole(['admin', 'push-subadmin', 'withdrawal-subadmin', 'ticket-manager'])) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    private function checkAdmin()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    public function index()
    {
        $this->checkSubadminAccess();
        $subadmins = User::role(['push-subadmin', 'withdrawal-subadmin', 'ticket-manager'])->get();
        return view('admin.subadmins.index', compact('subadmins'));
    }

    public function create()
    {
        $this->checkAdmin();
        return view('admin.subadmins.create');
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:push-subadmin,withdrawal-subadmin,ticket-manager',
        ]);

        // Debug log the input data
        \Log::debug('Creating subadmin', [
            'name' => $request->name,
            'email' => $request->email,
            'password_provided' => $request->password ? 'Yes' : 'No',
            'hashed_password' => Hash::make($request->password)
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_verified' => true,
            ]);
            
            // Debug log the created user
            \Log::debug('User created', [
                'id' => $user->id,
                'password_in_db' => $user->password ? 'Yes' : 'No'
            ]);
            
            $user->assignRole($request->role);

            return redirect()->route('admin.subadmins.index')
                ->with('success', 'Subadmin created successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Error creating subadmin: ' . $e->getMessage());
            return back()->with('error', 'Error creating subadmin: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $this->checkAdmin();
        $subadmin = User::findOrFail($id);
        if (!$subadmin->hasAnyRole(['push-subadmin', 'withdrawal-subadmin', 'ticket-manager'])) {
            abort(403, 'You do not have permission to access this page.');
        }
        $roles = Role::whereIn('name', ['push-subadmin', 'withdrawal-subadmin', 'ticket-manager'])->pluck('name', 'name');
        $currentRole = $subadmin->getRoleNames()->first();
        return view('admin.subadmins.edit', compact('subadmin', 'roles', 'currentRole'));
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $subadmin = User::findOrFail($id);
        if (!$subadmin->hasAnyRole(['push-subadmin', 'withdrawal-subadmin', 'ticket-manager'])) {
            abort(403, 'You do not have permission to access this page.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:push-subadmin,withdrawal-subadmin,ticket-manager',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $subadmin->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $subadmin->password = Hash::make($request->password);
            $subadmin->save();
        }

        // Update role
        $subadmin->syncRoles([$request->role]);

        return redirect()->route('admin.subadmins.index')->with('success', 'Subadmin updated successfully.');
    }

    public function destroy($id)
    {
        $this->checkAdmin();
        $user = User::findOrFail($id);
        if ($user->hasAnyRole(['push-subadmin', 'withdrawal-subadmin', 'ticket-manager'])) {
            $user->delete();
            return redirect()->route('admin.subadmins.index')->with('success', 'Subadmin deleted successfully.');
        }
        return redirect()->route('admin.subadmins.index')->with('error', 'Cannot delete this user.');
    }

    public function showChangePasswordForm($id)
    {
        $this->checkAdmin();
        $subadmin = User::findOrFail($id);
        if (!$subadmin->hasAnyRole(['push-subadmin', 'withdrawal-subadmin', 'ticket-manager'])) {
            abort(403, 'You do not have permission to access this page.');
        }
        return view('admin.subadmins.change-password', compact('subadmin'));
    }

    public function updatePassword(Request $request, $id)
    {
        $this->checkAdmin();
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);
        $subadmin = User::findOrFail($id);
        if (!$subadmin->hasAnyRole(['push-subadmin', 'withdrawal-subadmin', 'ticket-manager'])) {
            abort(403, 'You do not have permission to access this page.');
        }
        $subadmin->password = Hash::make($request->password);
        $subadmin->save();
        return redirect()->route('admin.subadmins.index')->with('success', 'Password updated successfully.');
    }
} 