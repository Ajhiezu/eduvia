<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Hasil Belajar (Rapor) — {{ $data['student']['name'] ?? 'Siswa' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #1F2937;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
        }

        /* School Header */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .kop-logo {
            width: 65px;
            vertical-align: middle;
            text-align: center;
        }

        .kop-text {
            text-align: center;
            vertical-align: middle;
        }

        .kop-title {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .kop-school {
            font-size: 16px;
            font-weight: 800;
            color: #E11D2E;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .kop-meta {
            font-size: 9px;
            color: #4B5563;
            line-height: 1.3;
        }

        .kop-divider {
            border-bottom: 2px solid #1F2937;
            border-top: 1px solid #1F2937;
            height: 2px;
            margin-top: 4px;
            margin-bottom: 12px;
        }

        .report-heading {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            color: #1F2937;
        }

        /* Identity Meta Grid */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10px;
        }

        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .meta-label {
            width: 15%;
            color: #4B5563;
        }

        .meta-colon {
            width: 2%;
            text-align: center;
        }

        .meta-val {
            width: 33%;
            font-weight: bold;
            color: #111827;
        }

        /* Academic Table */
        .academic-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5px;
        }

        .academic-table th,
        .academic-table td {
            border: 1px solid #D1D5DB;
            padding: 5px 6px;
            vertical-align: middle;
        }

        .academic-table th {
            background-color: #F9FAFB;
            color: #111827;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 9px;
        }

        .academic-table .text-center {
            text-align: center;
        }

        .academic-table .text-right {
            text-align: right;
        }

        .font-semibold {
            font-weight: bold;
        }

        .section-subheading {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            margin-top: 10px;
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 1px solid #E5E7EB;
        }

        /* Non-academic tables */
        .split-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .split-grid td {
            vertical-align: top;
            padding: 0;
        }

        .box-container {
            border: 1px solid #D1D5DB;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 12px;
            background-color: #FAFAFA;
        }

        .box-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1F2937;
            margin-bottom: 4px;
        }

        .notes-content {
            font-size: 10px;
            color: #374151;
            font-style: italic;
            line-height: 1.5;
        }

        /* Signature block */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .sig-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 10px;
        }

        .sig-space {
            height: 60px;
        }

        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            color: #111827;
        }

        .sig-nip {
            color: #6B7280;
            font-size: 9px;
            margin-top: 2px;
        }

        .badge-pass {
            color: #065F46;
            font-weight: bold;
        }

        .badge-fail {
            color: #991B1B;
            font-weight: bold;
        }

        .footer-note {
            margin-top: 15px;
            font-size: 8px;
            color: #9CA3AF;
            text-align: center;
            border-top: 1px dashed #E5E7EB;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT SEKOLAH -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if(!empty($data['school']['logo_url']))
                    <img src="{{ $data['school']['logo_url'] }}" alt="Logo" style="max-height: 55px; max-width: 60px;">
                @else
                    <div style="width: 50px; height: 50px; line-height: 50px; background-color: #E11D2E; color: white; font-weight: bold; border-radius: 6px; font-size: 18px; margin: 0 auto;">E</div>
                @endif
            </td>
            <td class="kop-text">
                <div class="kop-title">Pemerintah Provinsi / Yayasan Pendidikan</div>
                <div class="kop-school">{{ $data['school']['name'] }}</div>
                <div class="kop-meta">
                    NPSN: {{ $data['school']['npsn'] }} | Alamat: {{ $data['school']['address'] }}, {{ $data['school']['city'] }}<br>
                    Telepon: {{ $data['school']['phone'] }} | Surel: {{ $data['school']['email'] }}
                </div>
            </td>
        </tr>
    </table>

    <div class="kop-divider"></div>

    <div class="report-heading">Laporan Hasil Penilaian Capaian Pembelajaran Semester</div>

    <!-- IDENTITAS SISWA & PERIODE AKADEMIK -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Siswa</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $data['student']['name'] }}</td>

            <td class="meta-label">Kelas / Rombel</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $data['academic']['classroom_name'] }} (Tingkat {{ $data['academic']['grade_level'] }})</td>
        </tr>
        <tr>
            <td class="meta-label">Nomor Induk (NIS)</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $data['student']['nis'] }}</td>

            <td class="meta-label">Tahun Ajaran</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $data['academic']['academic_year_name'] }}</td>
        </tr>
        <tr>
            <td class="meta-label">NISN</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $data['student']['nisn'] }}</td>

            <td class="meta-label">Semester</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $data['academic']['semester_name'] }}</td>
        </tr>
    </table>

    <!-- A. PENILAIAN AKADEMIK -->
    <div class="section-subheading">A. Capaian Kompetensi Akademik</div>
    <table class="academic-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%; text-align: left;">Mata Pelajaran</th>
                <th style="width: 8%;">KKM</th>
                <th style="width: 10%;">Nilai Akhir</th>
                <th style="width: 8%;">Predikat</th>
                <th style="width: 44%; text-align: left;">Deskripsi Capaian Pembelajaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['items'] as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ $item['subject_name'] }}</td>
                    <td class="text-center">{{ number_format($item['passing_grade'], 0) }}</td>
                    <td class="text-center font-semibold" style="font-size: 10px;">{{ number_format($item['final_score'], 1) }}</td>
                    <td class="text-center font-semibold">{{ $item['predicate'] }}</td>
                    <td style="font-size: 9px; line-height: 1.35;">{{ $item['achievement_description'] ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="font-style: italic; color: #9CA3AF; padding: 12px;">Belum ada data nilai mata pelajaran yang terverifikasi.</td>
                </tr>
            @endforelse
            @if(count($data['items']) > 0)
                <tr style="background-color: #F9FAFB; font-weight: bold;">
                    <td colspan="3" class="text-center">RATA-RATA NILAI AKHIR SEMESTER</td>
                    <td class="text-center" style="color: #E11D2E; font-size: 10.5px;">{{ number_format($data['summary']['average_score'] ?? 0, 1) }}</td>
                    <td colspan="2" style="font-size: 9px; color: #4B5563;">
                        Tuntas: {{ $data['summary']['passed_count'] ?? 0 }} mata pelajaran | Perlu Perbaikan: {{ $data['summary']['failed_count'] ?? 0 }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- B. PENILAIAN NONAKADEMIK & KEHADIRAN -->
    <div class="section-subheading">B. Kehadiran & Perkembangan Diri Siswa</div>
    <table class="split-grid">
        <tr>
            <!-- Rekap Kehadiran -->
            <td style="width: 42%; padding-right: 10px;">
                <table class="academic-table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: left;">Rekapitulasi Presensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sakit (S)</td>
                            <td class="text-center font-semibold">{{ $data['attendance']['sick'] ?? 0 }} hari</td>
                        </tr>
                        <tr>
                            <td>Izin (I)</td>
                            <td class="text-center font-semibold">{{ $data['attendance']['permit'] ?? 0 }} hari</td>
                        </tr>
                        <tr>
                            <td>Tanpa Keterangan (A)</td>
                            <td class="text-center font-semibold" style="{{ ($data['attendance']['absent'] ?? 0) > 0 ? 'color: #DC2626;' : '' }}">{{ $data['attendance']['absent'] ?? 0 }} hari</td>
                        </tr>
                        <tr>
                            <td>Hadir Terdata</td>
                            <td class="text-center font-semibold">{{ $data['attendance']['present'] ?? 0 }} sesi</td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- Ekstrakurikuler -->
            <td style="width: 58%;">
                <table class="academic-table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 35%; text-align: left;">Kegiatan Ekstrakurikuler</th>
                            <th style="width: 15%;">Nilai</th>
                            <th style="width: 50%; text-align: left;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['extracurriculars'] as $ekskul)
                            <tr>
                                <td class="font-semibold">{{ $ekskul['name'] }}</td>
                                <td class="text-center font-semibold">{{ $ekskul['predicate'] ?? 'A' }}</td>
                                <td style="font-size: 8.5px;">{{ $ekskul['description'] ?? 'Aktif mengikuti kegiatan' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center" style="font-style: italic; color: #9CA3AF; padding: 10px;">Tidak ada kegiatan ekstrakurikuler yang diikuti.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- CATATAN WALI KELAS -->
    <div class="box-container">
        <div class="box-title">Catatan Wali Kelas:</div>
        <div class="notes-content">
            "{{ $data['homeroom_notes'] ?: 'Teruslah bersemangat dalam menuntut ilmu, kembangkan potensi bakat dan jaga integritas akhlak mulia dalam setiap langkah pembelajaran.' }}"
        </div>
    </div>

    <!-- PENGESAHAN TANDA TANGAN -->
    <table class="sig-table">
        <tr>
            <td>
                Mengetahui,<br>
                Orang Tua / Wali Siswa
                <div class="sig-space"></div>
                <div class="sig-name">...................................................</div>
            </td>
            <td>
                Wali Kelas {{ $data['academic']['classroom_name'] }}
                <div class="sig-space"></div>
                <div class="sig-name">{{ $data['homeroom_teacher']['name'] }}</div>
                <div class="sig-nip">NIP: {{ $data['homeroom_teacher']['nip'] ?: '-' }}</div>
            </td>
            <td>
                {{ $data['school']['city'] }}, {{ $data['published_info']['published_at'] ?? now()->translatedFormat('d F Y') }}<br>
                Kepala Sekolah
                <div class="sig-space"></div>
                <div class="sig-name">{{ $data['school']['principal_name'] }}</div>
                <div class="sig-nip">NIP: {{ $data['school']['principal_nip'] ?: '-' }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini diterbitkan secara sah dan terekam dalam Sistem Manajemen Pendidikan Eduvia Platform. Snapshot Verifikasi: {{ $data['generated_at'] ?? now()->toISOString() }}.
    </div>

</body>
</html>
