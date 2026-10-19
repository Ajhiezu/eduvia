<?php

namespace App\Providers;

use App\Models\ApprovalRequest;
use App\Models\AttendanceSession;
use App\Models\Exam;
use App\Models\FacilityReservation;
use App\Models\Invoice;
use App\Models\LibraryBook;
use App\Models\SchoolRoom;
use App\Policies\ApprovalRequestPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\ExamPolicy;
use App\Policies\FacilityPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LibraryPolicy;
use App\Services\Payment\MidtransPaymentDriver;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\TenantManager;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantManager::class, function () {
            return new TenantManager;
        });

        $this->app->bind(
            PaymentGatewayInterface::class,
            MidtransPaymentDriver::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(AttendanceSession::class, AttendancePolicy::class);
        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(SchoolRoom::class, FacilityPolicy::class);
        Gate::policy(FacilityReservation::class, FacilityPolicy::class);
        Gate::policy(LibraryBook::class, LibraryPolicy::class);
        Gate::policy(ApprovalRequest::class, ApprovalRequestPolicy::class);
    }
}
