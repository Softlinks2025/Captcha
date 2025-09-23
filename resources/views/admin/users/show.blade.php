<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details: {{ $user->name }}</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --error-color: #dc3545;
            --background-color: #f8f9fc;
            --card-bg: rgba(255, 255, 255, 0.1);
            --text-color: #333;
            --sidebar-bg: rgba(255, 255, 255, 0.95);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
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
            font-size: 1rem;
            font-weight: 500;
        }

        .nav-items .nav-link:hover, .nav-items .nav-link.active {
            background: var(--primary-color);
            color: white;
        }

        .sidebar .sidebar-header {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        .settings-dropdown {
            position: relative;
        }

        .settings-menu-card {
            display: none;
            position: absolute;
            left: 50%;
            top: 100%;
            transform: translateX(-50%);
            min-width: 180px;
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
            color: var(--error-color);
            font-weight: 500;
        }

        .settings-menu-card .settings-logout-btn:hover {
            background: #fbeaea;
            color: #b91c1c;
        }

        .settings-menu-card form {
            margin: 0;
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

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-in;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Cards */
        .info-card, .stats-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeIn 0.5s ease-in;
            margin-bottom: 20px;
        }

        .info-card:hover, .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
            background: linear-gradient(135deg, #10b981, #059669) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
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

        /* Milestone Cards */
        .milestone-card {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            padding: 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .milestone-card:hover {
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Progress Bars */
        .progress {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            transition: width 0.6s ease;
        }

        .progress-bar.bg-success {
            background: var(--success-color) !important;
        }

        .progress-bar.bg-warning {
            background: var(--warning-color) !important;
        }

        /* Table Styles */
        .table-sm th,
        .table-sm td {
            padding: 8px 12px;
            font-size: 0.85rem;
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        /* Bonus Section Icons */
        .fa-gift, .fa-trophy, .fa-history {
            color: var(--primary-color);
        }

        /* Responsive Design */
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

        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .grid {
                grid-template-columns: 1fr !important;
            }

            .milestone-card {
                padding: 12px;
            }

            .table-responsive {
                font-size: 0.8rem;
            }

            .table-sm th,
            .table-sm td {
                padding: 6px 8px;
            }

            .info-label {
                width: 100px;
                font-size: 0.8rem;
            }

            .info-value {
                font-size: 0.8rem;
            }

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

            .info-card, .stats-card {
                max-width: 100%;
            }

            .card-footer {
                flex-direction: column;
                align-items: flex-end;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .info-label {
                width: auto;
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

        /* Stats Card */
        .stats-card .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .stats-card .profile-photo:hover {
            transform: scale(1.05);
        }

        .stats-card h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .stats-card .email {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .stats-row {
            margin-bottom: 15px;
        }

        .stats-label {
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .stats-value {
            font-size: 0.9rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stats-value .fas {
            font-size: 1rem;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            color: white;
            border-radius: var(--border-radius);
            padding: 12px 24px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }

        .btn-danger {
            background: linear-gradient(90deg, var(--error-color), #f87171);
            color: white;
            border-radius: var(--border-radius);
            padding: 12px 24px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
        }

        .btn-info {
            background: linear-gradient(90deg, var(--info-color), #60a5fa);
            color: white;
            border-radius: var(--border-radius);
            padding: 12px 24px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(20, 184, 166, 0.3);
        }

        /* Section Spacing */
        .section-spacing {
            margin-top: 40px;
        }

        /* Enhanced Button Styles - Override Bootstrap */
        .action-buttons .btn.btn-primary {
            background: linear-gradient(135deg, #10b981, #059669) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
        }

        .action-buttons .btn.btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4) !important;
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
                <a href="{{ route('admin.users.index') }}" class="nav-link active">Users</a>
                <a href="{{ route('admin.subscription_plans.index') }}" class="nav-link">Subscription Plans</a>
                <a href="{{ route('admin.agent-plans.index') }}" class="nav-link">Agent Plans</a>
                <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link">Withdrawal Requests</a>
                <a href="{{ route('admin.push-notification.index') }}" class="nav-link">Push Notification</a>
                <a href="{{ route('admin.tickets.index') }}" class="nav-link">Tickets</a>
<!--                <a href="{{ route('admin.agent-plan-purchase-requests.index') }}" class="nav-link">Agent Plan Purchase Requests</a>
-->                <a href="{{ route('admin.subadmins.index') }}" class="nav-link">Sub-Admin</a>
                <div class="settings-dropdown">
                    <a href="#" class="nav-link" id="settings-toggle">Settings</a>
                    <div id="settings-menu-user-details" class="settings-menu-card">
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
                <!-- Header -->
                <div class="header">
                    <h1>User Details: {{ $user->name }}</h1>
                    <a href="{{ route('admin.users.index') }}" class="btn-info">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Users
                    </a>
                </div>

                <!-- Enhanced Profile Header -->
                <div class="profile-header">
                    @if($user->profile_photo_path)
                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Profile Image" class="profile-avatar">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=140&background=4e73df&color=fff" alt="Default Avatar" class="profile-avatar">
                    @endif
                    <div class="profile-name">{{ $user->name }}</div>
                    <div class="profile-role">{{ $user->roles->first()->name ?? 'User' }}</div>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-number">{{ \App\Models\CaptchaSolve::where('user_id', $user->id)->count() }}</div>
                            <div class="stat-label">Captcha Solves</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">₹{{ number_format($user->wallet_balance ?? 0, 2) }}</div>
                            <div class="stat-label">Wallet Balance</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ $user->referredUsers()->count() }}</div>
                            <div class="stat-label">Total Referrals</div>
                        </div>
                    </div>
                </div>

                <!-- Information Grid -->
                <div class="info-grid">
                    <!-- Personal Information Card -->
                    <div class="info-card">
                        <div class="card-title">
                            <i class="fas fa-user"></i>
                            Personal Information
                        </div>
                        <div class="info-item">
                            <span class="info-label">ID</span>
                            <span class="info-value">{{ $user->id }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value">{{ $user->email }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone</span>
                            <span class="info-value">{{ $user->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status</span>
                            <span class="badge {{ $user->is_verified ? 'badge-success' : 'badge-warning' }}">
                                {{ $user->is_verified ? 'Verified' : 'Pending Verification' }}
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Created At</span>
                            <span class="info-value">{{ $user->created_at->format('M d, Y H:i:s') }}</span>
                        </div>
                    </div>

                    <!-- Account Information Card -->
                    <div class="info-card">
                        <div class="card-title">
                            <i class="fas fa-crown"></i>
                            Account Information
                        </div>
                        <div class="info-item">
                            <span class="info-label">Level</span>
                            <span class="info-value">{{ $user->level ?? (\App\Models\CaptchaSolve::where('user_id', $user->id)->count()) }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Purchased Plan</span>
                            <span class="info-value">
                                @if($user->subscription_name)
                                    {{ $user->subscription_name }}
                                @else
                                    <span class="text-gray-500">No plan purchased</span>
                                @endif
                            </span>
                        </div>
                        @if($user->purchased_date)
                        <div class="info-item">
                            <span class="info-label">Purchase Date</span>
                            <span class="info-value">{{ $user->purchased_date->format('M d, Y') }}</span>
                        </div>
                        @endif
                        @if($user->total_amount_paid)
                        <div class="info-item">
                            <span class="info-label">Amount Paid</span>
                            <span class="info-value">₹{{ number_format($user->total_amount_paid, 2) }}</span>
                        </div>
                        @endif
                        <div class="info-item">
                            <span class="info-label">Last Login</span>
                            <span class="info-value">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never logged in' }}</span>
                        </div>
                    </div>

                    <!-- Banking Information Card -->
                    <div class="info-card">
                        <div class="card-title">
                            <i class="fas fa-university"></i>
                            Banking Information
                        </div>
                        <div class="info-item">
                            <span class="info-label">Bank Account</span>
                            <span class="info-value">{{ $user->bank_account_number ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">IFSC Code</span>
                            <span class="info-value">{{ $user->ifsc_code ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Account Holder</span>
                            <span class="info-value">{{ $user->account_holder_name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">PAN Number</span>
                            <span class="info-value">{{ $user->pan_number ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">UPI ID</span>
                            <span class="info-value">{{ $user->upi_id ?? 'N/A' }}</span>
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
                                        $user->address ?? null,
                                        $user->city ?? null,
                                        $user->state ?? null,
                                        $user->pincode ?? null
                                    ]);
                                    echo $addressParts ? e(implode(', ', $addressParts)) : 'N/A';
                                @endphp
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Referral Code</span>
                            <span class="info-value">{{ $user->referral_code ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Additional Contact</span>
                            <span class="info-value">{{ $user->additional_contact_number ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit User
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Users
                    </a>
                </div>

                <!-- Bonus & Referral Information Card -->
                <div class="info-card lg:col-span-2 section-spacing">
                    <div class="card-header">
                        <h2><i class="fas fa-gift mr-2"></i> Bonus & Referral Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @php
                                // Calculate referral statistics
                                $totalReferrals = $user->referredUsers()->count();
                                $referralEarnings = $user->walletTransactions()
                                    ->where('type', 'referral_earning')
                                    ->sum('amount');
                                $bonusEarnings = $user->walletTransactions()
                                    ->whereIn('type', ['bonus_10_referrals', 'bonus_20_referrals', 'daily_limit_bonus'])
                                    ->sum('amount');
                                $totalBonusEarnings = $referralEarnings + $bonusEarnings;
                                
                                // Get user's subscription plan for bonus rates
                                $subscriptionPlan = $user->subscriptionPlan;
                                
                                // Calculate milestone progress
                                $milestone10Progress = min(100, ($totalReferrals / 10) * 100);
                                $milestone20Progress = min(100, ($totalReferrals / 20) * 100);
                            @endphp
                            
                            <div class="col-md-6">
                                <div class="info-label">Total Referrals:</div>
                                <div class="info-value">
                                    <span class="badge badge-primary">{{ $totalReferrals }}</span>
                                    @if($subscriptionPlan)
                                        <br><small class="text-muted">Earning: ₹{{ number_format($subscriptionPlan->referral_earning_per_ref, 2) }} per referral</small>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-label">Total Referral Earnings:</div>
                                <div class="info-value">₹{{ number_format($referralEarnings, 2) }}</div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-label">Total Bonus Earnings:</div>
                                <div class="info-value">₹{{ number_format($bonusEarnings, 2) }}</div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-label">Total Bonus & Referral:</div>
                                <div class="info-value">
                                    <strong>₹{{ number_format($totalBonusEarnings, 2) }}</strong>
                                </div>
                            </div>
                            
                            @if($subscriptionPlan)
                            <div class="col-md-12">
                                <hr class="my-3">
                                <h5 class="mb-3"><i class="fas fa-trophy mr-2"></i> Referral Milestones</h5>
                                
                                <!-- 10 Referrals Milestone -->
                                <div class="milestone-card mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong>10 Referrals Milestone</strong>
                                            <br><small class="text-muted">Bonus: ₹{{ number_format($subscriptionPlan->bonus_10_referrals, 2) }} + {{ $subscriptionPlan->gift_10_referrals }}</small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge {{ $totalReferrals >= 10 ? 'badge-success' : 'badge-warning' }}">
                                                {{ $totalReferrals >= 10 ? 'Achieved' : 'Pending' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $totalReferrals >= 10 ? 'bg-success' : 'bg-warning' }}" 
                                             style="width: {{ $milestone10Progress }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $totalReferrals }}/10 referrals</small>
                                </div>
                                
                                <!-- 20 Referrals Milestone -->
                                <div class="milestone-card">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong>20 Referrals Milestone</strong>
                                            <br><small class="text-muted">Bonus: ₹{{ number_format($subscriptionPlan->bonus_20_referrals, 2) }} + {{ $subscriptionPlan->gift_20_referrals }}</small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge {{ $totalReferrals >= 20 ? 'badge-success' : 'badge-warning' }}">
                                                {{ $totalReferrals >= 20 ? 'Achieved' : 'Pending' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $totalReferrals >= 20 ? 'bg-success' : 'bg-warning' }}" 
                                             style="width: {{ $milestone20Progress }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $totalReferrals }}/20 referrals</small>
                                </div>
                                
                                @if($subscriptionPlan->daily_limit_bonus > 0)
                                <div class="col-md-12 mt-3">
                                    <div class="info-label">Daily Limit Bonus:</div>
                                    <div class="info-value">₹{{ number_format($subscriptionPlan->daily_limit_bonus, 2) }} per day when limit reached</div>
                                </div>
                                @endif
                            </div>
                            @endif
                            
                            <div class="col-md-12">
                                <hr class="my-3">
                                <h5 class="mb-3"><i class="fas fa-history mr-2"></i> Recent Bonus Transactions</h5>
                                @php
                                    $recentBonusTransactions = $user->walletTransactions()
                                        ->whereIn('type', ['referral_earning', 'bonus_10_referrals', 'bonus_20_referrals', 'daily_limit_bonus'])
                                        ->latest()
                                        ->take(5)
                                        ->get();
                                @endphp
                                
                                @if($recentBonusTransactions->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Amount</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentBonusTransactions as $transaction)
                                                    <tr>
                                                        <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                                        <td>
                                                            @switch($transaction->type)
                                                                @case('referral_earning')
                                                                    <span class="badge badge-info">Referral</span>
                                                                    @break
                                                                @case('bonus_10_referrals')
                                                                    <span class="badge badge-success">10 Ref Bonus</span>
                                                                    @break
                                                                @case('bonus_20_referrals')
                                                                    <span class="badge badge-success">20 Ref Bonus</span>
                                                                    @break
                                                                @case('daily_limit_bonus')
                                                                    <span class="badge badge-warning">Daily Bonus</span>
                                                                    @break
                                                                @default
                                                                    <span class="badge badge-secondary">{{ ucfirst($transaction->type) }}</span>
                                                            @endswitch
                                                        </td>
                                                        <td>₹{{ number_format($transaction->amount, 2) }}</td>
                                                        <td>{{ $transaction->description ?? 'N/A' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted text-center">No bonus transactions found.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction History Card -->
                <div class="info-card mt-4">
                    <div class="card-header">
                        <h2>Transaction History</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($user->walletTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->id }}</td>
                                        <td>{{ $transaction->type }}</td>
                                        <td>{{ $transaction->amount }}</td>
                                        <td>{{ $transaction->status }}</td>
                                        <td>{{ $transaction->created_at }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No transactions found.</td>
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
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $withdrawals = \App\Models\WithdrawalRequest::where('user_id', $user->id)->orderByDesc('created_at')->get(); @endphp
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
                                        <td colspan="5" class="text-center text-muted">No withdrawal history found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        // Hamburger menu and settings dropdown toggle
        document.addEventListener('DOMContentLoaded', function() {
            // Hamburger menu toggle
            document.querySelector('.hamburger').addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
            });

            // Settings dropdown toggle
            const toggle = document.getElementById('settings-toggle');
            const menu = document.getElementById('settings-menu-user-details');
            
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