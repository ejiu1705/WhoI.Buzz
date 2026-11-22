<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\SkpiCategory;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\SkpiCategoryPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Paginator::defaultView('vendor.pagination.whoibuzz');

        // Strict Eloquent Protection (Anti N+1 Lazy Loading & Anti Mass Assignment)
        Model::preventLazyLoading(! app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        // Policies (Modul 6: Anti-IDOR / BOLA)
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(SkpiCategory::class, SkpiCategoryPolicy::class);

        // Gates (Modul 6: Anti-BFLA)
        Gate::define('kelola-pengguna', fn (User $user) => $user->hasRole('admin'));
        Gate::define('lihat-progres', fn (User $user) => $user->hasAnyRole(['admin', 'dosen']));
        Gate::define('kelola-kurikulum-skpi', fn (User $user) => $user->hasAnyRole(['admin', 'dosen']));
        Gate::define('validasi-kegiatan', fn (User $user) => $user->hasAnyRole(['admin', 'dosen']));

        // Adaptive Rate Limiting (Anti-Brute Force & Anti-Scraping)
        RateLimiter::for('verify-public', fn (Request $request) => Limit::perMinute(45)->by($request->ip()));
        RateLimiter::for('certificate-upload', fn (Request $request) => Limit::perMinute(15)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('login-attempt', fn (Request $request) => Limit::perMinute(12)->by($request->ip()));
    }
}
