<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - Email Preferences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .unsubscribe-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            max-width: 520px;
            width: 100%;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

<div class="unsubscribe-card">
    <div class="p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="bg-danger-subtle text-danger p-3 rounded-circle d-inline-flex mb-3">
                <i class="fas fa-envelope-circle-xmark fa-2x"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Manage Email Preferences</h4>
            <p class="text-muted small mb-0">We are sorry to see you go. You can update your newsletter preferences below.</p>
        </div>

        <div class="alert alert-light border small text-muted mb-4 py-2 px-3">
            Target Email: <strong class="text-dark">{{ $subscriber->email }}</strong>
        </div>

        <form action="{{ url('email/unsubscribe/' . $token) }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label small fw-semibold text-dark">Please tell us why you are unsubscribing:</label>
                <div class="d-flex flex-column gap-2" style="font-size: 13px;">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="reason" id="reason1" value="Too many emails" checked>
                        <label class="form-check-label text-secondary" for="reason1">I receive too many promotional emails</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="reason" id="reason2" value="No longer interested">
                        <label class="form-check-label text-secondary" for="reason2">I am no longer interested in these products</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="reason" id="reason3" value="Never signed up">
                        <label class="form-check-label text-secondary" for="reason3">I never consented or signed up for this newsletter</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="reason" id="reason4" value="Content irrelevant">
                        <label class="form-check-label text-secondary" for="reason4">The content is not relevant to my interests</label>
                    </div>
                </div>
            </div>

            <div class="mb-4 form-check bg-light p-3 rounded border">
                <input class="form-check-input ms-0 me-2" type="checkbox" name="block_all" id="blockAllInput" value="1">
                <label class="form-check-label small fw-semibold text-dark" for="blockAllInput">
                    Permanently block all future marketing communications
                </label>
                <div class="text-muted" style="font-size: 11px; margin-left: 24px;">
                    Your email address will be placed on our strict suppression blacklist.
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-danger py-2 fw-semibold" style="border-radius: 6px;">
                    Confirm Unsubscribe
                </button>
                <a href="{{ url('/') }}" class="btn btn-light border py-2 text-muted small" style="border-radius: 6px;">
                    Keep My Subscription &amp; Return To Store
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>