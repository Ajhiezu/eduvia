import test from 'node:test';
import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { spawn } from 'node:child_process';
import { mkdtemp, writeFile, unlink, rmdir } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';

async function runHarness(t, responseStatus) {
    let inFlight = 0;
    let peak = 0;
    let requestCount = 0;
    const server = createServer(async (request, response) => {
        inFlight++;
        peak = Math.max(peak, inFlight);
        requestCount++;
        let rawBody = '';
        for await (const chunk of request) {
            rawBody += chunk;
        }
        const payload = JSON.parse(rawBody);
        assert.equal(payload.answer, 'A');
        assert.match(request.headers.authorization, /^Bearer fake-token-/);
        assert.match(request.url, /^\/api\/v1\/student\/cbt\/1\/save-answer$/);
        setTimeout(() => {
            inFlight--;
            response.writeHead(responseStatus, { 'Content-Type': 'application/json' });
            response.end(JSON.stringify({ success: responseStatus === 200 }));
        }, 40);
    });
    await new Promise((resolveListen) => server.listen(0, '127.0.0.1', resolveListen));
    t.after(() => new Promise((resolveClose) => server.close(resolveClose)));
    const folder = await mkdtemp(join(tmpdir(), 'eduvia-load-test-'));
    const participantsFile = join(folder, 'participants.json');
    await writeFile(participantsFile, JSON.stringify(Array.from({ length: 20 }, (_, index) => ({
        token: `fake-token-${index}`, studentId: index + 1, examId: 1, attemptId: index + 1,
        answers: [{ questionId: 1, answer: 'A' }],
    }))));
    t.after(async () => {
        await unlink(participantsFile);
        await rmdir(folder);
    });

    const child = spawn(process.execPath, [resolve('scripts/cbt-load-test.mjs')], {
        windowsHide: true,
        env: {
            ...process.env,
            EDUVIA_LOAD_ENVIRONMENT: 'staging',
            EDUVIA_LOAD_BASE_URL: `http://127.0.0.1:${server.address().port}`,
            EDUVIA_LOAD_PARTICIPANTS: participantsFile,
            EDUVIA_LOAD_CONCURRENCY: '20',
            EDUVIA_LOAD_ROUNDS: '2',
            EDUVIA_LOAD_INTERVAL_MS: '0',
            EDUVIA_LOAD_P95_MS: '10000',
            EDUVIA_LOAD_MAX_ERROR_RATE: '0',
        },
    });
    let stdout = '';
    let stderr = '';
    child.stdout.on('data', (chunk) => { stdout += chunk; });
    child.stderr.on('data', (chunk) => { stderr += chunk; });
    const exitCode = await new Promise((resolveExit, reject) => {
        child.once('error', reject);
        child.once('close', resolveExit);
    });
    assert.equal(stderr, '');

    return { exitCode, report: JSON.parse(stdout), peak, requestCount };
}

test('load runner sends requests concurrently and reports successful responses', async (t) => {
    const result = await runHarness(t, 200);
    assert.equal(result.exitCode, 0);
    assert.equal(result.requestCount, 40);
    assert.ok(result.peak > 1, 'requests must overlap');
    assert.equal(result.report.requests, 40);
    assert.equal(result.report.errorRate, 0);
    assert.equal(result.report.passed, true);
});

test('load runner fails its error threshold on rejected answers', async (t) => {
    const result = await runHarness(t, 422);
    assert.equal(result.exitCode, 1);
    assert.equal(result.report.failures, 40);
    assert.equal(result.report.errorRate, 1);
    assert.equal(result.report.passed, false);
});
