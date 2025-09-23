<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Requests</title>
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
            font-size: 1rem;
            font-weight: 500;
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
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .export-csv-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.65rem 1.5rem;
            background-color: #1cc88a;
            color: white;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
            height: 40px;
            min-width: 150px;
        }
        
        .export-csv-btn:hover {
            background-color: #17a673;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }

        .btn-info {
            background: linear-gradient(90deg, var(--info-color), #2dd4bf);
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
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(20, 184, 166, 0.3);
        }

        .btn-success-sm, .btn-danger-sm {
            padding: 8px 16px;
            font-size: 0.85rem;
        }

        .btn-success {
            background: linear-gradient(90deg, var(--success-color), #4ade80);
            color: white;
            border-radius: var(--border-radius);
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.3);
        }

        .btn-danger {
            background: linear-gradient(90deg, var(--error-color), #f87171);
            color: white;
            border-radius: var(--border-radius);
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

        /* Alert */
        .alert-warning {
            max-width: 1200px;
            margin: 0 auto 20px;
            background: rgba(245, 158, 11, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 15px;
            color: white;
            position: relative;
            animation: slideIn 0.5s ease-in;
        }

        .alert-success {
            max-width: 1200px;
            margin: 0 auto 20px;
            background: rgba(28, 200, 138, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 15px;
            color: white;
            position: relative;
            animation: slideIn 0.5s ease-in;
        }

        .alert-warning .close,
        .alert-success .close {
            position: absolute;
            top: 15px;
            right: 15px;
            color: white;
            cursor: pointer;
            font-size: 1rem;
            transition: transform 0.2s;
        }

        .alert-warning .close:hover,
        .alert-success .close:hover {
            transform: scale(1.2);
        }

        /* Cards */
        .request-card {
            max-width: 1200px;
            margin: 0 auto 20px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeIn 0.5s ease-in;
        }

        .request-card:hover {
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
            margin: -1px -1px 20px;
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

        .requests-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
        }

        .requests-table th,
        .requests-table td {
            padding: 14px;
            text-align: left;
            font-size: 0.9rem;
        }

        .requests-table th {
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            font-size: 0.75rem;
            background: rgba(0, 0, 0, 0.05);
        }

        .requests-table tbody tr {
            transition: background 0.3s ease, transform 0.2s ease;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.92));
        }

        .requests-table tbody tr:nth-child(even) {
            background: linear-gradient(to right, rgba(245, 245, 245, 0.98), rgba(245, 245, 245, 0.92));
        }

        .requests-table tbody tr:hover {
            background: rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        /* Badges */
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

        .badge-danger {
            background: var(--error-color);
            color: white;
        }

        .badge:hover {
            transform: scale(1.05);
        }

        /* Action Group */
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

        .total-count {
            font-size: 0.9rem;
            color: #6b7280;
            text-align: center;
            margin-top: 10px;
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

            .request-card, .alert-warning, .alert-success {
                max-width: 100%;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-group {
                flex-direction: column;
                gap: 8px;
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
                    <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link active">Withdrawal Requests</a>
                    <a href="{{ route('admin.push-notification.index') }}" class="nav-link">Push Notification</a>
                    <a href="{{ route('admin.tickets.index') }}" class="nav-link">Tickets</a>
    <!--                <a href="{{ route('admin.agent-plan-purchase-requests.index') }}" class="nav-link">Agent Plan Purchase Requests</a>
-->                    <a href="{{ route('admin.subadmins.index') }}" class="nav-link">Sub-Admin</a>
                @elseif($user->hasRole('withdrawal-subadmin'))
                    <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link active">Withdrawal Requests</a>
                @endif
                <div class="settings-dropdown">
                    <a href="#" class="nav-link" id="settings-toggle">Settings</a>
                    <div id="settings-menu-withdrawal" class="settings-menu-card">
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
                    <h1>Withdrawal Requests</h1>
                    
                    <!-- Date Range Filter -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <form method="GET" action="{{ route('admin.withdrawal-requests.index') }}" class="row g-3 align-items-end">
                                        <div class="col-auto">
                                            <label for="start_date" class="form-label mb-0">From</label>
                                            <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                        </div>
                                        <div class="col-auto">
                                            <label for="end_date" class="form-label mb-0">To</label>
                                            <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-outline-primary">Filter</button>
                                            @if(request()->has('start_date') || request()->has('end_date'))
                                                <a href="{{ route('admin.withdrawal-requests.index') }}" class="btn btn-outline-secondary ms-2">
                                                    <i class="fas fa-times me-1"></i> Clear
                                                </a>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="{{ route('admin.withdrawal-requests.export-csv') }}" class="export-csv-btn">
                            <i class="fas fa-file-csv mr-2"></i> Export to CSV
                        </a>
                    </div>
                </div>

                <!-- No Requests Alert -->
                @if(!isset($withdrawalRequests) && !isset($userWithdrawalRequests) && !isset($agentWithdrawalRequests))
                    <div class="alert-warning">
                        No withdrawal requests found.
                        <span class="close">×</span>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert-success">
                        {{ session('success') }}
                        <span class="close">×</span>
                    </div>
                @endif

                <!-- User Withdrawal Requests -->
                @if(isset($userWithdrawalRequests) && $userWithdrawalRequests->count())
                    <div class="mb-4" style="background:#f5f7fa; padding: 24px 16px; border-radius: 10px;">
                       <!-- <h4 style="font-weight:600; margin-bottom:18px;"> USER PAYMENT REQUESTS</h4> -->
                        @foreach ($userWithdrawalRequests as $request)
                            <div style="background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.03); padding:24px 32px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; position:relative;">
                                <div style="position:absolute; top:18px; left:32px; font-size:15px; font-weight:700; color:#4e73df; letter-spacing:0.5px;">User Payment Request</div>
                                <div style="min-width:220px; margin-top:32px;">
                                    <div style="font-size:15px; color:#888; margin-bottom:2px;">Request From:</div>
                                    <div style="font-weight:600; font-size:18px;">{{ optional($request->user)->name ?? 'N/A' }}</div>
                                    <div style="font-size:14px; color:#444; margin-top:8px;">
                                        <span style="color:#888;">UPI ID:</span> <span style="font-weight:600; color:#4e73df;">{{ $request->upi_id ?? 'Not provided' }}</span><br>
                                        <span style="color:#888;">Account Number:</span> <span style="font-weight:600; color:#4e73df;">{{ $request->account_number ?? 'Not provided' }}</span><br>
                                        <span style="color:#888;">IFSC Code:</span> <span style="font-weight:600; color:#4e73df;">{{ $request->ifsc_code ?? 'Not provided' }}</span><br>
                                        <span style="color:#888;">Bank Name:</span> <span style="font-weight:600; color:#4e73df;">{{ $request->bank_name ?? 'Not provided' }}</span>
                                        <span style="color:#888;">Phone Number:</span> <span style="font-weight:600; color:#4e73df;">{{ $request->user->phone ?? 'Not provided' }}</span>
                                    </div>
                                </div>
                                <div style="text-align:right; min-width:180px;">
                                    <div style="font-size:13px; color:#888;">Request Total</div>
                                    <div style="font-size:24px; font-weight:700; margin-bottom:16px;">₹{{ number_format($request->amount, 2) }}</div>
                                    @if($request->status === 'pending')
                                        <form action="{{ route('admin.withdrawal-requests.update', $request->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" style="background:#f6c23e; color:#fff; border:none; border-radius:6px; padding:10px 28px; font-weight:600; font-size:15px; cursor:pointer;">Pending</button>
                                        </form>
                                    @else
                                        <span class="btn-success" style="display:inline-block; border:none; border-radius:6px; padding:10px 28px; font-weight:600; font-size:15px; background:linear-gradient(90deg, #1cc88a, #4ade80); color:#fff; cursor:default;">Paid</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <!-- Agent Withdrawal Requests -->
                @if(isset($agentWithdrawalRequests) && $agentWithdrawalRequests->count())
                    <div class="mb-4" style="background:#f5f7fa; padding: 24px 16px; border-radius: 10px;">
                      <!-- <h4 style="font-weight:600; margin-bottom:18px;">AGENT PAYMENT REQUESTS</h4> -->
                        @foreach ($agentWithdrawalRequests as $request)
                            <div style="background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.03); padding:24px 32px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; position:relative;">
                                <div style="position:absolute; top:18px; left:32px; font-size:15px; font-weight:700; color:#1cc88a; letter-spacing:0.5px;">Agent Payment Request</div>
                                <div style="min-width:220px; margin-top:32px;">
                                    <div style="font-size:15px; color:#888; margin-bottom:2px;">Request From:</div>
                                    <div style="font-weight:600; font-size:18px;">{{ optional($request->agent)->name ?? 'N/A' }}</div>
                                    <div style="font-size:14px; color:#444; margin-top:8px;">
                                        <span style="color:#888;">UPI ID:</span> <span style="font-weight:600; color:#4e73df;">{{ $request->upi_id ?? 'Not provided' }}</span><br>
                                        <span style="color:#888;">Account Number:</span> <span style="font-weight:600; color:#4e73df;">{{ $request->account_number ?? 'Not provided' }}</span><br>
                                        <span style="color:#888;">IFSC Code:</span> <span style="font-weight:600; color:#4e73df;">{{ $request->ifsc_code ?? 'Not provided' }}</span><br>
                                        <span style="color:#888;">Bank Name:</span> <span style="font-weight:600; color:#4e73df;">{{ $request->bank_name ?? 'Not provided' }}</span>
                                        <span style="color:#888;">Phone Number:</span> <span style="font-weight:600; color:#4e73df;">{{ $request->agent->phone_number?? 'Not provided' }}</span>
                                    </div>
                                </div>
                                <div style="text-align:right; min-width:180px;">
                                    <div style="font-size:13px; color:#888;">Request Total</div>
                                    <div style="font-size:24px; font-weight:700; margin-bottom:16px;">₹{{ number_format($request->amount, 2) }}</div>
                                    @if($request->status === 'pending')
                                        <form action="{{ route('admin.agent-withdrawal-requests.approve', $request->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" style="background:#f6c23e; color:#fff; border:none; border-radius:6px; padding:10px 28px; font-weight:600; font-size:15px; cursor:pointer;">Pending</button>
                                        </form>
                                    @else
                                        <span class="btn-success" style="display:inline-block; border:none; border-radius:6px; padding:10px 28px; font-weight:600; font-size:15px; background:linear-gradient(90deg, #1cc88a, #4ade80); color:#fff; cursor:default;">Paid</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hamburger menu toggle
            document.querySelector('.hamburger').addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
            });

            // Close alert
            document.querySelectorAll('.alert-warning .close, .alert-success .close').forEach(button => {
                button.addEventListener('click', () => {
                    button.parentElement.style.display = 'none';
                });
            });

            // Settings dropdown toggle
            const toggle = document.getElementById('settings-toggle');
            const menu = document.getElementById('settings-menu-withdrawal');
            
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