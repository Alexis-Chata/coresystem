<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Ruta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'razon_social' => $this->faker->company . ' S.A.C.',
            'direccion' => $this->faker->address,
            'numero_documento' => $this->faker->numerify('20#########'),
            'celular' => $this->faker->numerify('9########'),
        ];
    }
}
