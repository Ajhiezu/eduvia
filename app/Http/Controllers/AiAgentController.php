<?php

namespace App\Http\Controllers;

use App\Http\Requests\AI\GenerateAgentDraftRequest;
use App\Http\Requests\AI\RunAgentRequest;
use App\Http\Requests\AI\UpdateAgentSettingsRequest;
use App\Services\AI\AiAgentExecutionService;
use App\Services\AI\AiAgentService;
use App\Services\AI\AiAgentSettings;
use App\Services\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class AiAgentController extends Controller
{
    public function __construct(
        protected AiAgentSettings $settings,
        protected AiAgentService $agent,
        protected TenantManager $tenantManager,
    ) {}

    public function index(Request $request): View
    {
        $status = $this->settings->status($this->schoolId($request));

        return view('ai-agent.index', compact('status'));
    }

    public function status(Request $request): JsonResponse
    {
        $schoolId = $this->schoolId($request);

        return response()->json(['school_id' => $schoolId, ...$this->settings->status($schoolId)]);
    }

    public function generate(GenerateAgentDraftRequest $request): JsonResponse
    {
        try {
            return response()->json($this->agent->generate($request->user(), $this->schoolId($request), $request->validated()));
        } catch (RuntimeException $exception) {
            $isProviderError = in_array($exception->getCode(), [429, 502, 503], true);
            $status = $isProviderError ? $exception->getCode() : 503;

            return response()->json(['message' => $isProviderError ? $exception->getMessage() : 'AI Agent belum dapat memproses permintaan. Coba lagi nanti.'], $status);
        }
    }

    public function settings(Request $request): View
    {
        $status = $this->settings->status($this->schoolId($request));

        return view('ai-agent.settings', compact('status'));
    }

    public function run(RunAgentRequest $request, AiAgentExecutionService $execution): JsonResponse
    {
        try {
            return response()->json($execution->nextStep($request->user(), $this->schoolId($request), $request->validated()));
        } catch (RuntimeException $exception) {
            $isExpected = in_array($exception->getCode(), [403, 422, 429, 502, 503], true);

            return response()->json(['message' => $isExpected ? $exception->getMessage() : 'Agen belum dapat memproses perintah. Coba lagi nanti.'], $isExpected ? $exception->getCode() : 503);
        }
    }

    public function updateSettings(UpdateAgentSettingsRequest $request): JsonResponse
    {
        $schoolId = $this->schoolId($request);
        $data = $request->validated();
        $key = $data['api_key'] ?? null;

        if ($data['enabled'] && ! $key && ! $this->settings->apiKey($schoolId)) {
            throw ValidationException::withMessages(['api_key' => 'Masukkan API key Gemini sebelum mengaktifkan AI Agent.']);
        }

        $this->settings->update($schoolId, $key, $data['enabled']);

        return response()->json([
            'message' => 'Pengaturan AI Agent berhasil disimpan.',
            ...$this->settings->status($schoolId),
        ]);
    }

    protected function schoolId(Request $request): int
    {
        $schoolId = $request->user()->isSuperAdmin()
            ? ($this->tenantManager->getSchoolId() ?? $request->user()->school_id ?? $this->tenantManager->getSchool()?->id)
            : ($request->user()->school_id ?? $this->tenantManager->getSchoolId());
        abort_unless($schoolId, 403, 'Belum ada sekolah aktif untuk AI Agent. Periksa pengaturan sekolah akun Anda.');
        $this->tenantManager->setSchoolId((int) $schoolId);

        return (int) $schoolId;
    }
}
