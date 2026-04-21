import { Cloud, Rocket, ShieldCheck, Workflow } from 'lucide-react';
import type { JobDetailItem } from '@/features/jobs/types';
import { DetailSection } from './detail-section';

const responsibilityIcons = [Rocket, Cloud, ShieldCheck, Workflow];

export function DetailInfo({ job }: { job: JobDetailItem }) {
    const overviewParagraphs = splitParagraphs(job.overview);
    const insightCards = job.summary_metrics.slice(0, 3);
    const featureBlocks = buildFeatureBlocks(job);
    const primarySkills = job.skills.slice(0, 4);
    const secondarySkills = job.skills.slice(4);

    return (
        <article className="flex flex-col gap-12">
            <DetailSection title="Job Overview">
                <div className="flex flex-col gap-8">
                    <div className="flex max-w-4xl flex-col gap-4 text-lg leading-8 text-slate-600">
                        {overviewParagraphs.map((paragraph) => (
                            <p key={paragraph}>{paragraph}</p>
                        ))}
                    </div>

                    <div className="grid gap-4 sm:grid-cols-3">
                        {insightCards.map((metric) => (
                            <div
                                key={metric.label}
                                className="rounded-3xl bg-slate-50 px-6 py-6 shadow-[inset_0_0_0_1px_rgba(148,163,184,0.14)]"
                            >
                                <p className="text-[10px] font-bold tracking-[0.24em] text-slate-500 uppercase">
                                    {metric.label}
                                </p>
                                <p className="mt-3 text-xl font-extrabold tracking-tight text-slate-950">
                                    {metric.value}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </DetailSection>

            <section className="rounded-[1.75rem] bg-slate-900 px-8 py-8 text-white shadow-[0_20px_40px_-32px_rgba(15,23,42,0.9)] sm:px-10 sm:py-10">
                <h3 className="font-display text-2xl font-extrabold tracking-tight">
                    What you'll work on
                </h3>
                <div className="mt-8 grid gap-8 md:grid-cols-2">
                    {featureBlocks.map((block) => (
                        <div key={block.title} className="flex flex-col gap-3">
                            <h4 className="text-base font-bold text-blue-300">{block.title}</h4>
                            <p className="text-sm leading-7 text-slate-300">{block.description}</p>
                        </div>
                    ))}
                </div>
            </section>

            <DetailSection title="Key Responsibilities">
                <ul className="flex flex-col gap-4">
                    {job.responsibilities.map((item, index) => {
                        const Icon = responsibilityIcons[index % responsibilityIcons.length];

                        return (
                            <li
                                key={item}
                                className="flex gap-5 rounded-3xl bg-white px-5 py-5 shadow-[inset_0_0_0_1px_rgba(148,163,184,0.16)] transition-colors hover:shadow-[inset_0_0_0_1px_rgba(37,99,235,0.22)]"
                            >
                                <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/5 text-primary">
                                    <Icon className="size-5" />
                                </div>
                                <div className="flex flex-col gap-1">
                                    <p className="text-base font-semibold text-slate-950">
                                        {responsibilityTitle(item, index)}
                                    </p>
                                    <p className="text-sm leading-7 text-slate-600">{item}</p>
                                </div>
                            </li>
                        );
                    })}
                </ul>
            </DetailSection>

            <section className="rounded-[1.75rem] bg-white px-8 py-8 shadow-[0_16px_36px_-30px_rgba(0,74,198,0.14)] sm:px-10 sm:py-10">
                <h3 className="font-display text-2xl font-extrabold tracking-tight text-slate-950">
                    Requirements
                </h3>
                <div className="mt-8 grid gap-8 md:grid-cols-2">
                    {job.requirements.map((requirement) => (
                        <div
                            key={requirement.label}
                            className="flex flex-col gap-2 rounded-3xl bg-slate-50 px-6 py-6"
                        >
                            <p className="text-[10px] font-bold tracking-[0.24em] text-primary uppercase">
                                {requirement.label}
                            </p>
                            <p className="text-base font-bold text-slate-950">
                                {requirement.value}
                            </p>
                            <p className="text-sm leading-7 text-slate-600">
                                {requirementDescription(requirement.label, requirement.value)}
                            </p>
                        </div>
                    ))}
                </div>
            </section>

            <DetailSection title="Technical Skills">
                <div className="flex flex-col gap-8">
                    <div className="flex flex-col gap-4">
                        <p className="text-sm font-bold text-slate-500">Core Proficiency</p>
                        <div className="flex flex-wrap gap-3">
                            {primarySkills.map((skill) => (
                                <span
                                    key={skill}
                                    className="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white shadow-[0_16px_28px_-20px_rgba(0,74,198,0.45)]"
                                >
                                    {skill}
                                </span>
                            ))}
                        </div>
                    </div>

                    {secondarySkills.length > 0 ? (
                        <div className="flex flex-col gap-4">
                            <p className="text-sm font-bold text-slate-500">Bonus &amp; Related</p>
                            <div className="flex flex-wrap gap-3">
                                {secondarySkills.map((skill) => (
                                    <span
                                        key={skill}
                                        className="rounded-xl border border-slate-200 bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700"
                                    >
                                        {skill}
                                    </span>
                                ))}
                            </div>
                        </div>
                    ) : null}
                </div>
            </DetailSection>
        </article>
    );
}

function splitParagraphs(content: string): string[] {
    return content
        .split(/\n+/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean)
        .slice(0, 2);
}

function buildFeatureBlocks(job: JobDetailItem): Array<{ title: string; description: string }> {
    const firstBenefit = job.benefits[0] ?? job.overview;
    const secondBenefit =
        job.benefits[1] ?? job.company.summary ?? job.requirements[0]?.value ?? job.overview;

    return [
        {
            title: 'Scaling Infrastructure',
            description: firstBenefit,
        },
        {
            title: 'Developer Experience',
            description: secondBenefit,
        },
    ];
}

function responsibilityTitle(item: string, index: number): string {
    const cannedTitles = [
        'Reliability Systems',
        'Cloud Management',
        'Observability',
        'Delivery Workflows',
    ];

    return cannedTitles[index] ?? item.split(' ').slice(0, 3).join(' ');
}

function requirementDescription(label: string, value: string): string {
    return `${label} context for this role: ${value}.`;
}
