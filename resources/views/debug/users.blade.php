<!DOCTYPE html>
<html>
<head>
    <title>Debug Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Users in Database</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Password Hash</th>
                    <th>Roles</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user['id'] }}</td>
                        <td>{{ $user['name'] }}</td>
                        <td>{{ $user['email'] }}</td>
                        <td><small class="text-muted">{{ substr($user['password'], 0, 30) }}...</small></td>
                        <td>
                            @if(count($user['roles']) > 0)
                                {{ implode(', ', $user['roles']) }}
                            @else
                                <span class="text-danger">No roles</span>
                            @endif
                        </td>
                        <td>{{ $user['created_at'] }}</td>
                        <td>
                            <a href="{{ url('/debug/assign-admin/' . $user['id']) }}" class="btn btn-sm btn-primary">Make Admin</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
