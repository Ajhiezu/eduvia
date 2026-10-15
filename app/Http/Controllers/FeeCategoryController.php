<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\FeeCategory;
use App\Models\FeeRule;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\StudentDiscount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeeCategoryController extends Controller
{
    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Display a listing of fee categories, rules, and discounts.
     */
    public function index(Request $request): View
    {
        $schoolId = $this->currentSchoolId();

        $categories = FeeCategory::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->withCount(['invoices', 'rules'])
            ->latest('id')
            ->get();

        $academicYears = AcademicYear::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('start_date')
            ->get();

        $school = $schoolId ? School::find($schoolId) : School::current();

        $gradeLevels = GradeLevel::query()
            ->when($school && ! empty($school->educational_levels), function ($q) use ($school) {
                $q->whereIn('educational_level', (array) $school->educational_levels);
            })
            ->orderBy('order')
            ->get();

        $classrooms = Classroom::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderBy('name')
            ->get();

        $rules = FeeRule::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['feeCategory', 'academicYear', 'gradeLevel', 'classroom'])
            ->latest('id')
            ->paginate(15, ['*'], 'rules_page');

        $discounts = StudentDiscount::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['student.studentProfile', 'feeCategory', 'academicYear'])
            ->latest('id')
            ->paginate(15, ['*'], 'discounts_page');

        return view('finance.categories.index', compact(
            'categories',
            'rules',
            'discounts',
            'academicYears',
            'gradeLevels',
            'classrooms'
        ));
    }

    /**
     * Store a new fee category.
     */
    public function store(Request $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();

        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:recurring,one_time',
            'billing_period' => 'required|in:monthly,semester,annual,once',
            'amount' => 'required|integer|min:0',
        ]);

        FeeCategory::create([
            'school_id' => $schoolId,
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'billing_period' => $validated['billing_period'],
            'amount' => $validated['amount'],
            'is_active' => true,
        ]);

        return redirect()->route('finance.categories.index')
            ->with('success', 'Kategori tarif biaya berhasil ditambahkan.');
    }

    /**
     * Update an existing fee category.
     */
    public function update(Request $request, FeeCategory $category): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $category->school_id !== (int) $schoolId) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'amount' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $category->update($validated);

        return redirect()->route('finance.categories.index')
            ->with('success', 'Kategori tarif biaya berhasil diperbarui.');
    }

    /**
     * Store a specific fee rule (override tariff).
     */
    public function storeRule(Request $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();

        $validated = $request->validate([
            'fee_category_id' => 'required|exists:fee_categories,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_level_id' => 'nullable|exists:grade_levels,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'amount' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        FeeRule::create([
            'school_id' => $schoolId,
            'fee_category_id' => $validated['fee_category_id'],
            'academic_year_id' => $validated['academic_year_id'],
            'grade_level_id' => $validated['grade_level_id'] ?? null,
            'classroom_id' => $validated['classroom_id'] ?? null,
            'amount' => $validated['amount'],
            'notes' => $validated['notes'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('finance.categories.index')
            ->with('success', 'Aturan tarif khusus berhasil disimpan.');
    }

    /**
     * Delete a fee rule.
     */
    public function destroyRule(FeeRule $rule): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $rule->school_id !== (int) $schoolId) {
            abort(403);
        }

        $rule->delete();

        return redirect()->route('finance.categories.index')
            ->with('success', 'Aturan tarif khusus berhasil dihapus.');
    }

    /**
     * Store a student discount / scholarship.
     */
    public function storeDiscount(Request $request): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'name' => 'required|string|max:150',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|integer|min:1',
            'fee_category_id' => 'nullable|exists:fee_categories,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
            return back()->withErrors(['value' => 'Persentase potongan tidak boleh lebih dari 100%.'])->withInput();
        }

        StudentDiscount::create([
            'school_id' => $schoolId,
            'student_id' => $validated['student_id'],
            'fee_category_id' => $validated['fee_category_id'] ?? null,
            'academic_year_id' => $validated['academic_year_id'] ?? null,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'reason' => $validated['reason'] ?? null,
            'authorized_by' => auth()->id(),
            'is_active' => true,
        ]);

        return redirect()->route('finance.categories.index')
            ->with('success', 'Data beasiswa / potongan biaya berhasil disimpan.');
    }

    /**
     * Delete / deactivate a student discount.
     */
    public function destroyDiscount(StudentDiscount $discount): RedirectResponse
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $discount->school_id !== (int) $schoolId) {
            abort(403);
        }

        $discount->delete();

        return redirect()->route('finance.categories.index')
            ->with('success', 'Data beasiswa / potongan biaya berhasil dihapus.');
    }
}
