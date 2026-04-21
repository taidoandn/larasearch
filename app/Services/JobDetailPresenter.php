<?php

namespace App\Services;

use App\Models\JobListing;

class JobDetailPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(JobListing $job): array
    {
        $primaryLocation = $job->primaryLocation?->display_name;
        $companySummary = (string) ($job->company?->description ?? '');
        $companyMeta = collect([
            $job->company?->industry,
            $job->company?->company_size,
            $job->company?->country_code,
        ])->filter()->implode(' • ');
        $skillNames = $job->skills->pluck('name')->values()->all();
        $categoryNames = $job->categories->pluck('name')->filter()->values()->all();

        return [
            'id' => $job->getKey(),
            'slug' => $job->slug,
            'title' => $job->title,
            'application_url' => $job->application_url,
            'company' => [
                'name' => $job->company?->name,
                'slug' => $job->company?->slug,
                'logo_url' => $job->company?->logo_url,
                'summary' => $companySummary,
                'meta' => $companyMeta,
                'website' => $job->company?->website_url,
                'industry' => $job->company?->industry,
                'company_size' => $job->company?->company_size,
                'founded_year' => $job->company?->founded_year,
                'is_verified' => (bool) ($job->company?->is_verified ?? false),
            ],
            'primary_location' => $primaryLocation,
            'locations' => $primaryLocation === null ? [] : [$primaryLocation],
            'job_type' => $job->job_type?->value,
            'job_type_label' => $job->job_type?->label(),
            'skills' => $skillNames,
            'benefits' => $this->paragraphLines((string) ($job->benefits ?: '')),
            'salary' => [
                'min' => $job->salary_min,
                'max' => $job->salary_max,
                'currency' => $job->salary_currency,
                'is_visible' => $job->salary_is_visible,
            ],
            'work_model' => $job->work_model?->value,
            'work_model_label' => $job->work_model?->label(),
            'experience_level' => $job->experience_level?->value,
            'experience_level_label' => $job->experience_level?->label(),
            'overview' => (string) ($job->description ?: $job->short_description ?: ''),
            'responsibilities' => $this->paragraphLines((string) ($job->description ?: $job->requirements ?: 'Review the full job details for responsibilities.')),
            'requirements' => [
                [
                    'label' => 'Category',
                    'value' => implode(', ', $categoryNames) ?: 'Generalist',
                ],
                [
                    'label' => 'Core Skills',
                    'value' => implode(', ', $skillNames) ?: 'Not specified',
                ],
                [
                    'label' => 'Experience',
                    'value' => $job->experience_level?->label() ?? 'Not specified',
                ],
                [
                    'label' => 'Application',
                    'value' => $job->application_url ?: 'Apply through Larasearch',
                ],
            ],
            'summary_metrics' => [
                [
                    'label' => 'Compensation',
                    'value' => $this->salarySummary($job),
                ],
                [
                    'label' => 'Experience Required',
                    'value' => $job->experience_level?->label() ?? 'Not specified',
                ],
                [
                    'label' => 'Work Location',
                    'value' => $primaryLocation ?? 'Remote',
                ],
                [
                    'label' => 'Job Type',
                    'value' => $job->job_type?->label() ?? 'Not specified',
                ],
            ],
            'map_label' => $primaryLocation,
            'published_at' => $job->published_at?->toAtomString(),
            'highlight' => [
                'title' => null,
                'description' => null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentRelated(JobListing $job): array
    {
        return [
            'id' => $job->getKey(),
            'slug' => $job->slug,
            'title' => $job->title,
            'application_url' => $job->application_url,
            'company' => [
                'name' => $job->company?->name,
                'slug' => $job->company?->slug,
                'logo_url' => $job->company?->logo_url,
                'website' => $job->company?->website_url,
            ],
            'locations' => $job->primaryLocation === null ? [] : [$job->primaryLocation->display_name],
            'skills' => $job->skills->pluck('name')->values()->all(),
            'salary' => [
                'min' => $job->salary_min,
                'max' => $job->salary_max,
                'currency' => $job->salary_currency,
                'is_visible' => $job->salary_is_visible,
            ],
            'job_type' => $job->job_type?->value,
            'job_type_label' => $job->job_type?->label(),
            'work_model' => $job->work_model?->value,
            'work_model_label' => $job->work_model?->label(),
            'experience_level' => $job->experience_level?->value,
            'experience_level_label' => $job->experience_level?->label(),
            'primary_location' => $job->primaryLocation?->display_name,
            'published_at' => $job->published_at?->toAtomString(),
            'highlight' => [
                'title' => null,
                'description' => null,
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function paragraphLines(string $content): array
    {
        return collect(preg_split('/\n+/', $content) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->take(4)
            ->values()
            ->all();
    }

    protected function salarySummary(JobListing $job): string
    {
        if (! $job->salary_is_visible || ($job->salary_min === null && $job->salary_max === null)) {
            return 'Comp undisclosed';
        }

        $currency = $job->salary_currency ? "{$job->salary_currency} " : '';

        if ($job->salary_min !== null && $job->salary_max !== null) {
            return "{$currency}{$job->salary_min} - {$job->salary_max}";
        }

        return "{$currency}".($job->salary_min ?? $job->salary_max);
    }
}
