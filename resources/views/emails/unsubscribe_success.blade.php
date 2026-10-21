<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed Successfully</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .success-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            max-width: 480px;
            width: 100%;
            border: 1px solid #e2e8f0;
            text-align: center;
            padding: 40px 30px;
        }
    </style>
</head>
<body>

<div class="success-card">
    <div class="bg-success-subtle text-success p-3 rounded-circle d-inline-flex mb-3">
        <i class="fas fa-circle-check fa-3x"></i>
    </div>
    <h4 class="fw-bold text-dark mb-2">Unsubscribed Successfully</h4>
    <p class="text-muted small mb-4">
        Your email address <strong class="text-dark">{{ $subscriber->email }}</strong> has been unsubscribed from our marketing broadcast list[cite: 20]. You will no longer receive promotional emails from us.
    </p>

    <div class="alert alert-light border small text-muted text-start mb-4 py-2 px-3">
        <i class="fas fa-info-circle me-1 text-primary"></i>
        Important transactional emails regarding active orders or password resets will still be delivered if you make a purchase.
    </div>

    <div>
        <a href="{{ url('/') }}" class="btn btn-primary px-4 py-2 fw-semibold" style="border-radius: 6px;">
            Return to Store
        </a>
    </div>
</div>

</body>
</html>