<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
            margin-left: 0;
            transition: margin-left 0.3s ease-in-out;
            z-index: 1000;
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
            padding: 0;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 0 30px 30px 30px;
            transition: margin-left 0.3s ease-in-out;
        }

        /* Cards */
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            min-height: 160px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .card-body {
            padding: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .card-text.display-4 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0;
            line-height: 1.2;
        }

        /* Ensure equal heights for cards in the same row */
        .row {
            display: flex;
            flex-wrap: wrap;
        }

        .row > [class*="col-"] {
            display: flex;
            flex-direction: column;
        }

        .row > [class*="col-"] > .card,
        .row > [class*="col-"] > a > .card {
            flex: 1;
        }



        /* Tables */
        .table {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .table th, .table td {
            padding: 15px;
            vertical-align: middle;
        }

        .badge {
            padding: 8px 12px;
            border-radius: var(--border-radius);
            font-weight: 500;
        }

        /* Buttons */
        .btn-outline-primary {
            border-radius: var(--border-radius);
            padding: 8px 20px;
            transition: background 0.3s, color 0.3s;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
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
            background: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
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

            .card {
                min-height: 140px;
            }
            
            .card-title {
                font-size: 1.1rem;
                margin-bottom: 0.75rem;
            }
            
            .card-text.display-4 {
                font-size: 2rem;
            }
            
            .card-body {
                padding: 15px;
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
                @php $user = auth()->user(); @endphp
                @if($user->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="nav-link active">Dashboard</a>
                    <a href="{{ route('admin.agents.index') }}" class="nav-link">Agents</a>
                    <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
                    <a href="{{ route('admin.subscription_plans.index') }}" class="nav-link">Subscription Plans</a>
                    <a href="{{ route('admin.agent-plans.index') }}" class="nav-link">Agent Plans</a>
                    <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link">Withdrawal Requests</a>
                    <a href="{{ route('admin.push-notification.index') }}" class="nav-link">Push Notification</a>
                    <a href="{{ route('admin.tickets.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">Tickets</a>
                    <a href="{{ route('admin.subadmins.index') }}" class="nav-link">Sub-Admin</a>
                @elseif($user->hasRole('push-subadmin'))
                    <a href="{{ route('admin.push-notification.index') }}" class="nav-link">Push Notification</a>
                @elseif($user->hasRole('withdrawal-subadmin'))
                    <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link">Withdrawal Requests</a>
                @elseif($user->hasRole('ticket-manager'))
                    <a href="{{ route('admin.tickets.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">Tickets</a>
                @endif
                <div class="settings-dropdown">
                    <a href="#" class="nav-link" id="settings-toggle">Settings</a>
                    <div id="settings-menu-dashboard" class="settings-menu-card">
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
            @php $user = auth()->user(); @endphp
            @if($user->hasRole('push-subadmin'))
                <div class="text-center mt-10">
                    <h2 style="font-size:2rem; color:#ef4444;">403 Forbidden</h2>
                    <p style="color:#6b7280;">You do not have access to the dashboard. Please use the Push Notification menu.</p>
                </div>
            @elseif($user->hasRole('withdrawal-subadmin'))
                <div class="text-center mt-10">
                    <h2 style="font-size:2rem; color:#ef4444;">403 Forbidden</h2>
                    <p style="color:#6b7280;">You do not have access to the dashboard. Please use the Withdrawal Requests menu.</p>
                </div>
            @elseif($user->hasRole('ticket-manager'))
                <div class="text-center mt-10">
                    <h2 style="font-size:2rem; color:#ef4444;">403 Forbidden</h2>
                    <p style="color:#6b7280;">You do not have access to the dashboard. Please use the Tickets menu.</p>
                </div>
            @else
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom" style="margin-top:0;">
                    <h1 class="h2" style="margin-top:0;">Dashboard</h1>
                </div>

                <!-- Revenue Card with Date Filter -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-end mb-3">
                                    <div class="col-auto">
                                        <label for="start_date" class="form-label mb-0">From</label>
                                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $start }}">
                                    </div>
                                    <div class="col-auto">
                                        <label for="end_date" class="form-label mb-0">To</label>
                                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $end }}">
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-outline-primary">Filter</button>
                                    </div>
                                </form>
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <div class="card bg-success text-white h-100">
                                            <div class="card-body d-flex flex-column justify-content-center">
                                                <h5 class="card-title">Total Revenue</h5>
                                                <p class="display-4 mb-0">₹{{ number_format($totalRevenue ?? 0, 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="card bg-primary text-white h-100">
                                            <div class="card-body d-flex flex-column justify-content-center">
                                                <h5 class="card-title">Agent Revenue</h5>
                                                <p class="display-4 mb-0">₹{{ number_format($agentRevenue ?? 0, 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="card bg-info text-white h-100">
                                            <div class="card-body d-flex flex-column justify-content-center">
                                                <h5 class="card-title">User Revenue</h5>
                                                <p class="display-4 mb-0">₹{{ number_format($userRevenue ?? 0, 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Agent Analytics Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="fas fa-users mr-2"></i>Agent Analytics
                                </h5>
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <a href="{{ route('admin.agents.index') }}" style="text-decoration:none;">
                                            <div class="card text-white bg-success h-100">
                                                <div class="card-body d-flex flex-column justify-content-center">
                                                    <h5 class="card-title">Total Agents</h5>
                                                    <p class="card-text display-4 mb-0">{{ $totalAgents ?? 0 }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="card text-white bg-primary h-100">
                                            <div class="card-body d-flex flex-column justify-content-center">
                                                <h5 class="card-title">Agents with Paid Joining fees</h5>
                                                <p class="card-text display-4 mb-0">{{ $totalAgentsPaid ?? 0 }}</p>
                                            </div>
                                        </div>
                                    </div>
                                   <!-- <div class="col-md-4 mb-4">
                                        <div class="card text-white bg-primary h-100">
                                            <div class="card-body d-flex flex-column justify-content-center">
                                                <h5 class="card-title">Total Agent Subscriptions</h5>
                                                <p class="card-text display-4 mb-0">{{ $totalAgentSubscriptions ?? 0 }}</p>
                                            </div>
                                        </div>
                                    </div> -->
                                    <div class="col-md-4 mb-4">
                                        <a href="{{ route('admin.withdrawal-requests.index') }}" style="text-decoration:none;">
                                            <div class="card text-white h-100" style="background-color: #36b9cc;">
                                                <div class="card-body d-flex flex-column justify-content-center">
                                                    <h5 class="card-title">Total Agent Withdrawal Requests</h5>
                                                    <p class="card-text display-4 mb-0">{{ $agentWithdrawalRequestCount ?? 0 }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Analytics Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="fas fa-user mr-2"></i>User Analytics
                                </h5>
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <a href="{{ route('admin.users.index') }}" style="text-decoration:none;">
                                            <div class="card text-white bg-success h-100">
                                                <div class="card-body d-flex flex-column justify-content-center">
                                                    <h5 class="card-title">Total Users</h5>
                                                    <p class="card-text display-4 mb-0">{{ $totalUsers ?? 0 }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="card text-white bg-primary h-100">
                                            <div class="card-body d-flex flex-column justify-content-center">
                                                <h5 class="card-title">Total Users Subscribed</h5>
                                                <p class="card-text display-4 mb-0">{{ $totalUserSubscribed ?? 0 }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <a href="{{ route('admin.withdrawal-requests.index') }}" style="text-decoration:none;">
                                            <div class="card text-white h-100" style="background-color: #36b9cc;">
                                                <div class="card-body d-flex flex-column justify-content-center">
                                                    <h5 class="card-title">Total User Withdrawal Requests</h5>
                                                    <p class="card-text display-4 mb-0">{{ $userWithdrawalRequestCount ?? 0 }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referral Analytics Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="fas fa-share-alt mr-2"></i>Referral Analytics
                                </h5>
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <div class="card text-white bg-success h-100">
                                            <div class="card-body d-flex flex-column justify-content-center">
                                                <h5 class="card-title">Total Agent Referrals</h5>
                                                <p class="card-text display-4 mb-0">{{ $totalReferrals ?? 0 }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="card text-white bg-primary h-100">
                                            <div class="card-body d-flex flex-column justify-content-center">
                                                <h5 class="card-title">Total User Referrals</h5>
                                                <p class="card-text display-4 mb-0">{{ $totalReferrals ?? 0 }}</p>
                                            </div>
                                        </div>
                                    </div>
                                 <!--   <div class="col-md-4 mb-4">
                                        <div class="card text-white h-100" style="background-color: #6f42c1;">
                                            <div class="card-body d-flex flex-column justify-content-center">
                                                <h5 class="card-title">Total Captcha Solves</h5>
                                                <p class="card-text display-4 mb-0">{{ \App\Models\CaptchaSolve::count() ?? 0 }}</p>
                                            </div>
                                        </div>
                                    </div>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hamburger menu toggle
            document.querySelector('.hamburger').addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
            });

            // Settings dropdown toggle
            const toggle = document.getElementById('settings-toggle');
            const menu = document.getElementById('settings-menu-dashboard');
            
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