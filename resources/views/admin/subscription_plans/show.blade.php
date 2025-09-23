<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Plan Details</title>
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
            display: block;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
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

        /* Buttons */
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

        /* Card */
        .details-card {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 24px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeIn 0.5s ease-in;
        }

        .details-card:hover {
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
            margin: -24px -24px 20px;
        }

        .card-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-color);
            text-align: center;
        }

        /* Details Table */
        .details-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
        }

        .details-table th,
        .details-table td {
            padding: 14px;
            text-align: left;
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .details-table th {
            font-weight: 700;
            color: #6b7280;
            width: 25%;
        }

        .details-table td {
            font-weight: 600;
            color: var(--text-color);
        }

        .details-table tr:last-child th,
        .details-table tr:last-child td {
            border-bottom: none;
        }

        .details-table tbody tr {
            transition: background 0.3s ease;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.92));
        }

        .details-table tbody tr:nth-child(even) {
            background: linear-gradient(to right, rgba(245, 245, 245, 0.98), rgba(245, 245, 245, 0.92));
        }

        .details-table tbody tr:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        /* Earnings Table */
        .earnings-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-top: 10px;
        }

        .earnings-table th,
        .earnings-table td {
            padding: 10px;
            text-align: left;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .earnings-table th {
            font-weight: 500;
            color: #6b7280;
            background: rgba(0, 0, 0, 0.05);
        }

        .earnings-table tbody tr {
            transition: background 0.3s ease, transform 0.2s ease;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.92));
        }

        .earnings-table tbody tr:nth-child(even) {
            background: linear-gradient(to right, rgba(245, 245, 245, 0.98), rgba(245, 245, 245, 0.92));
        }

        .earnings-table tbody tr:hover {
            background: rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        /* Icon and Image */
        .icon-display {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: var(--border-radius);
            transition: transform 0.2s ease;
        }

        .icon-display:hover {
            transform: scale(1.1);
        }

        .image-display {
            max-width: 120px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            transition: transform 0.2s ease;
        }

        .image-display:hover {
            transform: scale(1.05);
        }

        .text-muted {
            color: #6b7280;
            font-style: italic;
        }

        /* Table Container */
        .table-container {
            overflow-x: auto;
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

            .details-card {
                max-width: 100%;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
-->                <a href="{{ route('admin.subadmins.index') }}" class="nav-link">Subadmins</a>
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
                <!-- Header -->
                <div class="header">
                    <h1>Subscription Plan Details</h1>
                    <a href="{{ route('admin.subscription_plans.index') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Plans
                    </a>
                </div>

                <!-- Details Card -->
                <div class="details-card">
                    <div class="card-header">
                        <h2>Plan Details</h2>
                    </div>
                    <div class="table-container">
                        <table class="details-table">
                            <tbody>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $subscription_plan->name }}</td>
                                </tr>
                                <tr>
                                    <th>Captcha Per Day</th>
                                    <td>{{ $subscription_plan->captcha_per_day }}</td>
                                </tr>
                                <tr>
                                    <th>Captchas Per Level</th>
                                    <td>{{ $subscription_plan->captchas_per_level }} captchas</td>
                                </tr>
                                <tr>
                                    <th>Min Withdrawal Limit</th>
                                    <td>{{ $subscription_plan->min_withdrawal_limit }}</td>
                                </tr>
                                <tr>
                                    <th>Cost</th>
                                    <td>{{ $subscription_plan->cost }}</td>
                                </tr>
                                <tr>
                                    <th>Earning Type</th>
                                    <td>{{ $subscription_plan->earning_type }}</td>
                                </tr>
                                <tr>
                                    <th>Duration</th>
                                    <td>{{ ucfirst($subscription_plan->duration) }}</td>
                                </tr>
                                <tr>
                                    <th>Plan Type</th>
                                    <td>{{ ucfirst($subscription_plan->plan_type) }}</td>
                                </tr>
                                <tr>
                                    <th>Is Unlimited Plan</th>
                                    <td>{{ $subscription_plan->is_unlimited ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <th>Icon</th>
                                    <td>
                                        @if($subscription_plan->icon)
                                            <span class="icon-display"><i class="{{ $subscription_plan->icon }}"></i></span>
                                            {{ $subscription_plan->icon }}
                                        @else
                                            <span class="text-muted">No icon</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Image</th>
                                    <td>
                                        @if($subscription_plan->image)
                                            <img src="{{ asset('storage/' . $subscription_plan->image) }}" alt="Plan Image" class="image-display">
                                        @else
                                            <span class="text-muted">No image</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Captcha Limit</th>
                                    <td>{{ $subscription_plan->captcha_limit }}</td>
                                </tr>
                                <tr>
                                    <th>Earnings</th>
                                    <td>
                                        @php 
                                            // Get the earnings data
                                            $earnings = $subscription_plan->earnings;
                                            $earningsArray = [];
                                            
                                            // Handle different formats of earnings data
                                            if (is_string($earnings)) {
                                                $earningsArray = json_decode($earnings, true) ?: [];
                                            } elseif (is_array($earnings)) {
                                                $earningsArray = $earnings;
                                            }
                                            
                                            // Sort the earnings by level
                                            $sortedEarnings = [];
                                            $afterLevels = [];
                                            
                                            foreach ($earningsArray as $key => $value) {
                                                if (strpos($key, 'after_') === 0) {
                                                    $level = (int) str_replace('after_', '', $key);
                                                    $afterLevels[$level] = $value;
                                                } else {
                                                    $level = (int) $key;
                                                    $sortedEarnings[$level] = $value;
                                                }
                                            }
                                            
                                            // Sort the levels
                                            ksort($sortedEarnings);
                                            ksort($afterLevels);
                                        @endphp
                                        
                                        @if(!empty($sortedEarnings) || !empty($afterLevels))
                                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-4">
                                                <div class="grid grid-cols-2 bg-gray-50 border-b border-gray-200 px-4 py-3 font-medium text-gray-700">
                                                    <div>Level</div>
                                                    <div>Earnings (₹)</div>
                                                </div>
                                                
                                                @foreach($sortedEarnings as $level => $amount)
                                                    <div class="grid grid-cols-2 border-b border-gray-100 px-4 py-3 hover:bg-gray-50">
                                                        <div class="font-medium">Level {{ $level }}</div>
                                                        <div>₹{{ number_format($amount, 2) }}</div>
                                                    </div>
                                                @endforeach
                                                
                                                @foreach($afterLevels as $level => $amount)
                                                    <div class="grid grid-cols-2 border-b border-gray-100 px-4 py-3 hover:bg-gray-50">
                                                        <div class="font-medium">After Level {{ $level }}</div>
                                                        <div>₹{{ number_format($amount, 2) }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            
                                            <div class="mt-2 text-sm text-gray-500">
                                                <p class="mb-1"><i class="fas fa-info-circle mr-1"></i> Earnings are applied based on the user's current level.</p>
                                                <p>"After Level X" indicates the earnings rate for all levels beyond X.</p>
                                            </div>
                                        @else
                                            <span class="text-gray-500">No level-based earnings configured</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Min Daily Earning</th>
                                    <td>₹{{ number_format($subscription_plan->min_daily_earning, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Referral Earning per Referral</th>
                                    <td>₹{{ number_format($subscription_plan->referral_earning_per_ref, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Daily Captcha Earning with Referral</th>
                                    <td>₹{{ number_format($subscription_plan->daily_captcha_earning_with_ref, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Bonus for 10 Referrals</th>
                                    <td>₹{{ number_format($subscription_plan->bonus_10_referrals, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Gift for 10 Referrals</th>
                                    <td>{{ $subscription_plan->gift_10_referrals ?: 'None' }}</td>
                                </tr>
                                <tr>
                                    <th>Bonus for 20 Referrals</th>
                                    <td>₹{{ number_format($subscription_plan->bonus_20_referrals, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Gift for 20 Referrals</th>
                                    <td>{{ $subscription_plan->gift_20_referrals ?: 'None' }}</td>
                                </tr>
                                <tr>
                                    <th>Daily Limit Bonus</th>
                                    <td>₹{{ number_format($subscription_plan->daily_limit_bonus, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Unlimited Earning Rate</th>
                                    <td>₹{{ number_format($subscription_plan->unlimited_earning_rate, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $subscription_plan->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $subscription_plan->updated_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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