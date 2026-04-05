<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Backend Engineering',
            'Frontend Engineering',
            'Data Engineering',
            'Product Management',
            'DevOps',
            'Quality Assurance',
            'Mobile Engineering',
            'Security Engineering',
            'Platform Engineering',
            'Site Reliability Engineering',
            'AI Engineering',
            'Business Intelligence',
        ]);

        return [
            'parent_id' => null,
            'slug' => Str::slug($name),
            'name' => $name,
            'is_active' => true,
        ];
    }
}
