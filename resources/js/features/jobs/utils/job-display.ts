import type { JobDetailItem, JobResultItem } from '@/features/jobs/types';
import {
    formatDisplayDate,
    formatExperienceLevelLabel,
    formatJobTypeLabel,
    formatSalaryRange,
    formatWorkModelLabel,
} from './formatters';

type JobWithCompanyAndLocations = Pick<
    JobDetailItem | JobResultItem,
    'application_url' | 'company' | 'locations' | 'primary_location'
>;

export type JobDisplayChipType =
    | 'salary'
    | 'work-model'
    | 'experience'
    | 'job-type'
    | 'published-at';

export type JobDisplayChip = {
    label: string;
    type: JobDisplayChipType;
    emphasis?: 'primary';
};

export function jobCompanyName(job: Pick<JobDetailItem | JobResultItem, 'company'>): string {
    return job.company.name ?? 'Unknown company';
}

export function jobPrimaryLocation(
    job: Pick<JobDetailItem | JobResultItem, 'locations' | 'primary_location'>,
    fallback = 'Remote',
): string {
    return job.primary_location ?? job.locations[0] ?? fallback;
}

export function jobApplyUrl(job: JobWithCompanyAndLocations): string | null {
    return job.application_url ?? job.company.website;
}

export function jobPositionMeta(job: JobWithCompanyAndLocations): string {
    return [jobCompanyName(job), jobPrimaryLocation(job)].filter(Boolean).join(' • ');
}

export function buildJobDisplayChips(
    job: Pick<
        JobDetailItem | JobResultItem,
        | 'experience_level'
        | 'experience_level_label'
        | 'job_type'
        | 'job_type_label'
        | 'published_at'
        | 'salary'
        | 'work_model'
        | 'work_model_label'
    >,
    fallbacks: {
        workModel?: string;
        experience?: string;
        jobType?: string;
    } = {},
): JobDisplayChip[] {
    return [
        {
            label: formatSalaryRange(job.salary),
            type: 'salary',
            emphasis: 'primary',
        },
        {
            label:
                job.work_model_label ??
                formatWorkModelLabel(job.work_model, fallbacks.workModel ?? 'Unknown model'),
            type: 'work-model',
        },
        {
            label:
                job.experience_level_label ??
                formatExperienceLevelLabel(job.experience_level, fallbacks.experience ?? 'Unknown'),
            type: 'experience',
        },
        {
            label:
                job.job_type_label ??
                formatJobTypeLabel(job.job_type, fallbacks.jobType ?? 'Not specified'),
            type: 'job-type',
        },
        {
            label: formatDisplayDate(job.published_at),
            type: 'published-at',
        },
    ];
}

export function prioritizeSkills(skills: string[], activeSkills: string[]): string[] {
    const matchedSkills: string[] = [];
    const unmatchedSkills: string[] = [];

    for (const skill of skills) {
        if (isMatchingSkill(skill, activeSkills)) {
            matchedSkills.push(skill);
        } else {
            unmatchedSkills.push(skill);
        }
    }

    return [...matchedSkills, ...unmatchedSkills];
}

export function isMatchingSkill(skill: string, activeSkills: string[]): boolean {
    if (activeSkills.length === 0) {
        return false;
    }

    const normalizedSkill = normalizeSkillValue(skill);

    return activeSkills.some((activeSkill) => normalizeSkillValue(activeSkill) === normalizedSkill);
}

export function normalizeSkillValue(skill: string): string {
    return skill
        .trim()
        .toLowerCase()
        .replaceAll(/[^a-z0-9]+/g, '-')
        .replaceAll(/^-+|-+$/g, '');
}
