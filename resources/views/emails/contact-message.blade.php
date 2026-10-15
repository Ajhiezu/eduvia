<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pesan Kontak Baru</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f3f4f6; margin: 0; padding: 24px; color: #1f2937;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="background-color: #E11D2E; padding: 20px 24px; color: #ffffff;">
            <h1 style="margin: 0; font-size: 18px; font-weight: bold;">Eduvia &bull; Pesan Pengunjung Baru</h1>
            <p style="margin: 4px 0 0; font-size: 13px; opacity: 0.9;">Website Resmi: {{ $schoolMessage->school?->name ?? 'Sekolah' }}</p>
        </div>

        <div style="padding: 24px;">
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
                <p style="margin: 0; font-size: 13px; color: #64748b;">Nomor Referensi:</p>
                <p style="margin: 2px 0 0; font-size: 16px; font-weight: bold; color: #E11D2E; font-family: monospace;">{{ $schoolMessage->reference_number }}</p>
            </div>

            <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px;">
                <tr>
                    <td style="padding: 8px 0; color: #64748b; width: 130px; vertical-align: top;">Nama Pengirim:</td>
                    <td style="padding: 8px 0; font-weight: 600; color: #1e293b;">{{ $schoolMessage->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; vertical-align: top;">Email:</td>
                    <td style="padding: 8px 0; color: #1e293b;">{{ $schoolMessage->email }}</td>
                </tr>
                @if($schoolMessage->phone)
                <tr>
                    <td style="padding: 8px 0; color: #64748b; vertical-align: top;">Telepon:</td>
                    <td style="padding: 8px 0; color: #1e293b;">{{ $schoolMessage->phone }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 8px 0; color: #64748b; vertical-align: top;">Kategori:</td>
                    <td style="padding: 8px 0; color: #1e293b;">{{ $schoolMessage->categoryLabel() }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; vertical-align: top;">Subjek:</td>
                    <td style="padding: 8px 0; font-weight: 600; color: #1e293b;">{{ $schoolMessage->subject }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b; vertical-align: top;">Waktu Masuk:</td>
                    <td style="padding: 8px 0; color: #1e293b;">{{ $schoolMessage->created_at?->format('d/m/Y H:i') }} WIB</td>
                </tr>
            </table>

            <div style="border-top: 1px solid #e5e7eb; padding-top: 16px;">
                <p style="margin: 0 0 8px; font-size: 13px; font-weight: bold; color: #475569; text-transform: uppercase;">Isi Pesan:</p>
                <div style="background-color: #f8fafc; border-left: 4px solid #E11D2E; padding: 12px 16px; border-radius: 4px; font-size: 14px; line-height: 1.6; white-space: pre-wrap; color: #334155;">{{ $schoolMessage->message }}</div>
            </div>

            <div style="margin-top: 24px; text-align: center;">
                <a href="{{ route('website.messages.show', $schoolMessage) }}" style="display: inline-block; background-color: #E11D2E; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: bold; padding: 10px 20px; border-radius: 8px;">
                    Buka di Dashboard Eduvia &rarr;
                </a>
            </div>
        </div>

        <div style="background-color: #f9fafb; border-top: 1px solid #e5e7eb; padding: 14px 24px; text-align: center; font-size: 11px; color: #9ca3af;">
            Email notifikasi otomatis dari Sistem Informasi Sekolah Eduvia. Harap jangan membalas langsung ke alamat email ini.
        </div>
    </div>
</body>
</html>
