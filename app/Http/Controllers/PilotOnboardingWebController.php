<?php

namespace App\Http\Controllers;

use App\Models\Pilot\PilotImportBatch;
use App\Services\Pilot\PilotOnboardingImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PilotOnboardingWebController extends Controller
{
    public function __construct(
        protected PilotOnboardingImportService $importService
    ) {}

    /**
     * Download clean, standard CSV template in Indonesian.
     */
    public function downloadTemplate(string $type): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"templat_impor_{$type}_eduvia.csv\"",
        ];

        return response()->stream(function () use ($type) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel compatibility

            if ($type === 'students') {
                fputcsv($handle, ['name', 'email', 'phone', 'nis', 'nisn', 'gender', 'birth_place', 'birth_date', 'classroom']);
                fputcsv($handle, ['Ahmad Fauzan', 'ahmad.fauzan@eduvia.test', '081234567891', '1001', '0081234567', 'L', 'Surabaya', '2008-05-14', '10 MIPA 1']);
                fputcsv($handle, ['Siti Nurhaliza', 'siti.nurhaliza@eduvia.test', '081234567892', '1002', '0087654321', 'P', 'Sidoarjo', '2008-08-20', '10 MIPA 1']);
            } elseif ($type === 'teachers') {
                fputcsv($handle, ['name', 'email', 'phone', 'nip', 'gender']);
                fputcsv($handle, ['Budi Santoso S.Pd', 'budi.santoso@eduvia.test', '081398765432', '198501012010011001', 'L']);
                fputcsv($handle, ['Dewi Anggraini M.Pd', 'dewi.anggraini@eduvia.test', '081398765433', '198802022012022002', 'P']);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Preview CSV file and return validation results.
     */
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'entity_type' => 'required|string|in:students,teachers',
        ]);

        $school = $request->user()->school;
        $preview = $this->importService->previewCsv(
            $request->file('file'),
            $request->input('entity_type'),
            $school
        );

        return response()->json([
            'success' => true,
            'message' => 'Validasi pratinjau berkas selesai.',
            'data' => $preview,
        ]);
    }

    /**
     * Execute atomic batch import.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'validated_rows' => 'required|array|min:1',
            'entity_type' => 'required|string|in:students,teachers',
            'file_name' => 'required|string|max:255',
        ]);

        $school = $request->user()->school;
        $result = $this->importService->executeImport(
            $request->input('validated_rows'),
            $request->input('entity_type'),
            $request->input('file_name'),
            $school,
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => "Impor data berhasil. Kode Batch: {$result['batch_code']}",
            'data' => $result,
        ], 201);
    }

    /**
     * Rollback a previously imported batch.
     */
    public function rollback(Request $request, string $batchCode): JsonResponse
    {
        $school = $request->user()->school;
        $this->importService->rollbackBatch($batchCode, $school, $request->user());

        return response()->json([
            'success' => true,
            'message' => "Batch impor '{$batchCode}' berhasil dibatalkan (Rolled Back).",
        ]);
    }

    /**
     * List historical import batches.
     */
    public function listBatches(Request $request): JsonResponse
    {
        $school = $request->user()->school;
        $batches = PilotImportBatch::where('school_id', $school->id)
            ->with('importer:id,name', 'rollbacker:id,name')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat batch impor berhasil dimuat.',
            'data' => $batches,
        ]);
    }
}
