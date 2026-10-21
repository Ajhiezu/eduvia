<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Pilot\PilotWaveManager;
use Illuminate\Console\Command;

class PilotReadOnlyCommand extends Command
{
    protected $signature = 'pilot:readonly {module : Nama Modul (misal: attendance, finance, cbt)} {school : ID Sekolah Tenant} {--disable : Matikan mode baca-saja} {--operator= : ID Pengguna Operator}';

    protected $description = 'Atur status mode baca-saja (Read-Only) pada modul tertentu untuk proteksi data saat kendala teknis';

    public function handle(PilotWaveManager $waveManager): int
    {
        $module = $this->argument('module');
        $schoolId = (int) $this->argument('school');
        $readOnly = ! $this->option('disable');
        $operatorId = $this->option('operator');

        $operator = $operatorId ? User::find($operatorId) : User::where('role', 'super_admin')->first();
        if (! $operator) {
            $operator = User::first();
        }

        $waveManager->setModuleReadOnly($module, $schoolId, $readOnly, $operator);

        if ($readOnly) {
            $this->warn("MODUL '{$module}' pada Sekolah ID {$schoolId} sekarang dalam STATUS BACA-SAJA (READ-ONLY).");
        } else {
            $this->info("MODUL '{$module}' pada Sekolah ID {$schoolId} sekarang KEMBALI NORMAL (BACA-TULIS).");
        }

        return Command::SUCCESS;
    }
}
