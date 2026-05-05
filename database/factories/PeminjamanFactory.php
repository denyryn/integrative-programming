<?php

namespace Database\Factories;

use App\Models\Peminjaman;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Peminjaman>
 */
class PeminjamanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "anggota_id" => \App\Models\Anggota::factory(),
            "tanggal_pinjam" => $this->faker->date(),
            "jumlah_pinjam" => $this->faker->randomNumber(5),
            "status" => $this->faker->randomElement(['pending', 'selesai']),
        ];
    }
}
