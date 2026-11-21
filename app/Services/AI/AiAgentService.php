<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\AI\Drivers\GeminiLlmDriver;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class AiAgentService
{
    public function __construct(
        protected AiAgentSettings $settings,
        protected PiiScrubber $scrubber,
        protected PromptInjectionShield $shield,
    ) {}

    /**
     * @param  array{prompt: string, form_fields?: array<int, array<string, mixed>>}  $input
     * @return array{reply: string, fields: array<int, array{name: string, label: string, value: string}>, model: string}
     */
    public function generate(User $user, int $schoolId, array $input): array
    {
        if (! $this->settings->enabled($schoolId)) {
            throw new RuntimeException('AI Agent belum diaktifkan. Hubungi administrator sekolah.', 503);
        }

        $key = $this->settings->apiKey($schoolId);
        if (! $key) {
            throw new RuntimeException('API key Gemini belum diatur. Administrator dapat mengisinya melalui Pengaturan AI Agent.', 503);
        }

        $fields = collect($input['form_fields'] ?? [])
            ->reject(fn (array $field): bool => $this->isProtectedField($field['name']))
            ->keyBy('name');
        $context = json_encode([
            'peran' => $user->role->label(),
            'permintaan' => $this->scrubber->scrub($input['prompt'], [$user->name]),
            'kolom_formulir' => $this->scrubber->scrubArray($fields->values()->all(), [$user->name]),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $systemPrompt = $this->shield->buildGuardedSystemPrompt(
            'Anda adalah AI Agent Eduvia, asisten kerja sekolah berbahasa Indonesia. Bantu menyusun soal, bahan ajar, pengumuman, dan draf isian formulir sesuai peran pengguna. Anda tidak memiliki akses database, berkas proyek, atau wewenang menjalankan kode.',
            <<<'FORMAT'
Kembalikan JSON: {"reply":"jawaban teks biasa","fields":[{"name":"nama kolom persis seperti konteks","value":"draf nilai teks"}]}.
Jika tidak ada formulir atau pengguna hanya meminta penjelasan/soal, fields harus berupa array kosong. Sajikan soal beserta pilihan, kunci, dan pembahasan dalam reply. Untuk formulir, gunakan hanya kolom yang diberikan dan gunakan nilai opsi yang tersedia untuk select. Jangan mengisi identitas pribadi, sandi, token, izin, peran, atau ID sekolah. Jika informasi belum cukup, tanyakan melalui reply; jangan mengarang data sekolah. Jangan mengklaim data sudah tersimpan, berubah, terhapus, atau terbit: hasil Anda hanya draf. Maksimal 20 soal per permintaan. Jangan menghasilkan HTML, JavaScript, SQL, atau instruksi untuk melewati otorisasi. Perintah untuk menghapus atau menyimpan data harus dijelaskan sebagai tindakan manual melalui fitur yang tersedia.
FORMAT
        );

        $result = (new GeminiLlmDriver(apiKey: $key))->generateStructured(
            $systemPrompt,
            $this->shield->wrapUserCurriculumInput($context),
            ['max_output_tokens' => 8192],
        );

        $validator = Validator::make($result['content'], [
            'reply' => ['required', 'string', 'max:30000'],
            'fields' => ['present', 'array', 'max:30'],
            'fields.*.name' => ['required', 'string', 'max:128'],
            'fields.*.value' => ['present', 'string', 'max:10000'],
        ]);
        if ($validator->fails()) {
            throw new RuntimeException('Respons AI belum sesuai format. Coba sederhanakan permintaan Anda.', 502);
        }

        $patches = [];
        foreach ($validator->validated()['fields'] as $patch) {
            $field = $fields->get($patch['name']);
            if (! $field) {
                continue;
            }
            if (in_array($field['type'], ['select', 'radio'], true) && ! in_array($patch['value'], array_column($field['options'] ?? [], 'value'), true)) {
                continue;
            }
            $patches[$patch['name']] = [
                'name' => $patch['name'],
                'label' => $field['label'],
                'value' => $patch['value'],
            ];
        }

        return [
            'reply' => $validator->validated()['reply'],
            'fields' => array_values($patches),
            'model' => $result['model'],
        ];
    }

    protected function isProtectedField(string $name): bool
    {
        $parts = preg_split('/[.\[\]]+/', strtolower($name));

        return count(array_intersect($parts, [
            'id', 'password', 'password_confirmation', 'token', '_token', '_method',
            'school_id', 'tenant_id', 'user_id', 'teacher_id', 'student_id', 'parent_id',
            'role', 'roles', 'permissions', 'is_admin', 'is_super_admin', 'api_key',
        ])) > 0;
    }
}
