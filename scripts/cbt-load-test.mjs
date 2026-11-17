import { readFile } from 'node:fs/promises';
import { performance } from 'node:perf_hooks';
import { setTimeout as delay } from 'node:timers/promises';

if (process.argv.includes('--help')) {
    console.log(`Uji beban CBT pada lingkungan pengujian terisolasi.
Variabel wajib:
  EDUVIA_LOAD_ENVIRONMENT=staging
  EDUVIA_LOAD_BASE_URL=https://staging.example.test
  EDUVIA_LOAD_PARTICIPANTS=/lokasi/participants.json
Variabel opsional:
  EDUVIA_LOAD_CONCURRENCY=20
  EDUVIA_LOAD_ROUNDS=3
  EDUVIA_LOAD_INTERVAL_MS=30000
  EDUVIA_LOAD_P95_MS=1000
  EDUVIA_LOAD_MAX_ERROR_RATE=0.01
Format participants.json:
  [{"token":"token-pengujian","studentId":1,"examId":1,"attemptId":1,"answers":[{"questionId":1,"answer":"A"}]}]
Setiap peserta harus memiliki siswa dan attempt berbeda, dengan ujian aktif.
Gunakan data pengujian; jawaban akan disimpan. Jangan commit file token.
Contoh menjalankan: node scripts/cbt-load-test.mjs`);
    process.exit(0);
}

function integerSetting(name, fallback, min, max) {
    const value = Number(process.env[name] ?? fallback);
    if (!Number.isInteger(value) || value < min || value > max) {
        throw new Error(`${name} harus berupa bilangan ${min} sampai ${max}.`);
    }
    return value;
}

async function main() {
    if (process.env.EDUVIA_LOAD_ENVIRONMENT !== 'staging') {
        throw new Error('Tetapkan EDUVIA_LOAD_ENVIRONMENT=staging dan gunakan data pengujian terisolasi.');
    }
    const baseUrl = new URL(process.env.EDUVIA_LOAD_BASE_URL);
    if (!['http:', 'https:'].includes(baseUrl.protocol) || baseUrl.username || baseUrl.password) {
        throw new Error('URL target tidak valid.');
    }
    const concurrency = integerSetting('EDUVIA_LOAD_CONCURRENCY', 20, 1, 1000);
    const rounds = integerSetting('EDUVIA_LOAD_ROUNDS', 3, 1, 100);
    const interval = integerSetting('EDUVIA_LOAD_INTERVAL_MS', 30000, 0, 300000);
    const p95Limit = integerSetting('EDUVIA_LOAD_P95_MS', 1000, 1, 60000);
    const errorLimit = Number(process.env.EDUVIA_LOAD_MAX_ERROR_RATE ?? 0.01);
    if (!Number.isFinite(errorLimit) || errorLimit < 0 || errorLimit > 1) {
        throw new Error('EDUVIA_LOAD_MAX_ERROR_RATE harus berada di antara 0 dan 1.');
    }
    const allParticipants = JSON.parse(await readFile(process.env.EDUVIA_LOAD_PARTICIPANTS, 'utf8'));
    if (!Array.isArray(allParticipants) || allParticipants.length < concurrency) {
        throw new Error('Jumlah peserta pengujian kurang dari concurrency.');
    }
    const participants = allParticipants.slice(0, concurrency);
    const students = new Set();
    const attempts = new Set();
    for (const participant of participants) {
        const { token, studentId, examId, attemptId, answers } = participant;
        if (typeof token !== 'string' || token.length === 0
            || ![studentId, examId, attemptId].every((id) => Number.isSafeInteger(id) && id > 0)
            || students.has(studentId) || attempts.has(attemptId)
            || !Array.isArray(answers) || answers.length === 0
            || answers.some((answer) => !Number.isSafeInteger(answer.questionId) || answer.questionId <= 0 || !Object.hasOwn(answer, 'answer'))) {
            throw new Error('Data peserta tidak valid atau siswa/attempt digunakan lebih dari sekali.');
        }
        students.add(studentId);
        attempts.add(attemptId);
    }

    const durations = [];
    const statuses = {};
    let failures = 0;
    const startedAt = performance.now();
    for (let round = 0; round < rounds; round++) {
        const roundStartedAt = performance.now();
        await Promise.all(participants.map(async (participant) => {
            const answer = participant.answers[round % participant.answers.length];
            const started = performance.now();
            let status = 'network_error';
            try {
                const response = await fetch(new URL(`/api/v1/student/cbt/${participant.examId}/save-answer`, baseUrl), {
                    method: 'POST',
                    headers: { Authorization: `Bearer ${participant.token}`, 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({ attempt_id: participant.attemptId, question_id: answer.questionId, answer: answer.answer, is_flagged: false }),
                    signal: AbortSignal.timeout(30000),
                });
                status = String(response.status);
                const body = await response.json();
                if (!response.ok || body.success !== true) {
                    failures++;
                }
            } catch {
                failures++;
            } finally {
                durations.push(performance.now() - started);
                statuses[status] = (statuses[status] ?? 0) + 1;
            }
        }));
        if (round < rounds - 1) {
            await delay(Math.max(0, interval - (performance.now() - roundStartedAt)));
        }
    }
    durations.sort((a, b) => a - b);
    const percentile = (fraction) => Number(durations[Math.ceil(durations.length * fraction) - 1].toFixed(2));
    const errorRate = failures / durations.length;
    const passed = errorRate <= errorLimit && percentile(0.95) <= p95Limit;
    console.log(JSON.stringify({
        target: baseUrl.origin, concurrency, rounds, requests: durations.length,
        durationSeconds: Number(((performance.now() - startedAt) / 1000).toFixed(2)),
        failures, errorRate, statuses, p50Ms: percentile(0.5), p95Ms: percentile(0.95),
        p99Ms: percentile(0.99), criteria: { p95LimitMs: p95Limit, maxErrorRate: errorLimit }, passed,
    }, null, 2));
    process.exitCode = passed ? 0 : 1;
}

main().catch(() => {
    console.error('Uji beban tidak dapat dijalankan. Periksa variabel lingkungan dan format peserta melalui --help.');
    process.exitCode = 1;
});
