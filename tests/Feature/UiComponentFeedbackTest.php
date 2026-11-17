<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UiComponentFeedbackTest extends TestCase
{
    #[DataProvider('controls')]
    public function test_explicit_errors_are_exposed_and_linked_to_named_controls(string $template): void
    {
        $view = $this->blade($template, [
            'error' => 'Data belum sesuai. Periksa kembali isian ini.',
        ]);

        $view->assertSee('aria-invalid="true"', false);
        $view->assertSee('aria-describedby="control-error"', false);
        $view->assertSee('id="control-error"', false);
        $view->assertSeeText('Data belum sesuai. Periksa kembali isian ini.');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function controls(): array
    {
        return [
            'input' => ['<x-input id="control" name="name" :error="$error" />'],
            'select' => ['<x-select id="control" name="classroom" :error="$error"><option>Pilih kelas</option></x-select>'],
            'textarea' => ['<x-textarea id="control" name="description" :error="$error" />'],
        ];
    }

    public function test_server_validation_errors_are_exposed_on_the_matching_field(): void
    {
        $view = $this->withViewErrors(['email' => 'Alamat email sudah terdaftar.'])
            ->blade('<x-input id="email" name="email" type="email" />');

        $view->assertSee('aria-invalid="true"', false);
        $view->assertSee('aria-describedby="email-error"', false);
        $view->assertSeeText('Alamat email sudah terdaftar.');
    }

    public function test_field_hints_preserve_existing_accessible_descriptions(): void
    {
        $view = $this->withViewErrors([])
            ->blade('<x-input id="email" name="email" hint="Gunakan email aktif." aria-describedby="privacy-note" />');

        $view->assertSee('aria-describedby="privacy-note email-hint"', false);
        $view->assertSee('id="email-hint"', false);
        $view->assertSee('aria-invalid="false"', false);
    }

    public function test_legacy_alerts_display_their_message_and_error_severity(): void
    {
        $view = $this->blade('<x-alert type="error" message="Berkas gagal disimpan." />');

        $view->assertSeeText('Berkas gagal disimpan.');
        $view->assertSee('role="alert"', false);
    }

    public function test_success_alerts_announce_status_and_prefer_slot_content(): void
    {
        $view = $this->blade('<x-alert variant="success" message="Pesan lama">Perubahan berhasil disimpan.</x-alert>');

        $view->assertSeeText('Perubahan berhasil disimpan.');
        $view->assertSee('role="status"', false);
        $view->assertDontSeeText('Pesan lama');
    }

    public function test_loading_buttons_cannot_be_submitted(): void
    {
        $view = $this->blade('<x-button type="submit" loading>Simpan perubahan</x-button>');

        $view->assertSee('disabled', false);
        $view->assertSee('aria-busy="true"', false);
        $view->assertSeeText('Sedang memproses.');
    }

    public function test_disabled_links_cannot_be_followed(): void
    {
        $view = $this->blade('<x-button href="/students/create" disabled>Tambah siswa</x-button>');

        $view->assertSee('aria-disabled="true"', false);
        $view->assertSee('tabindex="-1"', false);
        $view->assertDontSee('href=', false);
    }
}
