<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Agent: {{ $agent->name }}</title>
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
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--background-color); color: var(--text-color); overflow-x: hidden; }
        .wrapper { display: flex; min-height: 100vh; }
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
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: var(--primary-color); color: white; }
        .sidebar .nav-link i { margin-right: 10px; }
        .sidebar .sidebar-header { font-size: 1.5rem; font-weight: 600; margin-bottom: 20px; text-align: center; }
        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 0;
            transition: margin-left 0.3s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .sidebar ul {
            list-style: none;
            padding-left: 0;
        }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; animation: fadeIn 0.5s ease-in; }
        .header h1 { font-size: 2rem; font-weight: 700; color: var(--text-color); display: flex; align-items: center; gap: 10px; }
        .alert-error { 
            max-width: 1200px; 
            margin: 0 auto 20px; 
            background: rgba(239, 68, 68, 0.95); 
            backdrop-filter: blur(10px); 
            border-radius: var(--border-radius); 
            box-shadow: var(--shadow); 
            padding: 15px; 
            color: white; 
            position: relative; 
            animation: slideIn 0.5s ease-in; 
        }
        .alert-error ul { margin: 0; padding-left: 20px; }
        .alert-error .close { position: absolute; top: 15px; right: 15px; color: white; cursor: pointer; font-size: 1rem; transition: transform 0.2s; }
        .alert-error .close:hover { transform: scale(1.2); }
        .form-card { max-width: 1200px; margin: 0 auto; background: var(--card-bg); backdrop-filter: blur(10px); border-radius: var(--border-radius); box-shadow: var(--shadow); padding: 20px; transition: transform 0.3s ease, box-shadow 0.3s ease; animation: fadeIn 0.5s ease-in; }
        .form-card:hover { transform: translateY(-5px); box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        .card-header { padding: 15px; background: linear-gradient(90deg, #e0e7ff, #f1f5f9); border-radius: var(--border-radius) var(--border-radius) 0 0; margin: -20px -20px 20px; }
        .card-header h2 { font-size: 1.75rem; font-weight: 700; color: var(--text-color); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; margin-bottom: 15px; position: relative; }
        .form-group label { font-size: 0.9rem; font-weight: 500; color: #6b7280; margin-bottom: 8px; }
        .form-group input, .form-group select, .form-group textarea { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; background: #f8fafc; }
        .form-group input[type="file"] { padding: 6px 0; background: none; }
        .form-group img.profile-photo-preview { margin-top: 8px; max-width: 120px; border-radius: 8px; border: 1px solid #d1d5db; }
        .form-group .error { color: #ef4444; font-size: 0.95em; margin-top: 4px; }
        .btn-primary { background: linear-gradient(90deg, #4e73df, #60a5fa); color: #fff; border: none; border-radius: 8px; padding: 12px 24px; font-weight: 600; font-size: 1rem; cursor: pointer; width: 100%; margin-top: 10px; }
        .btn-primary:hover { background: #2563eb; }
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
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .hamburger { display: block; }
            .form-grid { grid-template-columns: 1fr; }
            .form-card { max-width: 100%; }
        }
        @media (min-width: 769px) {
            .hamburger { display: none; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- Hamburger Menu -->
    <i class="fas fa-bars hamburger"></i>
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
-->            </li>
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
    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-user-edit mr-2"></i> Edit Agent: {{ $agent->name }}</h1>
            <a href="{{ route('admin.agents.index') }}" class="btn-primary" style="width:auto;"><i class="fas fa-arrow-left mr-2"></i> Back to Agents</a>
        </div>
        @if ($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <span class="close" onclick="this.parentElement.style.display='none';">×</span>
            </div>
        @endif
        <div class="form-card">
            <div class="card-header"><h2>Edit Agent Details</h2></div>
            <form action="{{ route('admin.agents.update', $agent) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group"><label for="name">Name</label><input type="text" name="name" id="name" value="{{ old('name', $agent->name) }}" required>@error('name')<div class="error">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label for="email">Email</label><input type="email" name="email" id="email" value="{{ old('email', $agent->email) }}">@error('email')<div class="error">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label for="date_of_birth">Date of Birth</label><input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $agent->date_of_birth) }}">@error('date_of_birth')<div class="error">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label for="phone_number">Phone Number</label><input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $agent->phone_number) }}" required>@error('phone_number')<div class="error">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label for="upi_id">UPI ID</label><input type="text" name="upi_id" id="upi_id" value="{{ old('upi_id', $agent->upi_id) }}">@error('upi_id')<div class="error">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label for="profile_photo">Profile Photo</label><input type="file" name="profile_photo" id="profile_photo" accept="image/*">@if($agent->profile_photo_path)<img src="{{ asset('storage/' . $agent->profile_photo_path) }}" alt="Profile Photo" class="profile-photo-preview">@endif @error('profile_photo')<div class="error">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label for="referral_code">Referral Code</label><input type="text" name="referral_code" id="referral_code" value="{{ old('referral_code', $agent->referral_code) }}" readonly></div>
                </div>
                <div class="form-group">
                    <label for="bank_name">Bank Name</label>
                    <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $agent->bank_name) }}">
                    @error('bank_name')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="bank_account_number">Bank Account Number</label>
                    <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $agent->bank_account_number) }}">
                    @error('bank_account_number')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="ifsc_code">IFSC Code</label>
                    <input type="text" name="ifsc_code" id="ifsc_code" value="{{ old('ifsc_code', $agent->ifsc_code) }}">
                    @error('ifsc_code')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="additional_contact_number">Additional Contact Number</label>
                    <input type="text" name="additional_contact_number" id="additional_contact_number" value="{{ old('additional_contact_number', $agent->additional_contact_number) }}">
                    @error('additional_contact_number')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="pan_number">PAN Number</label>
                    <input type="text" name="pan_number" id="pan_number" value="{{ old('pan_number', $agent->pan_number) }}">
                    @error('pan_number')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address" id="address">{{ old('address', $agent->address) }}</textarea>
                    @error('address')<div class="error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn-primary">Update Agent</button>
            </form>
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
</script>
</body>
</html>