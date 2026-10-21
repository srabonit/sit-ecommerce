<!DOCTYPE html>
<html lang="en" id="admin-auth-html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="admin-auth-page-title">Admin Sign In - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .admin-login-box {
            background: #ffffff;
            border-radius: 20px;
            padding: 36px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body id="admin-auth-body">

<div class="admin-login-box" id="admin-login-card-container">
    <div class="text-center mb-4" id="admin-login-header-block">
        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 54px; height: 54px;" id="admin-login-icon">
            <i class="fas fa-user-shield fs-4"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1" id="admin-login-main-title">Admin Dashboard</h4>
        <p class="text-muted small mb-0" id="admin-login-sub-text">Enter credentials to access administrative controls</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2 small rounded-3 mb-3" id="admin-login-error-alert">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success py-2 small rounded-3 mb-3" id="admin-login-success-alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.login.submit') }}" method="POST" id="admin-login-form">
        @csrf
        <div class="mb-3" id="admin-email-group">
            <label class="form-label small fw-bold text-dark" id="admin-email-label">Admin Email</label>
            <input type="email" name="email" id="admin-email-input" class="form-control rounded-3 py-2" value="{{ old('email', env('ADMIN_EMAIL', 'admi.mail@gmail.com')) }}" required autofocus>
        </div>

        <div class="mb-3" id="admin-password-group">
            <label class="form-label small fw-bold text-dark" id="admin-password-label">Password</label>
            <input type="password" name="password" id="admin-password-input" class="form-control rounded-3 py-2" placeholder="••••••••" required>
        </div>

        <div class="form-check mb-4" id="admin-remember-group">
            <input class="form-check-input" type="checkbox" name="remember" id="admin-remember-check">
            <label class="form-check-label small text-secondary" for="admin-remember-check" id="admin-remember-label">
                Remember session
            </label>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm" id="admin-login-submit-btn">
            Sign In to Panel
        </button>
    </form>
</div>

</body>
</html>