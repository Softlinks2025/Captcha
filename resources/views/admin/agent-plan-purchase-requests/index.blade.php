<!-- Standalone HTML file with custom styles, no Blade layout or section directives -->
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
            --card-bg: rgba(255, 255, 255, 0.1);
            --text-color: #333;
            --sidebar-bg: rgba(255, 255, 255, 0.95);
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
        .sidebar .nav {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }
        .sidebar .nav-link {
            color: var(--text-color);
            padding: 10px 15px;
            border-radius: var(--border-radius);
            margin-bottom: 10px;
            transition: background 0.3s, color 0.3s;
            display: flex;
            align-items: center;
            text-decoration: none;
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
        .sidebar .nav-item.settings-dropdown { position: relative; }
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
        .hamburger {
            display: none;
            font-size: 2rem;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .settings-dropdown {
            position: relative;
        }
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
        #settings-menu a:hover, #settings-menu button:hover {
            background: #f3f4f6;
            color: var(--primary-color);
        }
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
            .sidebar.active {
                left: 0;
            }
            .main-content {
                padding: 15px;
            }
            .hamburger {
                display: block;
            }
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
        /* Alerts */
        .alert {
            max-width: 1200px;
            margin: 0 auto 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 15px;
            color: white;
            position: relative;
            animation: slideIn 0.5s ease-in;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.95);
            backdrop-filter: blur(10px);
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.95);
            backdrop-filter: blur(10px);
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
        /* Table Card */
        .table-card {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeIn 0.5s ease-in;
        }
        .table-card:hover {
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
            margin: -20px -20px 20px;
        }
        .card-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-color);
        }
        /* Table */
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
        }
        th, td {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        th {
            background: #f1f5f9;
            font-weight: 600;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .btn {
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-approve { background: #22c55e; color: #fff; }
        .btn-approve:hover { background: #16a34a; }
        .btn-reject { background: #ef4444; color: #fff; }
        .btn-reject:hover { background: #b91c1c; }
        .status-pending { color: #f59e0b; font-weight: 600; }
        .status-approved { color: #22c55e; font-weight: 600; }
        .status-rejected { color: #ef4444; font-weight: 600; }
        .note { color: #64748b; font-size: 0.95rem; }
    </style>
</head>
<body>
    <div class="wrapper">
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
                    <a href="{{ route('admin.agent-plan-purchase-requests.index') }}" class="nav-link active">Agent Plan Purchase Requests</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.subadmins.index') }}" class="nav-link">Sub-Admin</a>
                </li>
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
            <div class="hamburger">☰</div>
            <section class="container mx-auto">
                <!-- Header -->
                <div class="header">
                    <h1>
                        <i class="fas fa-clipboard-list mr-2"></i> Agent Plan Purchase Requests
                    </h1>
                </div>
                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                        <span class="close">×</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                        <span class="close">×</span>
                    </div>
                @endif
                <!-- Table Card -->
                <div class="table-card">
                    <div class="card-header">
                        <h2>Requests List</h2>
                    </div>
                    <div class="table-container">
                        <table class="users-table" id="requestsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Agent</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                    <th>Approved/Rejected At</th>
                                    <th>Admin Note</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $req)
                                <tr>
                                    <td>{{ $req->id }}</td>
                                    <td>{{ $req->agent ? $req->agent->name : 'N/A' }}</td>
                                    <td>{{ $req->plan ? $req->plan->name : 'N/A' }}</td>
                                    <td class="status-{{ $req->status }}">{{ ucfirst($req->status) }}</td>
                                    <td>{{ $req->requested_at }}</td>
                                    <td>{{ $req->approved_at ?? $req->rejected_at ?? '-' }}</td>
                                    <td class="note">{{ $req->admin_note ?? '-' }}</td>
                                    <td>
                                        @if($req->status === 'pending')
                                        <form action="{{ route('admin.agent-plan-purchase-requests.approve', $req->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-approve">Approve</button>
                                        </form>
                                        <form action="{{ route('admin.agent-plan-purchase-requests.reject', $req->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <input type="text" name="admin_note" placeholder="Reason (optional)" style="padding:4px 8px;border-radius:6px;border:1px solid #e5e7eb;">
                                            <button type="submit" class="btn btn-reject">Reject</button>
                                        </form>
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($requests->isEmpty())
                    <div style="color:#64748b;">No plan purchase requests found.</div>
                @endif
            </section>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Hamburger menu toggle
        document.querySelector('.hamburger').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        // Settings dropdown toggle
        document.addEventListener('DOMContentLoaded', function() {
            var toggle = document.getElementById('settings-toggle');
            var menu = document.getElementById('settings-menu');
            if (toggle && menu) {
                menu.style.display = 'none';
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (menu.style.display === 'none' || menu.style.display === '') {
                        menu.style.display = 'block';
                    } else {
                        menu.style.display = 'none';
                    }
                });
                document.addEventListener('click', function(e) {
                    if (!menu.contains(e.target) && e.target !== toggle) {
                        menu.style.display = 'none';
                    }
                });
            }
            // Close alerts
            document.querySelectorAll('.alert .close').forEach(button => {
                button.addEventListener('click', () => {
                    button.parentElement.style.display = 'none';
                });
            });
        });
    </script>
</body>
</html> 