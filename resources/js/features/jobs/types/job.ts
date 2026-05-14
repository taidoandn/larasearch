export type JobFacetItem = {
    value: string;
    label?: string | null;
    count: number;
};

export type JobSuggestionItem = {
    label: string;
    type: string;
};

export type MultiSelectOption = {
    label: string;
    value: string;
    count?: number;
    disabled?: boolean;
};

export type JobCompanySummary = {
    name: string | null;
    slug: string | null;
    logo_url: string | null;
    website: string | null;
};

export type JobSalary = {
    min: number | null;
    max: number | null;
    currency: string | null;
    is_visible: boolean;
};

export type JobHighlight = {
    title: string | null;
    description: string | null;
};

export type JobResultItem = {
    id: number;
    slug: string;
    title: string;
    description: string;
    application_url: string | null;
    company: JobCompanySummary;
    primary_location: string | null;
    locations: string[];
    skills: string[];
    salary: JobSalary;
    job_type: string | null;
    job_type_label?: string | null;
    work_model: string | null;
    work_model_label?: string | null;
    experience_level: string | null;
    experience_level_label?: string | null;
    published_at: string | null;
    highlight: JobHighlight;
};

export type JobDetailMetric = {
    label: string;
    value: string;
};

export type JobDetailCompany = {
    name: string;
    slug: string;
    logo_url: string | null;
    summary: string;
    meta: string;
    website: string | null;
    industry: string | null;
    company_size: string | null;
    founded_year: number | null;
    is_verified: boolean;
};

export type JobDetailItem = {
    id: number;
    slug: string;
    title: string;
    application_url: string | null;
    company: JobDetailCompany;
    locations: string[];
    primary_location: string | null;
    work_model: string | null;
    work_model_label?: string | null;
    experience_level: string | null;
    experience_level_label?: string | null;
    job_type: string | null;
    job_type_label?: string | null;
    benefits: string[];
    salary: JobSalary;
    overview: string;
    responsibilities: string[];
    requirements: JobDetailMetric[];
    summary_metrics: JobDetailMetric[];
    skills: string[];
    map_label: string | null;
    published_at: string | null;
    highlight: JobHighlight;
};
