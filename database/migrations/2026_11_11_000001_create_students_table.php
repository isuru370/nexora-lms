<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('key', 50);
            $table->unsignedBigInteger('value')->default(0);

            $table->unique(['teacher_id', 'key']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('student_code', 20);
            $table->string('school')->nullable();
            $table->string('grade', 30)->nullable();
            $table->string('district', 50)->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['teacher_id', 'student_code']);
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('teacher_counters');
    }
};
