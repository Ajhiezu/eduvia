<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolExtracurricular;
use App\Models\SchoolFacility;
use App\Models\SchoolGallery;
use App\Models\SchoolMessage;
use App\Models\SchoolPost;
use App\Models\SchoolWebsiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SchoolWebsiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        // Ensure slug is set
        if (! $school->slug) {
            $school->slug = Str::slug($school->short_name ?: $school->name);
            $school->public_website_enabled = true;
            $school->save();
        }

        // 1. Website Settings
        SchoolWebsiteSetting::updateOrCreate(
            ['school_id' => $school->id],
            [
                'hero_headline' => 'Mewujudkan Generasi Unggul, Berkarakter, dan Berdaya Saing Global',
                'hero_subheadline' => 'Sekolah terakreditasi A dengan kurikulum merdeka komprehensif, pembinaan minat bakat intensif, dan ekosistem digital terpadu.',
                'hero_cta_text' => 'Jelajahi Profil Sekolah',
                'hero_cta_url' => '#profil',
                'welcome_message' => 'Selamat datang di situs resmi SMP/SMA Nusantara Utama. Kami berkomitmen menyelenggarakan pendidikan berkualitas tinggi yang mengedepankan integritas akademik, pembentukan akhlak mulia, serta penguasaan sains dan teknologi terkini. Melalui integrasi platform pembelajaran digital Eduvia, seluruh proses akademik dan interaksi sekolah terselenggara secara transparan, kolaboratif, dan akuntabel.',
                'principal_name' => 'Drs. H. Mulyadi Kartodirdjo, M.Pd.',
                'principal_title' => 'Kepala Sekolah SMP/SMA Nusantara Utama',
                'vision' => 'Menjadi institusi pendidikan terdepan yang menghasilkan lulusan bertakwa, cerdas, berintegritas, mandiri, dan berwawasan lingkungan global.',
                'mission' => [
                    'Menyelenggarakan proses pembelajaran aktif, kreatif, efektif, dan menyenangkan berbasis teknologi digital modern.',
                    'Menumbuhkan penghayatan terhadap ajaran agama dan budi pekerti luhur bagi seluruh warga sekolah.',
                    'Mengembangkan potensi akademik dan non-akademik siswa secara optimal melalui bimbingan intensif dan kompetisi berjenjang.',
                    'Membina kemitraan harmonis antara sekolah, orang tua, dunia industri, dan masyarakat luas.',
                ],
                'history' => 'Didirikan pada tahun 1998 di kawasan Kebayoran Baru Jakarta Selatan, SMP/SMA Nusantara Utama berawal dari tekad para pendidik untuk menyediakan pendidikan bermutu tinggi dengan lingkungan belajar inklusif. Selama lebih dari dua dekade, sekolah ini telah melahirkan ribuan alumni berprestasi yang berkiprah di perguruan tinggi ternama baik di dalam maupun luar negeri.',
                'accreditation_grade' => 'A (Unggul)',
                'accreditation_no' => '028/BAN-SM/SK/2023',
                'maps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.2952865768297!2d106.7978!3d-6.2247!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f14eb1!2sKebayoran%20Baru!5e0!3m2!1sid!2sid!4v1',
                'social_links' => [
                    'facebook' => 'https://facebook.com/nusantarautama',
                    'instagram' => 'https://instagram.com/nusantarautama',
                    'youtube' => 'https://youtube.com/@nusantarautama',
                ],
                'meta_description' => 'Website Resmi SMP/SMA Nusantara Utama - Lembaga pendidikan berkarakter, berprestasi, dan terakreditasi A di Jakarta Selatan.',
                'meta_keywords' => 'SMP Nusantara Utama, SMA Nusantara Utama, Sekolah Unggulan Jakarta, PPDB Nusantara Utama',
                'theme_color' => '#E11D2E',
                'is_published' => true,
            ]
        );

        // 2. Posts (News, Events, Achievements)
        $posts = [
            // Berita
            [
                'type' => 'news',
                'title' => 'Siswa Nusantara Utama Raih Juara 1 Olimpiade Sains Nasional (OSN) Bidang Fisika 2026',
                'slug' => 'siswa-nusantara-utama-raih-juara-1-osn-fisika-2026',
                'excerpt' => 'Ahmad Faiz dari kelas XI MIPA 1 sukses mempersembahkan medali emas untuk provinsi DKI Jakarta pada ajang OSN 2026.',
                'content' => 'Ahmad Faiz, siswa berbakat kelas XI MIPA 1 Nusantara Utama, berhasil meraih medali emas pada ajang bergengsi Olimpiade Sains Nasional (OSN) bidang Fisika tahun 2026. Kompetisi yang diselenggarakan di Yogyakarta ini diikuti oleh lebih dari 500 peserta terbaik dari seluruh penjuru Indonesia.

Kepala Sekolah menyampaikan rasa bangga yang mendalam atas kerja keras Faiz dan guru pembimbing. Prestasi ini membuktikan bahwa dedikasi dalam belajar serta pembinaan intensif yang terstruktur di sekolah mampu membawa siswa bersaing di level tertinggi nasional.',
                'category' => 'Akademik & Prestasi',
                'is_published' => true,
                'published_at' => now()->subDays(6),
                'order_index' => 1,
            ],
            [
                'type' => 'news',
                'title' => 'Peringatan Bulan Bahasa dan Budaya Nusantara 2026: Menguatkan Literasi Generasi Digital',
                'slug' => 'peringatan-bulan-bahasa-dan-budaya-nusantara-2026',
                'excerpt' => 'Rangkaian festival sastra, teater tradisional, dan cipta puisi memeriahkan peringatan Bulan Bahasa di lingkungan sekolah.',
                'content' => 'Dalam rangka memperingati Bulan Bahasa dan Hari Sumpah Pemuda, OSIS SMP/SMA Nusantara Utama menyelenggarakan Pekan Budaya Nusantara selama tiga hari penuh. Berbagai perlombaan seperti debat bahasa Indonesia, cipta dan baca puisi, pidato tiga bahasa, serta pameran buku hasil karya siswa digelar dengan antusiasme luar biasa.

Acara ini ditutup dengan pergelaran teater musikal yang mengangkat cerita rakyat Nusantara dengan balutan aransemen modern oleh ekstrakurikuler musik dan tari sekolah.',
                'category' => 'Kegiatan Sekolah',
                'is_published' => true,
                'published_at' => now()->subDays(3),
                'order_index' => 2,
            ],
            [
                'type' => 'news',
                'title' => 'Draft Panduan Asesmen Sumatif Akhir Semester Ganjil 2026/2027',
                'slug' => 'draft-panduan-asesmen-sumatif-akhir-semester-ganjil',
                'excerpt' => 'Panduan internal dan tata tertib ujian sumatif yang masih dalam proses verifikasi tim kurikulum.',
                'content' => 'Dokumen ini merupakan rancangan awal panduan teknis asesmen sumatif berbasis komputer menggunakan modul CBT Eduvia...',
                'category' => 'Pengumuman',
                'is_published' => false,
                'published_at' => null,
                'order_index' => 3,
            ],

            // Events (Acara Mendatang & Lampau)
            [
                'type' => 'event',
                'title' => 'Pameran Karya Inovasi Sains & Seni (Nusantara Expo 2026)',
                'slug' => 'pameran-karya-inovasi-sains-seni-nusantara-expo-2026',
                'excerpt' => 'Pameran karya riset ilmiah remaja, robotika, seni rupa, dan bazaar kewirausahaan siswa Nusantara Utama.',
                'content' => 'Nusantara Expo 2026 merupakan agenda tahunan sekolah yang menampilkan portofolio karya terbaik peserta didik dari jenjang SMP dan SMA. Pengunjung dapat menyaksikan demonstrasi purwarupa robotika, pameran lukisan, serta presentasi produk wirausaha muda siswa.',
                'category' => 'Pameran & Eksebisi',
                'event_date' => now()->addDays(20)->setHour(8)->setMinute(0),
                'event_end_date' => now()->addDays(20)->setHour(16)->setMinute(0),
                'event_location' => 'Auditorium Utama & Lapangan Sekolah Nusantara',
                'is_published' => true,
                'published_at' => now()->subDays(2),
                'order_index' => 1,
            ],
            [
                'type' => 'event',
                'title' => 'Workshop Parenting & Konsultasi Karir Masuk Perguruan Tinggi 2026',
                'slug' => 'workshop-parenting-konsultasi-karir-perguruan-tinggi-2026',
                'excerpt' => 'Sesi pendampingan peminatan jurusan dan strategi sukses seleksi PTN (SNBP & SNBT) bersama pakar pendidikan.',
                'content' => 'Sekolah mengundang seluruh wali murid kelas IX dan XII untuk berdiskusi bersama konselor karir terkemuka guna mempersiapkan masa depan akademik anak secara terarah dan terencana.',
                'category' => 'Seminar & Edukasi',
                'event_date' => now()->addDays(35)->setHour(9)->setMinute(0),
                'event_end_date' => now()->addDays(35)->setHour(12)->setMinute(30),
                'event_location' => 'Hall Multimedia Nusantara Gedung B',
                'is_published' => true,
                'published_at' => now()->subDay(),
                'order_index' => 2,
            ],
            [
                'type' => 'event',
                'title' => 'Latihan Dasar Kepemimpinan Siswa (LDKS) OSIS dan MPK 2026',
                'slug' => 'ldks-osis-mpk-nusantara-2026',
                'excerpt' => 'Penggemblengan karakter kepemimpinan, kerja sama tim, dan manajemen organisasi bagi pengurus baru.',
                'content' => 'Kegiatan LDKS yang berlangsung selama 3 hari di Cibubur telah sukses melantik 50 pengurus OSIS dan MPK yang siap memajukan iklim partisipasi kesiswaan.',
                'category' => 'Kepemimpinan',
                'event_date' => now()->subDays(25)->setHour(7)->setMinute(0),
                'event_end_date' => now()->subDays(23)->setHour(17)->setMinute(0),
                'event_location' => 'Bumi Perkemahan Cibubur',
                'is_published' => true,
                'published_at' => now()->subDays(22),
                'order_index' => 3,
            ],

            // Achievements (Prestasi)
            [
                'type' => 'achievement',
                'title' => 'Juara 1 Lomba Cipta Robotika Tingkat Nasional TechFest 2026',
                'slug' => 'juara-1-lomba-cipta-robotika-tingkat-nasional-techfest-2026',
                'excerpt' => 'Inovasi robot pemilah sampah pintar berbasis sensor AI menghantarkan tim robotika meraih peringkat pertama.',
                'content' => 'Tim Robotika Nusantara Utama yang beranggotakan Rafi Daniswara, Bagas Pratama, dan Nabila Rahma sukses menyisihkan 64 tim dari berbagai provinsi dengan inovasi robot otonom ramah lingkungan.',
                'category' => 'Teknologi & Inovasi',
                'competition_name' => 'National TechFest Robotics Competition 2026',
                'competition_level' => 'Tingkat Nasional',
                'awardee_name' => 'Tim Robotik Nusantara Utama',
                'is_published' => true,
                'published_at' => now()->subDays(10),
                'order_index' => 1,
            ],
            [
                'type' => 'achievement',
                'title' => 'Medali Emas Kejuaraan Karate Terbuka Piala Menpora 2026',
                'slug' => 'medali-emas-kejuaraan-karate-terbuka-piala-menpora-2026',
                'excerpt' => 'Prestasi membanggakan di cabang Kata Perorangan Putri Tingkat Kadet Nasional.',
                'content' => 'Zahra Almira membuktikan ketangguhan dan kedisiplinannya dengan mengoleksi medali emas pada ajang Karate Terbuka Piala Menpora 2026 yang berlangsung di GOR Ciracas.',
                'category' => 'Olahraga & Beladiri',
                'competition_name' => 'Piala Menpora Karate Open Championship 2026',
                'competition_level' => 'Tingkat Nasional',
                'awardee_name' => 'Zahra Almira (Kelas X-2)',
                'is_published' => true,
                'published_at' => now()->subDays(14),
                'order_index' => 2,
            ],
        ];

        foreach ($posts as $postData) {
            SchoolPost::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'slug' => $postData['slug'],
                ],
                $postData
            );
        }

        // 3. Extracurriculars
        $extracurriculars = [
            [
                'name' => 'Pramuka Wajib & Garuda',
                'slug' => 'pramuka-wajib-dan-garuda',
                'category' => 'Kepanduan & Karakter',
                'description' => 'Membina kedisiplinan, kemandirian, cinta alam, dan kecakapan hidup kepanduan berstandar Pramuka Garuda.',
                'coach_name' => 'Kak Irfan Maulana',
                'schedule_info' => 'Setiap Jumat, 15:30 - 17:00 WIB',
                'location' => 'Lapangan Utama Sekolah',
                'requirements' => 'Seragam pramuka lengkap dan buku saku SKU.',
                'is_published' => true,
                'order_index' => 1,
            ],
            [
                'name' => 'Robotika & Coding Academy',
                'slug' => 'robotika-dan-coding-academy',
                'category' => 'Sains & Teknologi',
                'description' => 'Mempelajari pemrograman mikrokontroler (Arduino, ESP32), sensor cerdas, algoritma Python, dan rancang bangun robot kompetisi.',
                'coach_name' => 'Fajar Ramadhan, S.Kom.',
                'schedule_info' => 'Setiap Kamis, 15:30 - 17:30 WIB',
                'location' => 'Laboratorium Komputer & AI Lab',
                'requirements' => 'Laptop pribadi (disarankan) dan komitmen mengikuti latihan kompetisi.',
                'is_published' => true,
                'order_index' => 2,
            ],
            [
                'name' => 'English Debating Society (EDS)',
                'slug' => 'english-debating-society',
                'category' => 'Bahasa & Diplomasi',
                'description' => 'Mengasah kemampuan berpikir kritis, public speaking, dan debat parlementer format Asian Parliamentary & British Parliamentary.',
                'coach_name' => 'Sarah Amanda, B.A.',
                'schedule_info' => 'Setiap Selasa, 15:30 - 17:00 WIB',
                'location' => 'Ruang Multimedia 1',
                'requirements' => 'Minat dalam isu global dan percakapan bahasa Inggris aktif.',
                'is_published' => true,
                'order_index' => 3,
            ],
            [
                'name' => 'Palang Merah Remaja (PMR) Wira',
                'slug' => 'palang-merah-remaja-pmr',
                'category' => 'Kemanusiaan & Kesehatan',
                'description' => 'Pelatihan pertolongan pertama, kepedulian sosial, donor darah, dan kesiapsiagaan bencana berkolaborasi dengan PMI Jakarta Selatan.',
                'coach_name' => 'dr. Siti Aminah',
                'schedule_info' => 'Setiap Rabu, 15:30 - 17:00 WIB',
                'location' => 'Ruang UKS & Aula',
                'requirements' => 'Sehat jasmani dan jiwa sosial tinggi.',
                'is_published' => true,
                'order_index' => 4,
            ],
            [
                'name' => 'Klub Bola Basket & Futsal',
                'slug' => 'klub-bola-basket-dan-futsal',
                'category' => 'Olahraga Prestasi',
                'description' => 'Pembinaan teknik dasar hingga strategi tanding untuk persiapan turnamen antar sekolah tingkat kota dan provinsi.',
                'coach_name' => 'Coach Rendy Wijaya',
                'schedule_info' => 'Senin & Kamis, 16:00 - 18:00 WIB',
                'location' => 'Sport Hall Indoor Nusantara',
                'requirements' => 'Sepatu olahraga standar dan perlengkapan pengaman diri.',
                'is_published' => true,
                'order_index' => 5,
            ],
        ];

        foreach ($extracurriculars as $extra) {
            SchoolExtracurricular::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'slug' => $extra['slug'],
                ],
                $extra
            );
        }

        // 4. Facilities & Featured Programs
        $facilities = [
            [
                'name' => 'Laboratorium Komputer & AI Space',
                'category' => 'facility',
                'description' => 'Dilengkapi dengan 40 unit PC modern spesifikasi tinggi, jaringan fiber optik gigabit, dan software pemrograman terkini untuk mendukung CBT serta praktikum IT.',
                'is_published' => true,
                'order_index' => 1,
            ],
            [
                'name' => 'Perpustakaan Digital Terintegrasi & Ruang Baca Multimedia',
                'category' => 'facility',
                'description' => 'Koleksi lebih dari 8.000 judul buku fisik, ribuan e-book referensi akademik, ruang diskusi ber-AC, dan terminal akses katalog digital.',
                'is_published' => true,
                'order_index' => 2,
            ],
            [
                'name' => 'Laboratorium Sains Terpadu (Fisika, Kimia, Biologi)',
                'category' => 'facility',
                'description' => 'Peralatan praktikum modern lengkap dengan standar keselamatan laboratorium, mikroskop digital, dan alat peraga interaktif.',
                'is_published' => true,
                'order_index' => 3,
            ],
            [
                'name' => 'Sport Hall Indoor & Lapangan Multifungsi',
                'category' => 'facility',
                'description' => 'Fasilitas olahraga multifungsi untuk bola basket, bulu tangkis, futsal, dan upacara bendera berkapasitas 1.000 siswa.',
                'is_published' => true,
                'order_index' => 4,
            ],
            [
                'name' => 'Program Bimbingan Sukses Masuk PTN Unggulan',
                'category' => 'featured_program',
                'description' => 'Program intensif persiapan SNBP & SNBT, try out berkala menggunakan modul ujian digital Eduvia, dan pemetaan minat bakat sejak kelas X.',
                'is_published' => true,
                'order_index' => 1,
            ],
            [
                'name' => 'Program Pembinaan Riset Ilmiah Remaja (KIR)',
                'category' => 'featured_program',
                'description' => 'Menuntun siswa menyusun penelitian ilmiah berbobot untuk diikutsertakan dalam kompetisi riset nasional dan internasional.',
                'is_published' => true,
                'order_index' => 2,
            ],
            [
                'name' => 'Bilingual & Global Communication Pathway',
                'category' => 'featured_program',
                'description' => 'Penguatan keterampilan bahasa Inggris dan Jepang melalui native speaker sessions, workshop debat, dan pertukaran budaya virtual.',
                'is_published' => true,
                'order_index' => 3,
            ],
        ];

        foreach ($facilities as $facility) {
            SchoolFacility::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'name' => $facility['name'],
                ],
                $facility
            );
        }

        // 5. Galleries
        $galleries = [
            [
                'album_name' => 'Kegiatan Belajar Mengajar',
                'title' => 'Pembelajaran Berbasis Proyek di Lab Sains',
                'caption' => 'Siswa kelas XI melakukan eksplorasi bioteknologi ramah lingkungan dengan bimbingan guru mata pelajaran.',
                'media_path' => 'demo/gallery-science-lab.svg',
                'media_type' => 'image',
                'alt_text' => 'Praktikum siswa di lab sains',
                'is_published' => true,
                'order_index' => 1,
            ],
            [
                'album_name' => 'Kesiswaan & Ekstrakurikuler',
                'title' => 'Latihan Rutin Tim Robotika Menjelang Kompetisi',
                'caption' => 'Uji coba lintasan robot otonom di ruang robotika sekolah.',
                'media_path' => 'demo/gallery-robotics.svg',
                'media_type' => 'image',
                'alt_text' => 'Siswa merakit robot',
                'is_published' => true,
                'order_index' => 2,
            ],
            [
                'album_name' => 'Peringatan Hari Besar',
                'title' => 'Upacara Peringatan Hari Sumpah Pemuda',
                'caption' => 'Keluarga besar Nusantara Utama mengenakan pakaian adat dari berbagai penjuru daerah di Indonesia.',
                'media_path' => 'demo/gallery-sumpah-pemuda.svg',
                'media_type' => 'image',
                'alt_text' => 'Upacara pakaian adat di lapangan sekolah',
                'is_published' => true,
                'order_index' => 3,
            ],
        ];

        foreach ($galleries as $gallery) {
            SchoolGallery::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'title' => $gallery['title'],
                ],
                $gallery
            );
        }

        // 6. Guest Contact Messages
        $messages = [
            [
                'reference_number' => 'MSG-20261014-NUSA01',
                'name' => 'Budi Prasetyo, S.T.',
                'email' => 'budi.prasetyo@gmail.com',
                'phone' => '081288991234',
                'category' => 'ppdb',
                'subject' => 'Informasi Jalur Prestasi PPDB Tahun Ajaran 2026/2027',
                'message' => 'Selamat pagi Bapak/Ibu Panitia PPDB, saya ingin menanyakan perihal syarat sertifikat OSN tingkat kabupaten untuk pendaftaran jalur prestasi sains. Apakah sertifikat juara 2 dapat diajukan? Terima kasih.',
                'status' => SchoolMessage::STATUS_NEW,
                'is_read' => false,
                'internal_notes' => null,
            ],
            [
                'reference_number' => 'MSG-20261014-NUSA02',
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@yahoo.com',
                'phone' => '081399887766',
                'category' => 'ekstrakurikuler',
                'subject' => 'Pendaftaran Klub Robotika untuk Siswa Baru',
                'message' => 'Halo, apakah ekstrakurikuler robotika terbuka untuk siswa yang belum memiliki dasar pemrograman sebelumnya? Apakah ada seleksi masuk khusus?',
                'status' => SchoolMessage::STATUS_IN_PROGRESS,
                'is_read' => true,
                'read_at' => now()->subHours(5),
                'internal_notes' => 'Telah dihubungi pembina robotika Bapak Hendra via email, siswa disarankan mengikuti pertemuan orientasi perdana.',
            ],
            [
                'reference_number' => 'MSG-20261014-NUSA03',
                'name' => 'PT Solusi Teknologi Bersama',
                'email' => 'partnership@solusiteknologi.co.id',
                'phone' => '0217890123',
                'category' => 'kerjasama',
                'subject' => 'Penawaran Program Magang Industri & Kunjungan Lapangan',
                'message' => 'Kepada Yth. Kepala Sekolah Nusantara Utama, kami dari PT Solusi Teknologi Bersama bermaksud menjalin kemitraan dalam penyelenggaraan program industri teknologi bagi siswa peminatan sains & komputer.',
                'status' => SchoolMessage::STATUS_RESOLVED,
                'is_read' => true,
                'read_at' => now()->subDays(2),
                'resolved_at' => now()->subDay(),
                'internal_notes' => 'Proposal telah diterima dan dijadwalkan pertemuan audiensi pada Jumat depan.',
            ],
        ];

        foreach ($messages as $msg) {
            SchoolMessage::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'reference_number' => $msg['reference_number'],
                ],
                $msg
            );
        }
    }
}
