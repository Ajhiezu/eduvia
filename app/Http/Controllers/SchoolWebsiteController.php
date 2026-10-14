<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolExtracurricular;
use App\Models\SchoolFacility;
use App\Models\SchoolGallery;
use App\Models\SchoolPost;
use App\Models\SchoolWebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SchoolWebsiteController extends Controller
{
    /**
     * Resolve the active school tenant for the current user.
     */
    protected function getSchool(): School
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $school = $user->school_id ? School::find($user->school_id) : School::current();
        abort_unless($school, 404, 'Data sekolah tidak ditemukan.');

        return $school;
    }

    /**
     * Verify that an entity belongs to the active school tenant.
     */
    protected function authorizeTenant(mixed $model, School $school): void
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            return;
        }

        if ($model->school_id !== $school->id) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang mengelola konten sekolah lain.');
        }
    }

    // =========================================================================
    // 1. DASHBOARD & RINGKASAN CMS
    // =========================================================================

    public function dashboard(): View
    {
        $school = $this->getSchool();
        $setting = $school->websiteSetting ?? new SchoolWebsiteSetting(['is_published' => true]);

        $stats = [
            'total_posts' => $school->posts()->count(),
            'published_posts' => $school->posts()->where('is_published', true)->count(),
            'draft_posts' => $school->posts()->where('is_published', false)->count(),
            'news_count' => $school->posts()->where('type', 'news')->count(),
            'event_count' => $school->posts()->where('type', 'event')->count(),
            'achievement_count' => $school->posts()->where('type', 'achievement')->count(),
            'extracurricular_count' => $school->extracurriculars()->count(),
            'facility_count' => $school->facilities()->where('category', 'facility')->count(),
            'program_count' => $school->facilities()->where('category', 'featured_program')->count(),
            'gallery_count' => $school->galleries()->count(),
        ];

        $recentPosts = $school->posts()->latest()->take(6)->get();

        return view('school.website.dashboard', compact('school', 'setting', 'stats', 'recentPosts'));
    }

    // =========================================================================
    // 2. PENGATURAN WEBSITE & PROFIL
    // =========================================================================

    public function settings(): View
    {
        $school = $this->getSchool();
        $setting = $school->websiteSetting ?? new SchoolWebsiteSetting([
            'school_id' => $school->id,
            'is_published' => true,
            'theme_color' => '#E11D2E',
        ]);

        return view('school.website.settings', compact('school', 'setting'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $school = $this->getSchool();

        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:schools,slug,'.$school->id],
            'public_website_enabled' => ['required', 'boolean'],
            'theme_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'hero_headline' => ['nullable', 'string', 'max:255'],
            'hero_subheadline' => ['nullable', 'string'],
            'hero_cta_text' => ['nullable', 'string', 'max:100'],
            'hero_cta_url' => ['nullable', 'string', 'max:255'],
            'hero_video_url' => ['nullable', 'url', 'max:255'],
            'welcome_message' => ['nullable', 'string'],
            'principal_name' => ['nullable', 'string', 'max:150'],
            'principal_title' => ['nullable', 'string', 'max:150'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'history' => ['nullable', 'string'],
            'accreditation_grade' => ['nullable', 'string', 'max:50'],
            'accreditation_no' => ['nullable', 'string', 'max:100'],
            'maps_embed_url' => ['nullable', 'string'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'hero_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072'],
            'remove_hero_image' => ['nullable', 'boolean'],
            'principal_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_principal_photo' => ['nullable', 'boolean'],
        ]);

        // Update school slug & public switch
        $school->slug = $validated['slug'];
        $school->public_website_enabled = (bool) $validated['public_website_enabled'];
        $school->save();

        $setting = SchoolWebsiteSetting::firstOrNew(['school_id' => $school->id]);

        // Handle mission lines to array
        $missionArray = [];
        if (! empty($validated['mission'])) {
            $lines = explode("\n", str_replace("\r", '', $validated['mission']));
            $missionArray = array_values(array_filter(array_map('trim', $lines)));
        }

        // Handle social links
        $socialLinks = [
            'facebook' => $validated['facebook_url'] ?? null,
            'instagram' => $validated['instagram_url'] ?? null,
            'youtube' => $validated['youtube_url'] ?? null,
        ];

        // Hero image upload or removal
        if ($request->hasFile('hero_image')) {
            if ($setting->hero_image_path && Storage::disk('public')->exists($setting->hero_image_path)) {
                Storage::disk('public')->delete($setting->hero_image_path);
            }
            $setting->hero_image_path = $request->file('hero_image')->store("schools/{$school->id}/website", 'public');
        } elseif ($request->boolean('remove_hero_image')) {
            if ($setting->hero_image_path && Storage::disk('public')->exists($setting->hero_image_path)) {
                Storage::disk('public')->delete($setting->hero_image_path);
            }
            $setting->hero_image_path = null;
        }

        // Principal photo upload or removal
        if ($request->hasFile('principal_photo')) {
            if ($setting->principal_photo_path && Storage::disk('public')->exists($setting->principal_photo_path)) {
                Storage::disk('public')->delete($setting->principal_photo_path);
            }
            $setting->principal_photo_path = $request->file('principal_photo')->store("schools/{$school->id}/website", 'public');
        } elseif ($request->boolean('remove_principal_photo')) {
            if ($setting->principal_photo_path && Storage::disk('public')->exists($setting->principal_photo_path)) {
                Storage::disk('public')->delete($setting->principal_photo_path);
            }
            $setting->principal_photo_path = null;
        }

        $setting->fill([
            'theme_color' => ! empty($validated['theme_color']) ? $validated['theme_color'] : '#E11D2E',
            'hero_headline' => $validated['hero_headline'] ?? null,
            'hero_subheadline' => $validated['hero_subheadline'] ?? null,
            'hero_cta_text' => $validated['hero_cta_text'] ?? null,
            'hero_cta_url' => $validated['hero_cta_url'] ?? null,
            'hero_video_url' => $validated['hero_video_url'] ?? null,
            'welcome_message' => $validated['welcome_message'] ?? null,
            'principal_name' => $validated['principal_name'] ?? null,
            'principal_title' => $validated['principal_title'] ?? null,
            'vision' => $validated['vision'] ?? null,
            'mission' => $missionArray,
            'history' => $validated['history'] ?? null,
            'accreditation_grade' => $validated['accreditation_grade'] ?? null,
            'accreditation_no' => $validated['accreditation_no'] ?? null,
            'maps_embed_url' => $validated['maps_embed_url'] ?? null,
            'social_links' => $socialLinks,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'is_published' => true,
        ]);
        $setting->save();

        return redirect()->route('website.settings')->with('success', 'Pengaturan website sekolah dan profil publik berhasil diperbarui.');
    }

    public function toggleWebsiteStatus(Request $request): RedirectResponse
    {
        $school = $this->getSchool();
        $school->public_website_enabled = ! $school->public_website_enabled;
        $school->save();

        $statusText = $school->public_website_enabled ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Website publik sekolah berhasil {$statusText}.");
    }

    // =========================================================================
    // 3. POSTS (BERITA, ACARA/AGENDA, PRESTASI)
    // =========================================================================

    public function posts(Request $request): View
    {
        $school = $this->getSchool();
        $type = $request->query('type');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = $school->posts()->latest();

        if ($type && in_array($type, ['news', 'event', 'achievement'])) {
            $query->where('type', $type);
        }

        if ($status === 'published') {
            $query->where('is_published', true);
        } elseif ($status === 'draft') {
            $query->where('is_published', false);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('awardee_name', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(12)->withQueryString();

        return view('school.website.posts.index', compact('school', 'posts', 'type', 'status', 'search'));
    }

    public function createPost(Request $request): View
    {
        $school = $this->getSchool();
        $type = $request->query('type', 'news');
        if (! in_array($type, ['news', 'event', 'achievement'])) {
            $type = 'news';
        }

        $post = new SchoolPost(['type' => $type, 'is_published' => false]);

        return view('school.website.posts.form', compact('school', 'post', 'type'));
    }

    public function storePost(Request $request): RedirectResponse
    {
        $school = $this->getSchool();

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:news,event,achievement'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_published' => ['required', 'boolean'],
            'order_index' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'event_date' => ['nullable', 'date'],
            'event_end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
            'event_location' => ['nullable', 'string', 'max:255'],
            'competition_name' => ['nullable', 'string', 'max:255'],
            'competition_level' => ['nullable', 'string', 'max:100'],
            'awardee_name' => ['nullable', 'string', 'max:255'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072'],
        ]);

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug ?: 'konten-'.time();
        $count = 1;
        while ($school->posts()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        $post = new SchoolPost($validated);
        $post->school_id = $school->id;
        $post->slug = $slug;
        $post->order_index = (int) ($validated['order_index'] ?? 0);

        if ($request->hasFile('cover_image')) {
            $post->cover_image_path = $request->file('cover_image')->store("schools/{$school->id}/posts", 'public');
        }

        if ($post->is_published) {
            $post->published_at = now();
        }

        $post->save();

        $typeLabel = match ($post->type) {
            'news' => 'Berita',
            'event' => 'Acara & Agenda',
            'achievement' => 'Prestasi',
        };

        return redirect()->route('website.posts.index', ['type' => $post->type])
            ->with('success', "{$typeLabel} berhasil ditambahkan.");
    }

    public function editPost(SchoolPost $post): View
    {
        $school = $this->getSchool();
        $this->authorizeTenant($post, $school);
        $type = $post->type;

        return view('school.website.posts.form', compact('school', 'post', 'type'));
    }

    public function updatePost(Request $request, SchoolPost $post): RedirectResponse
    {
        $school = $this->getSchool();
        $this->authorizeTenant($post, $school);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:news,event,achievement'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_published' => ['required', 'boolean'],
            'order_index' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'event_date' => ['nullable', 'date'],
            'event_end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
            'event_location' => ['nullable', 'string', 'max:255'],
            'competition_name' => ['nullable', 'string', 'max:255'],
            'competition_level' => ['nullable', 'string', 'max:100'],
            'awardee_name' => ['nullable', 'string', 'max:255'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072'],
            'remove_cover_image' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('cover_image')) {
            if ($post->cover_image_path) {
                $isUsedElsewhere = SchoolPost::where('id', '!=', $post->id)->where('cover_image_path', $post->cover_image_path)->exists();
                if (! $isUsedElsewhere && Storage::disk('public')->exists($post->cover_image_path)) {
                    Storage::disk('public')->delete($post->cover_image_path);
                }
            }
            $post->cover_image_path = $request->file('cover_image')->store("schools/{$school->id}/posts", 'public');
        } elseif ($request->boolean('remove_cover_image')) {
            if ($post->cover_image_path) {
                $isUsedElsewhere = SchoolPost::where('id', '!=', $post->id)->where('cover_image_path', $post->cover_image_path)->exists();
                if (! $isUsedElsewhere && Storage::disk('public')->exists($post->cover_image_path)) {
                    Storage::disk('public')->delete($post->cover_image_path);
                }
                $post->cover_image_path = null;
            }
        }

        $wasPublished = $post->is_published;
        $post->fill($validated);
        $post->order_index = (int) ($validated['order_index'] ?? 0);

        if (! $wasPublished && $post->is_published && ! $post->published_at) {
            $post->published_at = now();
        }

        $post->save();

        return redirect()->route('website.posts.index', ['type' => $post->type])
            ->with('success', 'Konten berhasil diperbarui.');
    }

    public function destroyPost(SchoolPost $post): RedirectResponse
    {
        $school = $this->getSchool();
        $this->authorizeTenant($post, $school);

        if ($post->cover_image_path) {
            Storage::disk('public')->delete($post->cover_image_path);
        }

        $type = $post->type;
        $post->delete();

        return redirect()->route('website.posts.index', ['type' => $type])
            ->with('success', 'Konten berhasil dihapus.');
    }

    public function togglePostPublish(SchoolPost $post): RedirectResponse
    {
        $school = $this->getSchool();
        $this->authorizeTenant($post, $school);

        $post->is_published = ! $post->is_published;
        if ($post->is_published && ! $post->published_at) {
            $post->published_at = now();
        }
        $post->save();

        $action = $post->is_published ? 'diterbitkan ke website publik' : 'ditarik ke status draft';

        return back()->with('success', "Konten berhasil {$action}.");
    }

    // =========================================================================
    // 4. EKSTRAKURIKULER
    // =========================================================================

    public function extracurriculars(): View
    {
        $school = $this->getSchool();
        $items = $school->extracurriculars()->orderBy('order_index')->latest()->get();

        return view('school.website.extracurriculars.index', compact('school', 'items'));
    }

    public function createExtracurricular(): View
    {
        $school = $this->getSchool();
        $item = new SchoolExtracurricular(['is_published' => true]);

        return view('school.website.extracurriculars.form', compact('school', 'item'));
    }

    public function storeExtracurricular(Request $request): RedirectResponse
    {
        $school = $this->getSchool();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'coach_name' => ['nullable', 'string', 'max:150'],
            'schedule_info' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:200'],
            'requirements' => ['nullable', 'string', 'max:500'],
            'is_published' => ['required', 'boolean'],
            'order_index' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072'],
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug ?: 'ekstra-'.time();
        $count = 1;
        while ($school->extracurriculars()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        $extra = new SchoolExtracurricular($validated);
        $extra->school_id = $school->id;
        $extra->slug = $slug;
        $extra->order_index = (int) ($validated['order_index'] ?? 0);

        if ($request->hasFile('cover_image')) {
            $extra->cover_image_path = $request->file('cover_image')->store("schools/{$school->id}/extracurriculars", 'public');
        }

        $extra->save();

        return redirect()->route('website.extracurriculars.index')->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function editExtracurricular(SchoolExtracurricular $extracurricular): View
    {
        $school = $this->getSchool();
        $this->authorizeTenant($extracurricular, $school);

        return view('school.website.extracurriculars.form', ['school' => $school, 'item' => $extracurricular]);
    }

    public function updateExtracurricular(Request $request, SchoolExtracurricular $extracurricular): RedirectResponse
    {
        $school = $this->getSchool();
        $this->authorizeTenant($extracurricular, $school);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'coach_name' => ['nullable', 'string', 'max:150'],
            'schedule_info' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:200'],
            'requirements' => ['nullable', 'string', 'max:500'],
            'is_published' => ['required', 'boolean'],
            'order_index' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072'],
            'remove_cover_image' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('cover_image')) {
            if ($extracurricular->cover_image_path) {
                $isUsedElsewhere = SchoolExtracurricular::where('id', '!=', $extracurricular->id)->where('cover_image_path', $extracurricular->cover_image_path)->exists();
                if (! $isUsedElsewhere && Storage::disk('public')->exists($extracurricular->cover_image_path)) {
                    Storage::disk('public')->delete($extracurricular->cover_image_path);
                }
            }
            $extracurricular->cover_image_path = $request->file('cover_image')->store("schools/{$school->id}/extracurriculars", 'public');
        } elseif ($request->boolean('remove_cover_image')) {
            if ($extracurricular->cover_image_path) {
                $isUsedElsewhere = SchoolExtracurricular::where('id', '!=', $extracurricular->id)->where('cover_image_path', $extracurricular->cover_image_path)->exists();
                if (! $isUsedElsewhere && Storage::disk('public')->exists($extracurricular->cover_image_path)) {
                    Storage::disk('public')->delete($extracurricular->cover_image_path);
                }
                $extracurricular->cover_image_path = null;
            }
        }

        $extracurricular->fill($validated);
        $extracurricular->order_index = (int) ($validated['order_index'] ?? 0);
        $extracurricular->save();

        return redirect()->route('website.extracurriculars.index')->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function destroyExtracurricular(SchoolExtracurricular $extracurricular): RedirectResponse
    {
        $school = $this->getSchool();
        $this->authorizeTenant($extracurricular, $school);

        if ($extracurricular->cover_image_path) {
            Storage::disk('public')->delete($extracurricular->cover_image_path);
        }

        $extracurricular->delete();

        return redirect()->route('website.extracurriculars.index')->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }

    // =========================================================================
    // 5. FASILITAS & PROGRAM UNGGULAN
    // =========================================================================

    public function facilities(): View
    {
        $school = $this->getSchool();
        $facilities = $school->facilities()->where('category', 'facility')->orderBy('order_index')->latest()->get();
        $programs = $school->facilities()->where('category', 'featured_program')->orderBy('order_index')->latest()->get();

        return view('school.website.facilities.index', compact('school', 'facilities', 'programs'));
    }

    public function createFacility(Request $request): View
    {
        $school = $this->getSchool();
        $category = $request->query('category', 'facility');
        $item = new SchoolFacility(['category' => $category, 'is_published' => true]);

        return view('school.website.facilities.form', compact('school', 'item', 'category'));
    }

    public function storeFacility(Request $request): RedirectResponse
    {
        $school = $this->getSchool();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'in:facility,featured_program'],
            'description' => ['nullable', 'string'],
            'is_published' => ['required', 'boolean'],
            'order_index' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072'],
        ]);

        $item = new SchoolFacility($validated);
        $item->school_id = $school->id;
        $item->order_index = (int) ($validated['order_index'] ?? 0);

        if ($request->hasFile('cover_image')) {
            $item->cover_image_path = $request->file('cover_image')->store("schools/{$school->id}/facilities", 'public');
        }

        $item->save();

        $label = $item->category === 'facility' ? 'Fasilitas' : 'Program Unggulan';

        return redirect()->route('website.facilities.index')->with('success', "{$label} berhasil ditambahkan.");
    }

    public function editFacility(SchoolFacility $facility): View
    {
        $school = $this->getSchool();
        $this->authorizeTenant($facility, $school);
        $category = $facility->category;

        return view('school.website.facilities.form', ['school' => $school, 'item' => $facility, 'category' => $category]);
    }

    public function updateFacility(Request $request, SchoolFacility $facility): RedirectResponse
    {
        $school = $this->getSchool();
        $this->authorizeTenant($facility, $school);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'in:facility,featured_program'],
            'description' => ['nullable', 'string'],
            'is_published' => ['required', 'boolean'],
            'order_index' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072'],
            'remove_cover_image' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('cover_image')) {
            if ($facility->cover_image_path) {
                $isUsedElsewhere = SchoolFacility::where('id', '!=', $facility->id)->where('cover_image_path', $facility->cover_image_path)->exists();
                if (! $isUsedElsewhere && Storage::disk('public')->exists($facility->cover_image_path)) {
                    Storage::disk('public')->delete($facility->cover_image_path);
                }
            }
            $facility->cover_image_path = $request->file('cover_image')->store("schools/{$school->id}/facilities", 'public');
        } elseif ($request->boolean('remove_cover_image')) {
            if ($facility->cover_image_path) {
                $isUsedElsewhere = SchoolFacility::where('id', '!=', $facility->id)->where('cover_image_path', $facility->cover_image_path)->exists();
                if (! $isUsedElsewhere && Storage::disk('public')->exists($facility->cover_image_path)) {
                    Storage::disk('public')->delete($facility->cover_image_path);
                }
                $facility->cover_image_path = null;
            }
        }

        $facility->fill($validated);
        $facility->order_index = (int) ($validated['order_index'] ?? 0);
        $facility->save();

        return redirect()->route('website.facilities.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroyFacility(SchoolFacility $facility): RedirectResponse
    {
        $school = $this->getSchool();
        $this->authorizeTenant($facility, $school);

        if ($facility->cover_image_path) {
            Storage::disk('public')->delete($facility->cover_image_path);
        }

        $facility->delete();

        return redirect()->route('website.facilities.index')->with('success', 'Data berhasil dihapus.');
    }

    // =========================================================================
    // 6. GALERI MEDIA (FOTO & ALBUM)
    // =========================================================================

    public function galleries(): View
    {
        $school = $this->getSchool();
        $galleries = $school->galleries()->latest()->get()->groupBy('album_name');

        return view('school.website.galleries.index', compact('school', 'galleries'));
    }

    public function storeGallery(Request $request): RedirectResponse
    {
        $school = $this->getSchool();

        $validated = $request->validate([
            'album_name' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:150'],
            'caption' => ['nullable', 'string', 'max:300'],
            'alt_text' => ['nullable', 'string', 'max:150'],
            'media_file' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $path = $request->file('media_file')->store("schools/{$school->id}/gallery", 'public');

        $school->galleries()->create([
            'album_name' => $validated['album_name'],
            'title' => $validated['title'],
            'caption' => $validated['caption'] ?? null,
            'alt_text' => $validated['alt_text'] ?? $validated['title'],
            'media_path' => $path,
            'media_type' => 'image',
            'is_published' => true,
        ]);

        return redirect()->route('website.galleries.index')->with('success', 'Foto galeri berhasil ditambahkan.');
    }

    public function destroyGallery(SchoolGallery $gallery): RedirectResponse
    {
        $school = $this->getSchool();
        $this->authorizeTenant($gallery, $school);

        if ($gallery->media_path) {
            Storage::disk('public')->delete($gallery->media_path);
        }

        $gallery->delete();

        return redirect()->route('website.galleries.index')->with('success', 'Foto galeri berhasil dihapus.');
    }
}
