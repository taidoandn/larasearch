<?php

use App\Enums\CompanyStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 160)->unique();
            $table->string('name', 160);
            $table->string('legal_name', 200)->nullable();
            $table->text('description')->nullable();
            $table->string('website_url')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('industry', 120)->nullable();
            $table->string('company_size', 50)->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('status', 30)->default(CompanyStatus::ACTIVE->value);
            $table->timestamps();

            $table->index('name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
