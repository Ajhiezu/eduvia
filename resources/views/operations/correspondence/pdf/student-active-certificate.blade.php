<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Siswa Aktif - {{ $student->name }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 20mm 20mm 20mm 20mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #111827;
            margin: 0;
            padding: 0;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #111827;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .kop-header {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0;
        }
        .kop-school-name {
            font-size: 16pt;
            font-weight: bold;
            color: #E11D2E;
            text-transform: uppercase;
            margin: 4px 0;
            letter-spacing: 0.5px;
        }
        .kop-details {
            font-size: 9pt;
            color: #4B5563;
            margin: 0;
            line-height: 1.4;
        }
        .doc-title-container {
            text-align: center;
            margin-bottom: 24px;
        }
        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        .doc-number {
            font-size: 10pt;
            color: #374151;
            margin-top: 3px;
        }
        .content-paragraph {
            text-align: justify;
            margin-bottom: 14px;
            text-indent: 30px;
        }
        .info-table {
            width: 90%;
            margin: 15px auto 20px auto;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 6px;
            font-size: 10.5pt;
            vertical-align: top;
        }
        .info-table td.label {
            width: 32%;
            color: #374151;
        }
        .info-table td.separator {
            width: 3%;
            text-align: center;
        }
        .info-table td.value {
            width: 65%;
            font-weight: bold;
            color: #111827;
        }
        .signature-section {
            margin-top: 40px;
            float: right;
            width: 250px;
            text-align: left;
        }
        .signature-date {
            font-size: 10.5pt;
            margin-bottom: 6px;
        }
        .signature-title {
            font-size: 10.5pt;
            font-weight: bold;
            margin-bottom: 65px;
        }
        .signature-name {
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }
        .signature-nip {
            font-size: 9.5pt;
            color: #4B5563;
            margin: 0;
        }
        .footer-note {
            clear: both;
            margin-top: 80px;
            font-size: 8pt;
            color: #9CA3AF;
            border-top: 1px solid #E5E7EB;
            padding-top: 6px;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <div class="kop-surat">
        <p class="kop-header">Pemerintah Provinsi / Yayasan Pendidikan</p>
        <p class="kop-header">Sistem Manajemen Sekolah &amp; E-Learning Eduvia</p>
        <h1 class="kop-school-name">{{ $school->name }}</h1>
        <p class="kop-details">
            NPSN: {{ $school->npsn ?? '30201928' }} &bull; Jenjang: {{ strtoupper($school->educational_level ?? 'Sekolah Menengah') }}<br>
            {{ $school->address ?? 'Jl. Pendidikan Nasional No. 45, Jakarta' }}<br>
            Telepon: (021) 7890123 &bull; Email: info@{{ $school->slug ?? 'eduvia' }}.sch.id &bull; Website: {{ config('app.url') }}
        </p>
    </div>

    <!-- JUDUL DAN NOMOR SURAT -->
    <div class="doc-title-container">
        <h2 class="doc-title">SURAT KETERANGAN SISWA AKTIF</h2>
        <p class="doc-number">Nomor: {{ $letterNumber }}</p>
    </div>

    <!-- ISI SURAT -->
    <p class="content-paragraph">
        Yang bertanda tangan di bawah ini Kepala Sekolah <strong>{{ $school->name }}</strong>, menerangkan dengan sebenarnya bahwa:
    </p>

    <table class="info-table">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td class="separator">:</td>
            <td class="value">{{ $student->name }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Induk Siswa (NIS)</td>
            <td class="separator">:</td>
            <td class="value">{{ $student->studentProfile->nis ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Induk Siswa Nasional (NISN)</td>
            <td class="separator">:</td>
            <td class="value">{{ $student->studentProfile->nisn ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tempat, Tanggal Lahir</td>
            <td class="separator">:</td>
            <td class="value">
                {{ $student->studentProfile->birth_place ?? '-' }}, 
                {{ $student->studentProfile->birth_date ? \Carbon\Carbon::parse($student->studentProfile->birth_date)->translatedFormat('d F Y') : '-' }}
            </td>
        </tr>
        <tr>
            <td class="label">Jenis Kelamin</td>
            <td class="separator">:</td>
            <td class="value">{{ in_array($student->studentProfile->gender ?? '', ['female', 'P'], true) ? 'Perempuan' : 'Laki-laki' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Orang Tua / Wali</td>
            <td class="separator">:</td>
            <td class="value">{{ $student->studentProfile->parent_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Alamat Domisili</td>
            <td class="separator">:</td>
            <td class="value" style="font-weight: normal;">{{ $student->studentProfile->address ?? '-' }}</td>
        </tr>
    </table>

    <p class="content-paragraph">
        Adalah benar-benar peserta didik yang terdaftar aktif mengikuti kegiatan belajar mengajar pada <strong>{{ $school->name }}</strong> pada Tahun Ajaran berjalan dan senantiasa berkelakuan baik serta menaati seluruh tata tertib sekolah.
    </p>

    <p class="content-paragraph">
        Surat keterangan ini kami terbitkan secara resmi atas permintaan yang bersangkutan untuk dipergunakan sebagai: <strong>{{ $purpose }}</strong>.
    </p>

    <p class="content-paragraph">
        Demikian surat keterangan ini dibuat dengan sebenar-benarnya untuk dapat dipergunakan sebagaimana mestinya oleh pihak yang berkepentingan.
    </p>

    <!-- TANDA TANGAN KEPALA SEKOLAH -->
    <div class="signature-section">
        <div class="signature-date">
            Jakarta, {{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}
        </div>
        <div class="signature-title">
            Kepala Sekolah,
        </div>
        <div class="signature-name">
            {{ $school->principal_name ?? 'Drs. H. Mulyadi, M.Pd.' }}
        </div>
        <div class="signature-nip">
            NIP. {{ $school->principal_nip ?? '19740512 199903 1 002' }}
        </div>
    </div>

    <div class="footer-note">
        Dokumen resmi ini digenerate secara otomatis melalui Eduvia School Management System &bull; Keabsahan dokumen dapat diverifikasi pada Tata Usaha Sekolah.
    </div>

</body>
</html>
