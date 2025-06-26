<?php

namespace Database\Factories;

use App\Models\Price;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Price>
 */
class PriceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Price::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jenisList = [
            'Cuci Kering',
            'Cuci Setrika', 
            'Cuci Lipat',
            'Dry Clean',
            'Express',
            'Premium',
            'Ekonomis'
        ];

        return [
            'user_id' => User::factory(),
            'jenis' => $this->faker->randomElement($jenisList),
            'kg' => '1 kg',
            'harga' => $this->faker->randomFloat(2, 3000, 20000), // Rp 3,000 - Rp 20,000
            'hari' => $this->faker->numberBetween(1, 5), // 1-5 hari
            'status' => $this->faker->randomElement(['Active', 'Inactive']),
        ];
    }

    /**
     * Indicate that the price is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Active',
        ]);
    }

    /**
     * Indicate that the price is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Inactive',
        ]);
    }

    /**
     * Set specific service type.
     */
    public function jenis(string $jenis): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis' => $jenis,
        ]);
    }
}