<?php

namespace App\Http\Controllers;

use App\Models\AdmissionPeriod;
use App\Models\School;
use App\Models\SchoolWebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SchoolPublicController extends Controller
{
    /**
     * Check if visitor is authorized to view preview / draft content.
     */
    protected function canPreview(School $school): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isSchoolAdmin() && $user->school_id === $school->id;
    }

    /**
     * Verify website availability.
     */
    protected function ensureWebsiteAccessible(School $school): void
    {
        if (! $school->public_website_enabled && ! $this->canPreview($school)) {
            abort(404);
        }
    }

    /**
     * Public Landing Page of the School.
     */
    public function home(School $school): View
    {
        $this->ensureWebsiteAccessible($school);

        $setting = $school->websiteSetting ?? new SchoolWebsiteSetting([
            'school_id' => $school->id,
            'is_published' => true,
            'theme_color' => '#E11D2E',
        ]);

        $latestNews = $school->publishedPosts()
            ->ofType('news')
            ->latest()
            ->take(3)
            ->get();

        $upcomingEvents = $school->publishedPosts()
            ->ofType('event')
            ->where('event_date', '>=', now()->startOfDay())
            ->orderBy('event_date', 'asc')
            ->take(3)
            ->get();

        if ($upcomingEvents->isEmpty()) {
            $upcomingEvents = $school->publishedPosts()
                ->ofType('event')
                ->latest('event_date')
                ->take(3)
                ->get();
        }

        $achievements = $school->publishedPosts()
            ->ofType('achievement')
            ->orderBy('order_index', 'asc')
            ->latest('published_at')
            ->latest('id')
            ->take(4)
            ->get();

        $extracurriculars = $school->extracurriculars()
            ->published()
            ->orderBy('order_index', 'asc')
            ->latest('id')
            ->take(6)
            ->get();

        $facilities = $school->facilities()
            ->published()
            ->facilities()
            ->orderBy('order_index', 'asc')
            ->latest('id')
            ->take(4)
            ->get();

        $programs = $school->facilities()
            ->published()
            ->featuredPrograms()
            ->orderBy('order_index', 'asc')
            ->latest('id')
            ->take(3)
            ->get();

        $gallery = $school->galleries()
            ->published()
            ->latest()
            ->take(6)
            ->get();

        // PPDB integration: check active admission period
        $activeAdmissionPeriod = AdmissionPeriod::where('school_id', $school->id)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->with('tracks')
            ->first();

        $isPreview = ! $school->public_website_enabled && $this->canPreview($school);

        return view('public.school.home', compact(
            'school',
            'setting',
            'latestNews',
            'upcomingEvents',
            'achievements',
            'extracurriculars',
            'facilities',
            'programs',
            'gallery',
            'activeAdmissionPeriod',
            'isPreview'
        ));
    }

    /**
     * School Profile Page (History, Vision, Mission, Principal, Accreditation).
     */
    public function profile(School $school): View
    {
        $this->ensureWebsiteAccessible($school);

        $setting = $school->websiteSetting ?? new SchoolWebsiteSetting(['school_id' => $school->id]);

        $facilities = $school->facilities()
            ->published()
            ->facilities()
            ->orderBy('order_index', 'asc')
            ->latest('id')
            ->get();

        $programs = $school->facilities()
            ->published()
            ->featuredPrograms()
            ->orderBy('order_index', 'asc')
            ->latest('id')
            ->get();

        return view('public.school.profile', compact('school', 'setting', 'facilities', 'programs'));
    }

    /**
     * News and Articles List.
     */
    public function articles(School $school, Request $request): View
    {
        $this->ensureWebsiteAccessible($school);

        $search = $request->query('search');
        $category = $request->query('category');

        $query = $school->publishedPosts()
            ->ofType('news')
            ->latest('published_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        $articles = $query->paginate(9)->withQueryString();

        $categories = $school->publishedPosts()
            ->ofType('news')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('public.school.articles', compact('school', 'articles', 'categories', 'search', 'category'));
    }

    /**
     * News Article Detail.
     */
    public function articleDetail(School $school, string $slug): View
    {
        $this->ensureWebsiteAccessible($school);

        $canPreview = $this->canPreview($school);

        $postQuery = $school->posts()->where('slug', $slug);
        if (! $canPreview) {
            $postQuery->where('is_published', true);
        }

        $post = $postQuery->firstOrFail();

        // Increment view count on public visit
        if ($post->is_published) {
            $post->increment('view_count');
        }

        $relatedPosts = $school->publishedPosts()
            ->ofType($post->type)
            ->where('id', '!=', $post->id)
            ->latest()
            ->take(3)
            ->get();

        $isPreview = ! $post->is_published && $canPreview;

        return view('public.school.article-detail', compact('school', 'post', 'relatedPosts', 'isPreview'));
    }

    /**
     * Events & Agendas Page (Upcoming vs Past Documentation).
     */
    public function events(School $school): View
    {
        $this->ensureWebsiteAccessible($school);

        $upcomingEvents = $school->publishedPosts()
            ->ofType('event')
            ->where('event_date', '>=', now()->startOfDay())
            ->orderBy('event_date', 'asc')
            ->get();

        $pastEvents = $school->publishedPosts()
            ->ofType('event')
            ->where('event_date', '<', now()->startOfDay())
            ->orderBy('event_date', 'desc')
            ->paginate(6);

        return view('public.school.events', compact('school', 'upcomingEvents', 'pastEvents'));
    }

    /**
     * Achievements Page.
     */
    public function achievements(School $school, Request $request): View
    {
        $this->ensureWebsiteAccessible($school);

        $search = $request->query('search');
        $level = $request->query('level');

        $query = $school->publishedPosts()
            ->ofType('achievement')
            ->orderBy('order_index', 'asc')
            ->latest('published_at')
            ->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('competition_name', 'like', "%{$search}%")
                    ->orWhere('awardee_name', 'like', "%{$search}%");
            });
        }

        if ($level) {
            $query->where('competition_level', $level);
        }

        $achievements = $query->paginate(9)->withQueryString();

        $levels = $school->publishedPosts()
            ->ofType('achievement')
            ->whereNotNull('competition_level')
            ->distinct()
            ->pluck('competition_level');

        return view('public.school.achievements', compact('school', 'achievements', 'levels', 'search', 'level'));
    }

    /**
     * Extracurriculars Page.
     */
    public function extracurriculars(School $school): View
    {
        $this->ensureWebsiteAccessible($school);

        $items = $school->extracurriculars()
            ->published()
            ->orderBy('order_index', 'asc')
            ->latest('id')
            ->get();

        return view('public.school.extracurriculars', compact('school', 'items'));
    }

    /**
     * Facilities & Featured Programs Page.
     */
    public function facilities(School $school): View
    {
        $this->ensureWebsiteAccessible($school);

        $facilities = $school->facilities()
            ->published()
            ->facilities()
            ->orderBy('order_index', 'asc')
            ->latest('id')
            ->get();

        $programs = $school->facilities()
            ->published()
            ->featuredPrograms()
            ->orderBy('order_index', 'asc')
            ->latest('id')
            ->get();

        return view('public.school.facilities', compact('school', 'facilities', 'programs'));
    }

    /**
     * Media Gallery Page.
     */
    public function gallery(School $school, Request $request): View
    {
        $this->ensureWebsiteAccessible($school);

        $album = $request->query('album');

        $query = $school->galleries()->published()->latest();

        if ($album) {
            $query->where('album_name', $album);
        }

        $items = $query->paginate(12)->withQueryString();

        $albums = $school->galleries()
            ->published()
            ->distinct()
            ->pluck('album_name');

        return view('public.school.gallery', compact('school', 'items', 'albums', 'album'));
    }

    /**
     * Protected Preview of Full Landing Page (including Drafts).
     */
    public function preview(School $school): View
    {
        abort_unless($this->canPreview($school), 403, 'Akses preview khusus untuk Administrator Sekolah.');

        $setting = $school->websiteSetting ?? new SchoolWebsiteSetting(['school_id' => $school->id]);

        $latestNews = $school->posts()->ofType('news')->latest()->take(3)->get();
        $upcomingEvents = $school->posts()->ofType('event')->latest('event_date')->take(3)->get();
        $achievements = $school->posts()->ofType('achievement')->orderBy('order_index', 'asc')->latest('id')->take(4)->get();
        $extracurriculars = $school->extracurriculars()->orderBy('order_index', 'asc')->latest('id')->take(6)->get();
        $facilities = $school->facilities()->facilities()->orderBy('order_index', 'asc')->latest('id')->take(4)->get();
        $programs = $school->facilities()->featuredPrograms()->orderBy('order_index', 'asc')->latest('id')->take(3)->get();
        $gallery = $school->galleries()->latest()->take(6)->get();

        $activeAdmissionPeriod = AdmissionPeriod::where('school_id', $school->id)
            ->where('is_active', true)
            ->first();

        $isPreview = true;

        return view('public.school.home', compact(
            'school',
            'setting',
            'latestNews',
            'upcomingEvents',
            'achievements',
            'extracurriculars',
            'facilities',
            'programs',
            'gallery',
            'activeAdmissionPeriod',
            'isPreview'
        ));
    }

    /**
     * Generate dynamic XML sitemap for the school website.
     */
    public function sitemap(School $school): Response
    {
        if (! $school->public_website_enabled && ! $this->canPreview($school)) {
            abort(404);
        }

        $urls = [
            [
                'loc' => route('school.public.home', ['school' => $school->slug]),
                'lastmod' => $school->updated_at?->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('school.public.profile', ['school' => $school->slug]),
                'lastmod' => $school->websiteSetting?->updated_at?->toAtomString() ?? $school->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('school.public.articles', ['school' => $school->slug]),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => route('school.public.events', ['school' => $school->slug]),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ],
            [
                'loc' => route('school.public.achievements', ['school' => $school->slug]),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ],
            [
                'loc' => route('school.public.extracurriculars', ['school' => $school->slug]),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            [
                'loc' => route('school.public.facilities', ['school' => $school->slug]),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            [
                'loc' => route('school.public.gallery', ['school' => $school->slug]),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ],
        ];

        // Published posts (news, events, achievements)
        $posts = $school->publishedPosts()
            ->select(['id', 'school_id', 'slug', 'updated_at', 'published_at'])
            ->latest('published_at')
            ->limit(200)
            ->get();

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('school.public.article-detail', ['school' => $school->slug, 'slug' => $post->slug]),
                'lastmod' => ($post->updated_at ?? $post->published_at)?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        $xml = view('public.school.sitemap', compact('urls'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
