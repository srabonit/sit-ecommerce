<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Thank You</title>
</head>

<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif;">

    <div style="
        max-width:600px;
        margin:40px auto;
        background:#ffffff;
        border-radius:10px;
        padding:35px;
        border:1px solid #e5e7eb;
    ">

        <h2 style="margin-top:0;">
            Thank You!
        </h2>

        <p>
            We have successfully received your submission
            through <strong>{{ $form->name }}</strong>.
        </p>

        <p>
            Our team will review your message and get back to you
            if necessary.
        </p>

        <div style="
            margin-top:25px;
            padding:15px;
            background:#f9fafb;
            border-radius:8px;
        ">

            @foreach($submission->values as $item)

                @if($item->field->type !== 'html')

                    <p style="margin:7px 0;">

                        <strong>
                            {{ $item->field->label }}:
                        </strong>

                        @php
                            $value = $item->value;
                            $decoded = json_decode($value, true);

                            if (is_array($decoded)) {
                                $value = implode(', ', $decoded);
                            }
                        @endphp

                        {{ $value ?: '—' }}

                    </p>

                @endif

            @endforeach

        </div>

        <p style="
            margin-top:30px;
            color:#6b7280;
            font-size:13px;
        ">
            This is an automated confirmation email.
            Please do not reply directly to this email.
        </p>

    </div>

</body>

</html>