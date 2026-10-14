<?php

namespace App\Http\Controllers;

use App\Http\Requests\Classroom\AddStudentToClassroomRequest;
use App\Http\Requests\Classroom\TransferClassroomStudentRequest;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClassroomStudentController extends Controller
{
    /**
     * Add a student to the specified classroom.
     */
    public function store(AddStudentToClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        $classroom->addStudent(
            $request->input('student_id'),
            $request->input('joined_at'),
            $request->input('notes')
        );

        return redirect()->route('classrooms.show', $classroom)
            ->with('status', 'Peserta didik berhasil ditambahkan ke rombongan belajar.');
    }

    /**
     * Transfer a student from this classroom to another classroom.
     */
    public function transfer(TransferClassroomStudentRequest $request, Classroom $classroom, User $student): RedirectResponse
    {
        $targetClassroom = Classroom::findOrFail($request->input('target_classroom_id'));

        $classroom->transferStudent(
            $student,
            $targetClassroom,
            $request->input('effective_date'),
            $request->input('notes')
        );

        return redirect()->route('classrooms.show', $classroom)
            ->with('status', "Peserta didik berhasil dipindahkan ke rombongan belajar '{$targetClassroom->name}'.");
    }

    /**
     * Remove or drop a student from active enrollment.
     */
    public function destroy(Request $request, Classroom $classroom, User $student): RedirectResponse
    {
        $status = $request->input('status', 'dropped');
        $notes = $request->input('notes');

        $classroom->removeStudent($student, $status, $notes);

        return redirect()->route('classrooms.show', $classroom)
            ->with('status', 'Peserta didik berhasil dikeluarkan dari keanggotaan rombongan belajar.');
    }
}
