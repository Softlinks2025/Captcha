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
            --background-color: #f8f9fc;
            --card-bg: rgba(255, 255, 255, 0.1);
            --text-color: #333;
            --sidebar-bg: rgba(255, 255, 255, 0.95);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --error-color: #dc3545;
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
        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 0;
            transition: margin-left 0.3s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        .card-body {
            padding: 20px;
        }
        .form-label {
            font-weight: 500;
            color: #333;
        }
        .form-control, .form-select {
            border-radius: var(--border-radius);
            box-shadow: none;
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
            color: #fff;
            border-radius: var(--border-radius);
            padding: 10px 24px;
            font-weight: 500;
            transition: background 0.3s;
        }
        .btn-primary:hover {
            background: #2e59d9;
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
            color: #fff;
            border-radius: var(--border-radius);
            padding: 10px 24px;
            font-weight: 500;
            transition: background 0.3s;
        }
        .btn-secondary:hover {
            background: #495057;
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
        }
        @media (min-width: 769px) {
            .hamburger {
                display: none;
            }
        }
        .invalid-feedback {
            display: none;
        }
        .is-invalid ~ .invalid-feedback {
            display: block;
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
-->                <div class="settings-dropdown">
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Create New User Subscription Plan</h2>
                <a href="{{ route('admin.subscription_plans.index') }}" class="btn btn-secondary">Back to Plans</a>
            </div>
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.subscription_plans.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Plan Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="form-control">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="cost" class="form-label">Price (₹) *</label>
                                <input type="number" name="cost" id="cost" value="{{ old('cost') }}" step="0.01" min="0" required class="form-control">
                                @error('cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="duration" class="form-label">Duration *</label>
                                <select name="duration" id="duration" required class="form-select">
                                    <option value="">Select Duration</option>
                                    <option value="lifetime" {{ old('duration') == 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                                </select>
                                @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="plan_type" class="form-label">Plan Type *</label>
                                <select name="plan_type" id="plan_type" required class="form-select">
                                    <option value="">Select Plan Type</option>
                                    <option value="basic" {{ old('plan_type') == 'basic' ? 'selected' : '' }}>Basic</option>
                                    <option value="premium" {{ old('plan_type') == 'premium' ? 'selected' : '' }}>Premium</option>
                                </select>
                                @error('plan_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="earning_type" class="form-label">Earning Type *</label>
                                <select name="earning_type" id="earning_type" required class="form-select">
                                    <option value="">Select Earning Type</option>
                                    <option value="limited" {{ old('earning_type') == 'limited' ? 'selected' : '' }}>Limited</option>
                                    <option value="unlimited" {{ old('earning_type') == 'unlimited' ? 'selected' : '' }}>Unlimited</option>
                                </select>
                                @error('earning_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="is_unlimited" class="form-label">Is Unlimited Plan</label>
                                <select name="is_unlimited" id="is_unlimited" class="form-select">
                                    <option value="0" {{ old('is_unlimited') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('is_unlimited') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                                @error('is_unlimited')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="captchas_per_level" class="form-label">Captchas Per Level *</label>
                                <input type="number" name="captchas_per_level" id="captchas_per_level" 
                                       value="{{ old('captchas_per_level', 10) }}" 
                                       min="1" required class="form-control"
                                       placeholder="e.g., 10 captchas = 1 level">
                                <small class="form-text text-muted">
                                    Number of captchas needed to complete one level
                                </small>
                                @error('captchas_per_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="icon" class="form-label">Icon (Upload Image)</label>
                                <input type="file" name="icon" id="icon" accept="image/*" class="form-control">
                                <small class="text-muted">Upload an image for the plan icon</small>
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mt-4">
                                <h5>Level-Based Earnings</h5>
                                <div class="mb-4">
                                    <div class="overflow-x-auto">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Level</th>
                                                    <th>Reward Amount (₹)</th>
                                                    <th class="text-end">
                                                        <button type="button" id="add-earning-row" class="btn btn-sm btn-success">
                                                            <i class="fas fa-plus"></i> Add Row
                                                        </button>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="earnings-tbody">
                                                <!-- Rows will be added here by JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Enter level numbers (e.g., "1", "2") and corresponding reward amounts in rupees.
                                        </small>
                                        @error('earnings')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <input type="hidden" name="earnings" id="earnings-json" value='{{ old('earnings', '{}') }}'>
                                </div>
                            </div>
                            <div class="col-md-12 mt-4">
                                <h5>Bonuses</h5>
                            </div>
                            <div class="col-md-4">
                                <label for="bonus_10_logins" class="form-label">Bonus at 10 logins</label>
                                <input type="text" name="bonus_10_logins" id="bonus_10_logins" value="{{ old('bonus_10_logins') }}" placeholder="e.g., Cap" class="form-control">
                                @error('bonus_10_logins')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="bonus_50_logins" class="form-label">Bonus at 50 logins</label>
                                <input type="text" name="bonus_50_logins" id="bonus_50_logins" value="{{ old('bonus_50_logins') }}" placeholder="e.g., T-shirt" class="form-control">
                                @error('bonus_50_logins')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="bonus_100_logins" class="form-label">Bonus at 100 logins</label>
                                <input type="text" name="bonus_100_logins" id="bonus_100_logins" value="{{ old('bonus_100_logins') }}" placeholder="e.g., Bag" class="form-control">
                                @error('bonus_100_logins')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="captcha_per_day" class="form-label">Captcha per Day *</label>
                                <input type="text" name="captcha_per_day" id="captcha_per_day" value="{{ old('captcha_per_day') }}" required class="form-control" placeholder="Enter a number or 'unlimited'">
                                @error('captcha_per_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="min_withdrawal_limit" class="form-label">Minimum Withdrawal Limit</label>
                                <input type="number" name="min_withdrawal_limit" id="min_withdrawal_limit" value="{{ old('min_withdrawal_limit') }}" class="form-control">
                                @error('min_withdrawal_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="caption_limit" class="form-label">Captcha Limit</label>
                                <input type="text" name="caption_limit" id="caption_limit" value="{{ old('caption_limit') }}" class="form-control" placeholder="Enter a number or 'unlimited'">
                                @error('caption_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="min_daily_earning" class="form-label">Minimum Daily Earning</label>
                                <input type="number" name="min_daily_earning" id="min_daily_earning" value="{{ old('min_daily_earning') }}" class="form-control">
                                @error('min_daily_earning')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mt-4">
                                <h5>Referral Earnings</h5>
                            </div>
                            <div class="col-md-6">
                                <label for="referral_earning_per_ref" class="form-label">Referral Earning per Referral (₹)</label>
                                <input type="number" name="referral_earning_per_ref" id="referral_earning_per_ref" value="{{ old('referral_earning_per_ref') }}" step="0.01" min="0" class="form-control">
                                @error('referral_earning_per_ref')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="daily_captcha_earning_with_ref" class="form-label">Daily Captcha Earning with Referral (₹)</label>
                                <input type="number" name="daily_captcha_earning_with_ref" id="daily_captcha_earning_with_ref" value="{{ old('daily_captcha_earning_with_ref') }}" step="0.01" min="0" class="form-control">
                                @error('daily_captcha_earning_with_ref')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mt-4">
                                <h5>Referral Milestone Bonuses</h5>
                            </div>
                            <div class="col-md-6">
                                <label for="bonus_10_referrals" class="form-label">Bonus for 10 Referrals (₹)</label>
                                <input type="number" name="bonus_10_referrals" id="bonus_10_referrals" value="{{ old('bonus_10_referrals') }}" step="0.01" min="0" class="form-control">
                                @error('bonus_10_referrals')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="gift_10_referrals" class="form-label">Gift for 10 Referrals</label>
                                <input type="text" name="gift_10_referrals" id="gift_10_referrals" value="{{ old('gift_10_referrals') }}" placeholder="e.g., Cap" class="form-control">
                                @error('gift_10_referrals')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="bonus_20_referrals" class="form-label">Bonus for 20 Referrals (₹)</label>
                                <input type="number" name="bonus_20_referrals" id="bonus_20_referrals" value="{{ old('bonus_20_referrals') }}" step="0.01" min="0" class="form-control">
                                @error('bonus_20_referrals')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="gift_20_referrals" class="form-label">Gift for 20 Referrals</label>
                                <input type="text" name="gift_20_referrals" id="gift_20_referrals" value="{{ old('gift_20_referrals') }}" placeholder="e.g., T-shirt" class="form-control">
                                @error('gift_20_referrals')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="daily_limit_bonus" class="form-label">Daily Limit Bonus (₹)</label>
                                <input type="number" name="daily_limit_bonus" id="daily_limit_bonus" value="{{ old('daily_limit_bonus') }}" step="0.01" min="0" class="form-control">
                                @error('daily_limit_bonus')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="unlimited_earning_rate" class="form-label">Unlimited Earning Rate (₹)</label>
                                <input type="number" name="unlimited_earning_rate" id="unlimited_earning_rate" value="{{ old('unlimited_earning_rate') }}" step="0.01" min="0" class="form-control">
                                @error('unlimited_earning_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.subscription_plans.index') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Create Plan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const earningsTbody = document.getElementById('earnings-tbody');
            const addRowBtn = document.getElementById('add-earning-row');
            const earningsJsonInput = document.getElementById('earnings-json');
            
            // Initialize with existing earnings if any (for form validation errors)
            let earnings = {};
            try {
                const existingEarnings = {!! json_encode(old('earnings', '{}')) !!};
                if (existingEarnings && existingEarnings !== '{}') {
                    earnings = typeof existingEarnings === 'string' ? JSON.parse(existingEarnings) : existingEarnings;
                }
            } catch (e) {
                console.error('Error parsing earnings JSON:', e);
                earnings = {};
            }
            
            // Add a new row to the table
            function addEarningRow(level = '', reward = '') {
                const row = document.createElement('tr');
                row.className = 'earning-row';
                row.innerHTML = `
                    <td>
                        <input type="number" 
                               class="form-control earnings-level" 
                               placeholder="e.g., 1, 2, 3" 
                               value="${level}"
                               min="1"
                               required>
                        <div class="invalid-feedback">Please enter a valid level number (positive integer).</div>
                    </td>
                    <td>
                        <input type="number" 
                               step="0.01" 
                               class="form-control earnings-reward" 
                               placeholder="Reward amount (₹)" 
                               value="${reward}"
                               min="0"
                               required>
                        <div class="invalid-feedback">Please enter a valid reward amount (positive number).</div>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-danger remove-earning">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                earningsTbody.appendChild(row);
                updateEarningsJson();
                return row;
            }
            
            // Update the hidden JSON input with current form data
            function updateEarningsJson() {
                const earnings = {};
                let isValid = true;
                
                document.querySelectorAll('.earning-row').forEach((row) => {
                    const levelInput = row.querySelector('.earnings-level');
                    const rewardInput = row.querySelector('.earnings-reward');
                    
                    // Skip if inputs don't exist (e.g., row was just removed)
                    if (!levelInput || !rewardInput) return;
                    
                    const level = levelInput.value.trim() ? parseInt(levelInput.value.trim()) : NaN;
                    const reward = rewardInput.value.trim() ? parseFloat(rewardInput.value) : NaN;
                    
                    // Only add to earnings if both level and reward are valid numbers
                    if (!isNaN(level) && level > 0 && !isNaN(reward) && reward >= 0) {
                        earnings[level] = reward;
                    } else {
                        isValid = false;
                    }
                });
                
                try {
                    earningsJsonInput.value = isValid && Object.keys(earnings).length > 0 ? 
                        JSON.stringify(earnings) : '{}';
                } catch (e) {
                    console.error('Error stringifying earnings:', e);
                    earningsJsonInput.value = '{}';
                }
            }
            
            // Load existing earnings into the table
            Object.entries(earnings).forEach(([level, reward]) => {
                addEarningRow(level, reward);
            });
            
            // Add a new row when clicking the Add Row button
            addRowBtn.addEventListener('click', function() {
                const lastRow = earningsTbody.lastElementChild;
                let nextLevel = 1;
                if (lastRow) {
                    const lastLevel = parseInt(lastRow.querySelector('.earnings-level').value) || 0;
                    nextLevel = lastLevel + 1;
                }
                addEarningRow(nextLevel, '');
            });
            
            // Remove a row when clicking the trash icon
            earningsTbody.addEventListener('click', function(e) {
                if (e.target.closest('.remove-earning')) {
                    e.target.closest('tr').remove();
                    updateEarningsJson();
                }
            });
            
            // Update JSON when inputs change
            earningsTbody.addEventListener('input', function(e) {
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
                    const rows = document.querySelectorAll('.earning-row');
                    
                    // Validate that all rows have valid level and reward
                    rows.forEach(row => {
                        const levelInput = row.querySelector('.earnings-level');
                        const rewardInput = row.querySelector('.earnings-reward');
                        const level = parseInt(levelInput.value.trim());
                        const reward = parseFloat(rewardInput.value);
                        
                        if (!level || level < 1) {
                            isValid = false;
                            levelInput.classList.add('is-invalid');
                        } else {
                            levelInput.classList.remove('is-invalid');
                        }
                        
                        if (isNaN(reward) || reward < 0) {
                            isValid = false;
                            rewardInput.classList.add('is-invalid');
                        } else {
                            rewardInput.classList.remove('is-invalid');
                        }
                    });
                    
                    // Check for duplicate levels
                    const levels = Array.from(rows).map(row => parseInt(row.querySelector('.earnings-level').value));
                    const uniqueLevels = new Set(levels);
                    if (uniqueLevels.size !== levels.length) {
                        isValid = false;
                        alert('Duplicate level numbers detected. Each level must be unique.');
                        rows.forEach(row => {
                            const level = parseInt(row.querySelector('.earnings-level').value);
                            if (levels.indexOf(level) !== levels.lastIndexOf(level)) {
                                row.querySelector('.earnings-level').classList.add('is-invalid');
                            }
                        });
                    }
                    
                    if (!isValid || rows.length === 0) {
                        e.preventDefault();
                        alert('Please fill in all level-based earnings with valid values. Levels must be unique positive integers, and rewards must be non-negative numbers.');
                    }
                });
            }
            
            // Add a default row if none exist
            if (earningsTbody.children.length === 0) {
                addEarningRow('1', '10');
            }
            
            // Settings dropdown toggle
            document.getElementById('settings-toggle')?.addEventListener('click', function(e) {
                e.preventDefault();
                const menu = document.getElementById('settings-menu-agent-plans');
                menu.classList.toggle('show');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                const menu = document.getElementById('settings-menu-agent-plans');
                const toggle = document.getElementById('settings-toggle');
                
                if (menu && toggle && !menu.contains(e.target) && e.target !== toggle) {
                    menu.classList.remove('show');
                }
            });

            // Close dropdown when clicking a link or button inside
            const menu = document.getElementById('settings-menu-agent-plans');
            menu.querySelectorAll('a, button').forEach(item => {
                item.addEventListener('click', () => {
                    menu.classList.remove('show');
                });
            });

            // Hamburger menu toggle
            document.querySelector('.hamburger').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
            });
        });
    </script>
</body>
</html>