<?php

namespace App\Services\AI\Contracts;

interface LlmDriverInterface
{
    /**
     * Send prompt payload to LLM provider and receive structured JSON response.
     *
     * @param  string  $systemPrompt  System instruction / guardrails
     * @param  string  $userContent  User prompt containing sanitized curriculum data
     * @param  array<string, mixed>  $options  Model options (temperature, json schema, etc.)
     * @return array{
     *     content: array<string, mixed>,
     *     prompt_tokens: int,
     *     completion_tokens: int,
     *     total_tokens: int,
     *     model: string,
     *     provider: string
     * }
     */
    public function generateStructured(string $systemPrompt, string $userContent, array $options = []): array;
}
