<?php

namespace App\Providers;

// use App\Models\Product;
// use App\Observers\ProductObserver;

use App\Models\Blog;
use App\Models\Contact;
use App\Models\Faq;
use App\Models\InsuranceProvider;
use App\Models\MetaData;
use App\Models\NotificationAlert;
use App\Models\PolicyCategory;
use App\Models\Review;
use App\Models\User;
use App\Observers\Admin\BlogObserver;
use App\Observers\Admin\FaqObserver;
use App\Observers\InsuranceProviderObserver;
use App\Observers\Admin\NotificationAlertObserver;
use App\Observers\Admin\PolicyCategoryObserver;
use App\Observers\ContactObserver;
use App\Observers\MetaDataObserver;
use App\Observers\User\ReviewObserver;
use App\Observers\UserObserve;
use App\Policies\ContactPolicy;
use App\Policies\InsuranceProviderPolicy;
use App\Policies\MetaDataPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // ...existing code...
        Review::class => ReviewPolicy::class,
        InsuranceProvider::class => InsuranceProviderPolicy::class,
        MetaData::class => MetaDataPolicy::class,
        Contact::class => ContactPolicy::class,
        \Illuminate\Notifications\DatabaseNotification::class => \App\Policies\NotificationPolicy::class,
    ];


    /**
     * Register any application services.
     */
    public function register(): void
    {
         if ($this->app->isLocal()) {
        $this->app->register(\Laravel\Pail\PailServiceProvider::class);
    }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Defining super-admin through Gate
        // This code will run before checking any permission
         Gate::before(function ($user, $ability) {
            return $user && $user->hasRole('admin') ? true : null;
        });

        // Observers
        // Product::observe(ProductObserver::class);
        User::observe(UserObserve::class);
        PolicyCategory::observe(PolicyCategoryObserver::class);
        InsuranceProvider::observe(InsuranceProviderObserver::class);
        NotificationAlert::observe(NotificationAlertObserver::class);
        Faq::observe(FaqObserver::class);
        Blog::observe(BlogObserver::class);
        Review::observe(ReviewObserver::class);
        Contact::observe(ContactObserver::class);
        MetaData::observe(MetaDataObserver::class);

    }
}
