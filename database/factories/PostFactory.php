<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_at' => now(),
            'header' => fake()->text(30),
            'view_count' => rand(1, 99),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
        ];
    }

    public function text(): Factory
    {
        return $this->state(function () {
            return [
                'text' => fake()->text()
            ];
        });
    }

    public function video(): Factory
    {
        return $this->state(function () {
            return [
                'video' => fake()->url()
            ];
        });
    }

    public function link(): Factory
    {
        return $this->state(function () {
            return [
                'link' => Str::of(fake()->url())->replace('http:', 'https:')->toString(),
                'link_preview' => fake()->imageUrl()
            ];
        });
    }

    public function quote(): Factory
    {
        return $this->state(function () {
            return [
                'quote' => fake()->text(100),
                'quote_author' => fake()->text(30),
            ];
        });
    }

    public function photo_as_file(): Factory
    {
        return $this->state(function () {
            return [
                'photo_file' => fake()->image(),
            ];
        });
    }

    public function photo_as_url(): Factory
    {
        return $this->state(function () {
            $imageUrl = Str::of(fake()->imageUrl());
            $trim = $imageUrl->after('.png');
            return [
                'photo_url' => $imageUrl->remove($trim)->toString(),
            ];
        });
    }
}
