<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3b82f6;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --info-color: #14b8a6;
            --background-color: #f8f9fc;
            --text-color: #1e293b;
            --sidebar-bg: rgba(255, 255, 255, 0.95);
            --card-bg: rgba(255, 255, 255, 0.95);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --accent-color: #60a5fa;
            --header-bg-start: #e0e7ff;
            --header-bg-end: #f1f5f9;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: var(--sidebar-bg);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            padding: 20px;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }

        .nav-items .nav-link {
            color: var(--text-color);
            padding: 10px 15px;
            border-radius: var(--border-radius);
            margin-bottom: 10px;
            transition: background 0.3s, color 0.3s;
            display: flex;
            align-items: center;
            text-decoration: none !important;
        }

        .nav-items .nav-link:hover, .nav-items .nav-link.active {
            background: var(--primary-color);
            color: white;
        }

        .nav-items .nav-link i {
            margin-right: 10px;
        }

        .sidebar .sidebar-header {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 0;
            transition: margin-left 0.3s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Cards */
        .profile-card, .account-card, .info-card {
            max-width: 1200px;
            margin: 0 auto 20px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeIn 0.5s ease-in;
        }

        .profile-card:hover, .account-card:hover, .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            padding: 15px;
            background: linear-gradient(90deg, var(--header-bg-start), var(--header-bg-end));
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            margin: -20px -20px 20px;
        }

        .card-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-color);
        }

        .card-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .card-content p {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 0.9rem;
        }

        .card-content p strong {
            font-weight: 500;
            color: #6b7280;
        }

        .card-content p span {
            font-weight: 600;
            color: var(--text-color);
        }

        .badge {
            padding: 6px 12px;
            border-radius: var(--border-radius);
            font-size: 0.8rem;
            font-weight: 500;
            transition: transform 0.2s ease;
            display: inline-block;
        }

        .badge:hover {
            transform: scale(1.05);
        }

        .badge-success {
            background: var(--success-color);
            color: white;
        }

        .badge-warning {
            background: var(--warning-color);
            color: white;
        }

        .badge-error {
            background: var(--error-color);
            color: white;
        }

        .badge-info {
            background: var(--info-color);
            color: white;
        }

        /* Referred Users Table */
        .referred-users-section {
            max-width: 1200px;
            margin: 20px auto;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 20px;
        }

        .referred-users-section h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
        }

        .table-container {
            overflow-x: auto;
        }

        .referred-users-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .referred-users-table th,
        .referred-users-table td {
            padding: 14px;
            text-align: left;
            font-size: 0.9rem;
        }

        .referred-users-table th {
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            font-size: 0.75rem;
            background: rgba(0, 0, 0, 0.05);
        }

        .referred-users-table tbody tr {
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .referred-users-table tbody tr:hover {
            background: rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        /* Back Button */
        .btn-secondary {
            background: linear-gradient(90deg, #6b7280, #9ca3af);
            color: white;
            border-radius: var(--border-radius);
            padding: 12px 24px;
            font-weight: 500;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(107, 114, 128, 0.3);
        }

        /* Hamburger Menu */
        .hamburger {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1100;
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--text-color);
        }

        /* Settings Dropdown */
        .settings-dropdown {
            position: relative;
        }

        .settings-menu-card {
            display: none;
            position: absolute;
            left: 50%;
            top: 100%;
            transform: translateX(-50%);
            min-width: 200px;
            background: #fff;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            z-index: 2000;
            margin-top: 12px;
            padding: 12px 0;
            transition: opacity 0.2s ease, transform 0.2s ease;
            opacity: 0;
            transform: translateX(-50%) translateY(-10px);
        }

        .settings-menu-card.show {
            display: block;
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .settings-menu-card:before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 0 10px 10px 10px;
            border-style: solid;
            border-color: transparent transparent #fff transparent;
            filter: drop-shadow(0 -2px 2px rgba(0,0,0,0.08));
        }

        .settings-menu-card .settings-link,
        .settings-menu-card .settings-logout-btn {
            display: block;
            width: 100%;
            background: none;
            border: none;
            text-align: left;
            padding: 12px 24px;
            color: #333;
            font-size: 1rem;
            border-radius: 0;
            transition: background 0.2s, color 0.2s;
            text-decoration: none;
            cursor: pointer;
        }

        .settings-menu-card .settings-link:hover {
            background: #f3f4f6;
            color: var(--primary-color);
            text-decoration: underline;
        }

        .settings-menu-card .settings-logout-btn {
            color: #e3342f;
            font-weight: 500;
        }

        .settings-menu-card .settings-logout-btn:hover {
            background: #fbeaea;
            color: #b91c1c;
        }

        .settings-menu-card form {
            margin: 0;
        }

        /* Enhanced Profile Styles */
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: var(--border-radius);
            padding: 40px 30px;
            margin-bottom: 30px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            margin: 0 auto 20px;
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }

        .profile-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        .profile-role {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            position: relative;
            z-index: 2;
        }

        .stat-item {
            text-align: center;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Enhanced Card Styles */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-top: 30px;
        }

        .info-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: var(--primary-color);
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .info-value {
            font-weight: 600;
            color: var(--text-color);
            text-align: right;
            max-width: 60%;
        }

        /* Enhanced Badge Styles */
        .badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .badge-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .badge-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .badge-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .badge-info {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .hamburger {
                display: block;
            }

            .card-content {
                grid-template-columns: 1fr;
            }

            .profile-card,
            .account-card,
            .info-card,
            .referred-users-section {
                max-width: 100%;
            }

            .referred-users-table {
                min-width: 800px;
            }

            .profile-stats {
                flex-direction: column;
                gap: 15px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .profile-name {
                font-size: 2rem;
            }
        }

        @media (min-width: 769px) {
            .hamburger {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Hamburger Menu -->
        <i class="fas fa-bars hamburger"></i>
        <!-- Sidebar -->
        <nav class="sidebar">
            <img src="{{ asset('images/logo c2c 2.png') }}" alt="Logo" style="max-width: 160px; margin: 0 auto 10px; display: block;">
            <div class="nav-items">
                <a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>
                <a href="{{ route('admin.agents.index') }}" class="nav-link">Agents</a>
                <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
                <a href="{{ route('admin.subscription_plans.index') }}" class="nav-link">Subscription Plans</a>
                <a href="{{ route('admin.agent-plans.index') }}" class="nav-link">Agent Plans</a>
                <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link">Withdrawal Requests</a>
                <a href="{{ route('admin.push-notification.index') }}" class="nav-link">Push Notification</a>
                <a href="{{ route('admin.tickets.index') }}" class="nav-link">Tickets</a>
<!--                <a href="{{ route('admin.agent-plan-purchase-requests.index') }}" class="nav-link">Agent Plan Purchase Requests</a>
-->                <a href="{{ route('admin.subadmins.index') }}" class="nav-link">Sub-Admin</a>
                <div class="settings-dropdown">
                    <a href="#" class="nav-link" id="settings-toggle">Settings</a>
                    <div id="settings-menu-agent-plans" class="settings-menu-card">
                        <a href="{{ route('admin.change-password') }}" class="d-block settings-link">Change Password</a>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
                            @csrf
                            <button type="submit" class="settings-logout-btn">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <section class="container mx-auto">
                <!-- Profile Info Card -->
                <div class="profile-card">
                    <div class="card-header">
                        <h2>
                            <i class="fas fa-user-circle"></i> Agent Profile
                        </h2>
                    </div>
                    <div class="profile-header">
                        @if($agent->profile_image)
                            <img src="{{ asset('storage/' . $agent->profile_image) }}" alt="Profile Image" class="profile-avatar">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($agent->name) }}&size=120&background=3b82f6&color=fff" alt="Default Avatar" class="profile-avatar">
                        @endif
                        <div class="profile-name">{{ $agent->name }}</div>
                        <div class="profile-role">{{ $agent->role ?? 'Agent' }}</div>
                        <div class="profile-stats">
                            <div class="stat-item">
                                <div class="stat-number">{{ $agent->referredUsers()->count() }}</div>
                                <div class="stat-label">Total Referrals</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">₹{{ number_format($agent->total_earnings ?? 0, 2) }}</div>
                                <div class="stat-label">Total Earnings</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">₹{{ number_format($agent->wallet_balance ?? 0, 2) }}</div>
                                <div class="stat-label">Wallet Balance</div>
                            </div>
                        </div>
                    </div>
                    <div class="info-grid">
                        <!-- Personal Information Card -->
                        <div class="info-card">
                            <div class="card-title">
                                <i class="fas fa-user"></i>
                                Personal Information
                            </div>
                            <div class="info-item">
                                <span class="info-label">Phone Number</span>
                                <span class="info-value">{{ $agent->phone_number }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <span class="info-value">{{ $agent->email ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Referral Code</span>
                                <span class="info-value">{{ $agent->referral_code }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status</span>
                                <span class="badge {{ $agent->status === 'active' ? 'badge-success' : 'badge-error' }}">
                                    {{ ucfirst($agent->status ?? 'inactive') }}
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Profile Completed</span>
                                <span class="badge {{ $agent->profile_completed ? 'badge-success' : 'badge-warning' }}">
                                    {{ $agent->profile_completed ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>

                        <!-- Plan Information Card -->
                        <div class="info-card">
                            <div class="card-title">
                                <i class="fas fa-crown"></i>
                                Plan Information
                            </div>
                            @php
                                $activePlanSubscription = $agent->activePlanSubscription()->latest('started_at')->first();
                            @endphp
                            <div class="info-item">
                                <span class="info-label">Active Plan</span>
                                <span class="info-value">
                                    @if($activePlanSubscription && $activePlanSubscription->plan)
                                        {{ $activePlanSubscription->plan->name }}
                                    @else
                                        <span class="text-gray-500">No active plan</span>
                                    @endif
                                </span>
                            </div>
                            @if($activePlanSubscription && $activePlanSubscription->started_at)
                            <div class="info-item">
                                <span class="info-label">Started Date</span>
                                <span class="info-value">{{ $activePlanSubscription->started_at->format('M d, Y') }}</span>
                            </div>
                            @endif
                            @if($activePlanSubscription && $activePlanSubscription->amount_paid)
                            <div class="info-item">
                                <span class="info-label">Amount Paid</span>
                                <span class="info-value">₹{{ number_format($activePlanSubscription->amount_paid, 2) }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Banking Information Card -->
                        <div class="info-card">
                            <div class="card-title">
                                <i class="fas fa-university"></i>
                                Banking Information
                            </div>
                            <div class="info-item">
                                <span class="info-label">Bank Account</span>
                                <span class="info-value">{{ $agent->bank_account_number ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">IFSC Code</span>
                                <span class="info-value">{{ $agent->ifsc_code ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Account Holder</span>
                                <span class="info-value">{{ $agent->account_holder_name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">PAN Number</span>
                                <span class="info-value">{{ $agent->pan_number ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <!-- Address Information Card -->
                        <div class="info-card">
                            <div class="card-title">
                                <i class="fas fa-map-marker-alt"></i>
                                Address Information
                            </div>
                            <div class="info-item">
                                <span class="info-label">Address</span>
                                <span class="info-value">
                                    @php
                                        $addressParts = array_filter([
                                            $agent->address ?? null,
                                            $agent->city ?? null,
                                            $agent->state ?? null,
                                            $agent->pincode ?? null
                                        ]);
                                        echo $addressParts ? e(implode(', ', $addressParts)) : 'N/A';
                                    @endphp
                                </span>
                            </div>
                        </div>

                        <!-- Contact Information Card -->
                        <div class="info-card">
                            <div class="card-title">
                                <i class="fas fa-phone"></i>
                                Contact Information
                            </div>
                            <div class="info-item">
                                <span class="info-label">Phone Verified</span>
                                <span class="badge {{ $agent->phone_verified_at ? 'badge-success' : 'badge-warning' }}">
                                    {{ $agent->phone_verified_at ? 'Yes' : 'No' }}
                                </span>
                            </div>
                            @if($agent->phone_verified_at)
                            <div class="info-item">
                                <span class="info-label">Verified At</span>
                                <span class="info-value">{{ $agent->phone_verified_at->format('M d, Y') }}</span>
                            </div>
                            @endif
                            <div class="info-item">
                                <span class="info-label">Date of Birth</span>
                                <span class="info-value">{{ $agent->date_of_birth ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Created At</span>
                                <span class="info-value">{{ $agent->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i>
                            Edit Agent
                        </a>
                        <a href="{{ route('admin.agents.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Agents
                        </a>
                    </div>
                </div>

                <!-- Account Info Card -->
                <div class="account-card">
                    <div class="card-header">
                        <h2>
                            <i class="fas fa-wallet"></i> Account Information
                        </h2>
                    </div>
                    <div class="card-content">
                        <p>
                            <strong>Wallet Balance:</strong>
                            <span class="badge badge-success">₹{{ number_format($agent->wallet_balance ?? 0, 2) }}</span>
                        </p>
                        <p>
                            <strong>Total Referred Users:</strong>
                            <span class="badge badge-info">{{ $agent->referredUsers->count() }}</span>
                        </p>
                        <p>
                            <strong>Total Earnings:</strong>
                            <span>₹{{ number_format($agent->total_earnings ?? 0, 2) }}</span>
                        </p>
                        <p>
                            <strong>Total Withdrawals:</strong>
                            <span>₹{{ number_format($agent->total_withdrawals ?? 0, 2) }}</span>
                        </p>
                        <p>
                            <strong>Last Login At:</strong>
                            <span>{{ optional($agent->last_login_at)->format('d M Y, H:i') ?? 'Never' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Milestone Bonuses Card -->
                <div class="account-card">
                    <div class="card-header">
                        <h2>
                            <i class="fas fa-gift"></i> Referral Milestone Bonuses
                        </h2>
                    </div>
                    <div class="card-content">
                        <p>
                            <strong>10 Referrals:</strong>
                            @if($agent->milestone_10_reached)
                                <span class="badge badge-success">Reached</span>
                                <span class="badge badge-info">Earnings Cap: ₹{{ $agent->earnings_cap ?? 'N/A' }}</span>
                            @else
                                <span class="badge badge-warning">Not Reached</span>
                            @endif
                        </p>
                        <p>
                            <strong>50 Referrals (T-shirt Bonus):</strong>
                            @if($agent->milestone_50_reached)
                                <span class="badge badge-success">Reached</span>
                                @if($agent->bonus_tshirt_claimed)
                                    <span class="badge badge-info">Claimed</span>
                                @else
                                    <span class="badge badge-warning">Not Claimed</span>
                                @endif
                            @else
                                <span class="badge badge-warning">Not Reached</span>
                            @endif
                        </p>
                        <p>
                            <strong>100 Referrals (Bag Bonus):</strong>
                            @if($agent->milestone_100_reached)
                                <span class="badge badge-success">Reached</span>
                                @if($agent->bonus_bag_claimed)
                                    <span class="badge badge-info">Claimed</span>
                                @else
                                    <span class="badge badge-warning">Not Claimed</span>
                                @endif
                            @else
                                <span class="badge badge-warning">Not Reached</span>
                            @endif
                        </p>
                        <p>
                            <strong>Total Referrals:</strong>
                            <span class="badge badge-info">{{ $agent->total_referrals }}</span>
                        </p>
                    </div>
                </div>

                <!-- Referred Users Section -->
                <div class="referred-users-section">
                    <h3>Referred Users</h3>
                    @if($agent->referredUsers->count() > 0)
                        <div class="table-container">
                            <table class="referred-users-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Profile Completed</th>
                                        <th>Joined Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($agent->referredUsers as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->name ?? 'N/A' }}</td>
                                            <td>{{ $user->email ?? 'N/A' }}</td>
                                            <td>{{ $user->phone ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge {{ $user->profile_completed ? 'badge-success' : 'badge-warning' }}">
                                                    {{ $user->profile_completed ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                            <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 italic">No users have been referred by this agent yet.</p>
                    @endif
                </div>
                
                <!-- Add extra space between referred users and transaction history -->
                <div style="height: 40px;"></div>
                
                <!-- Transaction History Card -->
                <div class="info-card mt-4">
                    <div class="card-header">
                        <h2>Transaction History</h2>
                    </div>
                    <div class="card-body" style="background: #f8f9fc; border-radius: 0 0 12px 12px; padding: 20px;">
                        <div class="table-container">
                            <table class="referred-users-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($agent->walletTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->id }}</td>
                                        <td>{{ $transaction->type }}</td>
                                        <td>₹{{ number_format($transaction->amount, 2) }}</td>
                                        <td>{{ ucfirst($transaction->status) }}</td>
                                        <td>{{ $transaction->created_at }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-500">No transactions found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Withdrawal History Card -->
                <div class="info-card mt-4">
                    <div class="card-header">
                        <h2>Withdrawal History</h2>
                    </div>
                    <div class="card-body" style="background: #f8f9fc; border-radius: 0 0 12px 12px; padding: 20px;">
                        <div class="table-container">
                            <table class="referred-users-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $withdrawals = $agent->withdrawalRequests()->orderByDesc('created_at')->get(); @endphp
                                    @forelse($withdrawals as $withdrawal)
                                    <tr>
                                        <td>{{ $withdrawal->id }}</td>
                                        <td>₹{{ number_format($withdrawal->amount, 2) }}</td>
                                        <td>{{ ucfirst($withdrawal->status) }}</td>
                                        <td>{{ $withdrawal->created_at }}</td>
                                        <td>{{ $withdrawal->remarks ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-gray-500">No withdrawal history found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-6 text-center">
                    <a href="{{ route('admin.agents.index') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Agents List
                    </a>
                </div>
            </section>
        </div>
    </div>

    <script>
        // Hamburger menu toggle
        document.querySelector('.hamburger').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Settings dropdown toggle
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('settings-toggle');
            const menu = document.getElementById('settings-menu-agent-plans');
            
            if (toggle && menu) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    menu.classList.toggle('show');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!menu.contains(e.target) && e.target !== toggle) {
                        menu.classList.remove('show');
                    }
                });

                // Close dropdown when clicking a link or button inside
                menu.querySelectorAll('a, button').forEach(item => {
                    item.addEventListener('click', () => {
                        menu.classList.remove('show');
                    });
                });
            }
        });
    </script>
</body>
</html>