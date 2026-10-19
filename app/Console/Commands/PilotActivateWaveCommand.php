<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Pilot\PilotWaveManager;
use Illuminate\Console\Command;

class PilotActivateWaveCommand extends Command
{
    protected $signature = 'pilot:activate-wave {wave : Nomor Gelombang (1-6)} {school : ID Sekolah Tenant} {--operator= : ID Pengguna Operator}';

    protected $description = 'Aktifkan gelombang pilot secara resmi setelah verifikasi pre-flight lolos';

    public function handle(PilotWaveManager $waveManager): int
    {
        $wave = (int) $this->argument('wave');
        $schoolId = (int) $this->argument('school');
        $operatorId = $this->option('operator');

        $operator = $operatorId ? User::find($operatorId) : User::where('role', 'super_admin')->first();
        if (! $operator) {
            $operator = User::first();
        }

        try {
            $waveManager->activateWave($wave, $schoolId, $operator);
            $this->info("BERHASIL: Gelombang {$wave} pada Sekolah ID {$schoolId} resmi diaktifkan.");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("GAGAL: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
