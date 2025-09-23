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
            --secondary-color: #9333ea;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --info-color: #14b8a6;
            --background-color: #f3f4f6;
            --text-color: #1e293b;
            --sidebar-bg: linear-gradient(135deg, #ffffff, #f9fafb);
            --card-bg: linear-gradient(135deg, #ffffff, #f9fafb);
            --shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --accent-color: #60a5fa;
            --header-bg-start: #e0e7ff;
            --header-bg-end: #f1f5f9;
            --glow: 0 0 15px rgba(59, 130, 246, 0.3);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--background-color);
            color: var(--text-color);
            overflow-x: hidden;
            transition: all 0.3s ease;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
            background: url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"%3E%3Cpath fill="%23e0e7ff" fill-opacity="0.1" d="M0,64L48,80C96,96,192,128,288,128C384,128,480,96,576,85.3C672,75,768,85,864,106.7C960,128,1056,160,1152,149.3C1248,139,1344,85,1392,58.7L1440,32V320H1392C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320H0Z"%3E%3C/path%3E%3C/svg%3E');
            background-size: cover;
        }

        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            padding: 25px;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
        }

        .nav-items .nav-link {
            color: var(--text-color);
            padding: 12px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            position: relative;
        }

        .nav-items .nav-link:hover, .nav-items .nav-link.active {
            background: var(--primary-color);
            color: white;
            transform: translateX(8px);
        }

        .nav-items .nav-link:hover::before,
        .nav-items .nav-link.active::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 40px;
            background: var(--accent-color);
            border-radius: 2px;
        }

        .sidebar .sidebar-header {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 1px;
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
            min-width: 220px;
            background: white;
            box-shadow: var(--shadow);
            border-radius: var(--border-radius);
            border: 1px solid rgba(0, 0, 0, 0.05);
            z-index: 2000;
            margin-top: 10px;
            padding: 8px 0;
            transition: all 0.2s ease;
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
            top: -6px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 0 8px 8px 8px;
            border-style: solid;
            border-color: transparent transparent white transparent;
            filter: drop-shadow(0 -1px 2px rgba(0,0,0,0.1));
        }

        .settings-menu-card .settings-link,
        .settings-menu-card .settings-logout-btn {
            display: block;
            width: 100%;
            background: none;
            border: none;
            text-align: left;
            padding: 10px 20px;
            color: #4b5563;
            font-size: 0.95rem;
            border-radius: 0;
            transition: all 0.2s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .settings-menu-card .settings-link:hover {
            background: #f3f4f6;
            color: var(--primary-color);
        }

        .settings-menu-card .settings-logout-btn {
            color: var(--error-color);
            font-weight: 500;
        }

        .settings-menu-card .settings-logout-btn:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        .main-content {
            flex: 1;
            padding: 40px;
            margin-left: 0;
            transition: margin-left 0.3s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .form-card {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 30px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .form-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            padding: 20px;
            background: linear-gradient(90deg, var(--header-bg-start), var(--header-bg-end));
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            margin: -30px -30px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            font-size: 0.95rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 8px;
            text-transform: capitalize;
        }

        .form-group input,
        .form-group select {
            padding: 12px 40px 12px 16px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            color: var(--text-color);
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2), inset 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .form-group .icon {
            position: absolute;
            right: 12px;
            top: 38px;
            color: #9ca3af;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus + .icon,
        .form-group select:focus + .icon {
            color: var(--primary-color);
        }

        .form-group .error {
            color: var(--error-color);
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .form-group small {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 5px;
        }

        .earnings-section {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #ffffff, #f9fafb);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .earnings-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .earnings-section h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
            display: inline-block;
        }

        .earnings-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .earning-card {
            flex: 1 1 calc(33.33% - 20px);
            min-width: 240px;
            background: white;
            padding: 15px;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .earning-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .earning-card label {
            font-size: 0.95rem;
            color: #6b7280;
            margin-bottom: 8px;
            display: block;
        }

        .earning-card input {
            width: 80%;
            padding: 10px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .earning-card input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .earning-card .remove-btn {
            color: var(--error-color);
            margin-top: 10px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .earning-card .remove-btn:hover {
            color: #dc2626;
            transform: scale(1.1);
        }

        .add-row-btn {
            background: var(--success-color);
            color: white;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }

        .add-row-btn:hover {
            background: #16a34a;
            transform: translateY(-2px);
        }

        .button-group {
            display: flex;
            gap: 20px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--glow);
        }

        .btn-secondary {
            background: linear-gradient(90deg, #6b7280, #9ca3af);
            color: white;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(107, 114, 128, 0.3);
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
            transition: all 0.3s ease;
        }

        .hamburger:hover {
            color: var(--primary-color);
            transform: rotate(90deg);
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

            .hamburger {
                display: block;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .earning-card {
                flex: 1 1 100%;
            }

            .button-group {
                flex-direction: column;
                gap: 15px;
            }
        }

        @media (min-width: 769px) {
            .hamburger {
                display: none;
            }
        }

        .error {
            border-color: var(--error-color) !important;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <i class="fas fa-bars hamburger"></i>
        <nav class="sidebar">
            <img src="{{ asset('images/logo c2c 2.png') }}" alt="Logo" style="max-width: 160px; margin: 0 auto 15px; display: block;">
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
-->                <div class="settings-dropdown">
                    <a href="#" class="nav-link" id="settings-toggle">Settings</a>
                    <div id="settings-menu-agent-plans" class="settings-menu-card">
                        <a href="{{ route('admin.change-password') }}" class="settings-link">Change Password</a>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
                            @csrf
                            <button type="submit" class="settings-logout-btn">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <div class="main-content">
            <section class="container mx-auto">
                <div class="form-card">
                    <div class="card-header">
                        <h2>Edit Subscription Plan</h2>
                        <a href="{{ route('admin.subscription_plans.index') }}" class="btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i> Back
                        </a>
                    </div>
                    <form action="{{ route('admin.subscription_plans.update', $subscription_plan) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Plan Name</label>
                                <input type="text" name="name" id="name" class="@error('name') error @enderror" value="{{ old('name', $subscription_plan->name) }}" required>
                                <i class="fas fa-signature icon"></i>
                                @error('name')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="captcha_per_day">Captcha per Day</label>
                                <input type="text" name="captcha_per_day" id="captcha_per_day" class="@error('captcha_per_day') error @enderror" value="{{ old('captcha_per_day', $subscription_plan->captcha_per_day) }}" required>
                                <i class="fas fa-shield-alt icon"></i>
                                @error('captcha_per_day')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="captchas_per_level">Captchas Per Level</label>
                                <input type="number" name="captchas_per_level" id="captchas_per_level" value="{{ old('captchas_per_level', $subscription_plan->captchas_per_level) }}" min="1" required>
                                <small>Number of captchas needed to complete one level</small>
                                @error('captchas_per_level')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="min_withdrawal_limit">Minimum Withdrawal Limit</label>
                                <input type="number" name="min_withdrawal_limit" id="min_withdrawal_limit" class="@error('min_withdrawal_limit') error @enderror" value="{{ old('min_withdrawal_limit', $subscription_plan->min_withdrawal_limit) }}">
                                <i class="fas fa-money-bill-wave icon"></i>
                                @error('min_withdrawal_limit')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="cost">Price (₹)</label>
                                <input type="number" step="0.01" name="cost" id="cost" class="@error('cost') error @enderror" value="{{ old('cost', $subscription_plan->cost) }}" required>
                                <i class="fas fa-rupee-sign icon"></i>
                                @error('cost')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="duration">Duration</label>
                                <select name="duration" id="duration" class="@error('duration') error @enderror" required>
                                    <option value="">Select Duration</option>
                                    <option value="lifetime" {{ old('duration', $subscription_plan->duration) == 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                                </select>
                                <i class="fas fa-clock icon"></i>
                                @error('duration')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="plan_type">Plan Type</label>
                                <select name="plan_type" id="plan_type" class="@error('plan_type') error @enderror" required>
                                    <option value="">Select Plan Type</option>
                                    <option value="basic" {{ old('plan_type', $subscription_plan->plan_type) == 'basic' ? 'selected' : '' }}>Basic</option>
                                    <option value="premium" {{ old('plan_type', $subscription_plan->plan_type) == 'premium' ? 'selected' : '' }}>Premium</option>
                                </select>
                                <i class="fas fa-tag icon"></i>
                                @error('plan_type')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="earning_type">Earning Type</label>
                                <select name="earning_type" id="earning_type" class="@error('earning_type') error @enderror" required>
                                    <option value="">Select Earning Type</option>
                                    <option value="limited" {{ old('earning_type', $subscription_plan->earning_type) == 'limited' ? 'selected' : '' }}>Limited</option>
                                    <option value="unlimited" {{ old('earning_type', $subscription_plan->earning_type) == 'unlimited' ? 'selected' : '' }}>Unlimited</option>
                                </select>
                                <i class="fas fa-money-check-alt icon"></i>
                                @error('earning_type')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="is_unlimited">Is Unlimited Plan</label>
                                <select name="is_unlimited" id="is_unlimited" class="@error('is_unlimited') error @enderror">
                                    <option value="0" {{ old('is_unlimited', $subscription_plan->is_unlimited) == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('is_unlimited', $subscription_plan->is_unlimited) == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                                <i class="fas fa-infinity icon"></i>
                                @error('is_unlimited')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="icon">Icon (Optional)</label>
                                <input type="text" name="icon" id="icon" class="@error('icon') error @enderror" value="{{ old('icon', $subscription_plan->icon) }}" placeholder="e.g., fas fa-star">
                                <i class="fas fa-icons icon"></i>
                                <small>Use Font Awesome icon classes</small>
                                @error('icon')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="image">Image</label>
                                <input type="file" name="image" id="image" class="@error('image') error @enderror">
                                @if($subscription_plan->image)
                                    <small>Current image: {{ basename($subscription_plan->image) }}</small>
                                @endif
                                @error('image')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="caption_limit">Captcha Limit</label>
                                <input type="text" name="caption_limit" id="caption_limit" class="@error('caption_limit') error @enderror" value="{{ old('caption_limit', $subscription_plan->caption_limit) }}" placeholder="Enter a number or 'unlimited'">
                                <i class="fas fa-lock icon"></i>
                                @error('caption_limit')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="min_daily_earning">Minimum Daily Earning</label>
                                <input type="number" step="0.01" name="min_daily_earning" id="min_daily_earning" class="@error('min_daily_earning') error @enderror" value="{{ old('min_daily_earning', $subscription_plan->min_daily_earning) }}">
                                <i class="fas fa-coins icon"></i>
                                @error('min_daily_earning')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="referral_earning_per_ref">Referral Earning per Referral (₹)</label>
                                <input type="number" step="0.01" name="referral_earning_per_ref" id="referral_earning_per_ref" class="@error('referral_earning_per_ref') error @enderror" value="{{ old('referral_earning_per_ref', $subscription_plan->referral_earning_per_ref) }}">
                                <i class="fas fa-user-plus icon"></i>
                                @error('referral_earning_per_ref')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="daily_captcha_earning_with_ref">Daily Captcha Earning with Referral (₹)</label>
                                <input type="number" step="0.01" name="daily_captcha_earning_with_ref" id="daily_captcha_earning_with_ref" class="@error('daily_captcha_earning_with_ref') error @enderror" value="{{ old('daily_captcha_earning_with_ref', $subscription_plan->daily_captcha_earning_with_ref) }}">
                                <i class="fas fa-calendar-day icon"></i>
                                @error('daily_captcha_earning_with_ref')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="bonus_10_referrals">Bonus for 10 Referrals (₹)</label>
                                <input type="number" step="0.01" name="bonus_10_referrals" id="bonus_10_referrals" class="@error('bonus_10_referrals') error @enderror" value="{{ old('bonus_10_referrals', $subscription_plan->bonus_10_referrals) }}">
                                <i class="fas fa-gift icon"></i>
                                @error('bonus_10_referrals')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="gift_10_referrals">Gift for 10 Referrals</label>
                                <input type="text" name="gift_10_referrals" id="gift_10_referrals" class="@error('gift_10_referrals') error @enderror" value="{{ old('gift_10_referrals', $subscription_plan->gift_10_referrals) }}" placeholder="e.g., Cap">
                                <i class="fas fa-gift icon"></i>
                                @error('gift_10_referrals')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="bonus_20_referrals">Bonus for 20 Referrals (₹)</label>
                                <input type="number" step="0.01" name="bonus_20_referrals" id="bonus_20_referrals" class="@error('bonus_20_referrals') error @enderror" value="{{ old('bonus_20_referrals', $subscription_plan->bonus_20_referrals) }}">
                                <i class="fas fa-gift icon"></i>
                                @error('bonus_20_referrals')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="gift_20_referrals">Gift for 20 Referrals</label>
                                <input type="text" name="gift_20_referrals" id="gift_20_referrals" class="@error('gift_20_referrals') error @enderror" value="{{ old('gift_20_referrals', $subscription_plan->gift_20_referrals) }}" placeholder="e.g., T-shirt">
                                <i class="fas fa-gift icon"></i>
                                @error('gift_20_referrals')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="daily_limit_bonus">Daily Limit Bonus (₹)</label>
                                <input type="number" step="0.01" name="daily_limit_bonus" id="daily_limit_bonus" class="@error('daily_limit_bonus') error @enderror" value="{{ old('daily_limit_bonus', $subscription_plan->daily_limit_bonus) }}">
                                <i class="fas fa-star icon"></i>
                                @error('daily_limit_bonus')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="unlimited_earning_rate">Unlimited Earning Rate (₹)</label>
                                <input type="number" step="0.01" name="unlimited_earning_rate" id="unlimited_earning_rate" class="@error('unlimited_earning_rate') error @enderror" value="{{ old('unlimited_earning_rate', $subscription_plan->unlimited_earning_rate) }}">
                                <i class="fas fa-infinity icon"></i>
                                @error('unlimited_earning_rate')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="earnings-section">
                            <h3>Level-Based Earnings</h3>
                            <div class="earnings-container" id="earnings-container">
                                <!-- Rows will be added here by JavaScript -->
                            </div>
                            <p class="text-sm text-gray-500 mt-4">
                                Enter level numbers (e.g., "1", "2", "3") and corresponding reward amounts in rupees.
                            </p>
                            @error('earnings')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <button type="button" id="add-earning-row" class="add-row-btn">
                                <i class="fas fa-plus"></i> Add Row
                            </button>
                            <input type="hidden" name="earnings" id="earnings-json" value='{{ old('earnings', json_encode($subscription_plan->earnings)) }}'>
                        </div>

                        <div class="button-group">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save mr-2"></i> Update Plan
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const earningsContainer = document.getElementById('earnings-container');
            const addRowBtn = document.getElementById('add-earning-row');
            const earningsJsonInput = document.getElementById('earnings-json');
            
            // Initialize with existing earnings if any
            let earnings = {};
            try {
                earnings = JSON.parse(earningsJsonInput.value || '{}');
            } catch (e) {
                console.error('Error parsing earnings JSON:', e);
                earnings = {};
            }
            
            // Add a new row to the container
            function addEarningRow(level = '', reward = '') {
                const card = document.createElement('div');
                card.className = 'earning-card';
                card.innerHTML = `
                    <label>Level</label>
                    <input type="number" 
                           class="earnings-level w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="e.g., 1, 2, 3" 
                           value="${level}"
                           min="1"
                           required>
                    <p class="mt-1 text-sm text-red-600 hidden earnings-level-error">Please enter a valid level number (positive integer).</p>
                    <label>Reward Amount (₹)</label>
                    <input type="number" 
                           step="0.01" 
                           class="earnings-reward w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="Reward amount (₹)" 
                           value="${reward}"
                           min="0"
                           required>
                    <p class="mt-1 text-sm text-red-600 hidden earnings-reward-error">Please enter a valid reward amount (non-negative number).</p>
                    <button type="button" class="remove-earning remove-btn">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                `;
                earningsContainer.appendChild(card);
                updateEarningsJson();
                return card;
            }
            
            // Update the hidden JSON input with current form data
            function updateEarningsJson() {
                const earnings = {};
                let isValid = true;
                
                document.querySelectorAll('.earning-card').forEach((card) => {
                    const levelInput = card.querySelector('.earnings-level');
                    const rewardInput = card.querySelector('.earnings-reward');
                    const level = parseInt(levelInput.value.trim());
                    const reward = parseFloat(rewardInput.value);
                    
                    if (level > 0 && !isNaN(reward) && reward >= 0) {
                        earnings[level] = reward;
                    } else {
                        isValid = false;
                    }
                });
                
                earningsJsonInput.value = isValid && Object.keys(earnings).length > 0 ? JSON.stringify(earnings) : '{}';
            }
            
            // Load existing earnings into the container
            Object.entries(earnings).forEach(([level, reward]) => {
                addEarningRow(level, reward);
            });
            
            // Add a new row when clicking the Add Row button
            addRowBtn.addEventListener('click', function() {
                const lastCard = earningsContainer.lastElementChild;
                let nextLevel = 1;
                if (lastCard) {
                    const lastLevel = parseInt(lastCard.querySelector('.earnings-level').value) || 0;
                    nextLevel = lastLevel + 1;
                }
                addEarningRow(nextLevel, '');
            });
            
            // Remove a row when clicking the trash icon
            earningsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-earning')) {
                    e.target.closest('.earning-card').remove();
                    updateEarningsJson();
                }
            });
            
            // Update JSON when inputs change
            earningsContainer.addEventListener('input', function(e) {
                if (e.target.matches('.earnings-level, .earnings-reward')) {
                    updateEarningsJson();
                }
            });
            
            // Add form validation before submission
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    updateEarningsJson();
                    
                    let isValid = true;
                    const cards = document.querySelectorAll('.earning-card');
                    
                    // Validate that all cards have valid level and reward
                    cards.forEach(card => {
                        const levelInput = card.querySelector('.earnings-level');
                        const rewardInput = card.querySelector('.earnings-reward');
                        const levelError = card.querySelector('.earnings-level-error');
                        const rewardError = card.querySelector('.earnings-reward-error');
                        const level = parseInt(levelInput.value.trim());
                        const reward = parseFloat(rewardInput.value);
                        
                        if (!level || level < 1) {
                            isValid = false;
                            levelInput.classList.add('border-red-500');
                            levelError.classList.remove('hidden');
                        } else {
                            levelInput.classList.remove('border-red-500');
                            levelError.classList.add('hidden');
                        }
                        
                        if (isNaN(reward) || reward < 0) {
                            isValid = false;
                            rewardInput.classList.add('border-red-500');
                            rewardError.classList.remove('hidden');
                        } else {
                            rewardInput.classList.remove('border-red-500');
                            rewardError.classList.add('hidden');
                        }
                    });
                    
                    // Check for duplicate levels
                    const levels = Array.from(cards).map(card => parseInt(card.querySelector('.earnings-level').value));
                    const uniqueLevels = new Set(levels);
                    if (uniqueLevels.size !== levels.length) {
                        isValid = false;
                        alert('Duplicate level numbers detected. Each level must be unique.');
                        cards.forEach(card => {
                            const level = parseInt(card.querySelector('.earnings-level').value);
                            if (levels.indexOf(level) !== levels.lastIndexOf(level)) {
                                card.querySelector('.earnings-level').classList.add('border-red-500');
                                card.querySelector('.earnings-level-error').classList.remove('hidden');
                            }
                        });
                    }
                    
                    if (!isValid || cards.length === 0) {
                        e.preventDefault();
                        alert('Please fill in all level-based earnings with valid values. Levels must be unique positive integers, and rewards must be non-negative numbers.');
                    }
                });
            }
            
            // Add a default row if none exist
            if (earningsContainer.children.length === 0) {
                addEarningRow('1', '10');
            }
            
            // Hamburger menu toggle
            document.querySelector('.hamburger').addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
            });
            
            // Settings dropdown toggle
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