<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listing_skill', function (Blueprint $table): void {
            $table->foreignId('job_listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('weight')->default(1);
            $table->timestamps();

            $table->primary(['job_listing_id', 'skill_id']);
            $table->index('skill_id');
            $table->index(['job_listing_id', 'weight']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listing_skill');
    }
};
