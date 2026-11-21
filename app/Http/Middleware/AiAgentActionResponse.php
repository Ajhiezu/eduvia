<?php

namespace App\Http\Middleware;

use App\Services\AI\AiAgentToolCatalog;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AiAgentActionResponse
{
    public function __construct(protected AiAgentToolCatalog $catalog) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasHeader('X-Eduvia-Agent-Action')) {
            return $next($request);
        }
        $user = $request->user();
        abort_unless($user && $this->catalog->allowsMutation($user, $request->route()?->getName()), 403);
        $actionId = $request->header('X-Eduvia-Agent-Action');
        abort_unless(Str::isUuid($actionId), 422, 'ID tindakan agen tidak valid.');
        $cacheKey = 'ai-agent-action:'.$user->id.':'.$request->method().':'.$request->path().':'.$actionId;
        $lock = Cache::lock($cacheKey.':lock', 60);
        if (! $lock->get()) {
            return response()->json(['ok' => false, 'message' => 'Tindakan ini masih diproses. Jangan kirim ulang.'], 409);
        }
        try {
            if ($cached = Cache::get($cacheKey)) {
                return response()->json($cached['body'], $cached['status']);
            }
            $request->session()->forget(['status', 'success', 'error', 'warning', 'errors']);
            $response = $next($request);
            if ($response instanceof RedirectResponse) {
                $error = $request->session()->get('error') ?? $request->session()->get('warning');
                $error = $error ?? $request->session()->get('errors')?->first();
                $message = $error ?? $request->session()->get('success') ?? $request->session()->get('status');
                $success = ! $error && is_string($message) && $message !== '';
                $response = response()->json([
                    'ok' => $success,
                    'message' => $message ?: 'Hasil penyimpanan belum terkonfirmasi. Periksa data sebelum mengulangi perintah.',
                    'redirect' => $response->getTargetUrl(),
                    'uncertain' => ! $message,
                ], $success ? 200 : 422);
            }
            if ($response instanceof JsonResponse) {
                $body = $response->getData(true);
                Cache::put($cacheKey, ['body' => $body, 'status' => $response->getStatusCode()], now()->addHours(6));
                Log::info('AI Agent action completed.', ['user_id' => $user->id, 'school_id' => $user->school_id, 'route' => $request->route()?->getName(), 'action_id' => $actionId, 'status' => $response->getStatusCode()]);
            }

            return $response;
        } catch (\Throwable $exception) {
            Cache::put($cacheKey, ['body' => ['ok' => false, 'uncertain' => true, 'message' => 'Tindakan sebelumnya sudah dicoba. Periksa data sebelum mengulangi.'], 'status' => 409], now()->addHours(6));
            throw $exception;
        } finally {
            $lock->release();
        }
    }
}
