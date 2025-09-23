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
            --background-color: #f8f9fc;
            --text-color: #1e293b;
            --sidebar-bg: rgba(255, 255, 255, 0.95);
            --card-bg: rgba(255, 255, 255, 0.95);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --accent-color: #60a5fa;
            --form-bg-start: #e0e7ff;
            --form-bg-end: #f1f5f9;
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

        .sidebar .nav-link {
            color: var(--text-color);
            padding: 10px 15px;
            border-radius: var(--border-radius);
            margin-bottom: 10px;
            transition: background 0.3s, color 0.3s;
            display: flex;
            align-items: center;
            text-decoration: none !important;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: var(--primary-color);
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .sidebar .sidebar-header {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        .sidebar ul {
            list-style: none;
            padding-left: 0;
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

        /* Plan Details Section */
        .plan-details-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            background: linear-gradient(135deg, var(--form-bg-start), var(--form-bg-end));
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .plan-header {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .plan-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
        }

        .plan-header p {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .plan-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            padding: 10px;
            border-radius: var(--border-radius);
        }

        .plan-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px 0;
        }

        .detail-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 15px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .detail-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .detail-card dt {
            font-size: 0.9rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .detail-card dd {
            font-size: 1rem;
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

        .badge-info {
            background: var(--info-color);
            color: white;
        }

        .badge-error {
            background: var(--error-color);
            color: white;
        }

        .badge-warning {
            background: var(--warning-color);
            color: white;
        }

        /* Subscriptions Table */
        .subscriptions-section {
            max-width: 1200px;
            margin: 20px auto;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 20px;
            margin-top: 32px;
        }

        .subscriptions-header {
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .subscriptions-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .subscriptions-header p {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            font-size: 0.9rem;
        }

        .table th {
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .table tbody tr {
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .table tbody tr:hover {
            background: rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary-color);
            color: white;
            border-radius: var(--border-radius);
            padding: 10px 20px;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        .btn-primary:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }

        .btn-danger {
            background: var(--error-color);
            color: white;
            border-radius: var(--border-radius);
            padding: 10px 20px;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
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

            .plan-details-grid {
                grid-template-columns: 1fr;
            }

            .plan-details-section,
            .subscriptions-section {
                max-width: 100%;
            }

            .table {
                min-width: 800px;
            }

            .plan-actions {
                flex-direction: column;
                align-items: flex-end;
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
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.agents.index') }}" class="nav-link">Agents</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.subscription_plans.index') }}" class="nav-link">Subscription Plans</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.agent-plans.index') }}" class="nav-link">Agent Plans</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link">Withdrawal Requests</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.push-notification.index') }}" class="nav-link">Push Notification</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.tickets.index') }}" class="nav-link">Tickets</a>
                </li>
              <!--  <li class="nav-item">
    <!--                <a href="{{ route('admin.agent-plan-purchase-requests.index') }}" class="nav-link">Agent Plan Purchase Requests</a>
-->                </li>-->
                <li class="nav-item">
                    <a href="{{ route('admin.subadmins.index') }}" class="nav-link">Sub-Admin</a>
                </li>
                <li class="nav-item settings-dropdown">
                    <a href="#" class="nav-link" id="settings-toggle">Settings</a>
                    <div id="settings-menu-agent-plans" class="settings-menu-card">
                        <a href="{{ route('admin.change-password') }}" class="d-block settings-link">Change Password</a>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
                            @csrf
                            <button type="submit" class="settings-logout-btn">Logout</button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <section class="container mx-auto">
                <!-- Plan Details -->
                <div class="plan-details-section">
                    <div class="plan-header">
                        <div class="flex items-center gap-4">
                            @if($agentPlan->icon)
                                @if(str_starts_with($agentPlan->icon, 'http'))
                                    <img src="{{ $agentPlan->icon }}" alt="Plan Icon" class="h-12 w-12 rounded-lg">
                                @else
                                    <i class="{{ $agentPlan->icon }} text-4xl text-primary-color"></i>
                                @endif
                            @else
                                <div class="h-12 w-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <img src="/images/Vector.png" alt="No Icon" class="h-8 w-8">
                                </div>
                            @endif
                            <div>
                                <h1>{{ $agentPlan->name }}</h1>
                                <p>Plan details and statistics</p>
                            </div>
                        </div>
                        <div class="plan-actions">
                            <a href="{{ route('admin.agent-plans.edit', $agentPlan) }}" class="btn btn-primary">
                                <i class="fas fa-edit mr-2"></i> Edit Plan
                            </a>
                            <form action="{{ route('admin.agent-plans.destroy', $agentPlan) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Are you sure you want to delete this plan?')">
                                    <i class="fas fa-trash mr-2"></i> Delete Plan
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="plan-details-grid">
                        <div class="detail-card">
                            <dt>Plan Name</dt>
                            <dd>{{ $agentPlan->name }}</dd>
                        </div>
                        <div class="detail-card">
                            <dt>Description</dt>
                            <dd>{{ $agentPlan->description ?: 'No description provided' }}</dd>
                        </div>
                        <div class="detail-card">
                            <dt>Icon</dt>
                            <dd>
                                @if($agentPlan->icon)
                                    @if(str_starts_with($agentPlan->icon, 'http'))
                                        <img src="{{ $agentPlan->icon }}" alt="Plan Icon" class="h-8 w-8 rounded inline-block">
                                        <span class="ml-2 text-gray-500">{{ $agentPlan->icon }}</span>
                                    @else
                                        <i class="{{ $agentPlan->icon }} text-2xl text-gray-600 inline-block"></i>
                                        <span class="ml-2 text-gray-500">{{ $agentPlan->icon }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">No icon set</span>
                                @endif
                            </dd>
                        </div>
                        <div class="detail-card">
                            <dt>Price</dt>
                            <dd>₹{{ number_format($agentPlan->price, 2) }}</dd>
                        </div>
                        <div class="detail-card">
                            <dt>Duration</dt>
                            <dd>
                                <span class="badge badge-success">
                                    Lifetime
                                </span>
                            </dd>
                        </div>
                        <div class="detail-card">
                            <dt>Status</dt>
                            <dd>
                                <span class="badge {{ $agentPlan->is_active ? 'badge-success' : 'badge-error' }}">
                                    {{ $agentPlan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </div>
                        <div class="detail-card">
                            <dt>Earning Rates</dt>
                            <dd>
                                <div class="space-y-2">
                                    <div>1-50 logins: ₹{{ $agentPlan->rate_1_50 }}</div>
                                    <div>51-100 logins: ₹{{ $agentPlan->rate_51_100 }}</div>
                                    <div>After 100 logins: ₹{{ $agentPlan->rate_after_100 }}</div>
                                </div>
                            </dd>
                        </div>
                        <div class="detail-card">
                            <dt>Bonuses</dt>
                            <dd>
                                <div class="space-y-2">
                                    <div>10 logins: {{ $agentPlan->bonus_10_logins ?: 'None' }}</div>
                                    <div>50 logins: {{ $agentPlan->bonus_50_logins ?: 'None' }}</div>
                                    <div>100 logins: {{ $agentPlan->bonus_100_logins ?: 'None' }}</div>
                                </div>
                            </dd>
                        </div>
                        <div class="detail-card">
                            <dt>Withdrawal Settings</dt>
                            <dd>
                                <div class="space-y-2">
                                    <div>Minimum: ₹{{ $agentPlan->min_withdrawal }}</div>
                                    <div>Maximum: {{ $agentPlan->max_withdrawal ? '₹' . $agentPlan->max_withdrawal : 'Unlimited' }}</div>
                                    <div>Time: {{ $agentPlan->withdrawal_time }}</div>
                                </div>
                            </dd>
                        </div>
                        <div class="detail-card">
                            <dt>Features</dt>
                            <dd>
                                <div class="space-y-2">
                                    <div>Unlimited Earning: {{ $agentPlan->unlimited_earning ? 'Yes' : 'No' }}</div>
                                    <div>Unlimited Logins: {{ $agentPlan->unlimited_logins ? 'Yes' : 'No' }}</div>
                                    @if($agentPlan->max_logins_per_day)
                                        <div>Max Logins/Day: {{ $agentPlan->max_logins_per_day }}</div>
                                    @endif
                                </div>
                            </dd>
                        </div>
                        <div class="detail-card">
                            <dt>Sort Order</dt>
                            <dd>{{ $agentPlan->sort_order }}</dd>
                        </div>
                        <div class="detail-card">
                            <dt>Created</dt>
                            <dd>{{ $agentPlan->created_at->format('F j, Y \a\t g:i A') }}</dd>
                        </div>
                        <div class="detail-card">
                            <dt>Last Updated</dt>
                            <dd>{{ $agentPlan->updated_at->format('F j, Y \a\t g:i A') }}</dd>
                        </div>
                    </div>
                </div>

                <!-- Subscriptions -->
                <div class="subscriptions-section">
                    <div class="subscriptions-header">
                        <h2>Plan Subscriptions</h2>
                        <p>Agents who have purchased this plan</p>
                    </div>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Agent</th>
                                    <th>Amount Paid</th>
                                    <th>Status</th>
                                    <th>Started</th>
                                    <th>Expires</th>
                                    <th>Total Logins</th>
                                    <th>Total Earnings</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscriptions as $subscription)
                                    <tr>
                                        <td>
                                            <div class="font-medium">{{ $subscription->agent->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $subscription->agent->phone_number }}</div>
                                        </td>
                                        <td>₹{{ number_format($subscription->amount_paid, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $subscription->status === 'active' ? 'badge-success' : ($subscription->status === 'expired' ? 'badge-error' : 'badge-warning') }}">
                                                {{ ucfirst($subscription->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $subscription->started_at->format('M j, Y') }}</td>
                                        <td>{{ $subscription->expires_at ? $subscription->expires_at->format('M j, Y') : 'Unlimited' }}</td>
                                        <td>{{ $subscription->total_logins }}</td>
                                        <td>₹{{ number_format($subscription->total_earnings, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-gray-500">
                                            No subscriptions found for this plan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($subscriptions->hasPages())
                        <div class="mt-4">
                            {{ $subscriptions->links() }}
                        </div>
                    @endif
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