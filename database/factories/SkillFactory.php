<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Laravel',
            'PHP',
            'MySQL',
            'Redis',
            'React',
            'TypeScript',
            'Docker',
            'Kubernetes',
            'Elasticsearch',
            'AWS',
            'Go',
            'Python',
        ]);

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'category' => fake()->randomElement(['backend', 'frontend', 'devops', 'data']),
            'aliases_json' => null,
        ];
    }
}
