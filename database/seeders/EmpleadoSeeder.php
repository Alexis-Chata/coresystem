<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empleado;
use App\Models\F_tipo_documento;
use App\Models\Empresa;
use App\Models\Vehiculo;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        $tipoDocumento = F_tipo_documento::inRandomOrder()->first();
        
        if (!$tipoDocumento) {
            throw new \Exception('No hay tipos de documento en la base de datos. Asegúrate de ejecutar FTipoDocumentoSeeder primero.');
        }

        $empresa = Empresa::first();
        if (!$empresa) {
            throw new \Exception('No hay empresas en la base de datos. Asegúrate de ejecutar EmpresaSeeder primero.');
        }

        // Obtener todos los vehículos disponibles
        $vehiculos = Vehiculo::all();

        if ($vehiculos->isEmpty()) {
            throw new \Exception('No hay vehículos en la base de datos. Asegúrate de ejecutar VehiculoSeeder primero.');
        }

        $empleado = new Empleado();
        $empleado->name = 'Juan Pérez';
        $empleado->direccion = 'Av. Arequipa 123, Lima';
        $empleado->celular = '987654321';
        $empleado->f_tipo_documento_id = $tipoDocumento->id;
        $empleado->numero_documento = '45678912';
        $empleado->tipo_empleado = 'conductor';
        $empleado->numero_brevete = '1234567';
        $empleado->empresa_id = $empresa->id;
        $empleado->vehiculo_id = $vehiculos->shift()->id;  // Asigna un vehículo
        $empleado->save();

        $empleado = new Empleado();
        $empleado->name = 'María García';
        $empleado->direccion = 'Jr. Cusco 456, Lima';
        $empleado->celular = '987654322';
        $empleado->f_tipo_documento_id = $tipoDocumento->id;
        $empleado->numero_documento = '45678913';
        $empleado->tipo_empleado = 'vendedor';
        $empleado->empresa_id = $empresa->id;
        $empleado->save();

        $empleado = new Empleado();
        $empleado->name = 'Carlos Rodríguez';
        $empleado->direccion = 'Av. La Marina 789, Lima';
        $empleado->celular = '987654323';
        $empleado->f_tipo_documento_id = $tipoDocumento->id;
        $empleado->numero_documento = '45678914';
        $empleado->tipo_empleado = 'conductor';
        $empleado->numero_brevete = '2345678';
        $empleado->empresa_id = $empresa->id;
        $empleado->vehiculo_id = $vehiculos->shift()->id;  // Asigna un vehículo
        $empleado->save();

        $empleado = new Empleado();
        $empleado->name = 'Ana López';
        $empleado->direccion = 'Av. Brasil 321, Lima';
        $empleado->celular = '987654324';
        $empleado->f_tipo_documento_id = $tipoDocumento->id;
        $empleado->numero_documento = '45678915';
        $empleado->tipo_empleado = 'vendedor';
        $empleado->empresa_id = $empresa->id;
        $empleado->save();

        $empleado = new Empleado();
        $empleado->name = 'Luis Torres';
        $empleado->direccion = 'Jr. Huancayo 654, Lima';
        $empleado->celular = '987654325';
        $empleado->f_tipo_documento_id = $tipoDocumento->id;
        $empleado->numero_documento = '45678916';
        $empleado->tipo_empleado = 'conductor';
        $empleado->numero_brevete = '3456789';
        $empleado->empresa_id = $empresa->id;
        $empleado->vehiculo_id = $vehiculos->shift()->id;
        $empleado->save();

        // ... Continúa con los demás empleados hasta completar los 10
    }
}
