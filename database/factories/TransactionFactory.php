<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $berat = $this->faker->randomFloat(1, 1.0, 10.0); // 1.0 - 10.0 kg
        $hargaPerKg = $this->faker->numberBetween(5000, 15000);
        $totalHarga = $berat * $hargaPerKg;
        $diskon = $this->faker->numberBetween(0, 20); // 0-20%
        $hargaAkhir = $totalHarga - ($totalHarga * $diskon / 100);

        return [
            'customer_id' => User::factory()->state(['auth' => 'Customer']),
            'user_id' => User::factory()->state(['auth' => 'Admin']),
            'price_id' => Price::factory(),
            'invoice' => 'LND-' . date('Ymd') . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'berat' => $berat,
            'total_harga' => $totalHarga,
            'diskon' => $diskon,
            'harga_akhir' => $hargaAkhir,
            'catatan' => $this->faker->optional()->sentence(),
            'status_order' => $this->faker->randomElement(['Process', 'Done', 'Delivery']),
            'status_payment' => $this->faker->randomElement(['Pending', 'Success', 'Failed']),
            'tgl_ambil' => $this->faker->optional()->dateTimeBetween('now', '+7 days'),
        ];
    }

    /**
     * Indicate that the transaction is in process.
     */
    public function process(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_order' => 'Process',
            'status_payment' => 'Pending',
        ]);
    }

    /**
     * Indicate that the transaction is done.
     */
    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_order' => 'Done',
            'status_payment' => 'Success',
        ]);
    }

    /**
     * Indicate that the transaction is being delivered.
     */
    public function delivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_order' => 'Delivery',
            'status_payment' => 'Success',
        ]);
    }

    /**
     * Set specific customer.
     */
    public function forCustomer(User $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
        ]);
    }

    /**
     * Set specific price.
     */
    public function withPrice(Price $price): static
    {
        return $this->state(fn (array $attributes) => [
            'price_id' => $price->id,
        ]);
    }
}