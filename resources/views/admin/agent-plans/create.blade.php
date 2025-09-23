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
        .sidebar ul {
            list-style: none;
            padding-left: 0;
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Create New Agent Plan</h2>
                <a href="{{ route('admin.agent-plans.index') }}" class="btn btn-secondary">Back to Plans</a>
            </div>
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.agent-plans.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Plan Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="price" class="form-label">Price *</label>
                                <input type="number" name="price" id="price" step="0.01" min="0" required class="form-control" value="{{ old('price') }}">
                            </div>
                            <div class="col-md-6">
                                <input type="hidden" name="duration" value="lifetime">
                                <label class="form-label">Duration</label>
                                <input type="text" class="form-control" value="Lifetime" readonly>
                                <small class="form-text text-muted">All agent plans are lifetime duration</small>
                            </div>
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="icon" class="form-label">Icon (Upload Image)</label>
                                <input type="file" name="icon" id="icon" accept="image/*" class="form-control">
                                <small class="text-muted">Upload an image for the plan icon</small>
                            </div>
                            <div class="col-md-12 mt-4">
                                <h5>Earning Ranges</h5>
                                <div id="earning-ranges-list">
                                    <!-- JS will add earning range rows here -->
                                </div>
                                <button type="button" class="btn btn-info mt-2" onclick="addEarningRange()">Add Earning Range</button>
                            </div>
                            <div class="col-md-12 mt-4">
                                <h5>Bonuses</h5>
                            </div>
                            <div class="col-md-4">
                                <label for="bonus_10_logins" class="form-label">Bonus at 10 logins</label>
                                <input type="text" name="bonus_10_logins" id="bonus_10_logins" value="{{ old('bonus_10_logins') }}" placeholder="e.g., Cap" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="bonus_50_logins" class="form-label">Bonus at 50 logins</label>
                                <input type="text" name="bonus_50_logins" id="bonus_50_logins" value="{{ old('bonus_50_logins') }}" placeholder="e.g., T-shirt" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="bonus_100_logins" class="form-label">Bonus at 100 logins</label>
                                <input type="text" name="bonus_100_logins" id="bonus_100_logins" value="{{ old('bonus_100_logins') }}" placeholder="e.g., Bag" class="form-control">
                            </div>
                            <div class="col-md-12 mt-4">
                                <h5>Withdrawal Settings</h5>
                            </div>
                            <div class="col-md-6">
                                <label for="min_withdrawal" class="form-label">Minimum Withdrawal (₹) *</label>
                                <input type="number" name="min_withdrawal" id="min_withdrawal" value="{{ old('min_withdrawal', 250.00) }}" step="0.01" min="0" required class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="max_withdrawal" class="form-label">Maximum Withdrawal (₹)</label>
                                <input type="number" name="max_withdrawal" id="max_withdrawal" value="{{ old('max_withdrawal') }}" step="0.01" min="0" class="form-control">
                                <small class="text-muted">Leave empty for unlimited</small>
                            </div>
                            <div class="col-md-12">
                                <label for="withdrawal_time" class="form-label">Withdrawal Time *</label>
                                <input type="text" name="withdrawal_time" id="withdrawal_time" value="{{ old('withdrawal_time', 'Monday to Saturday 9:00AM to 18:00PM') }}" required class="form-control">
                            </div>
                            <div class="col-md-12 mt-4">
                                <h5>Features</h5>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="unlimited_earning" id="unlimited_earning" value="1" {{ old('unlimited_earning', true) ? 'checked' : '' }} class="form-check-input">
                                    <label for="unlimited_earning" class="form-check-label">Unlimited Earning</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="unlimited_logins" id="unlimited_logins" value="1" {{ old('unlimited_logins') ? 'checked' : '' }} class="form-check-input">
                                    <label for="unlimited_logins" class="form-check-label">Unlimited Logins</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="max_logins_per_day" class="form-label">Maximum Logins Per Day</label>
                                <input type="text" name="max_logins_per_day" id="max_logins_per_day" value="{{ old('max_logins_per_day') }}" class="form-control" placeholder="Enter a number or 'unlimited'">
                                <small class="text-muted">Leave empty if unlimited logins is enabled</small>
                            </div>
                            <div class="col-md-12 mt-4">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="form-check-input">
                                    <label for="is_active" class="form-check-label">Active Plan</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.agent-plans.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Plan</button>
                        </div>
                    </form>
                </div>
            </div>
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

        // Add earning range dynamically
        function addEarningRange(min = '', max = '', rate = '') {
            const list = document.getElementById('earning-ranges-list');
            const idx = list.children.length;
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2';
            row.innerHTML = `
                <div class="col-md-3">
                    <input type="number" name="earning_ranges[${idx}][min]" placeholder="Min" class="form-control" value="${min}" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="earning_ranges[${idx}][max]" placeholder="Max or 'unlimited'" class="form-control" value="${max}" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="earning_ranges[${idx}][rate]" placeholder="Rate" class="form-control" value="${rate}" required>
                </div>
                <div class="col-md-3 d-flex align-items-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">Remove</button>
                </div>
            `;
            list.appendChild(row);
        }

        // Add three default earning range rows
        if (document.getElementById('earning-ranges-list').children.length === 0) {
            addEarningRange(1, 50, '');
            addEarningRange(51, 100, '');
            addEarningRange(101, 'unlimited', '');
        }
    </script>
</body>
</html>