<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Applies the schema change.
     * @return void
     */
    public function up(): void
    {
        // -----------------------------------------------------------------
        // Laravel infrastructure
        // -----------------------------------------------------------------
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('email', 254)->unique();
            $table->boolean('is_admin')->default(false)->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('email_change_last_requested_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // -----------------------------------------------------------------
        // User/account/legal domain
        // -----------------------------------------------------------------
        Schema::create('user_email_updates', function (Blueprint $table): void {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('email', 254)->unique();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_email_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('email', 254);
            $table->timestamp('active_from');
            $table->timestamp('active_to');
            $table->timestamps();
        });

        Schema::create('user_accounts', function (Blueprint $table): void {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('name', 254)->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
        });

        Schema::create('register_email_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('agreement_accepted_at')->nullable();
            $table->string('agreement_version', 20)->nullable();
            $table->string('privacy_version', 20)->nullable();
            $table->string('acceptance_ip', 45)->nullable();
            $table->text('acceptance_user_agent')->nullable();
        });

        Schema::create('user_cookie_settings', function (Blueprint $table): void {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->timestamp('required_consent_at')->nullable();
            $table->timestamp('functional_consent_at')->nullable();
            $table->timestamp('marketing_consent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_bans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('banned_at')->nullable();
            $table->string('reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'banned_at']);
        });

        Schema::create('user_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('groups', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('name', 127);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('group_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['group_id', 'user_id']);
        });

        Schema::create('legal_acceptances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('agreement_version', 20);
            $table->string('privacy_version', 20);
            $table->timestamp('accepted_at');
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->index(['user_id', 'accepted_at'], 'la_u_at_ix');
        });

        // -----------------------------------------------------------------
        // Categories/reference content
        // -----------------------------------------------------------------
        Schema::create('recipe_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('recipe_categories')->nullOnDelete();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('is_active');
        });

        Schema::create('ingredient_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('ingredient_categories')->nullOnDelete();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('is_active');
        });

        Schema::create('article_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('article_categories')->cascadeOnDelete();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('is_active');
        });

        Schema::create('course_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('course_categories')->cascadeOnDelete();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('is_active');
        });

        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ingredients', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->constrained('ingredient_categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->longText('body')->nullable();
            $table->string('cover')->nullable();
            $table->decimal('protein', 5, 2)->default(0);
            $table->decimal('fat', 5, 2)->default(0);
            $table->decimal('carbs', 5, 2)->default(0);
            $table->decimal('kcal', 8, 2)->nullable();
            $table->decimal('default_grammage', 8, 2)->nullable();
            $table->text('storage_conditions')->nullable();
            $table->text('selection_notes')->nullable();
            $table->boolean('is_visible')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index('slug');
        });

        Schema::create('ingredient_measures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->string('unit');
            $table->decimal('grams', 8, 2);
            $table->timestamps();
            $table->index('ingredient_id');
        });

        Schema::create('ingredient_varieties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('calories')->default(0);
            $table->decimal('protein', 5, 2)->default(0);
            $table->decimal('fat', 5, 2)->default(0);
            $table->decimal('carbs', 5, 2)->default(0);
            $table->timestamps();
            $table->index('ingredient_id');
        });

        Schema::create('ingredient_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
            $table->index('ingredient_id');
        });

        // -----------------------------------------------------------------
        // Recipes
        // -----------------------------------------------------------------
        Schema::create('recipes', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('recipe_categories')->nullOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('cover')->nullable();
            $table->string('video')->nullable();
            $table->unsignedSmallInteger('duration')->default(0);
            $table->unsignedInteger('bookmarks')->default(0);
            $table->unsignedInteger('likes')->default(0);
            $table->string('visibility_status', 24)->default('draft')->index();
            $table->timestamp('publish_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('recipe_ingredient_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->string('title', 255);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['recipe_id', 'sort_order']);
        });

        Schema::create('recipe_ingredients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('group_id')->index()->nullable()->constrained('recipe_ingredient_groups')->nullOnDelete();
            $table->foreignId('reference_item_id')->index()->nullable()->constrained('ingredients')->nullOnDelete();
            $table->string('name');
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->nullable();
            $table->string('section')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index('recipe_id');
        });

        Schema::create('recipe_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->integer('step_number');
            $table->text('description');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->index('recipe_id');
        });

        Schema::create('recipe_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
            $table->index('recipe_id');
        });

        Schema::create('recipe_category', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('recipe_categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['recipe_id', 'category_id']);
        });

        // -----------------------------------------------------------------
        // Articles
        // -----------------------------------------------------------------
        Schema::create('articles', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('body')->nullable();
            $table->string('cover')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('article_categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_visible')->default(false)->index();
            $table->timestamp('publish_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('article_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('article_article_category', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('article_category_id')->constrained('article_categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['article_id', 'article_category_id']);
        });

        Schema::create('article_tag', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('article_tag_id')->constrained('article_tags')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['article_id', 'article_tag_id']);
        });

        // -----------------------------------------------------------------
        // Courses
        // -----------------------------------------------------------------
        Schema::create('courses', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('cover', 127)->nullable();
            $table->string('title', 127)->unique();
            $table->foreignId('category_id')->nullable()->constrained('course_categories')->nullOnDelete();
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->string('summary', 512)->nullable();
            $table->text('pitch')->nullable();
            $table->text('objective')->nullable();
            $table->text('skills')->nullable();
            $table->text('audience')->nullable();
            $table->text('features')->nullable();
            $table->text('prerequisites')->nullable();
            $table->text('preferables')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('publish_status', ['draft', 'scheduled', 'published'])->default('draft');
            $table->timestamp('publish_date', 0)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('course_modules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['course_id', 'sort_order']);
        });

        Schema::create('course_lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_preview')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['module_id', 'sort_order']);
        });

        Schema::create('course_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lesson_id')->constrained('course_lessons')->cascadeOnDelete();
            $table->enum('type', ['video', 'text', 'quiz'])->default('text');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('video_url')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->json('quiz_data')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['lesson_id', 'sort_order']);
        });

        Schema::create('teachers', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('avatar', 127)->nullable();
            $table->string('title', 127)->unique();
            $table->string('motto', 255)->nullable();
            $table->string('about', 512)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('course_teacher', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['course_id', 'teacher_id']);
            $table->index(['course_id', 'sort_order']);
        });

        // -----------------------------------------------------------------
        // Products and commerce
        // -----------------------------------------------------------------
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 28)->unique();
            $table->string('title', 255)->unique();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('file')->nullable();
            $table->string('cover')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_visible')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reference_item_product', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reference_item_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();
            $table->unique(['reference_item_id', 'product_id']);
            $table->index(['reference_item_id', 'sort_order']);
        });

        Schema::create('recipe_product', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();
            $table->unique(['recipe_id', 'product_id']);
        });

        Schema::create('article_product', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();
            $table->unique(['article_id', 'product_id']);
            $table->index(['article_id', 'sort_order']);
        });

        Schema::create('payment_intents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('purchasable_type');
            $table->unsignedBigInteger('purchasable_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('RUB');
            $table->string('status')->default('pending');
            $table->string('provider')->default('yookassa');
            $table->string('provider_payment_id')->nullable();
            $table->text('confirmation_url')->nullable();
            $table->json('payload')->nullable();
            $table->string('idempotence_key', 64)->nullable()->unique();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique('provider_payment_id', 'payment_intents_provider_payment_id_unique');
            $table->index(['purchasable_type', 'purchasable_id']);
            $table->index(['user_id', 'purchasable_type', 'purchasable_id', 'status'], 'payment_intents_reuse_ix');
        });


        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payment_intent_id')->nullable()->unique()->constrained('payment_intents')->nullOnDelete();
            $table->json('items');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->json('payment_details')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('product_purchases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->enum('status', ['active', 'revoked', 'expired'])->default('active');
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
            $table->index('status');
        });

        // -----------------------------------------------------------------
        // Course access/progress
        // -----------------------------------------------------------------
        Schema::create('course_enrollments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
            $table->timestamp('access_expires_at')->nullable();
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
            $table->index('status');
        });

        Schema::create('course_step_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('step_id')->constrained('course_steps')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'step_id']);
            $table->index(['user_id', 'course_id']);
        });

        // -----------------------------------------------------------------
        // Quizzes
        // -----------------------------------------------------------------
        Schema::create('quizzes', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug', 150)->unique();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->text('question_text');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['quiz_id', 'sort_order']);
        });

        Schema::create('quiz_question_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quiz_question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['quiz_question_id', 'sort_order']);
        });

        Schema::create('quizzables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->string('quizzable_type');
            $table->unsignedBigInteger('quizzable_id');
            $table->timestamps();
            $table->index(['quizzable_type', 'quizzable_id']);
            $table->unique(['quiz_id', 'quizzable_type', 'quizzable_id']);
        });

        Schema::create('quiz_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignId('course_enrollment_id')->nullable()->constrained('course_enrollments')->nullOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('course_lessons')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->boolean('passed')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'quiz_id']);
            $table->index(['course_id', 'user_id']);
        });

        Schema::create('quiz_attempt_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('quiz_question_options')->nullOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });

        // -----------------------------------------------------------------
        // Public social/marketing/operations
        // -----------------------------------------------------------------
        Schema::create('content_likes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('likeable_type')->default('');
            $table->unsignedBigInteger('likeable_id')->nullable();
            $table->timestamps();
            $table->index(['likeable_type', 'likeable_id']);
            $table->unique(['user_id', 'likeable_type', 'likeable_id']);
        });

        Schema::create('content_bookmarks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('bookmarkable_type')->default('');
            $table->unsignedBigInteger('bookmarkable_id')->nullable();
            $table->timestamps();
            $table->index(['bookmarkable_type', 'bookmarkable_id']);
            $table->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id']);
        });

        Schema::create('content_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('commentable_type')->default('');
            $table->unsignedBigInteger('commentable_id')->nullable();
            $table->text('body');
            $table->enum('status', ['pending', 'published', 'hidden'])->default('published');
            $table->timestamps();
            $table->index(['commentable_type', 'commentable_id']);
        });

        Schema::create('smm_publications', function (Blueprint $table): void {
            $table->id();
            $table->string('channel', 30);
            $table->string('title')->nullable();
            $table->text('caption');
            $table->string('promotable_type')->nullable();
            $table->unsignedBigInteger('promotable_id')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'ready', 'published', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['promotable_type', 'promotable_id']);
            $table->index(['status', 'scheduled_at']);
        });

        Schema::create('email_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('subject', 255);
            $table->longText('body');
            $table->enum('audience', ['all', 'buyers', 'students', 'no_purchase'])->default('all');
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'failed', 'cancelled'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['status', 'scheduled_at']);
        });

        Schema::create('email_campaign_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->unique(['email_campaign_id', 'user_id']);
            $table->index(['email_campaign_id', 'status']);
        });

        // -----------------------------------------------------------------
        // Payments — database-level duplicate protection is intentional.
        // -----------------------------------------------------------------

        // -----------------------------------------------------------------
        // SEO
        // -----------------------------------------------------------------
        Schema::create('seos', function (Blueprint $table): void {
            $table->id();
            $table->string('seoable_type');
            $table->unsignedBigInteger('seoable_id');
            $table->unique(['seoable_type', 'seoable_id']);
            $table->string('title', 255)->nullable();
            $table->string('description', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverses the schema change.
     * @return void
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'seos',
            'payment_intents',
            'email_campaign_recipients',
            'email_campaigns',
            'smm_publications',
            'content_comments',
            'content_bookmarks',
            'content_likes',
            'quiz_attempt_answers',
            'quiz_attempts',
            'quizzables',
            'quiz_question_options',
            'quiz_questions',
            'quizzes',
            'course_step_progress',
            'course_enrollments',
            'product_purchases',
            'transactions',
            'article_product',
            'recipe_product',
            'reference_item_product',
            'products',
            'course_teacher',
            'teachers',
            'course_steps',
            'course_lessons',
            'course_modules',
            'courses',
            'article_tag',
            'article_article_category',
            'article_tags',
            'articles',
            'recipe_category',
            'recipe_files',
            'recipe_steps',
            'recipe_ingredients',
            'recipe_ingredient_groups',
            'recipes',
            'ingredient_files',
            'ingredient_varieties',
            'ingredient_measures',
            'ingredients',
            'product_categories',
            'course_categories',
            'article_categories',
            'ingredient_categories',
            'recipe_categories',
            'legal_acceptances',
            'group_user',
            'groups',
            'user_messages',
            'user_bans',
            'user_cookie_settings',
            'register_email_tokens',
            'user_accounts',
            'user_email_history',
            'user_email_updates',
            'failed_jobs',
            'job_batches',
            'jobs',
            'cache_locks',
            'cache',
            'sessions',
            'password_reset_tokens',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }
};
