<!DOCTYPE html>
<html lang="en" id="admin-auth-html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="admin-auth-page-title">Admin Sign In - {{ config('app.name', 'Shop Now BD') }}</title>
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
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
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

    {{-- Error Alert --}}
    @if($errors->any())
        <div class="alert alert-danger py-2 small rounded-3 mb-3 border-0 shadow-xs" id="admin-login-error-alert" style="font-size: 12.5px;">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success py-2 small rounded-3 mb-3 border-0 shadow-xs" id="admin-login-success-alert" style="font-size: 12.5px;">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.login.submit') }}" method="POST" id="admin-login-form" novalidate>
        @csrf
        
        {{-- Email or Mobile Number Input --}}
        <div class="mb-3" id="admin-email-group">
            <label class="form-label small fw-bold text-dark" id="admin-email-label">
                Admin Email or Mobile Number
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 8px 0 0 8px;">
                    <i class="fas fa-user-circle"></i>
                </span>
                <input type="text" 
                       name="email" 
                       id="admin-email-input" 
                       class="form-control rounded-end-3 py-2 font-monospace @error('email') is-invalid @enderror" 
                       value="{{ old('email', env('ADMIN_EMAIL', '')) }}" 
                       placeholder="admin@example.com or 017XXXXXXXX" 
                       required 
                       autofocus>
            </div>
            @error('email')
                <div class="invalid-feedback d-block small fw-semibold">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password Input --}}
        <div class="mb-3" id="admin-password-group">
            <label class="form-label small fw-bold text-dark" id="admin-password-label">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 8px 0 0 8px;">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password" 
                       name="password" 
                       id="admin-password-input" 
                       class="form-control rounded-end-3 py-2 @error('password') is-invalid @enderror" 
                       placeholder="••••••••" 
                       required>
            </div>
            @error('password')
                <div class="invalid-feedback d-block small fw-semibold">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remember Session --}}
        <div class="form-check mb-4" id="admin-remember-group">
            <input class="form-check-input" type="checkbox" name="remember" id="admin-remember-check" style="cursor: pointer;">
            <label class="form-check-label small text-secondary" for="admin-remember-check" id="admin-remember-label" style="cursor: pointer;">
                Remember session
            </label>
        </div>

        {{-- Submit Button --}}
        <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold shadow-sm" id="admin-login-submit-btn">
            <i class="fas fa-shield-alt me-1.5"></i> Sign In to Panel
        </button>
    </form>
</div>

</body>
</html>