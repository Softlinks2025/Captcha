<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Change Subadmin Password</title>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Change Password for {{ $subadmin->name }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.subadmins.change-password.update', $subadmin->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                        <a href="{{ route('admin.subadmins.index') }}" class="btn btn-secondary ms-2">Back to Subadmins</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html> 