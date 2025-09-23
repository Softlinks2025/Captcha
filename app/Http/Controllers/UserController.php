<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
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

    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->checkAdmin();
        try {
            $status = $request->query('status');
            $name = $request->query('name');
            $phone = $request->query('phone');
            
            $query = User::with(['roles', 'referringAgent'])
                ->when($name, function($q) use ($name) {
                    $q->where('name', 'like', '%' . $name . '%');
                })
                ->when($phone, function($q) use ($phone) {
                    $q->where('phone', 'like', '%' . $phone . '%');
                })
                ->orderBy('created_at', 'desc');
                
            if ($status === 'active') {
                $query->whereNotNull('last_login_at')
                      ->where('last_login_at', '>=', Carbon::now()->subDays(3));
            } elseif ($status === 'inactive') {
                $query->where(function($q) {
                    $q->whereNull('last_login_at')
                      ->orWhere('last_login_at', '<', Carbon::now()->subDays(3));
                });
            }
            
            // Paginate the results - show 100 users per page
            $users = $query->paginate(100)->withQueryString();
            
            // Get counts for the dashboard cards
            $totalUsers = User::count();
            $activeUsers = User::whereNotNull('last_login_at')
                             ->where('last_login_at', '>=', Carbon::now()->subDays(3))
                             ->count();
            $inactiveUsers = User::whereNull('last_login_at')
                               ->orWhere('last_login_at', '<', Carbon::now()->subDays(3))
                               ->count();
                               
            return view('admin.users.index', compact(
                'users', 
                'totalUsers', 
                'activeUsers', 
                'inactiveUsers', 
                'status',
                'name',
                'phone'
            ));
        } catch (\Exception $e) {
            \Log::error('Error in UserController@index: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->route('admin.dashboard')
                ->with('error', 'An error occurred while loading the users. Please try again.');
        }
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->checkAdmin();
        // Only show the 'user' role for creating new users
        $userRole = Role::where('name', 'user')->first();
        $roles = $userRole ? [$userRole->id => $userRole->name] : [];
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => [
                'required',
                'string',
                'max:15',
                'unique:users',
                'regex:/^(\+91|0)?[6-9]\d{9}$/'
            ],
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'nullable|boolean',
            'email_verified' => 'nullable|boolean',
            'referral_code' => 'nullable|string|max:255',
            'upi_id' => 'required|string|max:255',
            'dob' => 'required|date',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        try {
            // Ensure phone number has +91 country code
            $phone = $validated['phone'];
            if (!str_starts_with($phone, '+91')) {
                $phone = '+91' . ltrim($phone, '0');
            }
            
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $phone,
                'upi_id' => $validated['upi_id'],
                'date_of_birth' => $validated['dob'],
                'is_verified' => true,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'email_verified_at' => $request->has('email_verified') ? now() : null,
            ];

            // Handle profile photo upload (file or URL)
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
                $userData['profile_photo_path'] = $path;
            } else if ($request->filled('profile_photo') && filter_var($request->input('profile_photo'), FILTER_VALIDATE_URL)) {
                $url = $request->input('profile_photo');
                $imageContents = @file_get_contents($url);
                if ($imageContents !== false) {
                    $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    $filename = 'profile-photos/' . uniqid('user_admin_' . time() . '_') . '.' . $extension;
                    \Storage::disk('public')->put($filename, $imageContents);
                    $userData['profile_photo_path'] = $filename;
                }
            }

            // Generate a unique referral code for the new user
            $userData['referral_code'] = $this->generateUniqueReferralCode();

            // First create the user without any roles
            $user = User::create($userData);

            // Get all roles with their current guards
            $allRoles = Role::whereIn('id', $validated['roles'])->get();
            
            if ($allRoles->isEmpty()) {
                throw new \Exception('No roles found with the provided IDs');
            }
            
            // Process each role
            foreach ($allRoles as $role) {
                try {
                    // If guard is not set or invalid, find or create a valid role
                    if (empty($role->guard_name) || !in_array($role->guard_name, ['web', 'api', 'agent'])) {
                        // Try to find an existing role with the same name and a valid guard
                        $validRole = Role::where('name', $role->name)
                            ->whereIn('guard_name', ['web', 'api', 'agent'])
                            ->first();
                            
                        if ($validRole) {
                            // Use the existing valid role
                            $user->assignRole($validRole);
                            continue;
                        } else {
                            // Create a new role with web guard if none exists
                            $validRole = Role::firstOrCreate(
                                ['name' => $role->name, 'guard_name' => 'web']
                            );
                            $user->assignRole($validRole);
                            continue;
                        }
                    }
                    
                    // If we get here, the role has a valid guard
                    $user->assignRole($role);
                    
                } catch (\Exception $e) {
                    \Log::error('Error assigning role to user: ' . $e->getMessage(), [
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                        'role_name' => $role->name,
                        'guard_name' => $role->guard_name
                    ]);
                    
                    // If there's a duplicate entry error, find the existing role and use it
                    if (str_contains($e->getMessage(), 'Duplicate entry')) {
                        $existingRole = Role::where('name', $role->name)
                            ->whereIn('guard_name', ['web', 'api', 'agent'])
                            ->first();
                            
                        if ($existingRole) {
                            $user->assignRole($existingRole);
                        }
                    }
                }
            }
            
            // Process referrer's code if provided
            if (!empty($validated['referral_code'])) {
                $this->processReferralCode($user, $validated['referral_code']);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully');
                
        } catch (\Exception $e) {
            \Log::error('Error creating user: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->withInput()
                ->with('error', 'Error creating user. Please try again.');
        }
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $this->checkAdmin();
        $user = User::with([
            'roles', 
            'walletTransactions', 
            'referredUsers',
            'subscriptionPlan'
        ])->findOrFail($id)->makeVisible(['profile_photo_url']);
        
        // Ensure the referral count is up to date
        if ($user->relationLoaded('referredUsers')) {
            $user->total_referrals = $user->referredUsers->count();
            // Save the updated referral count if it has changed
            if ($user->isDirty('total_referrals')) {
                $user->save();
            }
        }
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $this->checkAdmin();
        $user = User::findOrFail($id);
        // Only show the 'user' role for editing users
        $userRole = Role::where('name', 'user')->first();
        $roles = $userRole ? [$userRole->id => $userRole->name] : [];
        $userRoles = $user->roles->pluck('id')->toArray();
        
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => [
                'required',
                'string',
                'max:15',
                'unique:users,phone,' . $id,
                'regex:/^(\+91|0)?[6-9]\d{9}$/'
            ],
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'nullable|boolean',
            'email_verified' => 'nullable|boolean',
            'remove_photo' => 'nullable|boolean',
            'upi_id' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        try {
            // Ensure phone number has +91 country code
            $phone = $validated['phone'];
            if (!str_starts_with($phone, '+91')) {
                $phone = '+91' . ltrim($phone, '0');
            }
            
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $phone,
                'upi_id' => $validated['upi_id'],
                'date_of_birth' => $validated['date_of_birth'],
                'is_active' => $request->has('is_active') ? 1 : 0,
            ];
            
            // Update email verification status
            if ($request->has('email_verified')) {
                $updateData['email_verified_at'] = $user->email_verified_at ?? now();
            } else {
                $updateData['email_verified_at'] = null;
            }
            
           
            
            // Handle profile photo upload (file or URL)
            if ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($user->profile_photo_path) {
                    \Storage::disk('public')->delete($user->profile_photo_path);
                }
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
                $updateData['profile_photo_path'] = $path;
            } else if ($request->filled('profile_photo') && filter_var($request->input('profile_photo'), FILTER_VALIDATE_URL)) {
                if ($user->profile_photo_path) {
                    \Storage::disk('public')->delete($user->profile_photo_path);
                }
                $url = $request->input('profile_photo');
                $imageContents = @file_get_contents($url);
                if ($imageContents !== false) {
                    $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    $filename = 'profile-photos/' . uniqid('user_admin_' . time() . '_') . '.' . $extension;
                    \Storage::disk('public')->put($filename, $imageContents);
                    $updateData['profile_photo_path'] = $filename;
                }
            } elseif ($request->has('remove_photo') && $user->profile_photo_path) {
                // Remove profile photo if requested
                \Storage::disk('public')->delete($user->profile_photo_path);
                $updateData['profile_photo_path'] = null;
            }
            
            $user->update($updateData);
            
            // Sync roles
            $roles = Role::whereIn('id', $validated['roles'])->get();
            $user->syncRoles($roles);

            return redirect()->route('admin.users.show', $user->id)
                ->with('success', 'User updated successfully');
                
        } catch (\Exception $e) {
            \Log::error('Error updating user: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->withInput()
                ->with('error', 'Error updating user. Please try again.');
        }
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $this->checkAdmin();
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting own account
            if ($user->id === auth()->id()) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'You cannot delete your own account.');
            }
            
            // Delete profile photo if exists
            if ($user->profile_photo_path) {
                \Storage::disk('public')->delete($user->profile_photo_path);
            }
            
            // Detach all roles before deleting
            $user->roles()->detach();
            
            $user->delete();
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully');
                
        } catch (\Exception $e) {
            \Log::error('Error deleting user: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->route('admin.users.index')
                ->with('error', 'Error deleting user. Please try again.');
        }
    }
    
    /**
     * Generate a unique referral code
     * 
     * @return string
     */
    protected function generateUniqueReferralCode()
    {
        do {
            // Format: C2C25UR382C
            // C2C - Prefix
            // 25 - Random 2 digits
            // UR - Static text
            // 382 - Random 3 digits
            // C - Random uppercase letter
            
            $prefix = 'C2C';
            $randomDigits1 = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
            $middle = 'UR';
            $randomDigits2 = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $randomLetter = chr(rand(65, 90)); // A-Z
            
            $code = $prefix . $randomDigits1 . $middle . $randomDigits2 . $randomLetter;
            
        } while (User::where('referral_code', $code)->exists());
        
        return $code;
    }

    /**
     * Export users as CSV
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv(Request $request)
    {
        $this->checkAdmin();
        $status = $request->query('status');
        $query = User::with(['roles', 'referringAgent']);
        if ($status === 'active') {
            $query->whereNotNull('last_login_at')->where('last_login_at', '>=', Carbon::now()->subDays(3));
        } elseif ($status === 'inactive') {
            $query->where(function($q) {
                $q->whereNull('last_login_at')->orWhere('last_login_at', '<', Carbon::now()->subDays(3));
            });
        }
        $users = $query->latest()->get();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users.csv"',
        ];
        $columns = ['ID', 'Name', 'Email', 'Phone', 'Address', 'Status', 'Roles', 'Referral Code', 'Created At'];
        $callback = function() use ($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($users as $user) {
                $roles = $user->roles->pluck('name')->implode(', ');
                $status = (is_null($user->last_login_at) || \Carbon\Carbon::parse($user->last_login_at)->lt(\Carbon\Carbon::now()->subDays(3))) ? 'Inactive' : 'Active';
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->address,
                    $status,
                    $roles,
                    $user->referral_code,
                    $user->created_at,
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Process referral code and assign referrer
     *
     * @param  \App\Models\User  $user
     * @param  string  $referralCode
     * @return void
     */
    protected function processReferralCode(User $user, string $referralCode)
    {
        try {
            // Find referrer by referral code
            $referrer = User::where('referral_code', $referralCode)->first();
            
            if (!$referrer) {
                // Check if it's an agent referral code
                $agent = \App\Models\Agent::where('referral_code', $referralCode)->first();
                if ($agent && $agent->status === 'active') {
                    $user->agent_id = $agent->id;
                    $user->save();
                    
                    // Log the agent referral
                    \Log::info('Agent referral processed', [
                        'agent_id' => $agent->id,
                        'user_id' => $user->id,
                        'referral_code' => $referralCode
                    ]);
                }
                return;
            }
            
            // Prevent self-referral
            if ($referrer->id === $user->id) {
                return;
            }
            
            // Update user with referrer information
            $user->referred_by = $referrer->id;
            $user->save();
            
            // Create referral record
            $userReferral = \App\Models\UserReferral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id,
                'referral_code' => $referralCode,
                'used_at' => now(),
                'reward_credited' => false
            ]);
            
            // Check if referrer has a subscription to give rewards
            if ($referrer->subscriptionPlan) {
                $referralReward = $referrer->subscriptionPlan->referral_earnings ?? 0;
                
                if ($referralReward > 0) {
                    // Credit referrer's wallet
                    $referrer->wallet_balance += $referralReward;
                    $referrer->save();
                    
                    // Create wallet transaction
                    \App\Models\WalletTransaction::create([
                        'user_id' => $referrer->id,
                        'amount' => $referralReward,
                        'type' => 'referral_earning',
                        'description' => 'Referral reward for user #' . $user->id,
                        'reference_id' => $userReferral->id,
                        'reference_type' => get_class($userReferral)
                    ]);
                    
                    // Update referral record
                    $userReferral->update([
                        'reward_credited' => true,
                        'reward_amount' => $referralReward
                    ]);
                }
            }
            
            // Update referrer's total referrals count
            $referrer->increment('total_referrals');
            
        } catch (\Exception $e) {
            \Log::error('Error processing referral code: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'referral_code' => $referralCode,
                'error' => $e->getTraceAsString()
            ]);
        }
    }
}