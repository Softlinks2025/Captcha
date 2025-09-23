<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User: {{ $user->name }}</title>
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
            --error-color: #ef4444;
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

        .sidebar .nav-link {
            color: var(--text-color);
            padding: 10px 15px;
            border-radius: var(--border-radius);
            margin-bottom: 10px;
            transition: background 0.3s, color 0.3s;
            display: flex;
            align-items: center;
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

        /* Error Alert */
        .alert-danger {
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

        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert-danger .close {
            position: absolute;
            top: 15px;
            right: 15px;
            color: white;
            cursor: pointer;
            font-size: 1rem;
            transition: transform 0.2s;
        }

        .alert-danger .close:hover {
            transform: scale(1.2);
        }

        /* Form Card */
        .form-card {
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
            margin: -20px -20px 20px;
        }

        .card-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-color);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
            position: relative;
        }

        .form-group label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group .custom-file-input {
            padding: 10px 40px 10px 16px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            color: var(--text-color);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group input:focus,
        .form-group .custom-file-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }

        .form-group input:disabled {
            background: rgba(229, 231, 235, 0.8);
            cursor: not-allowed;
        }

        .form-group .icon {
            position: absolute;
            right: 12px;
            top: 38px;
            color: #6b7280;
            font-size: 1rem;
        }

        .form-group .error {
            color: var(--error-color);
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .form-group small {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 5px;
        }

        .form-group .custom-file {
            position: relative;
        }

        .form-group .custom-file-label {
            padding: 10px 16px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            color: #6b7280;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .form-group .custom-file-input:focus + .custom-file-label {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }

        .profile-photo-preview {
            max-width: 150px;
            border-radius: var(--border-radius);
            margin-top: 10px;
            transition: transform 0.3s ease;
        }

        .profile-photo-preview:hover {
            transform: scale(1.05);
        }

        /* Toggle Switches */
        .custom-switch {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .custom-switch input {
            display: none;
        }

        .custom-switch .toggle {
            width: 44px;
            height: 24px;
            background: #d1d5db;
            border-radius: 12px;
            position: relative;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .custom-switch .toggle::before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .custom-switch input:checked + .toggle {
            background: var(--success-color);
        }

        .custom-switch input:checked + .toggle::before {
            transform: translateX(20px);
        }

        .custom-switch label {
            font-size: 0.9rem;
            color: var(--text-color);
        }

        /* Checkboxes */
        .custom-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .custom-checkbox input {
            width: 16px;
            height: 16px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .custom-checkbox input:checked {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .custom-checkbox label {
            font-size: 0.9rem;
            color: var(--text-color);
            cursor: pointer;
        }

        /* Buttons */
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            color: white;
            border-radius: var(--border-radius);
            padding: 12px 24px;
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

        /* Responsive Design */
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

            .hamburger {
                display: block;
            }

            .main-content {
                margin-left: 0;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-card,
            .alert-danger {
                max-width: 100%;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .button-group {
                flex-direction: column;
                align-items: flex-end;
            }
        }

        @media (min-width: 769px) {
            .hamburger {
                display: none;
            }
        }
        .sidebar .nav-item.settings-dropdown { position: relative; }
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
                <a href="{{ route('admin.users.index') }}" class="nav-link active">Users</a>
                <a href="{{ route('admin.subscription_plans.index') }}" class="nav-link">Subscription Plans</a>
                <a href="{{ route('admin.agent-plans.index') }}" class="nav-link">Agent Plans</a>
                <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link">Withdrawal Requests</a>
                <a href="{{ route('admin.push-notification.index') }}" class="nav-link">Push Notification</a>
                <a href="{{ route('admin.tickets.index') }}" class="nav-link">Tickets</a>
<!--                <a href="{{ route('admin.agent-plan-purchase-requests.index') }}" class="nav-link">Agent Plan Purchase Requests</a>
-->                <a href="{{ route('admin.subadmins.index') }}" class="nav-link">Sub-Admin</a>
                <div class="settings-dropdown">
                    <a href="#" class="nav-link" id="settings-toggle">Settings</a>
                    <div id="settings-menu-users" class="settings-menu-card">
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
                    <h1>Edit User: {{ $user->name }}</h1>
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Back to User
                    </a>
                </div>

                <!-- Error Alert -->
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

                <!-- Form Card -->
                <div class="form-card">
                    <div class="card-header">
                        <h2>Edit User Details</h2>
                    </div>
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-grid">
                            <!-- Full Name -->
                            <div class="form-group">
                                <label for="name">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="@error('name') error @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                <i class="fas fa-user icon"></i>
                                @error('name')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email Address -->
                            <div class="form-group">
                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="@error('email') error @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                <i class="fas fa-envelope icon"></i>
                                @error('email')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Password (Optional) -->
                            <div class="form-group">
                                <label for="password">Password (Optional)</label>
                                <input type="password" class="@error('password') error @enderror" id="password" name="password">
                                <i class="fas fa-lock icon"></i>
                                <small>Leave blank to keep current password. Minimum 8 characters if changed.</small>
                                @error('password')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Password Confirmation (Optional) -->
                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password (Optional)</label>
                                <input type="password" class="@error('password_confirmation') error @enderror" id="password_confirmation" name="password_confirmation">
                                <i class="fas fa-lock icon"></i>
                                @error('password_confirmation')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div class="form-group">
                                <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="@error('phone') error @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
                                <i class="fas fa-phone icon"></i>
                                @error('phone')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- UPI ID -->
                            <div class="form-group">
                                <label for="upi_id">UPI ID <span class="text-danger">*</span></label>
                                <input type="text" class="@error('upi_id') error @enderror" id="upi_id" name="upi_id" value="{{ old('upi_id', $user->upi_id) }}" required>
                                @error('upi_id')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Date of Birth -->
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="@error('date_of_birth') error @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}" required>
                                @error('date_of_birth')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Profile Photo -->
                            <div class="form-group">
                                <label for="profile_photo">Profile Photo</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('profile_photo') error @enderror" id="profile_photo" name="profile_photo">
                                    <label class="custom-file-label" for="profile_photo">Choose file</label>
                                    @error('profile_photo')
                                        <span class="error">{{ $message }}</span>
                                    @enderror
                                </div>
                                @if($user->profile_photo_path)
                                    <img src="{{ $user->profile_photo_url }}" alt="Profile Photo" class="profile-photo-preview">
                                @endif
                            </div>

                            <!-- Account Status -->
                            <div class="form-group">
                                <label>Account Status</label>
                                <div class="custom-switch">
                                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
                                    <span class="toggle"></span>
                                    <label for="is_active" id="is_active_label">{{ $user->is_active ? 'Active' : 'Inactive' }}</label>
                                </div>
                                <small>Deactivating will prevent the user from logging in.</small>
                            </div>

                            <!-- Email Verification Status -->
                            <div class="form-group">
                                <label>Email Verification Status</label>
                                <div class="custom-switch">
                                    <input type="checkbox" id="email_verified" name="email_verified" value="1" {{ $user->hasVerifiedEmail() ? 'checked' : '' }}>
                                    <span class="toggle"></span>
                                    <label for="email_verified" id="email_verified_label">{{ $user->hasVerifiedEmail() ? 'Verified' : 'Not Verified' }}</label>
                                </div>
                            </div>

                            <!-- User Roles -->
                            <div class="form-group col-span-2">
                                <label>User Roles</label>
                                @if(count($roles) === 1)
                                    @foreach($roles as $id => $name)
                                        <input type="hidden" name="roles[]" value="{{ $id }}">
                                        <span class="badge bg-info">{{ $name }}</span>
                                    @endforeach
                                @else
                                    @foreach($roles as $id => $name)
                                        <div class="custom-checkbox">
                                            <input type="radio" id="role_{{ $id }}" name="roles[]" value="{{ $id }}" {{ in_array($id, $userRoles) ? 'checked' : '' }}>
                                            <label for="role_{{ $id }}">{{ $name }}</label>
                                        </div>
                                    @endforeach
                                @endif
                                @error('roles')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="bank_name">Bank Name</label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ old('bank_name', $user->bank_name) }}">
                            </div>
                            <div class="form-group">
                                <label for="bank_account_number">Bank Account Number</label>
                                <input type="text" name="bank_account_number" id="bank_account_number" class="form-control" value="{{ old('bank_account_number', $user->bank_account_number) }}">
                            </div>
                            <div class="form-group">
                                <label for="ifsc_code">IFSC Code</label>
                                <input type="text" name="ifsc_code" id="ifsc_code" class="form-control" value="{{ old('ifsc_code', $user->ifsc_code) }}">
                            </div>
                            <div class="form-group">
                                <label for="additional_contact_number">Additional Contact Number</label>
                                <input type="text" name="additional_contact_number" id="additional_contact_number" class="form-control" value="{{ old('additional_contact_number', $user->additional_contact_number) }}">
                            </div>
                            <div class="form-group">
                                <label for="pan_number">PAN Number</label>
                                <input type="text" name="pan_number" id="pan_number" class="form-control" value="{{ old('pan_number', $user->pan_number) }}">
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea name="address" id="address" class="form-control">{{ old('address', $user->address) }}</textarea>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="button-group">
                            <button type="submit" class="btn-primary" style="background: linear-gradient(90deg, #22c55e, #4ade80); color: #fff;">
                                <i class="fas fa-save mr-2"></i> Update User
                            </button>
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn-secondary">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script>
        // Update the file input label with the selected filename
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose file';
            const nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });

        // Toggle switch labels
        document.getElementById('is_active').addEventListener('change', function() {
            const label = document.getElementById('is_active_label');
            label.textContent = this.checked ? 'Active' : 'Inactive';
        });

        document.getElementById('email_verified').addEventListener('change', function() {
            const label = document.getElementById('email_verified_label');
            label.textContent = this.checked ? 'Verified' : 'Not Verified';
        });

        // Close error alert
        document.querySelectorAll('.alert-danger .close').forEach(button => {
            button.addEventListener('click', () => {
                button.parentElement.style.display = 'none';
            });
        });

        // Hamburger menu toggle
        document.querySelector('.hamburger').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Settings dropdown toggle
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('settings-toggle');
            const menu = document.getElementById('settings-menu-users');
            
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
