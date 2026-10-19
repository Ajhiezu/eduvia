<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AdminParentRelationController;
use App\Http\Controllers\AdmissionApplicantController;
use App\Http\Controllers\AdmissionDashboardController;
use App\Http\Controllers\AdmissionPeriodController;
use App\Http\Controllers\AdmissionPublicController;
use App\Http\Controllers\AssessmentWeightController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentGradingController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\AttendanceSessionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\ClassroomStudentController;
use App\Http\Controllers\CorrespondenceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamGradingController;
use App\Http\Controllers\ExamQuestionController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\LearningMaterialController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OperationsDashboardController;
use App\Http\Controllers\ParentAcademicController;
use App\Http\Controllers\ParentChildLinkController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\ParentFinanceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportCardPdfController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SchoolAnnouncementController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolPublicController;
use App\Http\Controllers\SchoolWebsiteController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StaffActivationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffImportController;
use App\Http\Controllers\StaffTeachingAssignmentController;
use App\Http\Controllers\StudentAssignmentController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentExamController;
use App\Http\Controllers\StudentFinanceController;
use App\Http\Controllers\StudentImportExportController;
use App\Http\Controllers\StudentLearningMaterialController;
use App\Http\Controllers\StudentPortalReportController;
use App\Http\Controllers\StudentPromotionController;
use App\Http\Controllers\StudentReportCardController;
use App\Http\Controllers\StudentReportController;
use App\Http\Controllers\StudentScheduleController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectGradebookController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\Teacher\TeacherAiAssistantController;
use App\Http\Controllers\TeacherParentCommunicationController;
use App\Http\Controllers\TeacherScheduleController;
use App\Models\School;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Eduvia Platform
|--------------------------------------------------------------------------
*/

// Public Welcome / Landing Page — Nusantara Utama as Primary School Homepage
Route::get('/', function () {
    $school = School::where('slug', 'nusantara-utama')->first()
        ?? School::where('public_website_enabled', true)->first()
        ?? School::first();

    if (! $school) {
        return view('welcome');
    }

    return app(SchoolPublicController::class)->home($school);
})->name('home');

// Direct Health Status
Route::get('/health', [HealthCheckController::class, 'check'])->name('health');

// UI Foundation: Interactive Design System Showcase
Route::get('/design-system', function () {
    return view('design-system');
})->name('design-system');

// Authentication Foundation
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// Aktivasi Mandiri Akun Guru & Staf (Token-based)
Route::get('/staff/activate/{token}', [StaffActivationController::class, 'show'])->name('staff.activate');
Route::post('/staff/activate/{token}', [StaffActivationController::class, 'activate'])->name('staff.activate.submit');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Application Shell (Phase 0 Foundation)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Foundation Verification Endpoint: Restricted to Administrative roles
    Route::get('/restricted-admin-demo', function () {
        return response()->json([
            'success' => true,
            'message' => 'Akses administrator berhasil diverifikasi.',
            'user' => [
                'name' => auth()->user()->name,
                'role' => auth()->user()->role->value,
            ],
        ]);
    })->middleware('role:super_admin,school_admin')->name('demo.admin');

    // Modul 1: Konfigurasi Sekolah & Sistem (Restricted to Super Admin & School Admin)
    Route::middleware('role:super_admin,school_admin')->group(function () {
        // School Profile
        Route::get('/school/profile', [SchoolController::class, 'show'])->name('school.profile');
        Route::get('/school/profile/edit', [SchoolController::class, 'edit'])->name('school.profile.edit');
        Route::put('/school/profile', [SchoolController::class, 'update'])->name('school.profile.update');
        Route::delete('/school/logo', [SchoolController::class, 'removeLogo'])->name('school.logo.remove');

        // Academic Years (Tahun Ajaran)
        Route::resource('academic-years', AcademicYearController::class);
        Route::post('/academic-years/{academic_year}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');

        // Semesters
        Route::resource('semesters', SemesterController::class);
        Route::post('/semesters/{semester}/activate', [SemesterController::class, 'activate'])->name('semesters.activate');

        // System Settings
        Route::get('/settings/system', [SystemSettingController::class, 'index'])->name('settings.system');
        Route::put('/settings/system', [SystemSettingController::class, 'update'])->name('settings.system.update');

        // Modul E-Rapor: Konfigurasi Bobot Penilaian (Admin)
        Route::resource('reports/weights', AssessmentWeightController::class)
            ->names('reports.weights')
            ->parameters(['weights' => 'weight']);

        // Modul 6: Manajemen Kelas & Rombongan Belajar
        Route::resource('classrooms', ClassroomController::class);
        Route::post('/classrooms/{classroom}/students', [ClassroomStudentController::class, 'store'])->name('classrooms.students.store');
        Route::post('/classrooms/{classroom}/students/{student}/transfer', [ClassroomStudentController::class, 'transfer'])->name('classrooms.students.transfer');
        Route::delete('/classrooms/{classroom}/students/{student}', [ClassroomStudentController::class, 'destroy'])->name('classrooms.students.destroy');

        // Modul 17: Manajemen Kesiswaan (Fase 8: Complete Student Master Data & Progression)
        Route::get('/students/import', [StudentImportExportController::class, 'index'])->name('students.import.index');
        Route::get('/students/import/template', [StudentImportExportController::class, 'downloadTemplate'])->name('students.import.template');
        Route::post('/students/import/preview', [StudentImportExportController::class, 'preview'])->name('students.import.preview');
        Route::post('/students/import/process', [StudentImportExportController::class, 'process'])->name('students.import.process');
        Route::get('/students/export/csv', [StudentImportExportController::class, 'exportCsv'])->name('students.export.csv');
        Route::get('/students/promotion/batch', [StudentPromotionController::class, 'index'])->name('students.promotion.index');
        Route::post('/students/promotion/batch', [StudentPromotionController::class, 'process'])->name('students.promotion.process');
        Route::get('/students/report/summary', [StudentReportController::class, 'index'])->name('students.report.index');
        Route::resource('students', StudentController::class);
        Route::post('/students/{student}/status', [StudentController::class, 'updateStatus'])->name('students.status.update');

        // Modul Manajemen Mata Pelajaran & Kurikulum (Fase 8: Master Data Mapel, SMA/SMK Tracks)
        Route::resource('subjects', SubjectController::class);

        // Modul Manajemen Guru & Staf (Pendidik dan Tenaga Kependidikan)
        Route::get('/staff/import', [StaffImportController::class, 'index'])->name('staff.import.index');
        Route::get('/staff/import/template', [StaffImportController::class, 'downloadTemplate'])->name('staff.import.template');
        Route::post('/staff/import/preview', [StaffImportController::class, 'preview'])->name('staff.import.preview');
        Route::post('/staff/import/process', [StaffImportController::class, 'process'])->name('staff.import.process');

        Route::resource('staff', StaffController::class);
        Route::post('/staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
        Route::post('/staff/{staff}/resend-invitation', [StaffController::class, 'resendInvitation'])->name('staff.resend-invitation');

        Route::post('/staff/{staff}/teaching-assignments', [StaffTeachingAssignmentController::class, 'store'])->name('staff.teaching-assignments.store');
        Route::delete('/staff/teaching-assignments/{assignment}', [StaffTeachingAssignmentController::class, 'destroy'])->name('staff.teaching-assignments.destroy');

        // Modul Manajemen Jadwal Pelajaran (Fase 3: Interactive Timetable & Lesson Calendar)
        Route::resource('schedules', ScheduleController::class);
        Route::post('/schedules/{schedule}/toggle-status', [ScheduleController::class, 'toggleStatus'])->name('schedules.toggle-status');

        // Modul 25: PPDB & SPMB - Administrasi Sekolah (Fase 5)
        Route::get('/admissions/dashboard', [AdmissionDashboardController::class, 'index'])->name('admissions.dashboard');
        Route::resource('admissions/periods', AdmissionPeriodController::class)
            ->names('admissions.periods')
            ->parameters(['periods' => 'period']);
        Route::get('/admissions/applicants', [AdmissionApplicantController::class, 'index'])->name('admissions.applicants.index');
        Route::get('/admissions/applicants/{applicant}', [AdmissionApplicantController::class, 'show'])->name('admissions.applicants.show');
        Route::post('/admissions/applicants/{applicant}/verify-document/{document}', [AdmissionApplicantController::class, 'verifyDocument'])->name('admissions.applicants.verify-document');
        Route::post('/admissions/applicants/{applicant}/status', [AdmissionApplicantController::class, 'updateStatus'])->name('admissions.applicants.status');
        Route::post('/admissions/applicants/{applicant}/re-register', [AdmissionApplicantController::class, 'reRegister'])->name('admissions.applicants.re-register');
        Route::post('/admissions/applicants/{applicant}/convert', [AdmissionApplicantController::class, 'convert'])->name('admissions.applicants.convert');
        Route::get('/admissions/documents/{document}/download', [AdmissionApplicantController::class, 'downloadDocument'])->name('admissions.documents.download');

        // Modul CMS Website Sekolah & Profil Publik
        Route::prefix('website')->name('website.')->group(function () {
            Route::get('/dashboard', [SchoolWebsiteController::class, 'dashboard'])->name('dashboard');
            Route::get('/settings', [SchoolWebsiteController::class, 'settings'])->name('settings');
            Route::put('/settings', [SchoolWebsiteController::class, 'updateSettings'])->name('settings.update');
            Route::post('/toggle-status', [SchoolWebsiteController::class, 'toggleWebsiteStatus'])->name('toggle-status');

            // Posts: Berita, Acara, Prestasi
            Route::get('/posts', [SchoolWebsiteController::class, 'posts'])->name('posts.index');
            Route::get('/posts/create', [SchoolWebsiteController::class, 'createPost'])->name('posts.create');
            Route::post('/posts', [SchoolWebsiteController::class, 'storePost'])->name('posts.store');
            Route::get('/posts/{post}/edit', [SchoolWebsiteController::class, 'editPost'])->name('posts.edit');
            Route::put('/posts/{post}', [SchoolWebsiteController::class, 'updatePost'])->name('posts.update');
            Route::delete('/posts/{post}', [SchoolWebsiteController::class, 'destroyPost'])->name('posts.destroy');
            Route::post('/posts/{post}/toggle-publish', [SchoolWebsiteController::class, 'togglePostPublish'])->name('posts.toggle-publish');

            // Ekstrakurikuler
            Route::get('/extracurriculars', [SchoolWebsiteController::class, 'extracurriculars'])->name('extracurriculars.index');
            Route::get('/extracurriculars/create', [SchoolWebsiteController::class, 'createExtracurricular'])->name('extracurriculars.create');
            Route::post('/extracurriculars', [SchoolWebsiteController::class, 'storeExtracurricular'])->name('extracurriculars.store');
            Route::get('/extracurriculars/{extracurricular}/edit', [SchoolWebsiteController::class, 'editExtracurricular'])->name('extracurriculars.edit');
            Route::put('/extracurriculars/{extracurricular}', [SchoolWebsiteController::class, 'updateExtracurricular'])->name('extracurriculars.update');
            Route::delete('/extracurriculars/{extracurricular}', [SchoolWebsiteController::class, 'destroyExtracurricular'])->name('extracurriculars.destroy');

            // Fasilitas & Program Unggulan
            Route::get('/facilities', [SchoolWebsiteController::class, 'facilities'])->name('facilities.index');
            Route::get('/facilities/create', [SchoolWebsiteController::class, 'createFacility'])->name('facilities.create');
            Route::post('/facilities', [SchoolWebsiteController::class, 'storeFacility'])->name('facilities.store');
            Route::get('/facilities/{facility}/edit', [SchoolWebsiteController::class, 'editFacility'])->name('facilities.edit');
            Route::put('/facilities/{facility}', [SchoolWebsiteController::class, 'updateFacility'])->name('facilities.update');
            Route::delete('/facilities/{facility}', [SchoolWebsiteController::class, 'destroyFacility'])->name('facilities.destroy');

            // Galeri Media
            Route::get('/galleries', [SchoolWebsiteController::class, 'galleries'])->name('galleries.index');
            Route::post('/galleries', [SchoolWebsiteController::class, 'storeGallery'])->name('galleries.store');
            Route::delete('/galleries/{gallery}', [SchoolWebsiteController::class, 'destroyGallery'])->name('galleries.destroy');

            // Pesan Pengunjung & Buku Tamu
            Route::get('/messages', [SchoolWebsiteController::class, 'messages'])->name('messages.index');
            Route::get('/messages/{message}', [SchoolWebsiteController::class, 'showMessage'])->name('messages.show');
            Route::put('/messages/{message}/status', [SchoolWebsiteController::class, 'updateMessageStatus'])->name('messages.update-status');
            Route::put('/messages/{message}/notes', [SchoolWebsiteController::class, 'updateMessageNotes'])->name('messages.update-notes');
            Route::delete('/messages/{message}', [SchoolWebsiteController::class, 'destroyMessage'])->name('messages.destroy');
            Route::post('/messages/bulk', [SchoolWebsiteController::class, 'bulkMessages'])->name('messages.bulk');
        });
    });

    // Modul 11: Presensi dan Kehadiran (Super Admin, School Admin, Teacher)
    Route::middleware('role:super_admin,school_admin,teacher')->group(function () {
        Route::resource('attendance-sessions', AttendanceSessionController::class)
            ->parameters(['attendance-sessions' => 'session']);
        Route::post('/attendance-sessions/{session}/finalize', [AttendanceSessionController::class, 'finalize'])->name('attendance-sessions.finalize');
        Route::post('/attendance-sessions/{session}/reopen', [AttendanceSessionController::class, 'reopen'])->name('attendance-sessions.reopen');
        Route::get('/attendance/report', [AttendanceReportController::class, 'index'])->name('attendance.report');

        // Core Learning: Materi Pembelajaran (Guru & Admin)
        Route::resource('learning-materials', LearningMaterialController::class)
            ->parameters(['learning-materials' => 'material']);
        Route::post('/learning-materials/{material}/archive', [LearningMaterialController::class, 'archive'])->name('learning-materials.archive');
        Route::get('/learning-materials/attachments/{attachment}/download', [LearningMaterialController::class, 'downloadAttachment'])->name('learning-materials.attachments.download');

        // Core Learning: Tugas Pembelajaran (Guru & Admin)
        Route::resource('assignments', AssignmentController::class);
        Route::get('/assignments/attachments/{attachment}/download', [AssignmentController::class, 'downloadAttachment'])->name('assignments.attachments.download');

        // Core Learning: Pemeriksaan & Penilaian Tugas (Guru & Admin)
        Route::get('/assignments/{assignment}/submissions/{submission}', [AssignmentGradingController::class, 'showSubmission'])->name('assignments.submissions.show');
        Route::post('/assignments/{assignment}/submissions/{submission}/grade', [AssignmentGradingController::class, 'grade'])->name('assignments.submissions.grade');
        Route::post('/assignments/{assignment}/submissions/{submission}/request-revision', [AssignmentGradingController::class, 'requestRevision'])->name('assignments.submissions.request-revision');
        Route::post('/assignments/{assignment}/submissions/{submission}/publish-grade', [AssignmentGradingController::class, 'publishGrade'])->name('assignments.submissions.publish-grade');
        Route::get('/assignment-submissions/attachments/{attachment}/download', [AssignmentGradingController::class, 'downloadSubmissionAttachment'])->name('assignments.submissions.attachments.download');

        // Jadwal Mengajar Guru & Wali Kelas
        Route::get('/teacher/schedule', [TeacherScheduleController::class, 'index'])->name('teacher.schedule.index');

        // Modul Computer Based Test (CBT) & Bank Soal (Guru & Admin)
        Route::post('/exam-questions/{question}/duplicate', [ExamQuestionController::class, 'duplicate'])->name('exam-questions.duplicate');
        Route::resource('exam-questions', ExamQuestionController::class)->parameters(['exam-questions' => 'question']);

        Route::get('/exams/{exam}/questions', [ExamController::class, 'questions'])->name('exams.questions');
        Route::post('/exams/{exam}/questions', [ExamController::class, 'addQuestion'])->name('exams.questions.add');
        Route::delete('/exams/{exam}/questions/{item}', [ExamController::class, 'removeQuestion'])->name('exams.questions.remove');
        Route::post('/exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
        Route::post('/exams/{exam}/close', [ExamController::class, 'close'])->name('exams.close');
        Route::resource('exams', ExamController::class);

        // CBT Penilaian & Grading (Guru & Admin)
        Route::get('/exams/{exam}/grading', [ExamGradingController::class, 'index'])->name('exams.grading.index');
        Route::get('/exams/{exam}/grading/attempts/{attempt}', [ExamGradingController::class, 'show'])->name('exams.grading.show');
        Route::post('/exams/{exam}/grading/attempts/{attempt}', [ExamGradingController::class, 'grade'])->name('exams.grading.grade');

        // Modul E-Rapor & Buku Nilai Semester (Guru & Admin)
        Route::get('/reports/gradebook', [SubjectGradebookController::class, 'index'])->name('reports.gradebook.index');
        Route::get('/reports/gradebook/create', [SubjectGradebookController::class, 'create'])->name('reports.gradebook.create');
        Route::post('/reports/gradebook', [SubjectGradebookController::class, 'store'])->name('reports.gradebook.store');
        Route::get('/reports/gradebook/{gradebook}', [SubjectGradebookController::class, 'show'])->name('reports.gradebook.show');
        Route::get('/reports/gradebook/{gradebook}/edit', [SubjectGradebookController::class, 'edit'])->name('reports.gradebook.edit');
        Route::put('/reports/gradebook/{gradebook}', [SubjectGradebookController::class, 'update'])->name('reports.gradebook.update');
        Route::post('/reports/gradebook/{gradebook}/submit', [SubjectGradebookController::class, 'submit'])->name('reports.gradebook.submit');
        Route::post('/reports/gradebook/{gradebook}/verify', [SubjectGradebookController::class, 'verify'])->name('reports.gradebook.verify');
        Route::post('/reports/gradebook/{gradebook}/request-revision', [SubjectGradebookController::class, 'requestRevision'])->name('reports.gradebook.request-revision');
        Route::post('/reports/gradebook/{gradebook}/sync', [SubjectGradebookController::class, 'sync'])->name('reports.gradebook.sync');

        // Modul E-Rapor Digital Siswa & Wali Kelas (Guru & Admin)
        Route::get('/reports/cards', [StudentReportCardController::class, 'index'])->name('reports.cards.index');
        Route::get('/reports/cards/classrooms/{classroom}', [StudentReportCardController::class, 'classroomView'])->name('reports.cards.classroom');
        Route::post('/reports/cards/classrooms/{classroom}/bulk-sync', [StudentReportCardController::class, 'bulkSync'])->name('reports.cards.bulk-sync');
        Route::post('/reports/cards/classrooms/{classroom}/bulk-submit', [StudentReportCardController::class, 'bulkSubmit'])->name('reports.cards.bulk-submit');
        Route::post('/reports/cards/classrooms/{classroom}/bulk-publish', [StudentReportCardController::class, 'bulkPublish'])->name('reports.cards.bulk-publish');
        Route::get('/reports/cards/{reportCard}', [StudentReportCardController::class, 'show'])->name('reports.cards.show');
        Route::get('/reports/cards/{reportCard}/edit', [StudentReportCardController::class, 'edit'])->name('reports.cards.edit');
        Route::put('/reports/cards/{reportCard}', [StudentReportCardController::class, 'update'])->name('reports.cards.update');
        Route::post('/reports/cards/{reportCard}/submit', [StudentReportCardController::class, 'submit'])->name('reports.cards.submit');
        Route::post('/reports/cards/{reportCard}/approve', [StudentReportCardController::class, 'approve'])->name('reports.cards.approve');
        Route::post('/reports/cards/{reportCard}/publish', [StudentReportCardController::class, 'publish'])->name('reports.cards.publish');
        Route::post('/reports/cards/{reportCard}/request-revision', [StudentReportCardController::class, 'requestRevision'])->name('reports.cards.request-revision');
        Route::post('/reports/cards/{reportCard}/sync', [StudentReportCardController::class, 'sync'])->name('reports.cards.sync');
    });

    // Cetak & Unduh PDF Rapor (Otorisasi peran siswa/guru/admin divalidasi ketat di Controller)
    Route::get('/reports/cards/{reportCard}/pdf', [ReportCardPdfController::class, 'download'])->name('reports.cards.pdf');

    // Modul Siswa (Presensi & Core Learning)
    Route::middleware('role:student')->group(function () {
        Route::get('/my-attendance', [StudentAttendanceController::class, 'index'])->name('attendance.my');
        Route::get('/student/schedule', [StudentScheduleController::class, 'index'])->name('student.schedule.index');

        // Core Learning Siswa: Materi
        Route::get('/student/materials', [StudentLearningMaterialController::class, 'index'])->name('student.materials.index');
        Route::get('/student/materials/{material}', [StudentLearningMaterialController::class, 'show'])->name('student.materials.show');
        Route::post('/student/materials/{material}/complete', [StudentLearningMaterialController::class, 'complete'])->name('student.materials.complete');
        Route::get('/student/materials/attachments/{attachment}/download', [StudentLearningMaterialController::class, 'downloadAttachment'])->name('student.materials.attachments.download');

        // Core Learning Siswa: Tugas
        Route::get('/student/assignments', [StudentAssignmentController::class, 'index'])->name('student.assignments.index');
        Route::get('/student/assignments/{assignment}', [StudentAssignmentController::class, 'show'])->name('student.assignments.show');
        Route::post('/student/assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('student.assignments.submit');
        Route::get('/student/assignments/attachments/{attachment}/download', [StudentAssignmentController::class, 'downloadAttachment'])->name('student.assignments.attachments.download');
        Route::get('/student/my-submissions/attachments/{attachment}/download', [StudentAssignmentController::class, 'downloadMySubmissionAttachment'])->name('student.assignments.my-submission-attachments.download');

        // CBT Siswa (Computer Based Test)
        Route::get('/student/exams', [StudentExamController::class, 'index'])->name('student.exams.index');
        Route::get('/student/exams/{exam}', [StudentExamController::class, 'show'])->name('student.exams.show');
        Route::post('/student/exams/{exam}/start', [StudentExamController::class, 'start'])->name('student.exams.start');
        Route::get('/student/exams/{exam}/attempts/{attempt}', [StudentExamController::class, 'take'])->name('student.exams.take');
        Route::post('/student/exams/{exam}/attempts/{attempt}/save-answer', [StudentExamController::class, 'saveAnswer'])->name('student.exams.save-answer');
        Route::post('/student/exams/{exam}/attempts/{attempt}/submit', [StudentExamController::class, 'submit'])->name('student.exams.submit');
        Route::get('/student/exams/{exam}/attempts/{attempt}/result', [StudentExamController::class, 'result'])->name('student.exams.result');

        // E-Rapor Digital Siswa
        Route::get('/student/reports', [StudentPortalReportController::class, 'index'])->name('student.reports.index');
        Route::get('/student/reports/{reportCard}', [StudentPortalReportController::class, 'show'])->name('student.reports.show');
    });

    // =========================================================================
    // FASE 7: PORTAL ORANG TUA / WALI MURID (Role: Parent)
    // =========================================================================
    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
        Route::match(['get', 'post'], '/switch-child/{child}', [ParentDashboardController::class, 'switchChild'])->name('switch-child');

        // Ringkasan Akademik Anak
        Route::get('/attendance', [ParentAcademicController::class, 'attendance'])->name('attendance');
        Route::get('/assignments', [ParentAcademicController::class, 'assignments'])->name('assignments');
        Route::get('/grades', [ParentAcademicController::class, 'grades'])->name('grades');
        Route::get('/reports', [ParentAcademicController::class, 'reports'])->name('reports');
        Route::get('/reports/{reportCard}', [ParentAcademicController::class, 'reportDetail'])->name('reports.show');

        // Hubungan Akun & Kode Penautan Mandiri
        Route::get('/children', [ParentChildLinkController::class, 'index'])->name('children.index');
        Route::post('/children/claim-code', [ParentChildLinkController::class, 'claimCode'])
            ->name('children.claim-code')
            ->middleware('throttle:5,1');
    });

    // =========================================================================
    // FASE 7: MANAJEMEN HUBUNGAN ORANG TUA & SISWA (Admin Sekolah & Super Admin)
    // =========================================================================
    Route::middleware('role:super_admin,school_admin')->prefix('admin/parents')->name('admin.parents.')->group(function () {
        Route::get('/', [AdminParentRelationController::class, 'index'])->name('index');
        Route::get('/create', [AdminParentRelationController::class, 'create'])->name('create');
        Route::post('/', [AdminParentRelationController::class, 'store'])->name('store');
        Route::post('/{relation}/verify', [AdminParentRelationController::class, 'verify'])->name('verify');
        Route::post('/{relation}/reject', [AdminParentRelationController::class, 'reject'])->name('reject');
        Route::post('/generate-code', [AdminParentRelationController::class, 'generateLinkCode'])->name('generate-code');
    });

    // =========================================================================
    // FASE 7: PENGUMUMAN SEKOLAH (School Announcements)
    // =========================================================================
    Route::resource('announcements', SchoolAnnouncementController::class);

    // =========================================================================
    // FASE 7: KOMUNIKASI GURU & ORANG TUA (Teacher-Parent Messaging)
    // =========================================================================
    Route::middleware('role:parent,teacher,school_admin,super_admin')->prefix('conversations')->name('conversations.')->group(function () {
        Route::get('/', [TeacherParentCommunicationController::class, 'index'])->name('index');
        Route::get('/create', [TeacherParentCommunicationController::class, 'create'])->name('create');
        Route::post('/', [TeacherParentCommunicationController::class, 'store'])
            ->name('store')
            ->middleware('throttle:20,1');
        Route::get('/{conversation}', [TeacherParentCommunicationController::class, 'show'])->name('show');
        Route::post('/{conversation}/reply', [TeacherParentCommunicationController::class, 'reply'])
            ->name('reply')
            ->middleware('throttle:30,1');
        Route::post('/{conversation}/close', [TeacherParentCommunicationController::class, 'close'])->name('close');
    });

    // =========================================================================
    // FASE 7: SISTEM NOTIFIKASI DALAM APLIKASI
    // =========================================================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/{id}/read', [NotificationController::class, 'read'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/mark-all-read-alias', [NotificationController::class, 'markAllAsRead'])->name('markAllRead');
    });

    // =========================================================================
    // FASE 9: MANAJEMEN KEUANGAN SEKOLAH (School Finance Management)
    // =========================================================================
    Route::middleware('role:super_admin,school_admin,staff')->prefix('finance')->name('finance.')->group(function () {
        // 1. Fee Configuration & Tariffs
        Route::get('/categories', [FeeCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [FeeCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [FeeCategoryController::class, 'update'])->name('categories.update');
        Route::post('/rules', [FeeCategoryController::class, 'storeRule'])->name('rules.store');
        Route::delete('/rules/{rule}', [FeeCategoryController::class, 'destroyRule'])->name('rules.destroy');
        Route::post('/discounts', [FeeCategoryController::class, 'storeDiscount'])->name('discounts.store');
        Route::delete('/discounts/{discount}', [FeeCategoryController::class, 'destroyDiscount'])->name('discounts.destroy');

        // 2. Invoices Management
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/bulk', [InvoiceController::class, 'bulkCreate'])->name('invoices.bulk');
        Route::post('/invoices/bulk', [InvoiceController::class, 'bulkStore'])->name('invoices.bulk.store');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
        Route::post('/invoices/{invoice}/reminder', [InvoiceController::class, 'sendReminder'])->name('invoices.reminder');

        // 3. Payments Recording & Verification
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/queue', [PaymentController::class, 'verificationQueue'])->name('payments.queue');
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
        Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
        Route::post('/payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');
        Route::get('/payments/{payment}/proof', [PaymentController::class, 'downloadProof'])->name('payments.proof');

        // 4. Financial Reports
        Route::get('/reports', [FinanceReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-csv', [FinanceReportController::class, 'exportCsv'])->name('reports.export-csv');
    });

    // PDF Downloads for Invoices & Receipts (Authorized users: Admin, Staff, Student, Parent)
    Route::prefix('finance/pdf')->name('finance.pdf.')->group(function () {
        Route::get('/invoices/{invoice}', [InvoicePdfController::class, 'downloadInvoice'])->name('invoice');
        Route::get('/receipts/{receipt}', [InvoicePdfController::class, 'downloadReceipt'])->name('receipt');
    });

    // Portal Keuangan Orang Tua (Parent Finance Portal)
    Route::middleware('role:parent')->prefix('parent/finance')->name('parent.finance.')->group(function () {
        Route::get('/', [ParentFinanceController::class, 'index'])->name('index');
        Route::get('/invoices/{invoice}', [ParentFinanceController::class, 'showInvoice'])->name('show');
        Route::get('/invoices/{invoice}/pay', [ParentFinanceController::class, 'pay'])->name('pay');
        Route::post('/invoices/{invoice}/pay', [ParentFinanceController::class, 'submitPayment'])->name('submit-pay')->middleware('throttle:10,1');
    });

    // Portal Keuangan Siswa (Student Finance Portal)
    Route::middleware('role:student')->prefix('student/finance')->name('student.finance.')->group(function () {
        Route::get('/', [StudentFinanceController::class, 'index'])->name('index');
        Route::get('/invoices/{invoice}', [StudentFinanceController::class, 'showInvoice'])->name('show');
    });

    // =========================================================================
    // Modul Operasional Sekolah (Phase 10: School Operations Management)
    // =========================================================================
    Route::prefix('operations')->name('operations.')->group(function () {
        // Operational Dashboard
        Route::get('/dashboard', [OperationsDashboardController::class, 'index'])
            ->middleware('role:super_admin,school_admin,staff')
            ->name('dashboard');

        // Module B: Perpustakaan Sekolah (School Library)
        Route::prefix('library')->name('library.')->group(function () {
            // Public to school members (students, teachers, staff, admin)
            Route::get('/books', [LibraryController::class, 'booksIndex'])->name('books.index');

            // Administrative management
            Route::middleware('role:super_admin,school_admin,staff')->group(function () {
                Route::get('/books/create', [LibraryController::class, 'bookCreate'])->name('books.create');
                Route::post('/books', [LibraryController::class, 'bookStore'])->name('books.store');
                Route::get('/books/{book}/edit', [LibraryController::class, 'bookEdit'])->name('books.edit');
                Route::put('/books/{book}', [LibraryController::class, 'bookUpdate'])->name('books.update');
                Route::delete('/books/{book}', [LibraryController::class, 'bookDestroy'])->name('books.destroy');
                Route::post('/books/{book}/add-copies', [LibraryController::class, 'addCopies'])->name('books.add-copies');

                Route::get('/loans', [LibraryController::class, 'loansIndex'])->name('loans.index');
                Route::get('/loans/create', [LibraryController::class, 'loanCreate'])->name('loans.create');
                Route::post('/loans', [LibraryController::class, 'loanStore'])->name('loans.store');
                Route::post('/loans/{loan}/return', [LibraryController::class, 'loanReturn'])->name('loans.return');
                Route::get('/reports', [LibraryController::class, 'reports'])->name('reports');
            });

            Route::get('/books/{book}', [LibraryController::class, 'bookShow'])->name('books.show');
            Route::get('/books/{book}/digital', [LibraryController::class, 'downloadDigital'])->name('books.digital');
        });

        // Module C: Inventaris & Manajemen Aset (Inventory & Asset Management)
        Route::middleware('role:super_admin,school_admin,staff')->prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/assets', [InventoryController::class, 'assetsIndex'])->name('assets.index');
            Route::get('/assets/create', [InventoryController::class, 'assetCreate'])->name('assets.create');
            Route::post('/assets', [InventoryController::class, 'assetStore'])->name('assets.store');
            Route::get('/assets/{asset}', [InventoryController::class, 'assetShow'])->name('assets.show');
            Route::get('/assets/{asset}/edit', [InventoryController::class, 'assetEdit'])->name('assets.edit');
            Route::put('/assets/{asset}', [InventoryController::class, 'assetUpdate'])->name('assets.update');
            Route::delete('/assets/{asset}', [InventoryController::class, 'assetDestroy'])->name('assets.destroy');
            Route::post('/assets/{asset}/transfer', [InventoryController::class, 'transferAsset'])->name('assets.transfer');
            Route::post('/assets/{asset}/condition', [InventoryController::class, 'updateCondition'])->name('assets.condition');

            Route::get('/consumables', [InventoryController::class, 'consumablesIndex'])->name('consumables.index');
            Route::post('/consumables', [InventoryController::class, 'consumableStore'])->name('consumables.store');
            Route::post('/consumables/{item}/adjust', [InventoryController::class, 'consumableAdjust'])->name('consumables.adjust');
            Route::get('/reports', [InventoryController::class, 'reports'])->name('reports');
        });

        // Module D: Laboratorium & Fasilitas Sekolah (Facilities & Laboratories)
        Route::prefix('facilities')->name('facilities.')->group(function () {
            // Accessible to teachers, staff, school admins
            Route::middleware('role:super_admin,school_admin,staff,teacher')->group(function () {
                Route::get('/rooms', [FacilityController::class, 'roomsIndex'])->name('rooms.index');
                Route::get('/reservations', [FacilityController::class, 'reservationsIndex'])->name('reservations.index');
                Route::get('/reservations/create', [FacilityController::class, 'reservationCreate'])->name('reservations.create');
                Route::post('/reservations', [FacilityController::class, 'reservationStore'])->name('reservations.store');
                Route::post('/reservations/{reservation}/cancel', [FacilityController::class, 'reservationCancel'])->name('reservations.cancel');

                Route::get('/incidents', [FacilityController::class, 'incidentsIndex'])->name('incidents.index');
                Route::post('/incidents', [FacilityController::class, 'incidentStore'])->name('incidents.store');
            });

            // Management and approval
            Route::middleware('role:super_admin,school_admin,staff')->group(function () {
                Route::get('/rooms/create', [FacilityController::class, 'roomCreate'])->name('rooms.create');
                Route::post('/rooms', [FacilityController::class, 'roomStore'])->name('rooms.store');
                Route::get('/rooms/{room}/edit', [FacilityController::class, 'roomEdit'])->name('rooms.edit');
                Route::put('/rooms/{room}', [FacilityController::class, 'roomUpdate'])->name('rooms.update');
                Route::delete('/rooms/{room}', [FacilityController::class, 'roomDestroy'])->name('rooms.destroy');

                Route::post('/reservations/{reservation}/approve', [FacilityController::class, 'reservationApprove'])->name('reservations.approve');
                Route::post('/reservations/{reservation}/reject', [FacilityController::class, 'reservationReject'])->name('reservations.reject');

                Route::put('/incidents/{incident}', [FacilityController::class, 'incidentUpdate'])->name('incidents.update');
            });

            Route::get('/rooms/{room}', [FacilityController::class, 'roomShow'])
                ->middleware('role:super_admin,school_admin,staff,teacher')
                ->name('rooms.show');
        });

        // Module E: Tata Usaha, Persuratan & Dokumen (Correspondence & Official Documents)
        Route::prefix('correspondence')->name('correspondence.')->group(function () {
            Route::get('/', [CorrespondenceController::class, 'index'])->name('index');
            Route::get('/create', [CorrespondenceController::class, 'create'])->name('create');
            Route::post('/', [CorrespondenceController::class, 'store'])->name('store');
            Route::get('/{letter}/show', [CorrespondenceController::class, 'show'])->name('show');
            Route::get('/{letter}/edit', [CorrespondenceController::class, 'edit'])->name('edit');
            Route::put('/{letter}', [CorrespondenceController::class, 'update'])->name('update');
            Route::delete('/{letter}', [CorrespondenceController::class, 'destroy'])->name('destroy');
            Route::get('/{letter}/download', [CorrespondenceController::class, 'downloadAttachment'])->name('download');

            // Template Dokumen: Surat Keterangan Siswa Aktif
            Route::get('/certificate/create', [CorrespondenceController::class, 'createCertificate'])->name('certificate.create');
            Route::post('/certificate/pdf', [CorrespondenceController::class, 'generateCertificatePdf'])->name('certificate.pdf');
        });
    });

    // =========================================================================
    // ASISTEN AI GURU (Phase 4B - Guarded by role & tenant.ai)
    // =========================================================================
    Route::middleware(['role:super_admin,school_admin,teacher', 'tenant.ai'])->prefix('teacher/ai')->name('teacher.ai.')->group(function () {
        Route::get('/', [TeacherAiAssistantController::class, 'index'])->name('index');
        Route::get('/lesson-plan', [TeacherAiAssistantController::class, 'lessonPlan'])->name('lesson_plan');
        Route::post('/lesson-plan', [TeacherAiAssistantController::class, 'storeLessonPlan'])->name('lesson_plan.store');
        Route::get('/questions', [TeacherAiAssistantController::class, 'questions'])->name('questions');
        Route::post('/questions', [TeacherAiAssistantController::class, 'storeQuestions'])->name('questions.store');
        Route::get('/rubrics', [TeacherAiAssistantController::class, 'rubrics'])->name('rubrics');
        Route::post('/rubrics', [TeacherAiAssistantController::class, 'storeRubric'])->name('rubrics.store');
        Route::get('/essay-feedback', [TeacherAiAssistantController::class, 'essayFeedback'])->name('essay_feedback');
        Route::post('/essay-feedback', [TeacherAiAssistantController::class, 'storeEssayFeedback'])->name('essay_feedback.store');
        Route::get('/drafts/{draft}', [TeacherAiAssistantController::class, 'show'])->name('drafts.show');
        Route::match(['put', 'post'], '/drafts/{draft}', [TeacherAiAssistantController::class, 'update'])->name('drafts.update');
        Route::post('/drafts/{draft}/import-to-cbt', [TeacherAiAssistantController::class, 'importToCbt'])->name('drafts.import_cbt');
    });
});

// Modul 25: Portal Publik PPDB / SPMB (Guest / Calon Siswa & Wali)
Route::prefix('ppdb')->group(function () {
    Route::get('/', [AdmissionPublicController::class, 'index'])->name('ppdb.index');
    Route::get('/check-status', [AdmissionPublicController::class, 'showCheckStatus'])->name('ppdb.check-status');
    Route::post('/check-status', [AdmissionPublicController::class, 'processCheckStatus'])->name('ppdb.check-status.process');
    Route::get('/success', [AdmissionPublicController::class, 'registrationSuccess'])->name('ppdb.success');
    Route::get('/{period:code}/register', [AdmissionPublicController::class, 'showRegister'])->name('ppdb.register');
    Route::post('/{period:code}/register', [AdmissionPublicController::class, 'submitRegister'])->name('ppdb.register.submit');
});

// =========================================================================
// Website Publik Multi-Sekolah (/s/{school:slug})
// =========================================================================
Route::prefix('s/{school:slug}')->name('school.public.')->group(function () {
    Route::get('/', [SchoolPublicController::class, 'home'])->name('home');
    Route::get('/profile', [SchoolPublicController::class, 'profile'])->name('profile');
    Route::get('/articles', [SchoolPublicController::class, 'articles'])->name('articles');
    Route::get('/articles/{slug}', [SchoolPublicController::class, 'articleDetail'])->name('article-detail');
    Route::get('/events', [SchoolPublicController::class, 'events'])->name('events');
    Route::get('/achievements', [SchoolPublicController::class, 'achievements'])->name('achievements');
    Route::get('/extracurriculars', [SchoolPublicController::class, 'extracurriculars'])->name('extracurriculars');
    Route::get('/facilities', [SchoolPublicController::class, 'facilities'])->name('facilities');
    Route::get('/gallery', [SchoolPublicController::class, 'gallery'])->name('gallery');
    Route::get('/contact', [SchoolPublicController::class, 'contact'])->name('contact');
    Route::post('/contact', [SchoolPublicController::class, 'submitContact'])->name('contact.submit')->middleware('throttle:10,1');
    Route::get('/sitemap.xml', [SchoolPublicController::class, 'sitemap'])->name('sitemap');
    Route::get('/preview', [SchoolPublicController::class, 'preview'])->name('preview');
});
