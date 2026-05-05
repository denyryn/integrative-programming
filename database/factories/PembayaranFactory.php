<?php

namespace Database\Factories;

use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pembayaran>
 */
class PembayaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "peminjaman_id" => \App\Models\Peminjaman::factory(),
            "jumlah_pembayaran" => $this->faker->randomNumber(5),
            "tanggal_pembayaran" => $this->faker->date(),
        ];
    }
}
