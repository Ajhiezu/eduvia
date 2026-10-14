<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AdmissionApplicantController;
use App\Http\Controllers\AdmissionDashboardController;
use App\Http\Controllers\AdmissionPeriodController;
use App\Http\Controllers\AdmissionPublicController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentGradingController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\AttendanceSessionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\ClassroomStudentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\LearningMaterialController;
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
use App\Http\Controllers\StudentLearningMaterialController;
use App\Http\Controllers\StudentPromotionController;
use App\Http\Controllers\StudentReportController;
use App\Http\Controllers\SystemSettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Eduvia Platform
|--------------------------------------------------------------------------
*/

// Public Welcome / Landing Page
Route::get('/', function () {
    return view('welcome');
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

        // Modul 6: Manajemen Kelas & Rombongan Belajar
        Route::resource('classrooms', ClassroomController::class);
        Route::post('/classrooms/{classroom}/students', [ClassroomStudentController::class, 'store'])->name('classrooms.students.store');
        Route::post('/classrooms/{classroom}/students/{student}/transfer', [ClassroomStudentController::class, 'transfer'])->name('classrooms.students.transfer');
        Route::delete('/classrooms/{classroom}/students/{student}', [ClassroomStudentController::class, 'destroy'])->name('classrooms.students.destroy');

        // Modul 17: Manajemen Kesiswaan (Fase 4)
        Route::get('/students/promotion/batch', [StudentPromotionController::class, 'index'])->name('students.promotion.index');
        Route::post('/students/promotion/batch', [StudentPromotionController::class, 'process'])->name('students.promotion.process');
        Route::get('/students/report/summary', [StudentReportController::class, 'index'])->name('students.report.index');
        Route::resource('students', StudentController::class);
        Route::post('/students/{student}/status', [StudentController::class, 'updateStatus'])->name('students.status.update');

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
    });

    // Modul Siswa (Presensi & Core Learning)
    Route::middleware('role:student')->group(function () {
        Route::get('/my-attendance', [StudentAttendanceController::class, 'index'])->name('attendance.my');

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
    Route::get('/sitemap.xml', [SchoolPublicController::class, 'sitemap'])->name('sitemap');
    Route::get('/preview', [SchoolPublicController::class, 'preview'])->name('preview');
});
