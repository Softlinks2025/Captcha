<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --error-color: #dc3545;
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
            margin: 0;
            padding: 0;
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
            display: flex;
            align-items: center;
            text-decoration: none !important;
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
            min-width: 180px;
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
            overflow-x: hidden;
        }

        .container.mx-auto {
            max-width: 100%;
            padding: 0;
            margin: 0;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-in;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .header-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .header-buttons a {
            white-space: nowrap;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            color: white;
            border-radius: var(--border-radius);
            padding: 12px 24px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
            animation: pulse 2s infinite;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
            animation: none;
        }

        .btn-info {
            background: linear-gradient(90deg, var(--info-color), #2dd4bf);
            color: white;
            border-radius: var(--border-radius);
            padding: 8px 16px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(20, 184, 166, 0.3);
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

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
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

        .agents-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
        }

        .agents-table th,
        .agents-table td {
            padding: 14px;
            text-align: left;
            font-size: 0.9rem;
        }

        /* Filter Form Styles */
        .filters-container {
            margin-top: 1rem;
            width: 100%;
        }

        .filter-form {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 1rem;
        }

        .filter-group {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
            width: 100%;
        }

        .filter-input {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: #f9fafb;
            font-size: 0.875rem;
            line-height: 1.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            flex: 1;
            max-width: 200px;
        }

        .filter-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }

        .filter-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .filter-button:hover {
            background-color: #4338ca;
        }

        .clear-filters {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            transition: background-color 0.2s, color 0.2s;
        }

        .clear-filters:hover {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        .agents-table th {
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            font-size: 0.75rem;
            background: rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .agents-table th.sortable::after {
            content: '↕';
            position: absolute;
            right: 8px;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .agents-table tbody tr {
            transition: background 0.3s ease, transform 0.2s ease;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.92));
        }

        .agents-table tbody tr:nth-child(even) {
            background: linear-gradient(to right, rgba(245, 245, 245, 0.98), rgba(245, 245, 245, 0.92));
        }

        .agents-table tbody tr:hover {
            background: rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: var(--border-radius);
            font-size: 0.8rem;
            font-weight: 500;
            transition: transform 0.2s ease;
        }

        .badge-success {
            background: var(--success-color);
            color: white;
        }

        .badge-warning {
            background: var(--warning-color);
            color: white;
        }

        .badge:hover {
            transform: scale(1.05);
        }

        /* Buttons */
        .btn-primary-sm, .btn-info-sm, .btn-danger-sm {
            padding: 8px 16px;
            font-size: 0.85rem;
        }

        .btn-danger {
            background: linear-gradient(90deg, var(--error-color), #f87171);
            color: #fff;
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

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
        }

        .action-group {
            display: flex;
            gap: 10px;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 16px;
            border-radius: var(--border-radius);
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            color: var(--text-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: transform 0.2s, box-shadow 0.3s, background 0.3s;
        }

        .pagination a:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }

        .pagination .current {
            background: var(--primary-color);
            color: white;
            font-weight: 600;
        }

        /* DataTable Search */
        .dataTables_filter {
            margin-bottom: 15px;
        }

        .dataTables_filter input {
            padding: 10px 40px 10px 16px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            color: var(--text-color);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .dataTables_filter input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }

        .dataTables_filter label {
            position: relative;
        }

        .dataTables_filter label::after {
            content: '\f002';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 1rem;
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
                position: fixed;
                height: 100vh;
                overflow-y: auto;
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

            .table-card, .alert {
                max-width: 100%;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header h1 {
                font-size: 1.5rem;
                margin-bottom: 0;
            }

            .header-buttons {
                flex-direction: column;
                width: 100%;
                align-items: stretch;
                gap: 10px;
            }

            .header-buttons a {
                width: 100%;
                justify-content: center;
                padding: 12px 20px;
                font-size: 0.9rem;
            }

            .action-group {
                flex-direction: column;
                gap: 8px;
            }

            .agents-table {
                min-width: auto;
                font-size: 0.8rem;
            }

            .agents-table th,
            .agents-table td {
                padding: 8px 6px;
            }

            .row.mb-4 {
                margin: 0;
                gap: 15px !important;
            }

            .col-lg-3, .col-md-6, .col-12 {
                padding: 0;
                margin-bottom: 15px;
            }

            .card {
                margin: 0;
                border-radius: 12px !important;
            }

            .card-body {
                padding: 15px;
            }

            .card-title {
                font-size: 1rem !important;
            }

            .card-body div {
                font-size: 1.5rem !important;
            }
        }

        @media (min-width: 769px) {
            .hamburger {
                display: none;
            }
        }

        @media (max-width: 1024px) and (min-width: 769px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header-buttons {
                flex-wrap: wrap;
                gap: 10px;
            }

            .header-buttons a {
                flex: 1;
                min-width: 150px;
                justify-content: center;
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
                <a href="{{ route('admin.agents.index') }}" class="nav-link active">Agents</a>
                <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
                <a href="{{ route('admin.subscription_plans.index') }}" class="nav-link">Subscription Plans</a>
                <a href="{{ route('admin.agent-plans.index') }}" class="nav-link">Agent Plans</a>
                <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link">Withdrawal Requests</a>
                <a href="{{ route('admin.push-notification.index') }}" class="nav-link">Push Notification</a>
                <a href="{{ route('admin.tickets.index') }}" class="nav-link">Tickets</a>
<!--                <a href="{{ route('admin.agent-plan-purchase-requests.index') }}" class="nav-link">Agent Plan Purchase Requests</a>
-->                <a href="{{ route('admin.subadmins.index') }}" class="nav-link">Sub-Admin</a>
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
                    <h1>Manage Agents</h1>
                    <div class="header-buttons">
                        <a href="{{ route('admin.agents.create') }}" class="btn-primary">
                            <i class="fas fa-plus mr-2"></i> Add New Agent
                        </a>
                        <a href="{{ route('admin.agents.export-csv', array_filter(['status' => request('status')])) }}" class="btn-info">
                            <i class="fas fa-file-csv mr-2"></i> Export CSV
                        </a>
                    </div>
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

                <!-- Agent Analytics Cards -->
                <div class="row mb-4" style="gap: 20px;">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card shadow-sm{{ empty($status) ? ' active' : '' }}" style="border-radius: 12px; background: #e0e7ff; cursor:pointer;" onclick="window.location='{{ route('admin.agents.index') }}'">
                            <div class="card-body text-center">
                                <h5 class="card-title mb-2" style="font-weight: 600;">Total Agents</h5>
                                <div style="font-size: 2rem; font-weight: bold; color: #4e73df;">{{ $totalAgents ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card shadow-sm{{ $status === 'active' ? ' active' : '' }}" style="border-radius: 12px; background: #d1fae5; cursor:pointer;" onclick="window.location='{{ route('admin.agents.index', ['status' => 'active']) }}'">
                            <div class="card-body text-center">
                                <h5 class="card-title mb-2" style="font-weight: 600;">Active Agents</h5>
                                <div style="font-size: 2rem; font-weight: bold; color: #1cc88a;">{{ $activeAgents ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card shadow-sm{{ $status === 'inactive' ? ' active' : '' }}" style="border-radius: 12px; background: #fde68a; cursor:pointer;" onclick="window.location='{{ route('admin.agents.index', ['status' => 'inactive']) }}'">
                            <div class="card-body text-center">
                                <h5 class="card-title mb-2" style="font-weight: 600;">Inactive Agents</h5>
                                <div style="font-size: 2rem; font-weight: bold; color: #f6c23e;">{{ $inactiveAgents ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="table-card">
                    <div class="card-header">
                        <h2>Agents List</h2>
                        <div class="filters-container">
                            <form action="{{ route('admin.agents.index') }}" method="GET" class="filter-form">
                                <div class="filter-group">
                                    <input type="text" name="name" placeholder="Search by name" value="{{ request('name') }}" class="filter-input">
                                    <input type="text" name="phone" placeholder="Search by phone" value="{{ request('phone') }}" class="filter-input">
                                    <button type="submit" class="filter-button">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    @if(request()->has('name') || request()->has('phone'))
                                        <a href="{{ route('admin.agents.index') }}" class="clear-filters">
                                            <i class="fas fa-times"></i> Clear Filters
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="agents-table" id="agentsTable">
                            <thead>
                                <tr>
                                    <th class="sortable">S.No</th>
                                    <th class="sortable">Agent Name</th>
                                    <th class="sortable">Date of Joining</th>
                                    <th class="sortable">Total Earning</th>
                                    <th class="sortable">Total Withdrawal</th>
                                    <th class="sortable">Balance</th>
                                    <th class="sortable">Phone Number</th>
                                    <th class="sortable">Address</th>
                                    <th class="sortable">Referral Code</th>
                                    <th>Joining Fee Status</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $startNumber = ($agents->currentPage() - 1) * $agents->perPage() + 1;
                                @endphp
                                @foreach ($agents as $agent)
                                    <tr>
                                        <td>{{ $startNumber + $loop->index }}</td>
                                        <td>{{ $agent->name }}</td>
                                        <td>{{ $agent->created_at->format('d/m/Y') }}</td>
                                        <td>₹{{ number_format($agent->total_earnings ?? 0, 2) }}</td>
                                        <td>₹{{ number_format($agent->total_withdrawals ?? 0, 2) }}</td>
                                        <td>₹{{ number_format($agent->wallet_balance ?? 0, 2) }}</td>
                                        <td>{{ $agent->phone_number }}</td>
                                        <td>{{ $agent->address }}</td>
                                        <td>{{ $agent->referral_code }}</td>
                                        <td>
                                            @if($agent->joining_fee_paid)
                                                <span class="badge badge-success">Paid</span>
                                            @else
                                                <span class="badge badge-warning">Not Paid</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $now = \Carbon\Carbon::now();
                                                $isNewAgent = $agent->created_at->gt($now->copy()->subDay());
                                                $isRecentlyActive = $agent->last_login_at && $agent->last_login_at->gt($now->copy()->subDays(3));
                                                $isActive = $isNewAgent || $isRecentlyActive;
                                            @endphp
                                            @if($isActive)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-warning">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <a href="{{ route('admin.agents.show', $agent) }}" class="btn-info btn-info-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.agents.edit', $agent) }}" class="btn-primary btn-primary-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.agents.destroy', $agent) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this agent?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-danger btn-danger-sm" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    {{ $agents->links('vendor.pagination.custom') }}
                </div>
            </section>
        </div>
    </div>

    <script>
        // DataTable and alert handling
        $(document).ready(function() {
            // Initialize DataTable
            if ($.fn.DataTable.isDataTable('#agentsTable')) {
                $('#agentsTable').DataTable().destroy();
            }

            $('#agentsTable').DataTable({
                responsive: true,
                order: [[3, 'desc']], // Sort by the 4th column (index 3) which is 'Created At' in descending order
                pageLength: 25,
                dom: '<"top"f>rt<"bottom"lip><"clear">',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search agents...",
                },
                columnDefs: [
                    { orderable: false, targets: [0] } // Make the first column (checkbox) not sortable
                ]
            });

            // Close alerts
            document.querySelectorAll('.alert .close').forEach(button => {
                button.addEventListener('click', () => {
                    button.parentElement.style.display = 'none';
                });
            });

            // Hamburger menu toggle
            document.querySelector('.hamburger').addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
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