<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conductor>
 */
class ConductorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'direccion' => $this->faker->address,
            'celular' => $this->faker->phoneNumber,
            //'f_tipo_documento_id' => $this->faker->numberBetween(1, 3),
            'numero_documento' => $this->faker->numberBetween(10000000, 99999999),
            'numero_brevete' => $this->faker->numberBetween(10000000, 99999999),
        ];
    }
}
