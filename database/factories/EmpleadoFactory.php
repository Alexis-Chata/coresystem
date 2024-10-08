<?php

namespace Database\Factories;

use App\Models\Empleado;
use App\Models\F_tipo_documento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empleado>
 */
class EmpleadoFactory extends Factory
{
    protected $model = Empleado::class;

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
            'f_tipo_documento_id' => F_tipo_documento::inRandomOrder()->first()->id,
            'numero_documento' => $this->faker->unique()->numerify('########'),
            'tipo_empleado' => $this->faker->randomElement(['conductor', 'vendedor']),
            // ... otros campos que sean necesarios
        ];
    }

    public function conductor()
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_empleado' => 'conductor',
                'numero_brevete' => $this->faker->numberBetween(1000000, 9999999),
            ];
        });
    }

    public function vendedor()
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_empleado' => 'vendedor',
                'numero_brevete' => null,
            ];
        });
    }
}
