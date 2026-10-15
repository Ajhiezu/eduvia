<x-layouts.app>
    <x-slot:headerTitle>
        Edit Butir Soal CBT
    </x-slot:headerTitle>

    <div class="max-w-4xl mx-auto space-y-6"
         x-data="{
            type: '{{ old('type', $question->type) }}',
            options: {{ json_encode(old('options', !empty($question->options) ? $question->options : [
                ['key' => 'A', 'text' => ''],
                ['key' => 'B', 'text' => ''],
                ['key' => 'C', 'text' => ''],
                ['key' => 'D', 'text' => ''],
            ])) }},
            correctSingle: '{{ old('correct_answer', is_string($question->correct_answer) ? $question->correct_answer : 'A') }}',
            correctMulti: {{ json_encode((array) old('correct_answer_multi', is_array($question->correct_answer) ? $question->correct_answer : ['A'])) }},
            correctTf: '{{ old('correct_answer', is_string($question->correct_answer) ? $question->correct_answer : 'true') }}',
            addOption() {
                const nextKey = String.fromCharCode(65 + this.options.length);
                if (this.options.length < 8) {
                    this.options.push({ key: nextKey, text: '' });
                }
            },
            removeOption(index) {
                if (this.options.length > 2) {
                    this.options.splice(index, 1);
                    this.options.forEach((opt, idx) => {
                        opt.key = String.fromCharCode(65 + idx);
                    });
                }
            }
         }">

        @if ($errors->any())
            <x-alert variant="danger" dismissible>
                <p class="font-bold">Terdapat kesalahan pada input:</p>
                <ul class="list-disc list-inside mt-1 text-xs">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('exam-questions.index') }}" class="inline-flex items-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 transition-colors mb-1">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Bank Soal
                </a>
                <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-900 tracking-tight">
                    Edit Butir Soal #{{ $question->id }}
                </h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Perbarui konten pertanyaan, opsi, kunci jawaban, atau penjelasan butir soal.
                </p>
            </div>
        </div>

        <form action="{{ route('exam-questions.update', $question) }}" method="POST">
            @csrf
            @method('PUT')

            <x-card padding class="space-y-6 border-zinc-200">
                <!-- Metadata Form Group -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pb-6 border-b border-zinc-100">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">
                            Mata Pelajaran <span class="text-red-500">*</span>
                        </label>
                        <select name="subject_id" required class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach ($subjects as $subj)
                                <option value="{{ $subj->id }}" {{ old('subject_id', $question->subject_id) == $subj->id ? 'selected' : '' }}>
                                    {{ $subj->name }} ({{ $subj->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">
                            Tipe Soal <span class="text-red-500">*</span>
                        </label>
                        <select name="type" x-model="type" required class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $question->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">
                            Jenjang / Tingkat Kelas
                        </label>
                        <input
                            type="text"
                            name="grade_level"
                            value="{{ old('grade_level', $question->grade_level) }}"
                            placeholder="Contoh: 10, 11, 12, VII, dsb."
                            class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                        />
                    </div>
                </div>

                <!-- Soal & Bobot -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">
                            Konten Pertanyaan <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            name="question"
                            rows="5"
                            required
                            placeholder="Tuliskan teks pertanyaan lengkap di sini..."
                            class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500 font-sans"
                        >{{ old('question', $question->question) }}</textarea>
                    </div>

                    <div class="w-full sm:w-1/3">
                        <label class="block text-xs font-bold text-zinc-700 mb-1">
                            Bobot Nilai Default <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            name="default_score"
                            step="0.5"
                            min="0.5"
                            value="{{ old('default_score', $question->default_score) }}"
                            required
                            class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                        />
                        <p class="text-[10px] text-zinc-400 mt-1">Bobot dapat disesuaikan kembali ketika soal dimasukkan ke dalam ujian.</p>
                    </div>
                </div>

                <!-- TIPE 1: Pilihan Ganda 1 Jawaban -->
                <div x-show="type === 'multiple_choice_single'" class="space-y-4 pt-4 border-t border-zinc-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold text-zinc-800">Opsi Pilihan Ganda</h3>
                            <p class="text-[11px] text-zinc-400">Pilih radio button di samping opsi yang merupakan kunci jawaban benar.</p>
                        </div>
                        <button type="button" @click="addOption()" class="inline-flex items-center text-xs font-bold text-brand-600 hover:text-brand-700">
                            + Tambah Opsi
                        </button>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="(opt, idx) in options" :key="idx">
                            <div class="flex items-center gap-2">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input
                                        type="radio"
                                        name="correct_answer"
                                        :value="opt.key"
                                        x-model="correctSingle"
                                        class="text-brand-600 focus:ring-brand-500"
                                    />
                                    <span class="ml-1.5 font-bold text-xs text-zinc-700 w-5" x-text="opt.key + '.'"></span>
                                </label>
                                <input
                                    type="hidden"
                                    :name="`options[${idx}][key]`"
                                    :value="opt.key"
                                />
                                <input
                                    type="text"
                                    :name="`options[${idx}][text]`"
                                    x-model="opt.text"
                                    placeholder="Teks opsi jawaban..."
                                    class="flex-1 text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                                />
                                <button
                                    type="button"
                                    @click="removeOption(idx)"
                                    x-show="options.length > 2"
                                    class="p-1.5 text-zinc-400 hover:text-red-600 rounded"
                                    title="Hapus opsi"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- TIPE 2: Pilihan Ganda Banyak Jawaban -->
                <div x-show="type === 'multiple_choice_multi'" class="space-y-4 pt-4 border-t border-zinc-100" style="display: none;">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold text-zinc-800">Opsi Pilihan Ganda (Multi Jawaban)</h3>
                            <p class="text-[11px] text-zinc-400">Centang kotak pada semua pilihan yang merupakan jawaban benar.</p>
                        </div>
                        <button type="button" @click="addOption()" class="inline-flex items-center text-xs font-bold text-brand-600 hover:text-brand-700">
                            + Tambah Opsi
                        </button>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="(opt, idx) in options" :key="idx">
                            <div class="flex items-center gap-2">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="correct_answer[]"
                                        :value="opt.key"
                                        :checked="Array.isArray(correctMulti) && correctMulti.includes(opt.key)"
                                        class="rounded text-brand-600 focus:ring-brand-500"
                                    />
                                    <span class="ml-1.5 font-bold text-xs text-zinc-700 w-5" x-text="opt.key + '.'"></span>
                                </label>
                                <input
                                    type="hidden"
                                    :name="`options[${idx}][key]`"
                                    :value="opt.key"
                                />
                                <input
                                    type="text"
                                    :name="`options[${idx}][text]`"
                                    x-model="opt.text"
                                    placeholder="Teks opsi jawaban..."
                                    class="flex-1 text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                                />
                                <button
                                    type="button"
                                    @click="removeOption(idx)"
                                    x-show="options.length > 2"
                                    class="p-1.5 text-zinc-400 hover:text-red-600 rounded"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- TIPE 3: Benar / Salah -->
                <div x-show="type === 'true_false'" class="space-y-3 pt-4 border-t border-zinc-100" style="display: none;">
                    <h3 class="text-xs font-bold text-zinc-800">Kunci Jawaban Benar / Salah</h3>
                    <div class="flex items-center gap-6">
                        <label class="inline-flex items-center gap-2 cursor-pointer p-3 rounded-lg border border-zinc-200 hover:bg-zinc-50">
                            <input type="radio" name="correct_answer_tf" value="true" x-model="correctTf" class="text-brand-600 focus:ring-brand-500" />
                            <span class="text-xs font-bold text-emerald-700">Benar (True)</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer p-3 rounded-lg border border-zinc-200 hover:bg-zinc-50">
                            <input type="radio" name="correct_answer_tf" value="false" x-model="correctTf" class="text-brand-600 focus:ring-brand-500" />
                            <span class="text-xs font-bold text-red-700">Salah (False)</span>
                        </label>
                    </div>
                </div>

                <!-- TIPE 4: Isian Singkat -->
                <div x-show="type === 'short_answer'" class="space-y-3 pt-4 border-t border-zinc-100" style="display: none;">
                    <h3 class="text-xs font-bold text-zinc-800">Kunci Jawaban Singkat</h3>
                    <input
                        type="text"
                        name="correct_answer_short"
                        value="{{ old('correct_answer_short', is_string($question->correct_answer) ? $question->correct_answer : '') }}"
                        placeholder="Ketikkan kunci jawaban tepat (tidak sensitif huruf besar/kecil)..."
                        class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                    />
                    <p class="text-[11px] text-zinc-400">Pencocokan jawaban otomatis mengabaikan spasi berlebih dan perbedaan huruf kapital.</p>
                </div>

                <!-- TIPE 5: Esai / Uraian -->
                <div x-show="type === 'essay'" class="p-3 bg-purple-50 rounded-lg border border-purple-200 text-purple-800 text-xs" style="display: none;">
                    <p class="font-bold">Penilaian Esai / Uraian</p>
                    <p class="text-[11px] text-purple-700 mt-0.5">
                        Soal jenis esai tidak memiliki kunci penilaian otomatis biner. Jawaban siswa akan dimasukkan ke dalam antrean penilaian guru untuk diberikan skor dan catatan revisi secara manual.
                    </p>
                </div>

                <!-- Pembahasan / Penjelasan Jawaban -->
                <div class="space-y-2 pt-4 border-t border-zinc-100">
                    <label class="block text-xs font-bold text-zinc-700">
                        Pembahasan & Penjelasan Soal (Opsional)
                    </label>
                    <textarea
                        name="explanation"
                        rows="3"
                        placeholder="Berikan langkah penyelesaian atau penjelasan alasan jawaban benar..."
                        class="w-full text-xs rounded-lg border-zinc-200 focus:border-brand-500 focus:ring-brand-500"
                    >{{ old('explanation', $question->explanation) }}</textarea>
                    <p class="text-[11px] text-zinc-400">Penjelasan ini hanya akan tampil bagi siswa jika konfigurasi ujian mengizinkan ulasan pembahasan.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('exam-questions.index') }}" variant="ghost" size="sm">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" size="sm">
                        Simpan Perubahan
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>
</x-layouts.app>
