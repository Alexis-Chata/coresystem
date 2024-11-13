<?php

namespace Database\Factories;

use App\Models\Empleado;
use App\Models\FTipoDocumento;
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
        $tipoEmpleado = $this->faker->randomElement(['conductor', 'vendedor']);
        $casos = [];
        if ($tipoEmpleado === 'conductor') {
            $casos= $this->conductor();
        }
        if ($tipoEmpleado === 'vendedor') {
            $casos= $this->vendedor();
        }
        return [
            'name' => $this->faker->name,
            'direccion' => $this->faker->address,
            'celular' => $this->faker->phoneNumber,
            'f_tipo_documento_id' => FTipoDocumento::inRandomOrder()->first()->id,
            'numero_documento' => $this->faker->unique()->numerify('########'),
            // ... otros campos que sean necesarios
        ]+$casos;
    }

    public function conductor()
    {
        return [
            'tipo_empleado' => 'conductor',
            'numero_brevete' => $this->faker->numberBetween(1000000, 9999999),
        ];
    }

    public function vendedor()
    {
        return [
            'tipo_empleado' => 'vendedor',
            'numero_brevete' => null,
        ];
    }
}
