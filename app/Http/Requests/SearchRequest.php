<?php

namespace App\Http\Requests;

use App\Enums\ExperienceLevel;
use App\Enums\JobType;
use App\Enums\WorkModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:120'],
            'job_type' => ['nullable', Rule::enum(JobType::class)],
            'work_model' => ['nullable', Rule::enum(WorkModel::class)],
            'experience_level' => ['nullable', Rule::enum(ExperienceLevel::class)],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0'],
            'sort' => ['nullable', Rule::in(['best_match', 'newest', 'salary_desc', 'salary_asc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
