<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SEKAR</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            text-align: center;
            padding: 30px 20px;
        }
        .header img {
            max-height: 50px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            color: #555;
            margin-bottom: 30px;
            line-height: 1.7;
        }
        .reset-button {
            text-align: center;
            margin: 30px 0;
        }
        .reset-button a {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .reset-button a:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        .info-box {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #1e3a8a;
            font-size: 16px;
        }
        .info-box ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
            color: #555;
        }
        .info-box li {
            margin-bottom: 5px;
            font-size: 14px;
        }
        .footer {
            background: #f8fafc;
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
        }
        .security-note {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .security-note p {
            margin: 0;
            color: #dc2626;
            font-size: 14px;
            font-weight: 500;
        }
        .token-info {
            background: #f0f9ff;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 12px;
            color: #0369a1;
            word-break: break-all;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
            }
            .header {
                padding: 20px 15px;
            }
            .header h1 {
                font-size: 20px;
            }
            .content {
                padding: 20px 15px;
            }
            .reset-button a {
                padding: 12px 25px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🔐 Reset Password SEKAR</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Sistem Elektronik Karyawan</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Halo, <strong>{{ $user->name }}</strong>
            </div>

            <div class="message">
                <p>Kami menerima permintaan untuk mereset password akun SEKAR Anda yang terdaftar dengan NIK <strong>{{ $user->nik }}</strong>.</p>
                <p>Jika Anda memang melakukan permintaan ini, silakan klik tombol di bawah untuk melanjutkan proses reset password:</p>
            </div>

            <!-- Reset Button -->
            <div class="reset-button">
                <a href="{{ $resetUrl }}" target="_blank">
                    🔑 Reset Password Sekarang
                </a>
            </div>

            <!-- Information Box -->
            <div class="info-box">
                <h3>📋 Informasi Penting</h3>
                <ul>
                    <li><strong>Link berlaku hingga:</strong> {{ $validUntil }}</li>
                    <li><strong>NIK:</strong> {{ $user->nik }}</li>
                    <li><strong>Email:</strong> {{ $user->email }}</li>
                    <li>Anda akan diminta memasukkan password portal untuk konfirmasi</li>
                    <li>Setelah reset berhasil, gunakan password portal sebagai password login</li>
                </ul>
            </div>

            <!-- Security Note -->
            <div class="security-note">
                <p>⚠️ Jika Anda tidak meminta reset password, abaikan email ini atau hubungi administrator segera.</p>
            </div>

            <!-- Alternative Link -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <p style="color: #666; font-size: 14px; margin-bottom: 10px;">
                    <strong>Tombol tidak bekerja?</strong> Salin dan tempel link berikut di browser Anda:
                </p>
                <div class="token-info">
                    {{ $resetUrl }}
                </div>
            </div>

            <div style="margin-top: 20px; color: #666; font-size: 14px;">
                <p><strong>Butuh bantuan?</strong></p>
                <p>Hubungi administrator SEKAR atau IT Support jika mengalami kesulitan.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>SEKAR - Sistem Elektronik Karyawan</strong></p>
            <p>Email otomatis, mohon tidak membalas email ini.</p>
            <p style="color: #999; font-size: 12px;">
                Dikirim pada {{ now()->format('d M Y, H:i') }} WIB
            </p>
        </div>
    </div>
</body>
</html>