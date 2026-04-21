<?php

namespace App\Http\Requests;

use App\Enums\ExperienceLevel;
use App\Enums\JobType;
use App\Enums\WorkModel;
use App\Services\JobSearchFilters;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'location' => $this->normalizeListInput($this->input('location')),
            'category' => $this->normalizeListInput($this->input('category')),
            'job_type' => $this->normalizeListInput($this->input('job_type')),
            'work_model' => $this->normalizeListInput($this->input('work_model')),
            'experience_level' => $this->normalizeListInput($this->input('experience_level')),
        ]);
    }

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
            'location' => ['nullable', 'array', 'list'],
            'location.*' => ['string', 'max:120'],
            'category' => ['nullable', 'array', 'list'],
            'category.*' => ['string', 'max:120'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:120'],
            'job_type' => ['nullable', 'array', 'list'],
            'job_type.*' => ['string', Rule::enum(JobType::class)],
            'work_model' => ['nullable', 'array'],
            'work_model.*' => ['string', Rule::enum(WorkModel::class)],
            'experience_level' => ['nullable', 'array'],
            'experience_level.*' => ['string', Rule::enum(ExperienceLevel::class)],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0'],
            'sort' => ['nullable', Rule::in(JobSearchFilters::SORT_OPTIONS)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * @return array<int, mixed>|null
     */
    protected function normalizeListInput(mixed $value): ?array
    {
        if (blank($value)) {
            return null;
        }

        return Arr::wrap($value);
    }
}
