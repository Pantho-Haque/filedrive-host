<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Folder;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'folder_id' => Folder::factory(),
            'file_link' => $this->faker->url(),
            'file_size' => $this->faker->numberBetween(10, 2097152),
            'file_name' => $this->faker->word() . '.pdf',
        ];
    }
}
