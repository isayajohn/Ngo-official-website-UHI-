<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('ughi.defaults.organization_name') }}</title>
</head>
<body style="margin:0;background:#f6f8fa;color:#17212b;font-family:Arial,sans-serif;">
    <table role="presentation" style="width:100%;border-collapse:collapse;background:#f6f8fa;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" style="width:100%;max-width:600px;border-collapse:collapse;background:#fff;border:1px solid #e5ecea;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px 32px;background:#0870bd;color:#fff;font-size:18px;font-weight:bold;">
                            {{ config('ughi.defaults.organization_name') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;line-height:1.6;">
                            @yield('content')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
