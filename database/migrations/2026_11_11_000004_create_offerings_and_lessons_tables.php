<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A monthly class or a one-off course.
        Schema::create('offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10); // class | course
            $table->string('title');
            $table->string('slug', 120);
            $table->text('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('subject', 80);
            $table->string('grade', 40)->nullable();
            $table->string('medium', 20)->nullable();
            $table->decimal('fee', 12, 2)->default(0);
            $table->unsignedSmallInteger('access_days')->nullable(); // course: null = lifetime

            // Teacher-controlled access settings (classes)
            $table->string('old_lessons_access', 20)->default('forever');
            $table->boolean('past_lessons_access')->default(false);
            $table->unsignedTinyInteger('grace_days')->default(0);

            $table->boolean('is_published')->default(false);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['teacher_id', 'slug']);
            $table->index(['teacher_id', 'is_published']);
        });

        Schema::create('enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offering_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('active');
            $table->timestamp('enrolled_at')->nullable();
            $table->date('access_until')->nullable(); // course
            $table->timestamps();

            $table->unique(['student_id', 'offering_id']);
            $table->index(['teacher_id', 'offering_id', 'status']);
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offering_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->char('month', 7)->nullable(); // YYYY-MM, classes only
            $table->string('video_provider', 20)->default('youtube');
            $table->string('video_ref', 64);
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('visibility', 20)->default('paid'); // paid | enrolled | public
            $table->date('available_until')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['offering_id', 'month', 'sort']);
        });

        Schema::create('lesson_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamp('first_viewed_at');
            $table->timestamp('last_viewed_at');
            $table->unsignedInteger('views')->default(1);
            $table->unsignedInteger('progress_seconds')->default(0);
            $table->timestamp('completed_at')->nullable();

            $table->unique(['lesson_id', 'student_id']);
            $table->index(['student_id', 'last_viewed_at']);
        });

        // "This month is covered" for a class enrolment.
        Schema::create('payment_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrolment_id')->constrained()->cascadeOnDelete();
            $table->char('month', 7);
            $table->string('source', 20); // payment | manual | free
            $table->unsignedBigInteger('payment_id')->nullable(); // FK added with payments (Phase 2)
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['enrolment_id', 'month']);
        });

        // Teacher decisions per student: temporary access or a block.
        Schema::create('access_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrolment_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10); // grant | block
            $table->date('until')->nullable(); // null = open-ended
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['enrolment_id', 'type', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_overrides');
        Schema::dropIfExists('payment_periods');
        Schema::dropIfExists('lesson_views');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('enrolments');
        Schema::dropIfExists('offerings');
    }
};
