<?php

namespace App\Console\Commands;

use App\Services\Pilot\PilotWaveManager;
use Illuminate\Console\Command;

class PilotPreFlightCommand extends Command
{
    protected $signature = 'pilot:preflight {wave : Nomor Gelombang (1-6)} {school : ID Sekolah Tenant}';

    protected $description = 'Jalankan pemeriksaan prasyarat otomatis sebelum aktivasi gelombang pilot';

    public function handle(PilotWaveManager $waveManager): int
    {
        $wave = (int) $this->argument('wave');
        $schoolId = (int) $this->argument('school');

        $this->info("Menjalankan Pre-Flight Check untuk Gelombang {$wave} pada Sekolah ID {$schoolId}...");

        $result = $waveManager->preFlightCheck($wave, $schoolId);

        $tableData = array_map(function ($c) {
            return [
                'Prasyarat' => $c['item'],
                'Status' => $c['passed'] ? '<info>LOLOS</info>' : '<error>GAGAL</error>',
                'Keterangan' => $c['notes'],
            ];
        }, $result['checks']);

        $this->table(['Prasyarat', 'Status', 'Keterangan'], $tableData);

        if ($result['can_activate']) {
            $this->info("STATUS: SELURUH PRASYARAT GELOMBANG {$wave} TERPENUHI (SIAP DIAKTIFKAN).");

            return Command::SUCCESS;
        }

        $this->error("STATUS: AKTIVASI GELOMBANG {$wave} TIDAK DIIZINKAN. Harap lengkapi prasyarat di atas.");

        return Command::FAILURE;
    }
}
