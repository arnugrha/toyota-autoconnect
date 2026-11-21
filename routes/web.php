<?php

use App\Http\Controllers\AdminLeasingController;
use App\Http\Controllers\AdminNotificationMonitoringController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminSalesOfferController;
use App\Http\Controllers\AdminSalesOpportunityController;
use App\Http\Controllers\AdminServiceBookingController;
use App\Http\Controllers\AdminServiceOrderController;
use App\Http\Controllers\AdminServiceReminderController;
use App\Http\Controllers\AdminServiceScheduleController;
use App\Http\Controllers\AdminSupportTicketController;
use App\Http\Controllers\AdminTestDriveController;
use App\Http\Controllers\AdminVehicleBookingController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CreditSimulationController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\CustomerLeasingApplicationController;
use App\Http\Controllers\CustomerNotificationPreferenceController;
use App\Http\Controllers\CustomerOfferController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\CustomerServiceBookingController;
use App\Http\Controllers\CustomerServiceHistoryController;
use App\Http\Controllers\CustomerServiceReminderController;
use App\Http\Controllers\CustomerSupportTicketController;
use App\Http\Controllers\CustomerTestDriveController;
use App\Http\Controllers\CustomerVehicleBookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExecutiveAnalyticsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MasterData\LeadSourceController;
use App\Http\Controllers\MasterData\PaymentMethodController;
use App\Http\Controllers\MasterData\ServiceTypeController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemSettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleCatalogController;
use App\Http\Controllers\VehicleCategoryController;
use App\Http\Controllers\VehicleManagementController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Toyota AutoConnect
|--------------------------------------------------------------------------
*/

// Public Website & Catalog Routes (Tahap 2)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/kendaraan', [VehicleCatalogController::class, 'index'])->name('vehicles.index');
Route::get('/kendaraan/{slug}', [VehicleCatalogController::class, 'show'])->name('vehicles.show');

Route::view('/promo', 'pages.promo')->name('promo');
Route::view('/tentang-kami', 'pages.about')->name('about');
Route::view('/kontak', 'pages.contact')->name('contact');

// Credit Simulation Live Calculation (Tahap 7 - Public & Interactive)
Route::post('/credit-simulations/calculate', [CreditSimulationController::class, 'calculate'])
    ->middleware('throttle:30,1')
    ->name('credit-simulations.calculate');

// Midtrans Webhook Notification Callback (Tahap 7)
Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handleNotification'])
    ->middleware('throttle:60,1')
    ->name('midtrans.notification');
Route::post('/api/midtrans/notification', [MidtransWebhookController::class, 'handleNotification'])
    ->middleware('throttle:60,1');

// Guest Authentication Routes (Tahap 3 & Dedicated Staff/Customer Login)
Route::middleware('guest')->group(function () {
    // 1. Customer Login (Pelanggan)
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // 2. Staff / Dashboard Login (Super Admin, Sales, Dealer Manager, dsb.)
    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);

    // Customer Registration
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

// Authenticated Routes (Tahap 3)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // General Dashboard Redirector (dispatches to staff or customer dashboard)
    Route::get('/dashboard', function () {
        /** @var User $user */
        $user = Auth::user();

        return $user->hasRole('Customer')
            ? redirect()->route('customer.dashboard')
            : redirect()->route('admin.dashboard');
    })->name('dashboard');

    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Inquiry Submission (Authenticated Customers)
    Route::post('/inquiries', [InquiryController::class, 'store'])
        ->middleware('throttle:15,1')
        ->name('inquiries.store');

    // Notification Center (Universal for all authenticated users - Tahap 9)
    Route::get('/notifications', [NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/dropdown', [NotificationCenterController::class, 'dropdown'])->name('notifications.dropdown');
    Route::post('/notifications/read-all', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationCenterController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::delete('/notifications/{id}', [NotificationCenterController::class, 'destroy'])->name('notifications.destroy');

    // Test Drive Booking Submission (Tahap 6)
    Route::post('/test-drives', [CustomerTestDriveController::class, 'store'])
        ->middleware('throttle:15,1')
        ->name('test-drives.store');

    // Customer Portal Area (Tahap 4 & Tahap 6)
    Route::middleware('role:Customer')->group(function () {
        Route::get('/customer/dashboard', [CustomerPortalController::class, 'dashboard'])->name('customer.dashboard');
        Route::get('/customer/inquiries', [CustomerPortalController::class, 'inquiriesIndex'])->name('customer.inquiries.index');
        Route::get('/customer/inquiries/{inquiry}', [CustomerPortalController::class, 'inquiryShow'])->name('customer.inquiries.show');

        // Customer Test Drive Portal (Tahap 6)
        Route::get('/customer/test-drives', [CustomerTestDriveController::class, 'index'])->name('customer.test-drives.index');
        Route::get('/customer/test-drives/{booking}', [CustomerTestDriveController::class, 'show'])->name('customer.test-drives.show');
        Route::post('/customer/test-drives/{booking}/cancel', [CustomerTestDriveController::class, 'cancel'])->name('customer.test-drives.cancel');

        // Customer Sales Offers (Tahap 6)
        Route::get('/customer/offers', [CustomerOfferController::class, 'index'])->name('customer.offers.index');
        Route::get('/customer/offers/{offer}', [CustomerOfferController::class, 'show'])->name('customer.offers.show');

        // Customer Vehicle Bookings (Tahap 6)
        Route::get('/customer/bookings', [CustomerVehicleBookingController::class, 'index'])->name('customer.bookings.index');
        Route::get('/customer/bookings/{booking}', [CustomerVehicleBookingController::class, 'show'])->name('customer.bookings.show');

        // Customer Credit Simulations (Tahap 7)
        Route::post('/customer/simulations', [CreditSimulationController::class, 'store'])->name('customer.simulations.store');
        Route::get('/customer/simulations', [CreditSimulationController::class, 'index'])->name('customer.simulations.index');
        Route::get('/customer/simulations/compare', [CreditSimulationController::class, 'compare'])->name('customer.simulations.compare');
        Route::get('/customer/simulations/{simulation}', [CreditSimulationController::class, 'show'])->name('customer.simulations.show');
        Route::delete('/customer/simulations/{simulation}', [CreditSimulationController::class, 'destroy'])->name('customer.simulations.destroy');

        // Customer Leasing Applications (Tahap 7)
        Route::get('/customer/leasing', [CustomerLeasingApplicationController::class, 'index'])->name('customer.leasing.index');
        Route::get('/customer/leasing/create', [CustomerLeasingApplicationController::class, 'create'])->name('customer.leasing.create');
        Route::post('/customer/leasing', [CustomerLeasingApplicationController::class, 'store'])->name('customer.leasing.store');
        Route::get('/customer/leasing/{application}', [CustomerLeasingApplicationController::class, 'show'])->name('customer.leasing.show');
        Route::post('/customer/leasing/{application}/cancel', [CustomerLeasingApplicationController::class, 'cancel'])->name('customer.leasing.cancel');

        // Customer Payments & Midtrans Checkout (Tahap 7)
        Route::get('/customer/payments', [CustomerPaymentController::class, 'index'])->name('customer.payments.index');
        Route::get('/customer/payments/finish', [CustomerPaymentController::class, 'finish'])->name('customer.payments.finish');
        Route::get('/customer/payments/{payment}', [CustomerPaymentController::class, 'show'])->name('customer.payments.show');
        Route::get('/customer/bookings/{booking}/pay', [CustomerPaymentController::class, 'checkout'])->name('customer.bookings.pay');
        Route::post('/customer/bookings/{booking}/pay', [CustomerPaymentController::class, 'checkout'])->name('customer.bookings.checkout');

        // Customer Service Bookings (Tahap 8)
        Route::get('/customer/service-bookings', [CustomerServiceBookingController::class, 'index'])->name('customer.service-bookings.index');
        Route::get('/customer/service-bookings/create', [CustomerServiceBookingController::class, 'create'])->name('customer.service-bookings.create');
        Route::get('/customer/service-bookings/available-slots', [CustomerServiceBookingController::class, 'availableSlots'])->name('customer.service-bookings.available-slots');
        Route::post('/customer/service-bookings', [CustomerServiceBookingController::class, 'store'])->name('customer.service-bookings.store');
        Route::get('/customer/service-bookings/{booking}', [CustomerServiceBookingController::class, 'show'])->name('customer.service-bookings.show');
        Route::post('/customer/service-bookings/{booking}/cancel', [CustomerServiceBookingController::class, 'cancel'])->name('customer.service-bookings.cancel');

        // Customer Service Histories & Invoices (Tahap 8)
        Route::get('/customer/service-histories', [CustomerServiceHistoryController::class, 'index'])->name('customer.service-histories.index');
        Route::get('/customer/service-histories/{order}', [CustomerServiceHistoryController::class, 'show'])->name('customer.service-histories.show');

        // Customer Service Reminders (Tahap 8)
        Route::get('/customer/service-reminders', [CustomerServiceReminderController::class, 'index'])->name('customer.service-reminders.index');

        // Customer Support & Helpdesk Tickets (Tahap 8)
        Route::get('/customer/tickets', [CustomerSupportTicketController::class, 'index'])->name('customer.tickets.index');
        Route::get('/customer/tickets/create', [CustomerSupportTicketController::class, 'create'])->name('customer.tickets.create');
        Route::post('/customer/tickets', [CustomerSupportTicketController::class, 'store'])->name('customer.tickets.store');
        Route::get('/customer/tickets/{ticket}', [CustomerSupportTicketController::class, 'show'])->name('customer.tickets.show');
        Route::post('/customer/tickets/{ticket}/reply', [CustomerSupportTicketController::class, 'reply'])->name('customer.tickets.reply');

        // Customer Notification Preferences (Tahap 9)
        Route::get('/customer/notification-preferences', [CustomerNotificationPreferenceController::class, 'edit'])->name('customer.notifications.preferences');
        Route::put('/customer/notification-preferences', [CustomerNotificationPreferenceController::class, 'update'])->name('customer.notifications.preferences.update');
    });

    // Internal Staff & Dealer Administration Area
    Route::prefix('admin')->as('admin.')->group(function () {
        // Staff Dashboard
        Route::middleware('permission:dashboard.view')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])->name('dashboard');
        });

        // CRM & Lead Management (Tahap 4)
        Route::middleware('permission:leads.view')->group(function () {
            Route::get('/crm', [CrmController::class, 'index'])->name('crm.index');
            Route::get('/leads', [CrmController::class, 'index'])->name('leads.index');
            Route::get('/leads/{lead}', [CrmController::class, 'show'])->name('leads.show');
        });

        Route::middleware('permission:leads.change-status')->group(function () {
            Route::put('/leads/{lead}/status', [CrmController::class, 'updateStatus'])->name('leads.status.update');
        });

        Route::middleware('permission:leads.assign')->group(function () {
            Route::put('/leads/{lead}/assign', [CrmController::class, 'assign'])->name('leads.assign');
        });

        Route::middleware('permission:leads.add-activity')->group(function () {
            Route::post('/leads/{lead}/activities', [CrmController::class, 'addActivity'])->name('leads.activities.store');
        });

        // User Management (Super Admin & Staff Managers with permission)
        Route::middleware('permission:users.view')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
        });

        Route::middleware('permission:users.create')->group(function () {
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
        });

        Route::middleware('permission:users.update')->group(function () {
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        });

        // Vehicle Management (Tahap 5)
        Route::middleware('permission:vehicles.view')->group(function () {
            Route::get('/vehicles', [VehicleManagementController::class, 'index'])->name('vehicles.index');
        });

        Route::middleware('permission:vehicles.create')->group(function () {
            Route::get('/vehicles/create', [VehicleManagementController::class, 'create'])->name('vehicles.create');
            Route::post('/vehicles', [VehicleManagementController::class, 'store'])->name('vehicles.store');
        });

        Route::middleware('permission:vehicles.update')->group(function () {
            Route::get('/vehicles/{vehicle}/edit', [VehicleManagementController::class, 'edit'])->name('vehicles.edit');
            Route::put('/vehicles/{vehicle}', [VehicleManagementController::class, 'update'])->name('vehicles.update');
            Route::delete('/vehicles/images/{image}', [VehicleManagementController::class, 'deleteImage'])->name('vehicles.images.delete');
            Route::put('/vehicles/images/{image}/primary', [VehicleManagementController::class, 'setPrimaryImage'])->name('vehicles.images.primary');
        });

        Route::middleware('permission:vehicles.delete')->group(function () {
            Route::delete('/vehicles/{vehicle}', [VehicleManagementController::class, 'destroy'])->name('vehicles.destroy');
        });

        // Vehicle Categories Management (Tahap 5 & Tahap 11)
        Route::middleware('permission:vehicles.view')->group(function () {
            Route::get('/categories', [VehicleCategoryController::class, 'index'])->name('categories.index');
        });
        Route::middleware('permission:vehicles.create')->group(function () {
            Route::post('/categories', [VehicleCategoryController::class, 'store'])->name('categories.store');
        });
        Route::middleware('permission:vehicles.update')->group(function () {
            Route::put('/categories/{category}', [VehicleCategoryController::class, 'update'])->name('categories.update');
            Route::put('/categories/{category}/toggle', [VehicleCategoryController::class, 'toggleStatus'])->name('categories.toggle');
        });
        Route::middleware('permission:vehicles.delete')->group(function () {
            Route::delete('/categories/{category}', [VehicleCategoryController::class, 'destroy'])->name('categories.destroy');
        });

        // Dealer Branch Management (Tahap 5 & Tahap 11)
        Route::middleware('permission:settings.manage')->group(function () {
            Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
            Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
            Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
            Route::put('/branches/{branch}/toggle', [BranchController::class, 'toggleStatus'])->name('branches.toggle');
            Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

            // Master Data - Service Types (Tahap 11)
            Route::get('/service-types', [ServiceTypeController::class, 'index'])->name('service-types.index');
            Route::post('/service-types', [ServiceTypeController::class, 'store'])->name('service-types.store');
            Route::put('/service-types/{serviceType}', [ServiceTypeController::class, 'update'])->name('service-types.update');
            Route::put('/service-types/{serviceType}/toggle', [ServiceTypeController::class, 'toggleStatus'])->name('service-types.toggle');
            Route::delete('/service-types/{serviceType}', [ServiceTypeController::class, 'destroy'])->name('service-types.destroy');

            // Master Data - Lead Sources (Tahap 11)
            Route::get('/lead-sources', [LeadSourceController::class, 'index'])->name('lead-sources.index');
            Route::post('/lead-sources', [LeadSourceController::class, 'store'])->name('lead-sources.store');
            Route::put('/lead-sources/{leadSource}', [LeadSourceController::class, 'update'])->name('lead-sources.update');
            Route::put('/lead-sources/{leadSource}/toggle', [LeadSourceController::class, 'toggleStatus'])->name('lead-sources.toggle');
            Route::delete('/lead-sources/{leadSource}', [LeadSourceController::class, 'destroy'])->name('lead-sources.destroy');

            // Master Data - Payment Methods (Tahap 11)
            Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
            Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
            Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
            Route::put('/payment-methods/{paymentMethod}/toggle', [PaymentMethodController::class, 'toggleStatus'])->name('payment-methods.toggle');
            Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
        });

        // Multi-Branch Inventory Management (Tahap 5)
        Route::middleware('permission:inventory.view')->group(function () {
            Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
            Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
        });

        Route::middleware('permission:inventory.manage')->group(function () {
            Route::get('/inventory/adjust', [InventoryController::class, 'showAdjustForm'])->name('inventory.adjust.form');
            Route::post('/inventory/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust.store');
        });

        // Test Drive Management (Tahap 6)
        Route::middleware('permission:test-drives.view')->group(function () {
            Route::get('/test-drives', [AdminTestDriveController::class, 'index'])->name('test-drives.index');
            Route::get('/test-drives/{booking}', [AdminTestDriveController::class, 'show'])->name('test-drives.show');
        });

        Route::middleware('permission:test-drives.manage')->group(function () {
            Route::put('/test-drives/{booking}/status', [AdminTestDriveController::class, 'updateStatus'])->name('test-drives.status.update');
        });

        // Sales Opportunities & Pipeline (Tahap 6)
        Route::middleware('permission:sales.view')->group(function () {
            Route::get('/sales/opportunities', [AdminSalesOpportunityController::class, 'index'])->name('sales.opportunities.index');
            Route::get('/sales/opportunities/create', [AdminSalesOpportunityController::class, 'create'])->name('sales.opportunities.create');
            Route::get('/sales/opportunities/{opportunity}', [AdminSalesOpportunityController::class, 'show'])->name('sales.opportunities.show');
        });

        Route::middleware('permission:sales.manage')->group(function () {
            Route::post('/sales/opportunities', [AdminSalesOpportunityController::class, 'store'])->name('sales.opportunities.store');
            Route::put('/sales/opportunities/{opportunity}', [AdminSalesOpportunityController::class, 'update'])->name('sales.opportunities.update');
            Route::post('/sales/opportunities/{opportunity}/notes', [AdminSalesOpportunityController::class, 'addNote'])->name('sales.opportunities.notes.store');
        });

        // Sales Offers (Tahap 6)
        Route::middleware('permission:sales.view')->group(function () {
            Route::get('/sales/offers/{offer}', [AdminSalesOfferController::class, 'show'])->name('sales.offers.show');
        });

        Route::middleware('permission:sales.manage')->group(function () {
            Route::get('/sales/opportunities/{opportunity}/offers/create', [AdminSalesOfferController::class, 'create'])->name('sales.offers.create');
            Route::post('/sales/offers', [AdminSalesOfferController::class, 'store'])->name('sales.offers.store');
            Route::put('/sales/offers/{offer}/send', [AdminSalesOfferController::class, 'send'])->name('sales.offers.send');
            Route::put('/sales/offers/{offer}/status', [AdminSalesOfferController::class, 'updateStatus'])->name('sales.offers.status.update');
        });

        // Vehicle Bookings & Stock Allocation (Tahap 6)
        Route::middleware('permission:sales.view')->group(function () {
            Route::get('/sales/bookings', [AdminVehicleBookingController::class, 'index'])->name('sales.bookings.index');
            Route::get('/sales/bookings/{booking}', [AdminVehicleBookingController::class, 'show'])->name('sales.bookings.show');
        });

        Route::middleware('permission:sales.manage')->group(function () {
            Route::get('/sales/opportunities/{opportunity}/bookings/create', [AdminVehicleBookingController::class, 'create'])->name('sales.bookings.create');
            Route::post('/sales/bookings', [AdminVehicleBookingController::class, 'store'])->name('sales.bookings.store');
            Route::put('/sales/bookings/{booking}/confirm', [AdminVehicleBookingController::class, 'confirm'])->name('sales.bookings.confirm');
            Route::put('/sales/bookings/{booking}/cancel', [AdminVehicleBookingController::class, 'cancel'])->name('sales.bookings.cancel');
            Route::put('/sales/bookings/{booking}/complete', [AdminVehicleBookingController::class, 'complete'])->name('sales.bookings.complete');
            Route::put('/sales/bookings/{booking}/payment-status', [AdminVehicleBookingController::class, 'updatePaymentStatus'])->name('sales.bookings.payment-status.update');
        });

        // Leasing & Financing Management (Tahap 7)
        Route::middleware('permission:financing.view')->group(function () {
            Route::get('/leasing', [AdminLeasingController::class, 'index'])->name('leasing.index');
            Route::get('/leasing/providers', [AdminLeasingController::class, 'providers'])->name('leasing.providers');
            Route::get('/leasing/{application}', [AdminLeasingController::class, 'show'])->name('leasing.show');
        });

        Route::middleware('permission:financing.manage')->group(function () {
            Route::put('/leasing/{application}/status', [AdminLeasingController::class, 'updateStatus'])->name('leasing.status.update');
            Route::post('/leasing/{application}/status', [AdminLeasingController::class, 'updateStatus'])->name('leasing.status');
            Route::post('/leasing/{application}/sync', [AdminLeasingController::class, 'syncProvider'])->name('leasing.sync');
            Route::post('/leasing/providers/{provider}/toggle', [AdminLeasingController::class, 'toggleProvider'])->name('leasing.providers.toggle');
        });

        // Payment Transactions & Midtrans Gateway (Tahap 7)
        Route::middleware('permission:payments.view')->group(function () {
            Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
        });

        Route::middleware('permission:payments.manage')->group(function () {
            Route::post('/payments/{payment}/refund', [AdminPaymentController::class, 'refund'])->name('payments.refund');
        });

        // Workshop & Service Booking Management (Tahap 8)
        Route::middleware('permission:service-bookings.view')->group(function () {
            Route::get('/service-bookings', [AdminServiceBookingController::class, 'index'])->name('service-bookings.index');
            Route::get('/service-bookings/calendar', [AdminServiceBookingController::class, 'calendar'])->name('service-bookings.calendar');
            Route::get('/service-bookings/{serviceBooking}', [AdminServiceBookingController::class, 'show'])->name('service-bookings.show');
        });

        Route::middleware('permission:service-bookings.manage')->group(function () {
            Route::post('/service-bookings/{serviceBooking}/confirm', [AdminServiceBookingController::class, 'confirm'])->name('service-bookings.confirm');
            Route::post('/service-bookings/{serviceBooking}/reschedule', [AdminServiceBookingController::class, 'reschedule'])->name('service-bookings.reschedule');
            Route::post('/service-bookings/{serviceBooking}/reject', [AdminServiceBookingController::class, 'reject'])->name('service-bookings.reject');
        });

        // Branch Service Schedules & Capacity (Tahap 8)
        Route::middleware('permission:service-schedules.manage')->group(function () {
            Route::get('/service-schedules', [AdminServiceScheduleController::class, 'index'])->name('service-schedules.index');
            Route::put('/service-schedules/{serviceSchedule}', [AdminServiceScheduleController::class, 'update'])->name('service-schedules.update');
        });

        // Service Orders & Work Orders (Tahap 8)
        Route::middleware('permission:service-orders.view')->group(function () {
            Route::get('/service-orders', [AdminServiceOrderController::class, 'index'])->name('service-orders.index');
            Route::get('/service-orders/{serviceOrder}', [AdminServiceOrderController::class, 'show'])->name('service-orders.show');
        });

        Route::middleware('permission:service-orders.manage')->group(function () {
            Route::put('/service-orders/{serviceOrder}/status', [AdminServiceOrderController::class, 'updateStatus'])->name('service-orders.status.update');
            Route::post('/service-orders/{serviceOrder}/inspection', [AdminServiceOrderController::class, 'recordInspection'])->name('service-orders.inspection.store');
            Route::post('/service-orders/{serviceOrder}/items', [AdminServiceOrderController::class, 'addItem'])->name('service-orders.items.store');
            Route::delete('/service-orders/{serviceOrder}/items/{item}', [AdminServiceOrderController::class, 'removeItem'])->name('service-orders.items.destroy');
        });

        // After-Sales Service Reminders (Tahap 8)
        Route::middleware('permission:service-orders.manage')->group(function () {
            Route::get('/service-reminders', [AdminServiceReminderController::class, 'index'])->name('service-reminders.index');
            Route::post('/service-reminders/trigger', [AdminServiceReminderController::class, 'trigger'])->name('service-reminders.trigger');
        });

        // Customer Service Tickets / Helpdesk (Tahap 8)
        Route::middleware('permission:support-tickets.view')->group(function () {
            Route::get('/tickets', [AdminSupportTicketController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/{ticket}', [AdminSupportTicketController::class, 'show'])->name('tickets.show');
        });

        Route::middleware('permission:support-tickets.manage')->group(function () {
            Route::post('/tickets/{ticket}/reply', [AdminSupportTicketController::class, 'reply'])->name('tickets.reply');
            Route::put('/tickets/{ticket}/status', [AdminSupportTicketController::class, 'updateStatus'])->name('tickets.status.update');
            Route::put('/tickets/{ticket}/assign', [AdminSupportTicketController::class, 'assign'])->name('tickets.assign');
        });

        // Notification Monitoring & Logs (Tahap 9)
        Route::middleware('permission:notifications.view')->group(function () {
            Route::get('/notifications/monitoring', [AdminNotificationMonitoringController::class, 'index'])->name('notifications.monitoring');
        });

        Route::middleware('permission:notifications.manage')->group(function () {
            Route::post('/notifications/monitoring/{log}/resend', [AdminNotificationMonitoringController::class, 'resend'])->name('notifications.resend');
        });

        // Analytics & Business Intelligence Dashboard (Tahap 10)
        Route::middleware('permission:analytics.view')->prefix('analytics')->as('analytics.')->group(function () {
            Route::get('/dashboard', [ExecutiveAnalyticsController::class, 'dashboard'])->name('dashboard');
            Route::get('/sales', [ExecutiveAnalyticsController::class, 'sales'])->name('sales');
            Route::get('/crm', [ExecutiveAnalyticsController::class, 'crm'])->name('crm');
            Route::get('/dealers', [ExecutiveAnalyticsController::class, 'dealers'])->name('dealers');
            Route::get('/inventory', [ExecutiveAnalyticsController::class, 'inventory'])->name('inventory');
            Route::get('/payments', [ExecutiveAnalyticsController::class, 'payments'])->name('payments');
            Route::get('/after-sales', [ExecutiveAnalyticsController::class, 'afterSales'])->name('after-sales');
            Route::get('/customers', [ExecutiveAnalyticsController::class, 'customers'])->name('customers');
        });

        // Reporting Center (Tahap 10)
        Route::middleware('permission:reports.view')->group(function () {
            Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
        });

        // System Settings Management (Tahap 11)
        Route::middleware('permission:settings.manage')->group(function () {
            Route::get('/settings', [SystemSettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings', [SystemSettingsController::class, 'update'])->name('settings.update');
        });

        // System Audit Trail & Logs (Tahap 11)
        Route::middleware('permission:audit.view')->group(function () {
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
            Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
        });
    });
});
