<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('primary_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('slug', 180)->unique();
            $table->string('title', 180);
            $table->string('normalized_title', 180)->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description');
            $table->longText('requirements')->nullable();
            $table->longText('benefits')->nullable();
            $table->string('job_type', 30);
            $table->string('work_model', 20);
            $table->string('experience_level', 20);
            $table->integer('salary_min')->nullable();
            $table->integer('salary_max')->nullable();
            $table->char('salary_currency', 3)->nullable();
            $table->boolean('salary_is_visible')->default(true);
            $table->string('application_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('source_type', 30)->default('direct');
            $table->timestamps();

            $table->index('company_id');
            $table->index('primary_location_id');
            $table->index('job_type');
            $table->index('work_model');
            $table->index('experience_level');
            $table->index(['is_active', 'published_at']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
