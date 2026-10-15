<?php

namespace App\Http\Controllers;

use App\Notifications\ParentLinkVerifiedNotification;
use App\Services\ParentPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ParentChildLinkController extends Controller
{
    public function __construct(
        protected ParentPortalService $parentPortalService
    ) {}

    /**
     * Show list of linked children and linking code input.
     */
    public function index(Request $request): View
    {
        $parent = $request->user();

        $relations = $parent->parentRelations()
            ->with([
                'student.studentProfile',
                'student.classroomEnrollments.classroom.gradeLevel',
                'student.classroomEnrollments.classroom.homeroomTeacher',
                'verifier',
            ])
            ->latest('id')
            ->get();

        return view('parent.children.index', compact('relations'));
    }

    /**
     * Claim / redeem an invitation link code to connect account.
     */
    public function claimCode(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:32'],
        ], [
            'code.required' => 'Silakan masukkan kode penautan yang diberikan pihak sekolah.',
            'code.min' => 'Format kode penautan tidak valid.',
        ]);

        $parent = $request->user();

        try {
            $relation = $this->parentPortalService->claimLinkCode(
                $parent,
                $request->input('code'),
                $request->input('relationship_type')
            );

            // Send database notification to parent
            $parent->notify(new ParentLinkVerifiedNotification($relation));

            return redirect()->route('parent.children.index')
                ->with('success', 'Akun anak atas nama '.$relation->student->name.' berhasil ditautkan dan terverifikasi.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['code' => 'Terjadi kendala saat memproses penautan: '.$e->getMessage()]);
        }
    }
}
