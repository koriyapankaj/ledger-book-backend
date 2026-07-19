<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your email</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#4f46e5; padding:28px 32px; text-align:center;">
                            <span style="color:#ffffff; font-size:20px; font-weight:700; letter-spacing:0.3px;">{{ config('app.name') }}</span>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 12px; font-size:20px; color:#111827;">Verify your email address</h1>
                            <p style="margin:0 0 24px; font-size:14px; line-height:22px; color:#4b5563;">
                                @if(!empty($name))Hi {{ $name }},<br>@endif
                                Use the code below to finish setting up your account. This code expires in
                                <strong>{{ $ttlMinutes }} minutes</strong>.
                            </p>

                            <!-- Code -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:8px 0 24px;">
                                        <div style="display:inline-block; background-color:#f3f4f6; border:1px solid #e5e7eb; border-radius:10px; padding:16px 28px;">
                                            <span style="font-size:34px; font-weight:700; letter-spacing:10px; color:#111827; font-family:'Courier New',Courier,monospace;">{{ $code }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0; font-size:13px; line-height:20px; color:#6b7280;">
                                If you didn't create an account, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 32px; border-top:1px solid #f0f0f0; text-align:center;">
                            <p style="margin:0; font-size:12px; color:#9ca3af;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
