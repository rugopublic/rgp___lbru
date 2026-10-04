<?php

declare(strict_types=1);

namespace App\Providers;

use App\Providers\Auth\CustomUserProvider;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\CourseCategoryRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\ArticleCategoryRepositoryInterface;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\ReferenceCategoryRepositoryInterface;
use App\Repositories\Contracts\RecipeCategoryRepositoryInterface;
use App\Repositories\Contracts\GroupRepositoryInterface;
use App\Repositories\Contracts\PaymentIntentRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\SeoRepositoryInterface;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Contracts\ProductPurchaseRepositoryInterface;
use App\Repositories\Contracts\CourseEnrollmentRepositoryInterface;
use App\Repositories\Contracts\CourseStructureRepositoryInterface;
use App\Repositories\Contracts\ContentInteractionRepositoryInterface;
use App\Repositories\Contracts\ContentMediaOwnershipRepositoryInterface;
use App\Repositories\Contracts\PublicAccountRepositoryInterface;
use App\Repositories\Contracts\RecipeStructureRepositoryInterface;
use App\Repositories\Contracts\RecipeStepRepositoryInterface;
use App\Repositories\Contracts\QuizRepositoryInterface;
use App\Repositories\Contracts\RegisterEmailTokenRepositoryInterface;
use App\Repositories\Contracts\PasswordResetTokenRepositoryInterface;
use App\Repositories\Contracts\LegalAcceptanceRepositoryInterface;
use App\Repositories\Contracts\UserEmailUpdateRepositoryInterface;
use App\Repositories\Contracts\UserEmailHistoryRepositoryInterface;
use App\Repositories\Contracts\UserCookieSettingsRepositoryInterface;
use App\Repositories\Contracts\OperationalRepositoryInterface;
use App\Repositories\Eloquent\TransactionRepository;
use App\Repositories\Eloquent\ProductPurchaseRepository;
use App\Repositories\Eloquent\CourseEnrollmentRepository;
use App\Repositories\Eloquent\CourseStructureRepository;
use App\Repositories\Eloquent\ContentInteractionRepository;
use App\Repositories\Eloquent\ContentMediaOwnershipRepository;
use App\Repositories\Eloquent\PublicAccountRepository;
use App\Repositories\Eloquent\RecipeStructureRepository;
use App\Repositories\Eloquent\RecipeStepRepository;
use App\Repositories\Eloquent\QuizRepository;
use App\Repositories\Eloquent\RegisterEmailTokenRepository;
use App\Repositories\Eloquent\PasswordResetTokenRepository;
use App\Repositories\Eloquent\LegalAcceptanceRepository;
use App\Repositories\Eloquent\UserEmailUpdateRepository;
use App\Repositories\Eloquent\UserEmailHistoryRepository;
use App\Repositories\Eloquent\UserCookieSettingsRepository;
use App\Repositories\Eloquent\OperationalRepository;
use App\Repositories\Eloquent\ArticleRepository;
use App\Repositories\Eloquent\CourseCategoryRepository;
use App\Repositories\Eloquent\CourseRepository;
use App\Repositories\Eloquent\ArticleCategoryRepository;
use App\Repositories\Eloquent\ProductCategoryRepository;
use App\Repositories\Eloquent\ReferenceCategoryRepository;
use App\Repositories\Eloquent\RecipeCategoryRepository;
use App\Repositories\Eloquent\GroupRepository;
use App\Repositories\Eloquent\PaymentIntentRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\RecipeRepository;
use App\Repositories\Eloquent\ReferenceRepository;
use App\Repositories\Eloquent\SeoRepository;
use App\Repositories\Eloquent\TeacherRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Handles register for the app service provider.
     * @return void
     */
    public function register(): void
    {
        $bindings = [
            ArticleRepositoryInterface::class => ArticleRepository::class,
            CourseCategoryRepositoryInterface::class => CourseCategoryRepository::class,
            CourseRepositoryInterface::class => CourseRepository::class,
            ArticleCategoryRepositoryInterface::class => ArticleCategoryRepository::class,
            ProductCategoryRepositoryInterface::class => ProductCategoryRepository::class,
            ReferenceCategoryRepositoryInterface::class => ReferenceCategoryRepository::class,
            RecipeCategoryRepositoryInterface::class => RecipeCategoryRepository::class,
            GroupRepositoryInterface::class => GroupRepository::class,
            PaymentIntentRepositoryInterface::class => PaymentIntentRepository::class,
            ProductRepositoryInterface::class => ProductRepository::class,
            RecipeRepositoryInterface::class => RecipeRepository::class,
            ReferenceRepositoryInterface::class => ReferenceRepository::class,
            SeoRepositoryInterface::class => SeoRepository::class,
            TeacherRepositoryInterface::class => TeacherRepository::class,
            UserRepositoryInterface::class => UserRepository::class,
            TransactionRepositoryInterface::class => TransactionRepository::class,
            ProductPurchaseRepositoryInterface::class => ProductPurchaseRepository::class,
            CourseEnrollmentRepositoryInterface::class => CourseEnrollmentRepository::class,
            CourseStructureRepositoryInterface::class => CourseStructureRepository::class,
            ContentInteractionRepositoryInterface::class => ContentInteractionRepository::class,
            ContentMediaOwnershipRepositoryInterface::class => ContentMediaOwnershipRepository::class,
            PublicAccountRepositoryInterface::class => PublicAccountRepository::class,
            RecipeStructureRepositoryInterface::class => RecipeStructureRepository::class,
            RecipeStepRepositoryInterface::class => RecipeStepRepository::class,
            QuizRepositoryInterface::class => QuizRepository::class,
            RegisterEmailTokenRepositoryInterface::class => RegisterEmailTokenRepository::class,
            PasswordResetTokenRepositoryInterface::class => PasswordResetTokenRepository::class,
            LegalAcceptanceRepositoryInterface::class => LegalAcceptanceRepository::class,
            UserEmailUpdateRepositoryInterface::class => UserEmailUpdateRepository::class,
            UserEmailHistoryRepositoryInterface::class => UserEmailHistoryRepository::class,
            UserCookieSettingsRepositoryInterface::class => UserCookieSettingsRepository::class,
            OperationalRepositoryInterface::class => OperationalRepository::class,
        ];

        foreach ($bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    /**
     * Handles boot for the app service provider.
     * @return void
     */
    public function boot(): void
    {
        if (app()->environment('production') || (bool) config('app.force_https', false)) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        Auth::provider('custom-user', static function (Application $app, array $config) {
            return new CustomUserProvider(Hash::driver(), $config['model']);
        });
    }
}
