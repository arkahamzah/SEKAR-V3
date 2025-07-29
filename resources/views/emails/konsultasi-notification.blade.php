<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $actionText }} - SEKAR</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .content {
            padding: 40px;
        }
        .action-badge {
            display: inline-block;
            background-color: {{ $actionColor }};
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2d3748;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 25px 0;
            background-color: #f7fafc;
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid {{ $actionColor }};
        }
        .info-item {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .info-value {
            color: #2d3748;
            font-size: 15px;
            word-wrap: break-word;
        }
        .description-box {
            background-color: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin: 25px 0;
        }
        .description-box h3 {
            color: #2d3748;
            font-size: 16px;
            margin: 0 0 15px 0;
            font-weight: 600;
        }
        .description-text {
            color: #4a5568;
            line-height: 1.7;
            white-space: pre-line;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin: 25px 0;
            transition: all 0.3s ease;
        }
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .footer {
            background-color: #f7fafc;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 0;
            color: #718096;
            font-size: 14px;
            line-height: 1.5;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 25px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-open { background-color: #ebf8ff; color: #2b6cb0; }
        .status-in-progress { background-color: #fefcbf; color: #975a16; }
        .status-closed { background-color: #f0f0f0; color: #4a5568; }
        
        @media only screen and (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 0;
            }
            .header, .content, .footer {
                padding: 20px;
            }
            .info-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>SEKAR - Serikat Karyawan Telkom</h1>
            <p>Sistem Konsultasi & Advokasi</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Action Badge -->
            <div class="action-badge">{{ $actionText }}</div>

            <!-- Greeting -->
            <div class="greeting">
                @if($actionType === 'new')
                    Pengajuan {{ strtolower($konsultasi->JENIS) }} baru telah diterima dan akan segera ditindaklanjuti.
                @elseif($actionType === 'comment')
                    Ada komentar baru pada {{ strtolower($konsultasi->JENIS) }} yang perlu ditindaklanjuti.
                @elseif($actionType === 'escalate')
                    {{ ucfirst($konsultasi->JENIS) }} telah diescalate ke level yang lebih tinggi untuk penanganan lebih lanjut.
                @elseif($actionType === 'closed')
                    {{ ucfirst($konsultasi->JENIS) }} telah diselesaikan dan ditutup.
                @else
                    Ada pembaruan pada {{ strtolower($konsultasi->JENIS) }} yang perlu Anda ketahui.
                @endif
            </div>

            <!-- Information Grid -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Judul</div>
                    <div class="info-value">{{ $konsultasi->JUDUL }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Jenis</div>
                    <div class="info-value">{{ ucfirst($konsultasi->JENIS) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tujuan</div>
                    <div class="info-value">
                        {{ $konsultasi->TUJUAN }}
                        @if($konsultasi->TUJUAN_SPESIFIK)
                            - {{ $konsultasi->TUJUAN_SPESIFIK }}
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ strtolower(str_replace('_', '-', $konsultasi->STATUS)) }}">
                            {{ $konsultasi->STATUS }}
                        </span>
                    </div>
                </div>
                @if($karyawan)
                <div class="info-item">
                    <div class="info-label">Pengirim</div>
                    <div class="info-value">{{ $karyawan->V_NAMA_KARYAWAN }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">NIK</div>
                    <div class="info-value">{{ $konsultasi->N_NIK }}</div>
                </div>
                @endif
                <div class="info-item">
                    <div class="info-label">Tanggal</div>
                    <div class="info-value">{{ date('d F Y, H:i', strtotime($konsultasi->CREATED_AT)) }} WIB</div>
                </div>
                @if($konsultasi->KATEGORI_ADVOKASI)
                <div class="info-item">
                    <div class="info-label">Kategori</div>
                    <div class="info-value">{{ $konsultasi->KATEGORI_ADVOKASI }}</div>
                </div>
                @endif
            </div>

            <!-- Description -->
            <div class="description-box">
                <h3>Deskripsi {{ ucfirst($konsultasi->JENIS) }}</h3>
                <div class="description-text">{{ $konsultasi->DESKRIPSI }}</div>
            </div>

            <!-- Action Button -->
            <div style="text-align: center;">
                <a href="{{ $viewUrl }}" class="action-button">
                    Lihat Detail {{ ucfirst($konsultasi->JENIS) }}
                </a>
            </div>

            <!-- Divider -->
            <div class="divider"></div>

            <!-- Additional Information -->
            <div style="background-color: #f7fafc; padding: 20px; border-radius: 6px; margin-top: 25px;">
                <h4 style="margin: 0 0 15px 0; color: #2d3748; font-size: 16px;">Informasi Penting</h4>
                <ul style="margin: 0; padding-left: 20px; color: #4a5568;">
                    @if($actionType === 'new')
                        <li>{{ ucfirst($konsultasi->JENIS) }} Anda telah diterima dengan ID: <strong>{{ $konsultasi->ID }}</strong></li>
                        <li>Tim admin akan menindaklanjuti dalam 1-3 hari kerja</li>
                        <li>Anda akan mendapat notifikasi setiap ada pembaruan</li>
                        <li>Silakan login ke sistem untuk melihat perkembangan</li>
                    @elseif($actionType === 'comment')
                        <li>Ada tanggapan baru yang perlu Anda lihat</li>
                        <li>Silakan login untuk melihat detail komentar</li>
                        <li>Anda dapat memberikan respon atau informasi tambahan</li>
                    @elseif($actionType === 'escalate')
                        <li>{{ ucfirst($konsultasi->JENIS) }} telah dieskalasi ke level {{ $konsultasi->TUJUAN }}</li>
                        <li>Penanganan akan dilakukan oleh tim yang lebih senior</li>
                        <li>Anda akan mendapat update status selanjutnya</li>
                    @elseif($actionType === 'closed')
                        <li>{{ ucfirst($konsultasi->JENIS) }} telah diselesaikan dan ditutup</li>
                        <li>Terima kasih atas kepercayaan Anda pada SEKAR</li>
                        <li>Silakan ajukan {{ strtolower($konsultasi->JENIS) }} baru jika diperlukan</li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>SEKAR - Serikat Karyawan Telkom</strong><br>
                Email ini dikirim secara otomatis dari sistem konsultasi SEKAR.<br>
                Jika Anda memiliki pertanyaan, silakan hubungi admin melalui sistem atau email resmi.
            </p>
            <p style="margin-top: 15px;">
                <a href="{{ config('app.url') }}">Kunjungi Portal SEKAR</a> | 
                <a href="mailto:admin@sekar.telkom.co.id">Kontak Admin</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #a0aec0;">
                © {{ date('Y') }} SEKAR. Semua hak cipta dilindungi undang-undang.
            </p>
        </div>
    </div>
</body>
</html>