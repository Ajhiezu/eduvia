<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi {{ $receipt->receipt_number }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 25px;
        }
        .receipt-box {
            border: 2px solid #E11D2E;
            padding: 20px;
            border-radius: 6px;
        }
        .header {
            border-bottom: 2px solid #111827;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .school-name {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
        }
        .school-meta {
            font-size: 8.5px;
            color: #4b5563;
        }
        .receipt-title {
            text-align: right;
            float: right;
        }
        .receipt-title h2 {
            font-size: 16px;
            color: #E11D2E;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .receipt-number {
            font-family: monospace;
            font-weight: bold;
            font-size: 12px;
            margin-top: 3px;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .content-table td {
            padding: 6px 4px;
            vertical-align: top;
            font-size: 11px;
        }
        .amount-box {
            background-color: #f3f4f6;
            border: 1px dashed #9ca3af;
            padding: 8px 14px;
            display: inline-block;
            font-size: 16px;
            font-family: monospace;
            font-weight: bold;
            color: #111827;
        }
        .signature-table {
            width: 100%;
            margin-top: 25px;
        }
        .signature-table td {
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt-box">
        <div class="header">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%; vertical-align: top;">
                        <div class="school-name">{{ $school?->name ?? 'SEKOLAH EDUVIA' }}</div>
                        <div class="school-meta">NPSN: {{ $school?->npsn ?? '-' }} &bull; {{ $school?->address ?? 'Indonesia' }}</div>
                        <div class="school-meta">Telepon: {{ $school?->phone ?? '-' }} &bull; Email: {{ $school?->email ?? '-' }}</div>
                    </td>
                    <td style="width: 40%; vertical-align: top; text-align: right;">
                        <div class="receipt-title">
                            <h2>KWITANSI PEMBAYARAN</h2>
                            <div class="receipt-number">{{ $receipt->receipt_number }}</div>
                            <div style="font-size: 9px; color: #4b5563; margin-top: 3px;">Tanggal: {{ $receipt->issued_at ? $receipt->issued_at->format('d/m/Y H:i') : '-' }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <table class="content-table">
            <tr>
                <td style="width: 25%;">Telah Diterima Dari</td>
                <td style="width: 2%;">:</td>
                <td style="width: 73%; font-weight: bold; font-size: 12px; color: #111827;">
                    {{ $receipt->student?->name }} (NIS: {{ $receipt->student?->studentProfile?->nis ?? '-' }})
                </td>
            </tr>
            <tr>
                <td>Untuk Pembayaran</td>
                <td>:</td>
                <td>
                    <strong>{{ $receipt->invoice?->title }}</strong><br>
                    <span style="font-size: 9px; color: #6b7280;">No. Tagihan: {{ $receipt->invoice?->invoice_number }} &bull; Metode: {{ $receipt->payment?->method_label }}</span>
                </td>
            </tr>
            <tr>
                <td>Jumlah Pembayaran</td>
                <td>:</td>
                <td>
                    <div class="amount-box">{{ $receipt->formatted_amount }}</div>
                </td>
            </tr>
            <tr>
                <td>Status Pelunasan</td>
                <td>:</td>
                <td>
                    @if ($receipt->invoice && $receipt->invoice->remaining_balance <= 0)
                        <span style="color: #059669; font-weight: bold;">LUNAS</span>
                    @else
                        <span style="color: #d97706; font-weight: bold;">SEBAGIAN (Sisa Piutang: {{ $receipt->invoice?->formatted_remaining }})</span>
                    @endif
                </td>
            </tr>
        </table>

        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    Pembayar,<br><br><br><br>
                    ( <strong>{{ $receipt->student?->name }}</strong> )
                </td>
                <td style="width: 50%;">
                    {{ $school?->city ?? 'Kota' }}, {{ date('d F Y') }}<br>
                    Petugas / Bendahara Sekolah,<br><br><br><br>
                    ( <strong>{{ $receipt->issuer?->name ?? 'Kasir Keuangan' }}</strong> )
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
