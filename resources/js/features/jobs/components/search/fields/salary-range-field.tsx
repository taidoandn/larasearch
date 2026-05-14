import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { sectionLabelClassName } from '@/features/jobs/utils';
import { controlClassName, renderFilterIcon, salaryBounds } from './shared';

export function SalaryRangeField({
    label,
    salaryMin,
    salaryMax,
    onSalaryMinChange,
    onSalaryMaxChange,
}: {
    label: string;
    salaryMin: number | null;
    salaryMax: number | null;
    onSalaryMinChange: (value: string) => void;
    onSalaryMaxChange: (value: string) => void;
}) {
    const normalizedMinimum = clampSalaryValue(salaryMin ?? salaryBounds.min);
    const normalizedMaximum = clampSalaryValue(salaryMax ?? salaryBounds.max);
    const sliderMinimum = Math.min(normalizedMinimum, normalizedMaximum);
    const sliderMaximum = Math.max(normalizedMinimum, normalizedMaximum);
    const sliderStart =
        ((sliderMinimum - salaryBounds.min) / (salaryBounds.max - salaryBounds.min)) * 100;
    const sliderEnd =
        ((sliderMaximum - salaryBounds.min) / (salaryBounds.max - salaryBounds.min)) * 100;

    return (
        <section className="space-y-3">
            <Label
                className={`${sectionLabelClassName} flex items-center gap-2 text-muted-foreground`}
            >
                {renderFilterIcon(label)}
                {label}
            </Label>

            <div className="rounded-2xl bg-card px-4 py-4 shadow-[inset_0_0_0_1px_rgba(25,28,30,0.08)]">
                <div className="relative mb-4 h-5">
                    <div className="absolute top-1/2 h-2 w-full -translate-y-1/2 rounded-full bg-secondary" />
                    <div
                        className="absolute top-1/2 h-2 -translate-y-1/2 rounded-full bg-primary"
                        style={{
                            left: `${sliderStart}%`,
                            width: `${Math.max(sliderEnd - sliderStart, 0)}%`,
                        }}
                    />
                    <input
                        type="range"
                        min={salaryBounds.min}
                        max={salaryBounds.max}
                        step={salaryBounds.step}
                        value={sliderMinimum}
                        onChange={(event) => {
                            const nextValue = Math.min(
                                Number(event.target.value),
                                sliderMaximum - salaryBounds.step,
                            );
                            onSalaryMinChange(String(nextValue));
                        }}
                        className="pointer-events-none absolute inset-0 z-20 w-full appearance-none bg-transparent [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:size-5 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-primary [&::-moz-range-thumb]:bg-white [&::-moz-range-thumb]:shadow-[0_8px_18px_-10px_rgba(0,74,198,0.45)] [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:size-5 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-primary [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:shadow-[0_8px_18px_-10px_rgba(0,74,198,0.45)]"
                    />
                    <input
                        type="range"
                        min={salaryBounds.min}
                        max={salaryBounds.max}
                        step={salaryBounds.step}
                        value={sliderMaximum}
                        onChange={(event) => {
                            const nextValue = Math.max(
                                Number(event.target.value),
                                sliderMinimum + salaryBounds.step,
                            );
                            onSalaryMaxChange(String(nextValue));
                        }}
                        className="pointer-events-none absolute inset-0 z-30 w-full appearance-none bg-transparent [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:size-5 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-primary [&::-moz-range-thumb]:bg-white [&::-moz-range-thumb]:shadow-[0_8px_18px_-10px_rgba(0,74,198,0.45)] [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:size-5 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-primary [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:shadow-[0_8px_18px_-10px_rgba(0,74,198,0.45)]"
                    />
                </div>

                <div className="mb-3 flex justify-between text-[11px] font-semibold text-primary">
                    <span>{formatCompactSalaryValue(sliderMinimum)}</span>
                    <span>{formatCompactSalaryValue(sliderMaximum)}</span>
                </div>

                <div className="grid grid-cols-2 gap-3">
                    <div className="rounded-2xl bg-secondary px-3 py-2">
                        <Input
                            type="number"
                            value={salaryMin ?? ''}
                            onChange={(event) => onSalaryMinChange(event.target.value)}
                            placeholder="Min"
                            className={controlClassName}
                        />
                    </div>
                    <div className="rounded-2xl bg-secondary px-3 py-2">
                        <Input
                            type="number"
                            value={salaryMax ?? ''}
                            onChange={(event) => onSalaryMaxChange(event.target.value)}
                            placeholder="Max"
                            className={controlClassName}
                        />
                    </div>
                </div>
            </div>
        </section>
    );
}

function clampSalaryValue(value: number): number {
    return Math.min(Math.max(value, salaryBounds.min), salaryBounds.max);
}

function formatCompactSalaryValue(value: number): string {
    return `$${Math.round(value / 1000)}k`;
}
