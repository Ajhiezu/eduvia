<?php

namespace App\Services\AI;

use App\Models\User;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AiAgentToolCatalog
{
    /** @return array<string, array{label: string, routes: array<string, string>}> */
    public function forUser(User $user): array
    {
        if (! AiAgentSettings::canUse($user)) {
            return [];
        }

        $catalog = [];
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (! $name || ! str_ends_with($name, '.index') || ! $this->isAvailable($route, $user) || count($route->parameterNames()) !== 0) {
                continue;
            }
            $resource = Str::beforeLast($name, '.index');
            $routes = [];
            foreach (['index', 'create', 'show', 'edit', 'store', 'update', 'destroy'] as $operation) {
                $candidate = Route::getRoutes()->getByName($resource.'.'.$operation);
                if (! $candidate || ! $this->isAvailable($candidate, $user)) {
                    continue;
                }
                $parameters = $candidate->parameterNames();
                if (count($parameters) !== (in_array($operation, ['show', 'edit', 'update', 'destroy'], true) ? 1 : 0)) {
                    continue;
                }
                $routes[$operation] = '/'.preg_replace('/\{[^}]+\}/', '__ID__', $candidate->uri());
            }
            if (! isset($routes['store']) && ! isset($routes['update']) && ! isset($routes['destroy'])) {
                continue;
            }
            $catalog[$resource] = ['label' => $this->label($resource), 'routes' => $routes];
        }

        return $catalog;
    }

    public function allowsMutation(User $user, ?string $routeName): bool
    {
        if (! $routeName || ! preg_match('/^(.+)\.(store|update|destroy)$/', $routeName, $matches)) {
            return false;
        }

        return isset($this->forUser($user)[$matches[1]]['routes'][$matches[2]]);
    }

    protected function isAvailable(RoutingRoute $route, User $user): bool
    {
        $middleware = $route->gatherMiddleware();
        if (! in_array('web', $middleware, true) || ! in_array('auth', $middleware, true)) {
            return false;
        }
        foreach ($middleware as $item) {
            if (str_starts_with($item, 'role:') || str_starts_with($item, 'App\\Http\\Middleware\\EnsureUserHasRole:')) {
                if (! in_array($user->role->value, explode(',', Str::after($item, ':')), true)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function label(string $resource): string
    {
        return [
            'academic-years' => 'Tahun ajaran', 'semesters' => 'Semester', 'subjects' => 'Mata pelajaran',
            'classrooms' => 'Kelas / rombel', 'students' => 'Data siswa', 'staff' => 'Guru dan staf',
            'schedules' => 'Jadwal pelajaran', 'exam-questions' => 'Bank soal CBT', 'exams' => 'Ujian CBT',
            'announcements' => 'Pengumuman sekolah', 'assignments' => 'Tugas pembelajaran',
            'learning-materials' => 'Materi pembelajaran', 'attendance-sessions' => 'Sesi presensi',
            'website.posts' => 'Artikel website sekolah', 'website.facilities' => 'Fasilitas sekolah',
            'website.extracurriculars' => 'Ekstrakurikuler sekolah',
            'finance.categories' => 'Kategori biaya sekolah', 'finance.invoices' => 'Tagihan sekolah',
            'finance.payments' => 'Pencatatan pembayaran', 'operations.correspondence' => 'Persuratan tata usaha',
            'operations.facilities.rooms' => 'Ruangan dan fasilitas', 'operations.facilities.reservations' => 'Pemesanan fasilitas',
            'operations.library.books' => 'Buku perpustakaan', 'operations.inventory.assets' => 'Aset inventaris',
        ][$resource] ?? Str::headline(str_replace('.', ' / ', $resource));
    }
}
