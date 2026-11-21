<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Toyota AutoConnect</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #F8FAFC;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1E293B;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #F8FAFC;
            padding-bottom: 40px;
        }
        .content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #E2E8F0;
            margin-top: 30px;
        }
        .header {
            background-color: #0F172A;
            padding: 24px 32px;
            text-align: left;
            border-bottom: 3px solid #EB0A1E;
        }
        .brand-title {
            color: #FFFFFF;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .brand-subtitle {
            color: #94A3B8;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .body-content {
            padding: 32px;
        }
        .category-badge {
            display: inline-block;
            background-color: #FEE2E2;
            color: #991B1B;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 10px;
            border-radius: 9999px;
            margin-bottom: 16px;
        }
        .notification-title {
            font-size: 20px;
            font-weight: 700;
            color: #0F172A;
            margin: 0 0 16px 0;
            line-height: 1.4;
        }
        .notification-body {
            font-size: 14px;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 24px;
            white-space: pre-line;
        }
        .button-wrapper {
            margin: 28px 0;
        }
        .action-button {
            display: inline-block;
            background-color: #EB0A1E;
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
        }
        .footer {
            padding: 24px 32px;
            background-color: #F8FAFC;
            border-top: 1px solid #E2E8F0;
            font-size: 12px;
            color: #64748B;
            line-height: 1.5;
            text-align: center;
        }
        .footer a {
            color: #EB0A1E;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="content">
            <div class="header">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <div class="brand-title">TOYOTA <span style="color: #EB0A1E;">AutoConnect</span></div>
                            <div class="brand-subtitle">Official Dealer Network Platform</div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="body-content">
                @if(!empty($categoryName))
                    <div class="category-badge">{{ $categoryName }}</div>
                @endif

                <h1 class="notification-title">{{ $title }}</h1>

                <div class="notification-body">
                    {!! nl2br(e($messageText)) !!}
                </div>

                @if(!empty($actionUrl))
                    <div class="button-wrapper">
                        <a href="{{ $actionUrl }}" class="action-button" target="_blank">
                            {{ $actionText ?? 'Buka Aplikasi' }} &rarr;
                        </a>
                    </div>
                @endif
            </div>

            <div class="footer">
                <p style="margin: 0 0 8px 0;">
                    Pemberitahuan ini dikirim secara otomatis oleh <strong>Toyota AutoConnect</strong> untuk akun Anda ({{ $recipientEmail }}).
                </p>
                <p style="margin: 0 0 8px 0;">
                    Anda dapat mengatur preferensi notifikasi Anda melalui
                    <a href="{{ url('/customer/notification-preferences') }}">Pengaturan Notifikasi</a>.
                </p>
                <p style="margin: 0; font-size: 11px; color: #94A3B8;">
                    &copy; {{ date('Y') }} PT Toyota AutoConnect Indonesia. Seluruh hak cipta dilindungi.
                </p>
            </div>
        </div>
    </div>
</body>
</html>

