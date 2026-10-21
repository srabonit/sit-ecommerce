<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $form->name }} - New Submission</title>
</head>

<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif;">

    <div style="max-width:700px;margin:30px auto;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">

        <div style="background:#111827;color:#ffffff;padding:25px;">
            <h2 style="margin:0;">
                {{ $form->name }}
            </h2>

            <p style="margin:8px 0 0;color:#d1d5db;">
                New form submission received
            </p>
        </div>

        <div style="padding:25px;">

            <table style="width:100%;border-collapse:collapse;">

                @foreach($submission->values as $item)

                    <tr>

                        <td style="
                            padding:12px;
                            border-bottom:1px solid #e5e7eb;
                            font-weight:bold;
                            width:35%;
                            vertical-align:top;
                        ">
                            {{ $item->field->label }}
                        </td>

                        <td style="
                            padding:12px;
                            border-bottom:1px solid #e5e7eb;
                            vertical-align:top;
                        ">

                            @php
                                $value = $item->value;

                                $decoded = json_decode($value, true);

                                if (is_array($decoded)) {
                                    $value = implode(', ', $decoded);
                                }
                            @endphp

                            {{ $value ?: '—' }}

                        </td>

                    </tr>

                @endforeach

            </table>

            <div style="
                margin-top:25px;
                padding:15px;
                background:#f9fafb;
                border-radius:8px;
                font-size:13px;
                color:#6b7280;
            ">
                <strong>IP Address:</strong>
                {{ $submission->ip_address }}
                <br>

                <strong>Submitted:</strong>
                {{ $submission->submitted_at }}
            </div>

        </div>

    </div>

</body>

</html>