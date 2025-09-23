<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets</title>
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
            --total-card-start: #e0e7ff;
            --total-card-end: #c7d2fe;
            --resolved-card-start: #d1fae5;
            --resolved-card-end: #a7f3d0;
            --pending-card-start: #fef3c7;
            --pending-card-end: #fde68a;
            --total-value: #4e73df;
            --resolved-value: #1cc88a;
            --pending-value: #f6c23e;
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

        /* Alerts */
        .alert-success, .alert-danger {
            max-width: 1200px;
            margin: 0 auto 20px;
            padding: 15px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            position: relative;
            animation: slideIn 0.5s ease-in;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.95);
            backdrop-filter: blur(10px);
            color: white;
        }

        .alert-success .close, .alert-danger .close {
            position: absolute;
            top: 15px;
            right: 15px;
            color: white;
            cursor: pointer;
            font-size: 1rem;
            transition: transform 0.2s;
        }

        .alert-success .close:hover, .alert-danger .close:hover {
            transform: scale(1.2);
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
        }

        /* Summary Cards */
        .summary-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeIn 0.5s ease-in;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .summary-card.total {
            background: linear-gradient(90deg, var(--total-card-start), var(--total-card-end));
        }

        .summary-card.resolved {
            background: linear-gradient(90deg, var(--resolved-card-start), var(--resolved-card-end));
        }

        .summary-card.pending {
            background: linear-gradient(90deg, var(--pending-card-start), var(--pending-card-end));
        }

        .summary-card h5 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .summary-card .value {
            font-size: 2rem;
            font-weight: 700;
        }

        .summary-card.total .value {
            color: var(--total-value);
        }

        .summary-card.resolved .value {
            color: var(--resolved-value);
        }

        .summary-card.pending .value {
            color: var(--pending-value);
        }

        /* Tickets Card */
        .tickets-card {
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

        .tickets-card:hover {
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
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-color);
            text-align: center;
        }

        /* Tickets Table */
        .tickets-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
        }

        .tickets-table th,
        .tickets-table td {
            padding: 14px;
            text-align: left;
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .tickets-table th {
            font-weight: 500;
            color: #6b7280;
            background: rgba(0, 0, 0, 0.05);
        }

        .tickets-table td {
            font-weight: 600;
            color: var(--text-color);
        }

        .tickets-table tbody tr {
            transition: background 0.3s ease, transform 0.2s ease;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.92));
        }

        .tickets-table tbody tr:nth-child(even) {
            background: linear-gradient(to right, rgba(245, 245, 245, 0.98), rgba(245, 245, 245, 0.92));
        }

        .tickets-table tbody tr:hover {
            background: rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 0.8rem;
            font-weight: 500;
            color: white;
            transition: transform 0.2s ease;
            backdrop-filter: blur(10px);
        }

        .badge:hover {
            transform: scale(1.1);
        }

        .badge-user {
            background: rgba(59, 130, 246, 0.95);
        }

        .badge-agent {
            background: rgba(20, 184, 166, 0.95);
        }

        .badge-resolved {
            background: rgba(34, 197, 94, 0.95);
        }

        .badge-pending {
            background: rgba(245, 158, 11, 0.95);
        }

        .text-muted {
            color: #6b7280;
            font-style: italic;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            color: white;
            border-radius: var(--border-radius);
            padding: 8px 16px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }

        .btn-resolved {
            background: linear-gradient(90deg, var(--success-color), #4ade80);
        }

        .btn-resolved:hover {
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.3);
        }

        .btn-pending {
            background: linear-gradient(90deg, var(--warning-color), #fbbf24);
        }

        .btn-pending:hover {
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.3);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination a, .pagination span {
            padding: 8px 16px;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.3s;
            backdrop-filter: blur(10px);
        }

        .pagination a {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            color: white;
        }

        .pagination a:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }

        .pagination span {
            background: rgba(107, 114, 128, 0.95);
            color: white;
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

            .summary-card,
            .tickets-card,
            .alert-success,
            .alert-danger {
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
            @php $user = auth()->user(); @endphp
            @if($user->hasRole('admin'))
                <a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>
                <a href="{{ route('admin.agents.index') }}" class="nav-link">Agents</a>
                <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
                <a href="{{ route('admin.subscription_plans.index') }}" class="nav-link">Subscription Plans</a>
                <a href="{{ route('admin.agent-plans.index') }}" class="nav-link">Agent Plans</a>
                <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link">Withdrawal Requests</a>
                <a href="{{ route('admin.push-notification.index') }}" class="nav-link">Push Notification</a>
                <a href="{{ route('admin.subadmins.index') }}" class="nav-link">Sub-Admin</a>

                @elseif($user->hasRole('ticket-manager'))
              <a href="{{ route('admin.tickets.index') }}" class="nav-link active">Tickets</a>
<!--                <a href="{{ route('admin.agent-plan-purchase-requests.index') }}" class="nav-link">Agent Plan Purchase Requests</a>-->
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
                    <h1>Support Tickets</h1>
                    <a href="{{ route('admin.tickets.export-csv', array_filter(['status' => request('status')])) }}" class="btn-primary">
                        <i class="fas fa-file-csv mr-2"></i> Export CSV
                    </a>
                </div>

                <!-- Success Message -->
                @if(session('success'))
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <span class="close">×</span>
                    </div>
                @endif

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <span class="close">×</span>
                    </div>
                @endif

                <!-- Summary Cards -->
                <div class="analytics-cards" style="display: flex; gap: 24px; margin-bottom: 32px; flex-wrap: wrap;">
                    <div class="summary-card total{{ empty($status) ? ' active' : '' }}" style="flex: 1; min-width: 220px; cursor:pointer;" onclick="window.location='{{ route('admin.tickets.index') }}'">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 12px;">
                            <i class="fas fa-ticket-alt fa-2x" style="color: var(--primary-color);"></i>
                            <div>
                                <h5>Total Tickets</h5>
                                <div class="value">{{ $totalTickets ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="summary-card resolved{{ $status === 'resolved' ? ' active' : '' }}" style="flex: 1; min-width: 220px; cursor:pointer;" onclick="window.location='{{ route('admin.tickets.index', ['status' => 'resolved']) }}'">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 12px;">
                            <i class="fas fa-check-circle fa-2x" style="color: var(--success-color);"></i>
                            <div>
                                <h5>Resolved</h5>
                                <div class="value">{{ $resolvedTickets ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="summary-card pending{{ $status === 'pending' ? ' active' : '' }}" style="flex: 1; min-width: 220px; cursor:pointer;" onclick="window.location='{{ route('admin.tickets.index', ['status' => 'pending']) }}'">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 12px;">
                            <i class="fas fa-hourglass-half fa-2x" style="color: var(--warning-color);"></i>
                            <div>
                                <h5>Pending</h5>
                                <div class="value">{{ $pendingTickets ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets Card -->
                <div class="tickets-card">
                    <div class="card-header">
                        <h2>Tickets</h2>
                    </div>
                    <div class="table-container">
                        <table class="tickets-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>User/Agent</th>
                                    <th>Phone Number</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tickets as $ticket)
                                    <tr>
                                        <td>{{ $ticket->id }}</td>
                                        <td>{{ $ticket->name }}</td>
                                        <td>{{ $ticket->description }}</td>
                                        <td>
                                            @if($ticket->user)
                                                <span class="badge badge-user">User: {{ $ticket->user->name }}</span>
                                            @elseif($ticket->agent)
                                                <span class="badge badge-agent">Agent: {{ $ticket->agent->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->user && $ticket->user->phone)
                                                {{ $ticket->user->phone }}
                                            @elseif($ticket->agent && $ticket->agent->phone_number)
                                                {{ $ticket->agent->phone_number }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->status === 'resolved')
                                                <span class="badge badge-resolved">Resolved</span>
                                            @else
                                                <span class="badge badge-pending">Pending</span>
                                            @endif
                                        </td>
                                        <td>{{ $ticket->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.tickets.update', $ticket->id) }}">
                                                @csrf
                                                @method('PUT')
                                                @if($ticket->status === 'pending')
                                                    <input type="hidden" name="status" value="resolved">
                                                    <button class="btn btn-resolved btn-sm">
                                                        <i class="fas fa-check mr-1"></i> Mark Resolved
                                                    </button>
                                                @else
                                                    <input type="hidden" name="status" value="pending">
                                                    <button class="btn btn-pending btn-sm">
                                                        <i class="fas fa-clock mr-1"></i> Mark Pending
                                                    </button>
                                                @endif
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination">
                        {{ $tickets->links() }}
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
    </script>
</body>
</html>