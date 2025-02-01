<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empleado;
use App\Models\FTipoDocumento;
use App\Models\FSede;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        $tipoDocumento = FTipoDocumento::inRandomOrder()->first();

        if (!$tipoDocumento) {
            throw new \Exception('No hay tipos de documento en la base de datos. Asegúrate de ejecutar FTipoDocumentoSeeder primero.');
        }

        $sede = FSede::first();
        if (!$sede) {
            throw new \Exception('No hay sedes en la base de datos. Asegúrate de ejecutar FSedeSeeder primero.');
        }

        // Obtener todos los vehículos disponibles
        $vehiculos = Vehiculo::all();

        if ($vehiculos->isEmpty()) {
            throw new \Exception('No hay vehículos en la base de datos. Asegúrate de ejecutar VehiculoSeeder primero.');
        }

        $empleado = new Empleado();
        $empleado->name = 'Edwin';
        $empleado->direccion = 'Av. Arequipa 123, Lima';
        $empleado->celular = '987654321';
        $empleado->f_tipo_documento_id = 1;
        $empleado->numero_documento = '45678912';
        $empleado->tipo_empleado = 'conductor';
        $empleado->numero_brevete = '1234567';
        $empleado->f_sede_id = $sede->id;
        $empleado->vehiculo_id = $vehiculos->shift()->id;  // Asigna un vehículo
        $empleado->save();

        $empleado = new Empleado();
        $empleado->name = 'Manuel';
        $empleado->direccion = 'Jr. Cusco 456, Lima';
        $empleado->celular = '987654322';
        $empleado->f_tipo_documento_id = 1;
        $empleado->numero_documento = '45678913';
        $empleado->tipo_empleado = 'conductor';
        $empleado->numero_brevete = '1234567';
        $empleado->f_sede_id = $sede->id;
        $empleado->vehiculo_id = $vehiculos->shift()->id;  // Asigna un vehículo
        $empleado->save();

        $empleado = new Empleado();
        $empleado->name = 'Hector';
        $empleado->direccion = 'Av. La Marina 789, Lima';
        $empleado->celular = '987654323';
        $empleado->f_tipo_documento_id = 1;
        $empleado->numero_documento = '45678914';
        $empleado->tipo_empleado = 'conductor';
        $empleado->numero_brevete = '2345678';
        $empleado->f_sede_id = $sede->id;
        $empleado->vehiculo_id = $vehiculos->shift()->id;  // Asigna un vehículo
        $empleado->save();

        $empleado = new Empleado();
        $empleado->name = 'Cristian';
        $empleado->direccion = 'Av. Brasil 321, Lima';
        $empleado->celular = '987654324';
        $empleado->f_tipo_documento_id = 1;
        $empleado->numero_documento = '45678915';
        $empleado->tipo_empleado = 'conductor';
        $empleado->numero_brevete = '1234567';
        $empleado->f_sede_id = $sede->id;
        $empleado->vehiculo_id = $vehiculos->shift()->id;  // Asigna un vehículo
        $empleado->save();

        $empleado = new Empleado();
        $empleado->name = 'Gustavo';
        $empleado->direccion = 'Jr. Huancayo 654, Lima';
        $empleado->celular = '987654325';
        $empleado->f_tipo_documento_id = 1;
        $empleado->numero_documento = '45678916';
        $empleado->tipo_empleado = 'conductor';
        $empleado->numero_brevete = '3456789';
        $empleado->f_sede_id = $sede->id;
        $empleado->vehiculo_id = $vehiculos->shift()->id;  // Asigna un vehículo
        $empleado->save();

        $empleado = new Empleado();
        $empleado->name = 'Jorge';
        $empleado->direccion = 'Av. Arequipa 123, Lima';
        $empleado->celular = '987654321';
        $empleado->f_tipo_documento_id = 1;
        $empleado->numero_documento = '45678912';
        $empleado->tipo_empleado = 'conductor';
        $empleado->numero_brevete = '1234567';
        $empleado->f_sede_id = $sede->id;
        $empleado->vehiculo_id = $vehiculos->shift()->id;  // Asigna un vehículo
        $empleado->save();

        $empleado = new Empleado();
        $empleado->name = 'Ronaldo';
        $empleado->direccion = 'Jr. Cusco 456, Lima';
        $empleado->celular = '987654322';
        $empleado->f_tipo_documento_id = 1;
        $empleado->numero_documento = '45678913';
        $empleado->tipo_empleado = 'conductor';
        $empleado->numero_brevete = '1234567';
        $empleado->f_sede_id = $sede->id;
        $empleado->vehiculo_id = $vehiculos->shift()->id;  // Asigna un vehículo
        $empleado->save();

        $empleado = new Empleado();
        $empleado->name = 'Reynoso Chincha';
        $empleado->direccion = 'Av. La Marina 789, Lima';
        $empleado->celular = '987654323';
        $empleado->f_tipo_documento_id = 1;
        $empleado->numero_documento = '45678914';
        $empleado->tipo_empleado = 'conductor';
        $empleado->numero_brevete = '2345678';
        $empleado->f_sede_id = $sede->id;
        $empleado->vehiculo_id = $vehiculos->shift()->id;  // Asigna un vehículo
        $empleado->save();

        $empleado = new Empleado();
        $empleado->name = 'Reynoso Mala';
        $empleado->direccion = 'Av. Brasil 321, Lima';
        $empleado->celular = '987654324';
        $empleado->f_tipo_documento_id = 1;
        $empleado->numero_documento = '45678915';
        $empleado->tipo_empleado = 'conductor';
        $empleado->numero_brevete = '1234567';
        $empleado->f_sede_id = $sede->id;
        $empleado->vehiculo_id = $vehiculos->shift()->id;  // Asigna un vehículo
        $empleado->save();

        DB::insert("insert  into `empleados`
        (`id`,`codigo`,`name`,`direccion`,`celular`,`f_tipo_documento_id`,`numero_documento`,`tipo_empleado`,`numero_brevete`,`f_sede_id`,`vehiculo_id`) values
        (10,NULL,'ALEXIS CHATA','MARISCAL CACERES','987654321',1,'99999999','vendedor',NULL,1,NULL),
        (11,NULL,'CHARLIE JARA','JICAMARCA','987654321',1,'99999999','vendedor',NULL,1,NULL),
        (12,NULL,'ROSELVI SEGURA','MZ 62 LT 24 CANTOGRANDE','987654322',1,'72728861','vendedor',NULL,1,NULL),
        (13,NULL,'PAUL CASTELO','JICAMARCA','987654323',1,'99999999','vendedor',NULL,1,NULL),
        (14,NULL,'RONALD AQUIZE','JICAMARCA','987654324',1,'99999999','vendedor',NULL,1,NULL),
        (15,NULL,'HUGO ROSALES','JICAMARCA','987654325',1,'99999999','vendedor',NULL,1,NULL),
        (16,NULL,'DIXON QUISPE','MZ A LT 22 JUAN PABLO II','999999999',1,'46880607','vendedor',NULL,1,NULL),
        (17,NULL,'ANGEL LOPEZ','JICAMARCA','999999999',1,'99999999','vendedor',NULL,1,NULL),
        (18,NULL,'PAUL LIZANA','JICAMARCA','999999999',1,'61833310','vendedor',NULL,1,NULL),
        (19,NULL,'JARA HUGO','JICAMARCA','999999999',1,'74526983','vendedor',NULL,1,NULL),
        (20,NULL,'LEAO DO SANTOS','JICAMARCA','999999999',1,'99999999','vendedor',NULL,1,NULL),
        (21,NULL,'ANGELA RIVAS MASKO','JICAMARCA','999999999',1,'99999999','vendedor',NULL,1,NULL),
        (22,NULL,'ALEXANDER GOMEZ','JICAMARCA','999999999',1,'99999999','vendedor',NULL,1,NULL),
        (23,NULL,'HUGO MAYO CAMPOS','JICAMARCA','999999999',1,'45608264','vendedor',NULL,1,NULL),
        (24,NULL,'WILLIAMS J.ZAMBRANO','JICAMARCA','999999999',2,'4680148','vendedor',NULL,1,NULL),
        (25,NULL,'ARMANDO HUAMANI','JICAMARCA','999999999',1,'47310134','vendedor',NULL,1,NULL),
        (26,NULL,'JUAN MACAVILCA','JICAMARCA','999999999',1,'99999999','vendedor',NULL,1,NULL),
        (27,NULL,'LUIS SANCHEZ','JICAMARCA','999999999',1,'99999999','vendedor',NULL,1,NULL),
        (28,NULL,'ROLANDO ALGUIAR','JICAMARCA','999999999',1,'99999999','vendedor',NULL,1,NULL),
        (29,NULL,'NILO QUISPE','JICAMARCA','999999999',1,'76770386','vendedor',NULL,1,NULL),
        (30,NULL,'REYNOSO JUSTO','JICAMARCA','999999999',1,'99999999','vendedor',NULL,1,NULL),
        (31,NULL,'GLADYS QUISPE','JICAMARCA','999999999',1,'76226786','vendedor',NULL,1,NULL),
        (32,NULL,'JHON BENITES','JICAMARCA','999999999',1,'76464963','vendedor',NULL,1,NULL);
        ");


        // ... Continúa con los demás empleados hasta completar los 10
    }
}
