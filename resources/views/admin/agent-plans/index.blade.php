<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    
        .sidebar ul {
            list-style: none;
            padding-left: 0;
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

        /* Cards and Tables */
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

        .card-header {
            padding: 20px;
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 20px;
        }

        .table {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            width: 100%;
        }

        .table th, .table td {
            padding: 15px;
            vertical-align: middle;
        }

        .table thead {
            background: #f1f3f5;
        }

        .table th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: var(--text-color);
        }

        .table tbody tr {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .badge {
            padding: 8px 12px;
            border-radius: var(--border-radius);
            font-weight: 500;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border-radius: var(--border-radius);
            padding: 8px 20px;
            transition: background 0.3s, color 0.3s;
        }

        .btn-primary:hover {
            background-color: #3b5bdb;
        }

        .btn-danger {
            background-color: #dc2626;
            color: white;
            border-radius: var(--border-radius);
            padding: 8px 20px;
            transition: background 0.3s, color 0.3s;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        /* Hamburger Menu */
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

        /* Responsive Design */
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

            .table-responsive {
                overflow-x: auto;
            }

            .table {
                min-width: 800px;
            }
        }

        @media (min-width: 769px) {
            .hamburger {
                display: none;
            }
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            font-size: 1.15rem;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 0;
        }
        .action-btn.view {
            background: #6366f1;
            color: #fff;
        }
        .action-btn.edit {
            background: #3b82f6;
            color: #fff;
        }
        .action-btn.delete {
            background: #ef4444;
            color: #fff;
        }
        .action-btn:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.10);
            opacity: 0.92;
        }
        .action-btn i {
            font-size: 1.1rem;
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
                    <a href="{{ route('admin.agent-plans.index') }}" class="nav-link active">Agent Plans</a>
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
                    <a href="{{ route('admin.agent-plan-purchase-requests.index') }}" class="nav-link">Agent Plan Purchase Requests</a>
                </li>-->
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
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <section class="container mx-auto">
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Agent Commission Tiers</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage commission tiers for subscription plans</p>
                        </div>
                        <a href="{{ route('admin.subscription_plans.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Subscription Plans
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Plan Name</th>
                                        <th>Price</th>
                                        <th>Commission Tiers</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($plans as $plan)
                                        <tr>
                                            <td class="font-medium">
                                                <div class="flex items-center">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-3">
                                                        <i class="fas fa-box"></i>
                                                    </div>
                                                    <div>
                                                        <div class="font-medium">{{ $plan->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $plan->description ?: 'No description' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>₹{{ number_format($plan->cost, 2) }}</td>
                                            <td>
                                                @if($plan->agentCommissionTiers->count() > 0)
                                                    <div class="space-y-1">
                                                        @foreach($plan->agentCommissionTiers->sortBy('min_referrals') as $tier)
                                                            <div class="text-sm">
                                                                <span class="font-medium">{{ $tier->referral_range }} referrals:</span>
                                                                <span class="text-green-600">₹{{ number_format($tier->commission_amount, 2) }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-sm text-gray-500">No commission tiers set</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('admin.agent-plans.edit', $plan) }}" class="btn btn-sm btn-primary" title="Manage Commission Tiers">
                                                        <i class="fas fa-percentage mr-1"></i> Manage Tiers
                                                    </a>
                                                  <!--  <a href="{{ route('admin.subscription_plans.edit', $plan) }}" class="btn btn-sm btn-secondary" title="Edit Plan">
                                                        <i class="fas fa-edit"></i>
                                                    </a> -->
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-sm text-gray-500 text-center py-4">
                                                No subscription plans found. <a href="{{ route('admin.subscription_plans.create') }}" class="text-blue-600 hover:text-blue-800">Create a subscription plan</a> first.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Joining Fees Section -->
            <div class="card mt-8">
                <div class="card-header flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Joining Fees Management</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Manage joining fees for agent plans
                        </p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Validity</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($joiningFees as $fee)
                                <tr>
                                    <td>{{ $fee->name }}</td>
                                    <td>₹{{ number_format($fee->amount, 2) }}</td>
                                    <td>{{ $fee->validity === 'lifetime' ? 'Lifetime' : $fee->validity . ' days' }}</td>
                                    <td>
                                        <span class="badge {{ $fee->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $fee->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="flex space-x-2">
                                        <button onclick="openEditJoiningFee({{ json_encode($fee) }})" 
                                                class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                       <!-- <form action="{{ route('admin.admin.agent-plans.joining-fees.destroy', $fee->id) }}" method="POST" class="inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">
        <i class="fas fa-trash"></i>
    </button>
</form>-->
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Joining Fee Modal -->
            <div id="joiningFeeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 transition-opacity duration-300">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all duration-300 scale-95 opacity-0" id="modalDialog">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-500 rounded-t-xl">
                        <h3 class="text-lg font-semibold text-white" id="modalTitle">Edit Joining Fee</h3>
                    </div>
                    
                    <!-- Modal Body -->
                    <form id="joiningFeeForm"  action="{{ url('admin/agent-plans/joining-fees/0') }}"  method="POST" class="p-6 space-y-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="feeId">

                        <!-- Name Field -->
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-tag text-gray-400"></i>
                                </div>
                                <input type="text" name="name" id="name" required
                                    class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-150 ease-in-out"
                                    placeholder="Enter fee name">
                            </div>
                        </div>

                        <!-- Amount Field -->
                        <div class="space-y-2">
                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">₹</span>
                                </div>
                                <input type="number" name="amount" id="amount" step="0.01" required
                                    class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-150 ease-in-out"
                                    placeholder="0.00">
                            </div>
                        </div>

                        <!-- Validity Field -->
                        <div class="space-y-2">
                            <label for="validity" class="block text-sm font-medium text-gray-700">Validity <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                </div>
                                <select name="validity" id="validity" required
                                    class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-150 ease-in-out">
                                    <option value="lifetime">Lifetime</option>
                                    <option value="1_year">1 Year</option>
                                    <option value="6_months">6 Months</option>
                                </select>
                            </div>
                        </div>

                     <!-- Description Field 
                        <div class="space-y-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <div class="relative">
                                <div class="absolute top-3 left-3">
                                    <i class="fas fa-align-left text-gray-400"></i>
                                </div>
                                <textarea name="description" id="description" rows="3"
                                    class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-150 ease-in-out"
                                    placeholder="Enter description (optional)"></textarea>
                            </div>
                        </div>-->

                        <!-- Active Toggle -->
                        <div class="flex items-center">
                            <div class="relative inline-block w-10 mr-2 align-middle select-none">
                                <input type="checkbox" name="is_active" id="is_active" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-transform duration-200 ease-in-out" />
                                <label for="is_active" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                            </div>
                            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <button type="button" onclick="closeJoiningFeeModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                Cancel
                            </button>
                            <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <style>
                /* Toggle Switch Styling */
                .toggle-checkbox:checked {
                    right: 0;
                    border-color: #3b82f6;
                    transform: translateX(100%);
                }
                .toggle-checkbox:checked + .toggle-label {
                    background-color: #3b82f6;
                }
                .toggle-checkbox:focus {
                    outline: none;
                    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
                }
                /* Modal Animation */
                #joiningFeeModal[data-state='open'] #modalDialog {
                    transform: scale(1);
                    opacity: 1;
                }
                /* Input Focus States */
                input:focus, select:focus, textarea:focus {
                    outline: none;
                    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
                }
                /* Smooth Transitions */
                .transition-all {
                    transition-property: all;
                    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                    transition-duration: 150ms;
                }
            </style>

            <script>
                // Joining Fee Modal Functions with Animation
                function openEditJoiningFee(fee) {
                    const modal = document.getElementById('joiningFeeModal');
                    const form = document.getElementById('joiningFeeForm');
                    const modalDialog = document.getElementById('modalDialog');
                    
                    // Set form values
                    document.getElementById('feeId').value = fee.id;
                    document.getElementById('name').value = fee.name || '';
                    document.getElementById('amount').value = fee.amount || '';
                    document.getElementById('validity').value = fee.validity || 'lifetime';
                    document.getElementById('description').value = fee.description || '';
                    document.getElementById('is_active').checked = Boolean(fee.is_active);
                    
                    // Set form action
                    form.action = '/admin/agent-plans/joining-fees/' + fee.id;
                    
                    // Show modal with animation
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modalDialog.classList.remove('opacity-0', 'scale-95');
                        modalDialog.classList.add('opacity-100', 'scale-100');
                    }, 10);
                }

                function closeJoiningFeeModal() {
                    const modal = document.getElementById('joiningFeeModal');
                    const modalDialog = document.getElementById('modalDialog');
                    
                    // Hide modal with animation
                    modalDialog.classList.remove('opacity-100', 'scale-100');
                    modalDialog.classList.add('opacity-0', 'scale-95');
                    
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 200);
                }

                // Handle form submit
                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.getElementById('joiningFeeForm');
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            
                            const formData = new FormData(form);
                            const url = form.action;
                            
                            // Get CSRF token from meta tag
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                            
                            if (!csrfToken) {
                                console.error('CSRF token not found');
                                alert('Security error: CSRF token missing');
                                return;
                            }
                            
                            // Add _method for Laravel to handle PUT request
                            if (!formData.has('_method')) {
                                formData.append('_method', 'PUT');
                            }
                            
                            fetch(url, {
                                method: 'POST', // Laravel will handle the method spoofing
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                                body: formData
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(err => { throw err; });
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    alert(data.message || 'Joining fee updated successfully!');
                                    window.location.reload();
                                } else {
                                    throw new Error(data.message || 'Failed to update joining fee');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Error: ' + (error.message || 'Failed to update joining fee'));
                            });
                        });
                    }

                    // Hamburger menu toggle
                    const hamburger = document.querySelector('.hamburger');
                    if (hamburger) {
                        hamburger.addEventListener('click', () => {
                            document.querySelector('.sidebar')?.classList.toggle('active');
                        });
                    }

                    // Settings dropdown toggle
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