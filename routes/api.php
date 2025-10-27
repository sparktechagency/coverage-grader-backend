<?php

use App\Http\Controllers\Api\V1\Admin\BlogController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\FaqController;
use App\Http\Controllers\Api\V1\Admin\MetaDataController;
use App\Http\Controllers\Api\V1\InsuranceProviderController;
use App\Http\Controllers\Api\V1\Admin\NotificationAlertController;
use App\Http\Controllers\Api\V1\Admin\PageController;
use App\Http\Controllers\Api\V1\Admin\PolicyManagementController;
use App\Http\Controllers\Api\V1\Admin\ReportsController;
use App\Http\Controllers\Api\V1\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Auth\VerificationController;
use App\Http\Controllers\Api\V1\ContactUsController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\ReviewVoteController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\User\BlogController as UserBlogController;
use App\Http\Controllers\Api\V1\User\PolicyManagementController as UserPolicyManagementController;
use App\Http\Controllers\Api\V1\User\UserController;




// --- Public Routes (Authentication) ---
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');

    Route::post('/verify', [VerificationController::class, 'verify'])->name('api.v1.auth.verify');
    Route::post('/resend-verification', [VerificationController::class, 'resendVerification'])->name('api.v1.auth.resendVerification');

    Route::post('/forgot-password', [PasswordController::class, 'forgotPassword'])->name('api.v1.auth.forgotPassword');
    Route::post('/verify-password-otp', [PasswordController::class, 'verifyResetOtp'])->name('api.v1.auth.verifyResetOtp');
    Route::post('/reset-password-with-token', [PasswordController::class, 'resetPasswordWithToken'])->name('api.v1.auth.resetPasswordWithToken');
});

// Route::post('/upload', [FileController::class, 'handleRequest'])->name('api.v1.file.upload');

// --- Protected Routes (User must be logged in) ---
Route::middleware('auth:sanctum', 'throttle:api')->prefix('v1')->group(function () {

    // Auth related protected routes
    Route::prefix('auth')->name('api.v1.auth.')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/update-password', [PasswordController::class, 'updatePassword'])->name('updatePassword');
    });

    // Profile related protected routes
    Route::prefix('profile')->name('api.v1.profile.')->group(function () {
        Route::get('/me', [ProfileController::class, 'me'])->name('me');
        Route::put('/update', [ProfileController::class, 'updateProfile'])->name('update');
    });


    //**-----Admin Routes------ */
    Route::middleware('can:access-admin')->prefix('admin')->name('api.v1.admin.')->group(function () {
        // User Management
        Route::apiResource('users', UserManagementController::class)->except(['create', 'edit', 'store', 'update']);
        Route::post('users/{user}/assign-role', [UserManagementController::class, 'assignRole'])->name('users.assignRole');
        Route::put('users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('users.updateStatus');

        //Policy Management
        Route::apiResource('policies', PolicyManagementController::class)->except(['create', 'edit']);
        Route::put('policies/{policy}/status', [PolicyManagementController::class, 'updateStatus'])->name('policies.updateStatus');

        //Notification Alert management
        Route::apiResource('notifications', NotificationAlertController::class)->except(['create', 'edit', 'update']);
        Route::get('notifications/stats', [NotificationAlertController::class, 'dashboardStats'])->name('notifications.stats');

        //Blog management
        Route::apiResource('blogs', BlogController::class)->except(['create', 'edit']);
        Route::put('blogs/{blog}/status', [BlogController::class, 'updateStatus'])->name('blogs.updateStatus');

        //Page management
        Route::apiResource('pages', PageController::class)->except(['create', 'edit', 'index', 'update']);
        //faq
        Route::apiResource('faqs', FaqController::class)->except(['edit', 'create']);

        //dashboard
        Route::get('dashboard/state', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('dashboard/recent-activity', [DashboardController::class, 'recentActivity']);

        Route::prefix('reports-analytics')->group(function () {
            Route::get('/charts', [ReportsController::class, 'getChartData']);
            Route::get('/recent-reports', [ReportsController::class, 'getRecentReports']);
            Route::post('/generate-report', [ReportsController::class, 'generateReport']);
            Route::get('/download/{id}', [ReportsController::class, 'downloadReport'])->name('report.download');
        });


        //social media settings
        Route::post('social-media-settings', [SettingsController::class, 'socialMediaSettings']);
    });

    //** ----------------Commone Routes---------- */
    //insurance provider management
    Route::apiResource('providers', InsuranceProviderController::class)->except(['create', 'edit']);
    Route::put('providers/{provider}/sponsorship', [InsuranceProviderController::class, 'updateSponsorshipStatus'])->name('providers.updateSponsorship');

    //Review vote routes
    Route::post('reviews/{review}/vote', [ReviewVoteController::class, 'vote'])->name('reviews.vote');
    //Contact us routes
    Route::apiResource('contacts', ContactUsController::class)->only(['store', 'index', 'show', 'destroy']);
    Route::put('contacts/{contact}/mark-as-read', [ContactUsController::class, 'markAsRead']);

    //**metadata */
    //metadata
    Route::apiResource('meta-datas', MetaDataController::class)->only(['store', 'destroy']);
    //**---------Notification routes----------- */
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/stats', [NotificationController::class, 'stats']);
    Route::put('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
});

//** middleware define inside constructor */
Route::middleware('throttle:api')->prefix('v1/')->name('v1')->group(function () {
    //insurance provider management
    Route::apiResource('providers', InsuranceProviderController::class)->except(['create', 'edit']);
    Route::put('providers/{provider}/sponsorship', [InsuranceProviderController::class, 'updateSponsorshipStatus'])->name('providers.updateSponsorship');
    Route::get('provider/compare', [InsuranceProviderController::class, 'compare'])->name('providers.compare');

    //Review routes
    Route::apiResource('reviews', ReviewController::class)->only(['store', 'index', 'show', 'destroy', 'update']);
    Route::put('reviews/{review}/status', [ReviewController::class, 'updateStatus'])->name('reviews.updateStatus');
    //Review vote routes
    Route::get('providers/{provider}/reviews', [ReviewVoteController::class, 'index']);

    //search route
    Route::get('search', [SearchController::class, 'search'])->name('search');

    //social media settings
    Route::get('social-media-settings', [SettingsController::class, 'getSocialMediaSettings'])->name('settings.socialMedia.get');

    //metadata
    Route::apiResource('meta-datas', MetaDataController::class)->only(['index', 'show']);
});

//** -------------User Routes-------------- */
Route::prefix('v1/user')->name('api.v1.user.')->group(function () {
    //Policy Management
    Route::apiResource('policies', UserPolicyManagementController::class)->only(['index', 'show']);
    //Blog management
    Route::apiResource('blogs', UserBlogController::class)->only(['index', 'show']);
    //get pages by type
    Route::get('pages/{type}', [UserController::class, 'getAllPages'])->name('pages.getByType');
    //get faqs
    Route::get('faqs', [UserController::class, 'getFaqs'])->name('faqs.getAll');

    //get all states
    Route::get('states', [UserController::class, 'getAllStates'])->name('states.getAll');
});

Route::fallback(function () {
    return response_error('The requested API endpoint does not exist.', [], 404);
});
