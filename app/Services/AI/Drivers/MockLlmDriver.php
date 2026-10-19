<?php

namespace App\Services\AI\Drivers;

use App\Services\AI\Contracts\LlmDriverInterface;
use RuntimeException;

class MockLlmDriver implements LlmDriverInterface
{
    protected bool $shouldFail = false;

    protected string $failureMessage = 'Provider connection timeout (simulated)';

    protected ?array $customResponse = null;

    /**
     * Force the mock driver to fail for testing timeout/error resilience.
     */
    public function setFailure(bool $fail = true, string $message = 'Provider connection timeout (simulated)'): self
    {
        $this->shouldFail = $fail;
        $this->failureMessage = $message;

        return $this;
    }

    /**
     * Set a custom structured response.
     */
    public function setResponse(array $response): self
    {
        $this->customResponse = $response;

        return $this;
    }

    /**
     * Generate structured mock response.
     */
    public function generateStructured(string $systemPrompt, string $userContent, array $options = []): array
    {
        if ($this->shouldFail) {
            throw new RuntimeException($this->failureMessage);
        }

        if ($this->customResponse !== null) {
            return [
                'content' => $this->customResponse,
                'prompt_tokens' => 250,
                'completion_tokens' => 450,
                'total_tokens' => 700,
                'model' => 'mock-model-v1',
                'provider' => 'mock',
            ];
        }

        $lowerText = strtolower($systemPrompt.' '.$userContent);

        // Determine type of request from system prompt/user content
        if (str_contains($lowerText, 'modul ajar') || str_contains($lowerText, 'modul_ajar') || str_contains($lowerText, 'rpp')) {
            $content = [
                'title' => 'Modul Ajar: Ekosistem dan Rantai Makanan',
                'grade_level' => 'SMP Kelas 7',
                'duration' => '2 x 40 menit',
                'learning_objectives' => [
                    'Peserta didik mampu mengidentifikasi komponen biotik dan abiotik dalam ekosistem.',
                    'Peserta didik mampu menganalisis hubungan timbal balik antar makhluk hidup melalui rantai makanan.',
                ],
                'pancasila_profile' => ['Bernalar Kritis', 'Gotong Royong'],
                'learning_activities' => [
                    'pendahuluan' => 'Guru menyapa, berdoa bersama, dan menampilkan video singkat interaksi organisme di alam.',
                    'inti' => 'Diskusi kelompok pembuatan diagram jaring-jaring makanan dan presentasi perwakilan siswa.',
                    'penutup' => 'Refleksi pembelajaran bersama, kuis kilat 3 menit, dan salam penutup.',
                ],
                'assessment_method' => 'Observasi unjuk kerja diskusi kelompok dan kuis formatif akhir sesi.',
            ];
        } elseif (str_contains($lowerText, 'soal ujian') || str_contains($lowerText, 'soal_ujian') || str_contains($lowerText, 'cbt') || str_contains($lowerText, 'butir soal')) {
            $content = [
                'questions' => [
                    [
                        'number' => 1,
                        'question_type' => 'multiple_choice_single',
                        'bloom_taxonomy' => 'C3',
                        'difficulty' => 'mudah',
                        'question_text' => 'Organisme yang berperan sebagai produsen utama dalam ekosistem darat adalah...',
                        'content' => 'Organisme yang berperan sebagai produsen utama dalam ekosistem darat adalah...',
                        'options' => [
                            ['key' => 'A', 'text' => 'Tumbuhan hijau penghasil klorofil', 'is_correct' => true],
                            ['key' => 'B', 'text' => 'Bakteri pengurai', 'is_correct' => false],
                            ['key' => 'C', 'text' => 'Kelinci herbivora', 'is_correct' => false],
                            ['key' => 'D', 'text' => 'Elang karnivora', 'is_correct' => false],
                        ],
                        'correct_answer' => 'A',
                        'default_score' => 10.0,
                        'score' => 10.0,
                        'explanation' => 'Tumbuhan hijau mampu melakukan fotosintesis untuk mengubah energi matahari menjadi energi kimia (produsen).',
                    ],
                    [
                        'number' => 2,
                        'question_type' => 'true_false',
                        'bloom_taxonomy' => 'C2',
                        'difficulty' => 'sedang',
                        'question_text' => 'Dekomposer bertugas menguraikan sisa organisme mati menjadi unsur hara yang menyuburkan tanah.',
                        'content' => 'Dekomposer bertugas menguraikan sisa organisme mati menjadi unsur hara yang menyuburkan tanah.',
                        'options' => [
                            ['key' => 'true', 'text' => 'Benar', 'is_correct' => true],
                            ['key' => 'false', 'text' => 'Salah', 'is_correct' => false],
                        ],
                        'correct_answer' => 'true',
                        'default_score' => 10.0,
                        'score' => 10.0,
                        'explanation' => 'Dekomposer seperti jamur dan bakteri saprofit mengembalikan bahan organik ke siklus hara tanah.',
                    ],
                    [
                        'number' => 3,
                        'question_type' => 'essay',
                        'bloom_taxonomy' => 'C4',
                        'difficulty' => 'sukar',
                        'question_text' => 'Jelaskan dampak yang terjadi terhadap populasi herbivora apabila populasi karnivora puncak mengalami kepunahan mendadak!',
                        'content' => 'Jelaskan dampak yang terjadi terhadap populasi herbivora apabila populasi karnivora puncak mengalami kepunahan mendadak!',
                        'options' => null,
                        'correct_answer' => 'Populasi herbivora akan melonjak drastis tanpa kendali pemangsaan alami, menyebabkan overgrazing vegetasi produsen hingga akhirnya memicu krisis pangan ekosistem.',
                        'default_score' => 20.0,
                        'score' => 20.0,
                        'explanation' => 'Ketiadaan predator puncak merusak keseimbangan rantai trofik secara berantai (trophic cascade).',
                    ],
                ],
            ];
        } elseif (str_contains($lowerText, 'rubrik') || str_contains($lowerText, 'kriteria')) {
            $content = [
                'title' => 'Rubrik Penilaian Presentasi Ilmiah',
                'criteria' => [
                    [
                        'name' => 'Penguasaan Materi',
                        'weight_percent' => 40,
                        'levels' => [
                            ['score' => 4, 'label' => 'Sangat Baik', 'description' => 'Menjelaskan konsep dengan runtut, akurat, dan menjawab pertanyaan dengan bukti ilmiah.'],
                            ['score' => 3, 'label' => 'Baik', 'description' => 'Menjelaskan konsep umum dengan baik, namun butuh klarifikasi pada detail lanjutan.'],
                            ['score' => 2, 'label' => 'Cukup', 'description' => 'Konsep dijelaskan terbatas dengan beberapa kekeliruan terminologi.'],
                            ['score' => 1, 'label' => 'Perlu Bimbingan', 'description' => 'Membaca teks secara pasif tanpa pemahaman konsep inti.'],
                        ],
                    ],
                    [
                        'name' => 'Kejelasan Penyampaian & Artikulasi',
                        'weight_percent' => 30,
                        'levels' => [
                            ['score' => 4, 'label' => 'Sangat Baik', 'description' => 'Intonasi jelas, kontak mata aktif, bahasa komunikatif dan memikat audiens.'],
                            ['score' => 3, 'label' => 'Baik', 'description' => 'Suara terdengar jelas oleh seluruh kelas dan tempo berbicara teratur.'],
                            ['score' => 2, 'label' => 'Cukup', 'description' => 'Volume suara rendah atau tempo terlalu cepat.'],
                            ['score' => 1, 'label' => 'Perlu Bimbingan', 'description' => 'Sulit didengar dan tidak ada kontak mata dengan teman sekelas.'],
                        ],
                    ],
                ],
            ];
        } else {
            // Essay feedback
            $content = [
                'observations' => [
                    'Struktur penulisan esai sudah memiliki pembuka, argumentasi, dan kesimpulan yang terarah.',
                    'Penggunaan data pendukung cukup relevan namun perlu penambahan kutipan sumber kredibel.',
                ],
                'strengths' => [
                    'Gagasan utama disampaikan dengan sudut pandang yang orisinal dan kritis.',
                    'Pilihan kosakata baku sesuai pedoman EYD.',
                ],
                'areas_for_improvement' => [
                    'Paragraf kedua dan ketiga membutuhkan kalimat transisi yang lebih mengalir.',
                    'Kesimpulan hendaknya merangkum seluruh poin utama, bukan sekadar mengulang kalimat pembuka.',
                ],
                'suggested_feedback_text' => 'Kerja yang sangat baik dalam menyusun gagasan esai ini. Argumen Anda berbobot dan kritis. Untuk penyempurnaan ke depan, perhatikan kalimat penghubung antar-paragraf agar pembaca lebih mudah mengikuti alur pemikiran Anda.',
                'suggested_score_range' => '82 - 88 (dari skala 100)',
            ];
        }

        return [
            'content' => $content,
            'prompt_tokens' => 320,
            'completion_tokens' => 580,
            'total_tokens' => 900,
            'model' => 'mock-gemini-flash',
            'provider' => 'mock',
        ];
    }
}
