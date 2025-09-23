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
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --background-color: #f8f9fc;
            --text-color: #333;
            --sidebar-bg: rgba(255, 255, 255, 0.95);
            --input-bg: rgba(255, 255, 255, 0.1);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --accent-color: #60a5fa;
            --error-color: #f87171;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e0e7ff, #f3e8ff);
            color: var(--text-color);
            overflow-x: hidden;
            animation: bg-animate 15s ease infinite;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles (Unchanged) */
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
            padding: 40px;
            margin-left: 0;
            transition: margin-left 0.3s ease-in-out;
            min-height: 100vh;
            display: flex;
            justify-content: center;
        }

        .form-container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.2);
            animation: fadeIn 0.5s ease-in;
        }

        .form-header {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(59, 130, 246, 0.2);
        }

        .form-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(90deg, #2563eb, #7e5bef);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            animation: fadeIn 0.5s ease-in;
        }

        .form-header .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(90deg, #6b7280, #9ca3af);
            color: white;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            text-decoration: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .form-header .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
        }

        .form-header .back-btn i {
            margin-right: 0;
        }

        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #ffffff, #f9fafb);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .form-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
        }

        .form-section h2,
        .form-section h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e3a8a;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1e3a8a;
            transition: transform 0.2s ease;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(96, 165, 250, 0.3);
            border-radius: var(--border-radius);
            padding: 12px;
            color: var(--text-color);
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group.checkbox {
            flex-direction: row;
            align-items: center;
            gap: 10px;
        }

        .form-group.checkbox input {
            accent-color: var(--accent-color);
            width: 20px;
            height: 20px;
        }

        .form-group .error-text {
            font-size: 0.9rem;
            margin-top: 5px;
            color: var(--error-color);
        }

        .tier-card {
            background: #fff;
            padding: 15px;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .tier-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .tier-card .grid {
            gap: 15px;
        }

        .tier-card input {
            width: 100%;
        }

        .earning-range-card {
            background: #fff;
            padding: 15px;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .earning-range-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .earning-range-card input {
            flex: 1;
            margin-right: 10px;
        }

        .btn-primary {
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            color: white;
            border-radius: var(--border-radius);
            padding: 12px 24px;
            transition: background 0.3s, transform 0.2s, box-shadow 0.2s;
            font-weight: 600;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #2563eb, #7e5bef);
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.6);
        }

        .btn-secondary {
            background: linear-gradient(90deg, #6b7280, #9ca3af);
            color: white;
            border-radius: var(--border-radius);
            padding: 12px 24px;
            transition: background 0.3s, transform 0.2s, box-shadow 0.2s;
            font-weight: 600;
            box-shadow: 0 0 10px rgba(107, 114, 128, 0.3);
        }

        .btn-secondary:hover {
            background: linear-gradient(90deg, #4b5563, #6b7280);
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(107, 114, 128, 0.5);
        }

        .btn-danger {
            background: linear-gradient(90deg, #ef4444, #dc2626);
            color: white;
            border-radius: var(--border-radius);
            padding: 10px 15px;
            transition: background 0.3s, transform 0.2s;
            font-weight: 600;
        }

        .btn-danger:hover {
            background: linear-gradient(90deg, #dc2626, #b91c1c);
            transform: scale(1.05);
        }

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

        .alert-success {
            background: linear-gradient(90deg, #10b981, #34d399);
            color: white;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            position: relative;
            animation: slideUp 0.5s ease-in;
        }

        .alert-error {
            background: linear-gradient(90deg, #ef4444, #dc2626);
            color: white;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            position: relative;
            animation: slideUp 0.5s ease-in;
        }

        .alert .close {
            position: absolute;
            top: 15px;
            right: 15px;
            color: white;
            cursor: pointer;
            font-size: 1rem;
            transition: transform 0.2s;
        }

        .alert .close:hover {
            transform: scale(1.2);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bg-animate {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
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
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-container {
                padding: 20px;
                max-width: 100%;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                margin-bottom: 10px;
            }

            .flex.justify-end {
                flex-direction: column;
                gap: 10px;
            }

            .tier-card .grid {
                grid-template-columns: 1fr;
            }

            .earning-range-card {
                flex-direction: column;
                align-items: stretch;
            }

            .form-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-header .back-btn {
                width: 100%;
                text-align: center;
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
              <!-- <li class="nav-item">
                 <a href="{{ route('admin.agent-plan-purchase-requests.index') }}" class="nav-link">Agent Plan Purchase Requests</a>
                </li>-->
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
            <section class="container mx-auto">
                <div class="form-container">
                    <div class="form-header">
                        <div>
                            <h1>Manage Commission Tiers</h1>
                            <p class="text-gray-600">{{ $plan->name }} (₹{{ number_format($plan->cost, 2) }})</p>
                        </div>
                        <a href="{{ route('admin.agent-plans.index') }}" class="back-btn">
                            <i class="fas fa-arrow-left"></i> Back to Plans
                        </a>
                    </div>

                    <form action="{{ route('admin.agent-plans.update', $plan) }}" method="POST" id="commission-tiers-form">
                        @csrf
                        @method('PUT')

                        <div class="form-section">
                            <h2><i class="fas fa-coins"></i> Commission Tiers</h2>
                            <p class="text-sm text-gray-600 mb-4">
                                Define commission amounts based on the number of referrals. Each tier applies to agents who have reached the specified minimum number of referrals.
                            </p>
                            <div id="tiers-container" class="space-y-4">
                                @if($tiers->count() > 0)
                                    @foreach($tiers as $index => $tier)
                                        <div class="tier-card">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Min Referrals</label>
                                                    <input type="number" name="tiers[{{ $index }}][min_referrals]" 
                                                           value="{{ old('tiers.'.$index.'.min_referrals', $tier->min_referrals) }}" 
                                                           class="form-control" min="0" required>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Referrals (leave empty for no max)</label>
                                                    <input type="number" name="tiers[{{ $index }}][max_referrals]" 
                                                           value="{{ old('tiers.'.$index.'.max_referrals', $tier->max_referrals) }}" 
                                                           class="form-control" min="0">
                                                </div>
                                                <div class="flex items-end space-x-2">
                                                    <div class="flex-1">
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Commission Amount (₹)</label>
                                                        <input type="number" name="tiers[{{ $index }}][commission_amount]" 
                                                               value="{{ old('tiers.'.$index.'.commission_amount', $tier->commission_amount) }}" 
                                                               class="form-control" min="0" step="0.01" required>
                                                    </div>
                                                    <button type="button" class="btn btn-danger remove-tier">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <input type="hidden" name="tiers[{{ $index }}][id]" value="{{ $tier->id }}">
                                        </div>
                                    @endforeach
                                @else
                                    <div class="tier-card">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Min Referrals</label>
                                                <input type="number" name="tiers[0][min_referrals]" 
                                                       value="{{ old('tiers.0.min_referrals', 0) }}" 
                                                       class="form-control" min="0" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Max Referrals (leave empty for no max)</label>
                                                <input type="number" name="tiers[0][max_referrals]" 
                                                       value="{{ old('tiers.0.max_referrals', '') }}" 
                                                       class="form-control" min="0">
                                            </div>
                                            <div class="flex items-end space-x-2">
                                                <div class="flex-1">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Commission Amount (₹)</label>
                                                    <input type="number" name="tiers[0][commission_amount]" 
                                                           value="{{ old('tiers.0.commission_amount', '') }}" 
                                                           class="form-control" min="0" step="0.01" required>
                                                </div>
                                                <button type="button" class="btn btn-danger remove-tier">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" id="add-tier" class="btn btn-primary mt-4">
                                <i class="fas fa-plus mr-2"></i> Add Tier
                            </button>
                        </div>

                        <div class="form-section">
                            <h2><i class="fas fa-gift"></i> Bonuses</h2>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="bonus_10_logins">Bonus at 10 Logins</label>
                                    <input type="text" name="bonus_10_logins" id="bonus_10_logins" value="{{ old('bonus_10_logins', $plan->bonus_10_logins ?? '') }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="bonus_50_logins">Bonus at 50 Logins</label>
                                    <input type="text" name="bonus_50_logins" id="bonus_50_logins" value="{{ old('bonus_50_logins', $plan->bonus_50_logins ?? '') }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="bonus_100_logins">Bonus at 100 Logins</label>
                                    <input type="text" name="bonus_100_logins" id="bonus_100_logins" value="{{ old('bonus_100_logins', $plan->bonus_100_logins ?? '') }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2><i class="fas fa-wallet"></i> Withdrawal Settings</h2>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="min_withdrawal">Minimum Withdrawal (₹)</label>
                                    <input type="number" name="min_withdrawal" id="min_withdrawal" value="{{ old('min_withdrawal', $plan->min_withdrawal) }}" step="0.01" min="0" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="max_withdrawal">Maximum Withdrawal (₹)</label>
                                    <input type="text" name="max_withdrawal" id="max_withdrawal" value="{{ old('max_withdrawal', $plan->max_withdrawal) }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="withdrawal_time">Withdrawal Time</label>
                                    <input type="text" name="withdrawal_time" id="withdrawal_time" value="{{ old('withdrawal_time', $plan->withdrawal_time ?? '') }}" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2><i class="fas fa-cogs"></i> Features</h2>
                            <div class="form-grid">
                                <div class="form-group checkbox">
                                    <input type="checkbox" name="unlimited_earning" id="unlimited_earning" value="1" {{ old('unlimited_earning', $plan->unlimited_earning ?? '') ? 'checked' : '' }}>
                                    <label for="unlimited_earning">Unlimited Earning</label>
                                </div>
                                <div class="form-group checkbox">
                                    <input type="checkbox" name="unlimited_logins" id="unlimited_logins" value="1" {{ old('unlimited_logins', $plan->unlimited_logins ?? '') ? 'checked' : '' }}>
                                    <label for="unlimited_logins">Unlimited Logins</label>
                                </div>
                                <div class="form-group">
                                    <label for="max_logins_per_day">Max Logins Per Day</label>
                                    <input type="text" name="max_logins_per_day" id="max_logins_per_day" value="{{ old('max_logins_per_day', $plan->max_logins_per_day ?? '') }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="sort_order">Sort Order</label>
                                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" min="0" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $plan->description ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 mt-6">
                            <a href="{{ route('admin.agent-plans.index') }}" class="btn-secondary flex items-center"><i class="fas fa-arrow-left mr-2"></i> Back</a>
                            <button type="submit" class="btn-primary flex items-center"><i class="fas fa-save mr-2"></i> Update Plan</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add tier button
            const addTierBtn = document.getElementById('add-tier');
            const tiersContainer = document.getElementById('tiers-container');
            
            if (addTierBtn && tiersContainer) {
                addTierBtn.addEventListener('click', function() {
                    const tierCount = document.querySelectorAll('.tier-card').length;
                    const newTier = document.createElement('div');
                    newTier.className = 'tier-card';
                    newTier.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Min Referrals</label>
                                <input type="number" name="tiers[${tierCount}][min_referrals]" 
                                       class="form-control" min="0" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Max Referrals (leave empty for no max)</label>
                                <input type="number" name="tiers[${tierCount}][max_referrals]" 
                                       class="form-control" min="0">
                            </div>
                            <div class="flex items-end space-x-2">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Commission Amount (₹)</label>
                                    <input type="number" name="tiers[${tierCount}][commission_amount]" 
                                           class="form-control" min="0" step="0.01" required>
                                </div>
                                <button type="button" class="btn btn-danger remove-tier">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="tiers[${tierCount}][id]" value="">
                    `;
                    tiersContainer.appendChild(newTier);
                });
            }
            
            // Remove tier using event delegation
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-tier')) {
                    const tierItem = e.target.closest('.tier-card');
                    if (tierItem) {
                        tierItem.remove();
                    }
                }
            });
            
            // Form validation and submission
            const form = document.getElementById('commission-tiers-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Form submission started');
                    const tierItems = document.querySelectorAll('.tier-card');
                    
                    // Check if there's at least one tier
                    if (tierItems.length === 0) {
                        e.preventDefault();
                        alert('Please add at least one commission tier.');
                        return false;
                    }
                    
                    // Re-index tier inputs before submission
                    tierItems.forEach((item, index) => {
                        const inputs = item.querySelectorAll('input');
                        inputs.forEach(input => {
                            const name = input.getAttribute('name');
                            if (name) {
                                const newName = name.replace(/\[\d+\]/, `[${index}]`);
                                input.setAttribute('name', newName);
                            }
                        });
                    });
                    
                    console.log('Form submitting...');
                    return true;
                });
            }
            
            // Close alerts with null check
            const alertCloseButtons = document.querySelectorAll('.alert .close');
            if (alertCloseButtons.length > 0) {
                alertCloseButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const alertBox = this.closest('.alert');
                        if (alertBox) {
                            alertBox.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
    <script>
        document.getElementById('add-range').addEventListener('click', function() {
            var list = document.getElementById('earning-ranges-list');
            var idx = list.children.length;
            var div = document.createElement('div');
            div.className = 'earning-range-card';
            div.innerHTML = `
                <input type="number" name="earning_ranges[${idx}][min]" class="form-control" min="1" required>
                <input type="text" name="earning_ranges[${idx}][max]" class="form-control" required>
                <input type="number" step="0.01" name="earning_ranges[${idx}][rate]" class="form-control" required>
                <button type="button" class="btn btn-danger remove-range">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            list.appendChild(div);
        });
        document.querySelectorAll('.remove-range').forEach(function(btn) {
            btn.addEventListener('click', function() {
                this.closest('.earning-range-card').remove();
            });
        });

        // Close alerts
        document.querySelectorAll('.alert .close').forEach(button => {
            button.addEventListener('click', () => {
                button.parentElement.style.display = 'none';
            });
        });
    </script>
</body>
</html>