<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
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
            --background-color: #f8f9fc;
            --card-bg: rgba(255, 255, 255, 0.1);
            --text-color: #333;
            --sidebar-bg: rgba(255, 255, 255, 0.95);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
        }
        body { font-family: 'Inter', sans-serif; background: var(--background-color); }
        .wrapper { display: flex; min-height: 100vh; position: relative; }
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
        .sidebar .nav-link {
            color: var(--text-color);
            padding: 10px 15px;
            border-radius: var(--border-radius);
            margin-bottom: 10px;
            transition: background 0.3s, color 0.3s;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: var(--primary-color);
            color: white;
        }
        .sidebar .nav-link i { margin-right: 10px; }
        .sidebar .sidebar-header {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }
        .settings-dropdown { position: relative; }
        #settings-menu {
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
            padding: 0.5rem 0;
        }
        #settings-menu:before {
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
        #settings-menu a, #settings-menu button {
            display: block;
            width: 100%;
            background: none;
            border: none;
            text-align: left;
            padding: 12px 24px;
            color: #333;
            font-size: 1rem;
            border-radius: 0;
            transition: background 0.2s;
            text-decoration: none;
        }
        #settings-menu a:hover, #settings-menu button:hover { background: #f3f4f6; color: var(--primary-color); }
        #settings-menu form { margin: 0; }
        .sidebar .nav-item.settings-dropdown { position: relative; }
        @media (max-width: 900px) {
            .sidebar {
                position: fixed;
                left: -250px;
                top: 0;
                height: 100vh;
                transition: left 0.3s;
            }
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .hamburger {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1100;
                cursor: pointer;
                font-size: 1.5rem;
            }
        }
        @media (min-width: 769px) { .hamburger { display: none; } }
        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--background-color);
        }
        .change-password-card {
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            padding: 32px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }
        .change-password-card h2 {
            margin-bottom: 2rem;
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
        }
        .back-btn {
            margin-bottom: 2rem;
            display: inline-flex;
            align-items: center;
            background: linear-gradient(90deg, var(--primary-color), #60a5fa);
            color: #fff;
            border-radius: 8px;
            padding: 8px 18px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s, transform 0.2s;
            font-size: 1rem;
            gap: 8px;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.10);
        }
        .back-btn:hover { 
            background: linear-gradient(90deg, #2563eb, #60a5fa); 
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(59, 130, 246, 0.18);
        }
        .form-label { font-weight: 500; }
        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 12px 14px;
            font-size: 1rem;
            background: #f8fafc;
            box-shadow: none;
            margin-bottom: 15px;
            transition: border 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            border: 1.5px solid var(--primary-color);
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.2);
        }
        .btn-primary {
            width: 100%;
            background: linear-gradient(90deg, var(--primary-color), #60a5fa);
            color: #fff;
            border-radius: 8px;
            padding: 12px 0;
            font-size: 1.1rem;
            font-weight: 600;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.10);
            border: none;
            margin-top: 10px;
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #2563eb, #60a5fa);
            box-shadow: 0 6px 24px rgba(59, 130, 246, 0.3);
            transform: translateY(-2px);
        }
        .alert {
            max-width: 400px;
            margin: 0 auto 20px;
            border-radius: var(--border-radius);
            padding: 15px;
            position: relative;
            animation: slideIn 0.5s ease-in;
        }
        .alert-success {
            background: rgba(28, 200, 138, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            color: white;
        }
        .alert-danger {
            background: rgba(220, 53, 69, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            color: white;
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
        .alert .close:hover { transform: scale(1.2); }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @media (max-width: 600px) {
            .change-password-card {
                width: 90vw;
                max-width: 450px;
                padding: 20px;
            }
            .main-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Hamburger Menu -->
        <i class="fas fa-bars hamburger" style="position: fixed; top: 20px; left: 20px; z-index: 1100; cursor: pointer; font-size: 1.5rem;"></i>
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
                <li class="nav-item">
    <!--                <a href="{{ route('admin.agent-plan-purchase-requests.index') }}" class="nav-link">Agent Plan Purchase Requests</a>
-->                </li>
                <li class="nav-item settings-dropdown">
                    <a href="#" class="nav-link" id="settings-toggle">Settings</a>
                    <div id="settings-menu">
                        <a href="{{ route('admin.change-password') }}" class="d-block">Change Password</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-danger">Logout</button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- Main Content -->
        <div class="main-content">
            <a href="{{ route('admin.dashboard') }}" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
            <div class="change-password-card">
                <h2 class="mb-4 text-center">Change Password</h2>
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                        <span class="close">×</span>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @php $allErrors = $errors->all(); @endphp
                            @if(is_array($allErrors))
                                @foreach($allErrors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            @else
                                <li>{{ $allErrors }}</li>
                            @endif
                        </ul>
                        <span class="close">×</span>
                    </div>
                @endif
                <form method="POST" action="{{ route('admin.change-password.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hamburger menu toggle
            document.querySelector('.hamburger').addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
            });

            // Settings dropdown toggle
            var toggle = document.getElementById('settings-toggle');
            var menu = document.getElementById('settings-menu');
            if (toggle && menu) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!menu.contains(e.target) && e.target !== toggle) {
                        menu.style.display = 'none';
                    }
                });

                // Close dropdown when clicking a link or button inside
                menu.querySelectorAll('a, button').forEach(item => {
                    item.addEventListener('click', () => {
                        menu.style.display = 'none';
                    });
                });
            }

            // Close alert
            document.querySelectorAll('.alert .close').forEach(button => {
                button.addEventListener('click', () => {
                    button.parentElement.style.display = 'none';
                });
            });
        });
    </script>
</body>
</html>