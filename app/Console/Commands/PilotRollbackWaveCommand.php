<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Pilot\PilotWaveManager;
use Illuminate\Console\Command;

class PilotRollbackWaveCommand extends Command
{
    protected $signature = 'pilot:rollback-wave {wave : Nomor Gelombang (1-6)} {school : ID Sekolah Tenant} {--reason= : Alasan pembatalan/penundaan} {--operator= : ID Pengguna Operator}';

    protected $description = 'Nonaktifkan/mundurkan (rollback) gelombang pilot dalam situasi darurat';

    public function handle(PilotWaveManager $waveManager): int
    {
        $wave = (int) $this->argument('wave');
        $schoolId = (int) $this->argument('school');
        $reason = $this->option('reason') ?: 'Penundaan operasional atas instruksi manajemen';
        $operatorId = $this->option('operator');

        $operator = $operatorId ? User::find($operatorId) : User::where('role', 'super_admin')->first();
        if (! $operator) {
            $operator = User::first();
        }

        $waveManager->deactivateWave($wave, $schoolId, $operator, $reason);
        $this->warn("GELOMBANG {$wave} pada Sekolah ID {$schoolId} telah DINONAKTIFKAN (ROLLBACK).");

        return Command::SUCCESS;
    }
}
