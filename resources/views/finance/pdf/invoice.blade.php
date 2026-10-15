<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tagihan {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #E11D2E;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .school-name {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
        }
        .school-meta {
            font-size: 9px;
            color: #4b5563;
            margin-top: 2px;
        }
        .invoice-title {
            text-align: right;
            float: right;
        }
        .invoice-title h2 {
            font-size: 18px;
            color: #E11D2E;
            margin: 0;
            text-transform: uppercase;
        }
        .invoice-meta-table, .client-meta-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px;
            border-radius: 4px;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-items th {
            background-color: #f3f4f6;
            color: #374151;
            font-size: 10px;
            text-transform: uppercase;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            text-align: left;
        }
        .table-items td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-table {
            width: 45%;
            float: right;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .totals-table td {
            padding: 4px 8px;
            font-size: 11px;
        }
        .total-highlight {
            font-size: 13px;
            font-weight: bold;
            color: #E11D2E;
            border-top: 1px solid #e5e7eb;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #e5e7eb;
            color: #374151;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-issued {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .footer-note {
            clear: both;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px dashed #d1d5db;
            font-size: 9px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div class="school-name">{{ $school?->name ?? 'SEKOLAH EDUVIA' }}</div>
                    <div class="school-meta">NPSN: {{ $school?->npsn ?? '-' }} &bull; {{ $school?->address ?? 'Indonesia' }}</div>
                    <div class="school-meta">Telepon: {{ $school?->phone ?? '-' }} &bull; Email: {{ $school?->email ?? '-' }}</div>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right;">
                    <div class="invoice-title">
                        <h2>FAKTUR TAGIHAN</h2>
                        <div style="font-family: monospace; font-weight: bold; margin-top: 4px;">{{ $invoice->invoice_number }}</div>
                        <div style="margin-top: 4px;">
                            <span class="status-badge {{ $invoice->isPaid() ? 'status-paid' : 'status-issued' }}">
                                {{ $invoice->status_label }}
                            </span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="client-meta-table">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                <div class="meta-box">
                    <strong>DITAGIHKAN KEPADA:</strong><br>
                    <span style="font-size: 12px; font-weight: bold; color: #111827;">{{ $invoice->student?->name }}</span><br>
                    NIS: {{ $invoice->student?->studentProfile?->nis ?? '-' }}<br>
                    NISN: {{ $invoice->student?->studentProfile?->nisn ?? '-' }}<br>
                    Tahun Ajaran: {{ $invoice->academicYear?->name }}
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                <div class="meta-box">
                    <strong>INFORMASI TRANSAKSI:</strong><br>
                    Tanggal Terbit: {{ $invoice->issue_date ? $invoice->issue_date->format('d/m/Y') : '-' }}<br>
                    Batas Jatuh Tempo: <span style="color: #dc2626; font-weight: bold;">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</span><br>
                    Perihal: {{ $invoice->title }}<br>
                    Periode: {{ $invoice->billing_period_key ?? 'Reguler' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 50%;">Deskripsi Komponen Biaya</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 15%;" class="text-right">Harga Satuan</th>
                <th style="width: 20%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->item_name }}</strong>
                        @if ($item->discount_amount > 0)
                            <div style="font-size: 8px; color: #059669;">Potongan: - {{ $item->formatted_discount }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $item->formatted_unit_price }}</td>
                    <td class="text-right"><strong>{{ $item->formatted_subtotal }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal Kotor:</td>
            <td class="text-right">{{ $invoice->formatted_subtotal }}</td>
        </tr>
        @if ($invoice->discount_amount > 0)
            <tr>
                <td style="color: #059669;">Potongan Beasiswa:</td>
                <td class="text-right" style="color: #059669;">- {{ $invoice->formatted_discount }}</td>
            </tr>
        @endif
        <tr class="total-highlight">
            <td>TOTAL TAGIHAN:</td>
            <td class="text-right">{{ $invoice->formatted_total }}</td>
        </tr>
        <tr>
            <td style="color: #059669;">Sudah Dibayar:</td>
            <td class="text-right" style="color: #059669;">{{ $invoice->formatted_paid }}</td>
        </tr>
        <tr style="border-top: 1px solid #111827; font-weight: bold;">
            <td>SISA KEWAJIBAN:</td>
            <td class="text-right" style="color: #dc2626;">{{ $invoice->formatted_remaining }}</td>
        </tr>
    </table>

    <div class="footer-note">
        <strong>Ketentuan Pembayaran:</strong><br>
        1. Pembayaran tunai dapat dilakukan di Bagian Keuangan / Kasir Sekolah pada jam kerja.<br>
        2. Pembayaran transfer bank harap menyertakan nomor tagihan (<strong>{{ $invoice->invoice_number }}</strong>) pada berita transfer.<br>
        3. Dokumen ini dicetak otomatis dari Sistem Manajemen Keuangan Eduvia dan merupakan dokumen penagihan sah.
    </div>
</body>
</html>
