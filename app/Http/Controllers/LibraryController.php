<?php

namespace App\Http\Controllers;

use App\Models\LibraryBook;
use App\Models\LibraryBookCopy;
use App\Models\LibraryLoan;
use App\Models\School;
use App\Models\User;
use App\Services\SchoolOperationsService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LibraryController extends Controller
{
    /**
     * Display library book catalogue with filters.
     */
    public function booksIndex(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $category = $request->query('category');
        $search = $request->query('search');

        $books = LibraryBook::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($search, function ($q, $s) {
                $q->where(function ($query) use ($s) {
                    $query->where('title', 'like', "%{$s}%")
                        ->orWhere('author', 'like', "%{$s}%")
                        ->orWhere('isbn', 'like', "%{$s}%")
                        ->orWhere('call_number', 'like', "%{$s}%");
                });
            })
            ->withCount('copies')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $categories = ['Pelajaran', 'Referensi', 'Fiksi', 'Non-Fiksi', 'Kamus', 'Majalah/Jurnal'];

        return view('operations.library.books.index', compact('books', 'categories', 'category', 'search'));
    }

    public function bookCreate(): View
    {
        $categories = ['Pelajaran', 'Referensi', 'Fiksi', 'Non-Fiksi', 'Kamus', 'Majalah/Jurnal'];

        return view('operations.library.books.create', compact('categories'));
    }

    public function bookStore(Request $request, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if(! $school, 403, 'Sesi sekolah tidak valid.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'author' => ['required', 'string', 'max:150'],
            'publisher' => ['nullable', 'string', 'max:150'],
            'publication_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'isbn' => ['nullable', 'string', 'max:30'],
            'call_number' => ['nullable', 'string', 'max:50'],
            'category' => ['required', 'string', 'max:50'],
            'language' => ['required', 'string', 'max:30'],
            'location_shelf' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'initial_copies' => ['required', 'integer', 'min:1', 'max:50'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'digital_file' => ['nullable', 'file', 'mimes:pdf,epub', 'max:20480'], // max 20MB
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('library_covers', 'public');
        }

        $digitalPath = null;
        if ($request->hasFile('digital_file')) {
            $digitalPath = $request->file('digital_file')->store('private/library_digital', 'local');
        }

        $book = DB::transaction(function () use ($school, $validated, $coverPath, $digitalPath, $service) {
            $book = LibraryBook::create([
                'school_id' => $school->id,
                'title' => $validated['title'],
                'author' => $validated['author'],
                'publisher' => $validated['publisher'] ?? null,
                'publication_year' => $validated['publication_year'] ?? null,
                'isbn' => $validated['isbn'] ?? null,
                'call_number' => $validated['call_number'] ?? null,
                'category' => $validated['category'],
                'language' => $validated['language'],
                'location_shelf' => $validated['location_shelf'] ?? null,
                'description' => $validated['description'] ?? null,
                'cover_image_path' => $coverPath,
                'digital_file_path' => $digitalPath,
                'total_copies' => $validated['initial_copies'],
                'available_copies' => $validated['initial_copies'],
                'is_active' => true,
            ]);

            // Automatically generate physical copies with unique barcodes
            for ($i = 1; $i <= $validated['initial_copies']; $i++) {
                LibraryBookCopy::create([
                    'school_id' => $school->id,
                    'library_book_id' => $book->id,
                    'barcode' => $service->generateCopyBarcode($book, $i),
                    'copy_number' => $i,
                    'status' => 'available',
                ]);
            }

            return $book;
        });

        return redirect()->route('operations.library.books.show', $book)
            ->with('status', "Buku '{$book->title}' berhasil ditambahkan ke katalog perpustakaan beserta {$validated['initial_copies']} eksemplar.");
    }

    public function bookShow(LibraryBook $book): View
    {
        $school = School::current();
        abort_if($book->school_id !== $school?->id, 403, 'Anda tidak memiliki hak akses pada katalog sekolah lain.');

        $book->load(['copies.activeLoan.borrower']);

        return view('operations.library.books.show', compact('book'));
    }

    public function bookEdit(LibraryBook $book): View
    {
        $school = School::current();
        abort_if($book->school_id !== $school?->id, 403);

        $categories = ['Pelajaran', 'Referensi', 'Fiksi', 'Non-Fiksi', 'Kamus', 'Majalah/Jurnal'];

        return view('operations.library.books.edit', compact('book', 'categories'));
    }

    public function bookUpdate(Request $request, LibraryBook $book): RedirectResponse
    {
        $school = School::current();
        abort_if($book->school_id !== $school?->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'author' => ['required', 'string', 'max:150'],
            'publisher' => ['nullable', 'string', 'max:150'],
            'publication_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'isbn' => ['nullable', 'string', 'max:30'],
            'call_number' => ['nullable', 'string', 'max:50'],
            'category' => ['required', 'string', 'max:50'],
            'language' => ['required', 'string', 'max:30'],
            'location_shelf' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'digital_file' => ['nullable', 'file', 'mimes:pdf,epub', 'max:20480'],
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = $request->file('cover_image')->store('library_covers', 'public');
        }

        if ($request->hasFile('digital_file')) {
            $validated['digital_file_path'] = $request->file('digital_file')->store('private/library_digital', 'local');
        }

        $book->update($validated);

        return redirect()->route('operations.library.books.show', $book)
            ->with('status', "Informasi buku '{$book->title}' berhasil diperbarui.");
    }

    public function bookDestroy(LibraryBook $book): RedirectResponse
    {
        $school = School::current();
        abort_if($book->school_id !== $school?->id, 403);

        // Check if any copy is actively borrowed
        $activeLoans = LibraryLoan::whereHas('copy', fn ($q) => $q->where('library_book_id', $book->id))
            ->where('status', 'borrowed')
            ->count();

        if ($activeLoans > 0) {
            return redirect()->back()->with('error', 'Buku tidak dapat dihapus karena masih memiliki eksemplar yang sedang dipinjam.');
        }

        $book->delete();

        return redirect()->route('operations.library.books.index')
            ->with('status', 'Judul buku dan eksemplar terkait berhasil dihapus dari katalog.');
    }

    public function addCopies(Request $request, LibraryBook $book, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if($book->school_id !== $school?->id, 403);

        $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $count = (int) $request->input('count');
        $currentHighestCopy = $book->copies()->max('copy_number') ?? 0;

        for ($i = 1; $i <= $count; $i++) {
            $copyNum = $currentHighestCopy + $i;
            LibraryBookCopy::create([
                'school_id' => $school->id,
                'library_book_id' => $book->id,
                'barcode' => $service->generateCopyBarcode($book, $copyNum),
                'copy_number' => $copyNum,
                'status' => 'available',
            ]);
        }

        $book->recalculateCopies();

        return redirect()->route('operations.library.books.show', $book)
            ->with('status', "Berhasil menambahkan {$count} eksemplar baru.");
    }

    /**
     * Download digital e-book resource securely.
     */
    public function downloadDigital(LibraryBook $book): StreamedResponse|RedirectResponse
    {
        $school = School::current();
        abort_if($book->school_id !== $school?->id, 403);

        if (! $book->digital_file_path || ! Storage::disk('local')->exists($book->digital_file_path)) {
            return redirect()->back()->with('error', 'Berkas digital buku tidak tersedia.');
        }

        return Storage::disk('local')->response(
            $book->digital_file_path,
            $book->title.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    // =========================================================================
    // LOANS & RETURNS
    // =========================================================================

    public function loansIndex(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $status = $request->query('status');
        $search = $request->query('search');

        $query = LibraryLoan::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['copy.book', 'borrower', 'issuer', 'receiver'])
            ->when($status, function ($q, $st) {
                if ($st === 'overdue') {
                    $q->where('status', 'borrowed')->where('due_date', '<', Carbon::today());
                } else {
                    $q->where('status', $st);
                }
            })
            ->when($search, function ($q, $s) {
                $q->where(function ($query) use ($s) {
                    $query->where('loan_code', 'like', "%{$s}%")
                        ->orWhereHas('copy.book', fn ($b) => $b->where('title', 'like', "%{$s}%"))
                        ->orWhereHas('copy', fn ($c) => $c->where('barcode', 'like', "%{$s}%"))
                        ->orWhereHas('borrower', fn ($u) => $u->where('name', 'like', "%{$s}%"));
                });
            });

        $loans = $query->orderBy('borrow_date', 'desc')->paginate(15)->withQueryString();

        $totalActive = LibraryLoan::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', 'borrowed')
            ->count();
        $totalOverdue = LibraryLoan::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', 'borrowed')
            ->where('due_date', '<', Carbon::today())
            ->count();

        return view('operations.library.loans.index', compact('loans', 'status', 'search', 'totalActive', 'totalOverdue'));
    }

    public function loanCreate(): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $availableCopies = LibraryBookCopy::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', 'available')
            ->with('book')
            ->orderBy('barcode')
            ->get();

        $users = User::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereIn('role', ['student', 'teacher', 'staff'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('operations.library.loans.create', compact('availableCopies', 'users'));
    }

    public function loanStore(Request $request, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if(! $school, 403);

        $request->validate([
            'library_book_copy_id' => ['required', 'exists:library_book_copies,id'],
            'user_id' => ['required', 'exists:users,id'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $copy = LibraryBookCopy::where('school_id', $school->id)->findOrFail($request->input('library_book_copy_id'));
        $borrower = User::where('school_id', $school->id)->findOrFail($request->input('user_id'));

        $loan = $service->borrowBook(
            $borrower,
            $copy,
            $request->input('due_date'),
            $request->input('notes'),
            $request->user()
        );

        return redirect()->route('operations.library.loans.index')
            ->with('status', "Peminjaman buku '{$copy->book->title}' untuk {$borrower->name} berhasil dicatat (Kode: {$loan->loan_code}).");
    }

    public function loanReturn(Request $request, LibraryLoan $loan, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if($loan->school_id !== $school?->id, 403);

        $service->returnBook($loan, $request->input('notes'), $request->user());

        $msg = 'Buku telah berhasil dikembalikan.';
        if ($loan->fine_amount > 0) {
            $msg .= ' Dikenakan denda keterlambatan sebesar Rp '.number_format($loan->fine_amount, 0, ',', '.').'.';
        }

        return redirect()->route('operations.library.loans.index')->with('status', $msg);
    }

    public function reports(): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $totalTitles = LibraryBook::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->count();
        $totalCopies = LibraryBookCopy::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->count();
        $availableCopies = LibraryBookCopy::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('status', 'available')->count();
        $borrowedCopies = LibraryBookCopy::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('status', 'borrowed')->count();

        $categoryDistribution = LibraryBook::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get();

        $recentReturns = LibraryLoan::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', 'returned')
            ->with(['copy.book', 'borrower'])
            ->orderBy('return_date', 'desc')
            ->limit(10)
            ->get();

        return view('operations.library.reports', compact(
            'totalTitles',
            'totalCopies',
            'availableCopies',
            'borrowedCopies',
            'categoryDistribution',
            'recentReturns'
        ));
    }
}
