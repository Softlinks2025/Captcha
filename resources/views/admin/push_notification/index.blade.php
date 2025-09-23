<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Push Notification</title>
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
            align-items: center;
        }

        /* Header */
        .header {
            width: 100%;
            max-width: 540px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .notification-count-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(59,130,246,0.07);
            padding: 12px 28px;
            font-size: 1.1rem;
            color: #2563eb;
            font-weight: 600;
            margin-bottom: 8px;
            display: inline-block;
        }

        /* Alerts */
        .alert-success, .alert-danger {
            width: 100%;
            max-width: 540px;
            margin: 0 auto 18px auto;
            border-radius: 10px;
            padding: 16px 24px;
            font-size: 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(59,130,246,0.07);
            position: relative;
        }

        .alert-success {
            background: linear-gradient(90deg, #d1fae5 0%, #bbf7d0 100%);
            color: #166534;
            border: 1.5px solid #22c55e;
        }

        .alert-danger {
            background: linear-gradient(90deg, #fee2e2 0%, #fecaca 100%);
            color: #b91c1c;
            border: 1.5px solid #ef4444;
        }

        .alert-success .close, .alert-danger .close {
            position: absolute;
            right: 18px;
            top: 16px;
            font-size: 1.2rem;
            color: #64748b;
            cursor: pointer;
            background: none;
            border: none;
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
        }

        /* Form Card */
        .form-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(30,41,59,0.09);
            padding: 36px 32px 28px 32px;
            width: 100%;
            max-width: 720px;
            margin: 0 auto 32px auto;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .form-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .card-header {
            padding: 15px;
            background: linear-gradient(90deg, var(--header-bg-start), var(--header-bg-end));
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            margin: -24px -24px 20px;
        }

        .card-header h2 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2563eb;
            margin-bottom: 18px;
            letter-spacing: 0.5px;
        }

        .form-card form {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 18px;
        }

        .form-group {
            width: 100%;
            display: flex;
            flex-direction: column;
            margin-bottom: 10px;
        }

        .form-group label {
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
            font-size: 1rem;
            color: #1e293b;
            margin-bottom: 4px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px #2563eb33;
            background: #fff;
        }

        .form-group .icon {
            position: absolute;
            right: 12px;
            top: 38px;
            color: #6b7280;
            font-size: 1rem;
        }

        .form-group .error {
            color: #dc2626;
            font-size: 0.9rem;
            margin-top: 2px;
        }

        /* Button */
        .btn-primary {
            background: linear-gradient(90deg, #2563eb, #60a5fa);
            color: white;
            border-radius: 10px;
            padding: 14px 0;
            font-weight: 600;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(59,130,246,0.08);
        }

        .btn-primary:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 6px 24px rgba(59,130,246,0.13);
        }

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

        /* Enhanced Push Notification Form Styles */
        .enhanced-form-card {
            max-width: 520px;
            margin: 40px auto 32px auto;
            padding: 40px 32px 32px 32px;
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(59,130,246,0.13), 0 1.5px 8px rgba(30,41,59,0.07);
            background: linear-gradient(120deg, #f8fafc 60%, #e0e7ff 100%);
            display: flex;
            flex-direction: column;
            gap: 24px;
            animation: fadeIn 0.7s;
        }

        .enhanced-card-header {
            background: linear-gradient(90deg, #2563eb0d 0%, #60a5fa0d 100%);
            border-radius: 18px 18px 0 0;
            margin: -40px -32px 18px -32px;
            padding: 22px 0 18px 0;
            text-align: center;
        }

        .enhanced-input, .enhanced-input-file {
            width: 100%;
            box-sizing: border-box;
        }

        /* Responsive Design */
        @media (max-width: 700px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 12px;
            }

            .hamburger {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1100;
                cursor: pointer;
                font-size: 1.5rem;
                color: var(--text-color);
            }

            .form-card, .header, .alert-success, .alert-danger {
                max-width: 100%;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .enhanced-form-card {
                max-width: 98vw;
                padding: 18px 4vw 18px 4vw;
                margin: 18px 0 18px 0;
            }

            .enhanced-card-header {
                margin: -18px -4vw 12px -4vw;
                padding: 16px 0 12px 0;
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
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>
                    <a href="{{ route('admin.agents.index') }}" class="nav-link">Agents</a>
                    <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
                    <a href="{{ route('admin.subscription_plans.index') }}" class="nav-link">Subscription Plans</a>
                    <a href="{{ route('admin.agent-plans.index') }}" class="nav-link">Agent Plans</a>
                    <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link">Withdrawal Requests</a>
                    <a href="{{ route('admin.push-notification.index') }}" class="nav-link active">Push Notification</a>
                    <a href="{{ route('admin.tickets.index') }}" class="nav-link">Tickets</a>
    <!--                <a href="{{ route('admin.agent-plan-purchase-requests.index') }}" class="nav-link">Agent Plan Purchase Requests</a>
-->                    <a href="{{ route('admin.subadmins.index') }}" class="nav-link">Sub-Admin</a>
                @elseif($user->hasRole('push-subadmin'))
                    <a href="{{ route('admin.push-notification.index') }}" class="nav-link active">Push Notification</a>
                @elseif($user->hasRole('withdrawal-subadmin'))
                    <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link">Withdrawal Requests</a>
                @endif
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
                    <h1><i class="fas fa-bell"></i> Push Notification</h1>
                    <div class="notification-count-card">
                        <i class="fas fa-paper-plane"></i> Total Notifications Sent: <span style="color:#1e293b;">{{ \App\Models\PushNotification::count() }}</span>
                    </div>
                </div>

                <!-- Success Message -->
                @if(session('success'))
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button class="close" onclick="this.parentElement.style.display='none';">×</button>
                    </div>
                @endif

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert-danger">
                        <ul style="margin:0; padding-left:18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button class="close" onclick="this.parentElement.style.display='none';">×</button>
                    </div>
                @endif

                <!-- Form Card -->
                <div class="form-card enhanced-form-card">
                    <div class="card-header enhanced-card-header">
                        <h2>Send Notification</h2>
                    </div>
                    <form method="POST" action="{{ route('admin.push-notification.send') }}" enctype="multipart/form-data" id="pushForm">
                        @csrf
                        <div class="form-group enhanced-form-group">
                            <label for="recipient_type" class="enhanced-label">Recipient Type <span style="color:#ef4444;">*</span></label>
                            <select name="recipient_type" id="recipient_type" required class="enhanced-input" onchange="handleRecipientTypeChange()">
                                <option value="user">All Users</option>
                                <option value="agent">All Agents</option>
                                <option value="both">Both</option>
                                <!--<option value="individual">Individual</option>-->
                            </select>
                        </div>
                        <div class="form-group enhanced-form-group">
                            <label for="title" class="enhanced-label">Title <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="title" id="title" required maxlength="255" placeholder="Enter notification title" class="enhanced-input">
                        </div>
                        <div class="form-group enhanced-form-group">
                            <label for="message" class="enhanced-label">Message <span style="color:#ef4444;">*</span></label>
                            <textarea name="message" id="message" rows="3" required maxlength="1000" placeholder="Enter your message here..." class="enhanced-input"></textarea>
                        </div>
                        <div class="form-group enhanced-form-group">
                            <label for="image" class="enhanced-label">Image (optional)</label>
                            <input type="file" name="image" id="image" accept="image/*" class="enhanced-input-file">
                        </div>
                        <button type="submit" class="btn-primary enhanced-btn-primary"><i class="fas fa-paper-plane"></i> Send Notification</button>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script>
        // Hamburger menu toggle
        document.querySelector('.hamburger').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close alerts
        document.querySelectorAll('.alert-success .close, .alert-danger .close').forEach(button => {
            button.addEventListener('click', () => {
                button.parentElement.style.display = 'none';
            });
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

        // Form submission handling
        var pushForm = document.getElementById('pushForm');
        if (pushForm) {
            pushForm.addEventListener('submit', function(e) {
                // Placeholder for form submission logic
            });
        }

        function handleRecipientTypeChange() {
            var type = document.getElementById('recipient_type').value;
            var individualTypeGroup = document.getElementById('individual-type-group');
            var individualUserGroup = document.getElementById('individual-user-group');
            var individualAgentGroup = document.getElementById('individual-agent-group');
            if (individualTypeGroup) individualTypeGroup.style.display = (type === 'individual') ? '' : 'none';
            if (individualUserGroup) individualUserGroup.style.display = 'none';
            if (individualAgentGroup) individualAgentGroup.style.display = 'none';
        }

        function handleIndividualTypeChange() {
            var type = document.getElementById('individual_type').value;
            var individualUserGroup = document.getElementById('individual-user-group');
            var individualAgentGroup = document.getElementById('individual-agent-group');
            if (individualUserGroup) individualUserGroup.style.display = (type === 'user') ? '' : 'none';
            if (individualAgentGroup) individualAgentGroup.style.display = (type === 'agent') ? '' : 'none';
        }

        var recipientType = document.getElementById('recipient_type');
        if (recipientType) {
            recipientType.addEventListener('change', handleRecipientTypeChange);
        }
        var individualType = document.getElementById('individual_type');
        if (individualType) {
            individualType.addEventListener('change', handleIndividualTypeChange);
        }
        var userSearch = document.getElementById('user_search');
        if (userSearch) {
            userSearch.addEventListener('input', function() {
                var q = this.value;
                var select = document.getElementById('individual_user_id');
                if (q.length < 2) { select.innerHTML = ''; return; }
                fetch('/admin/push-notification/search-users?q=' + encodeURIComponent(q))
                    .then(res => res.json())
                    .then(data => {
                        select.innerHTML = '';
                        data.forEach(function(user) {
                            var opt = document.createElement('option');
                            opt.value = user.id;
                            opt.textContent = user.text;
                            select.appendChild(opt);
                        });
                    });
            });
        }
        var agentSearch = document.getElementById('agent_search');
        if (agentSearch) {
            agentSearch.addEventListener('input', function() {
                var q = this.value;
                var select = document.getElementById('individual_agent_id');
                if (q.length < 2) { select.innerHTML = ''; return; }
                fetch('/admin/push-notification/search-agents?q=' + encodeURIComponent(q))
                    .then(res => res.json())
                    .then(data => {
                        select.innerHTML = '';
                        data.forEach(function(agent) {
                            var opt = document.createElement('option');
                            opt.value = agent.id;
                            opt.textContent = agent.text;
                            select.appendChild(opt);
                        });
                    });
            });
        }
    </script>
</body>
</html>