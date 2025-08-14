<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Spatie\Permission\Models\Role;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('csv:export-compress 7')->dailyAt('23:00')->onFailure(function () {
    Log::channel('respuesta_envio_sftp')->error("CSV Export 7: Falló la ejecución del comando.");
});

Schedule::command('csv:export-compress 10')->dailyAt('23:00')->onFailure(function () {
    Log::channel('respuesta_envio_sftp')->error("CSV Export 10: Falló la ejecución del comando.");
});

Schedule::call(function () {
    $role = Role::findByName('vendedor', 'web');

    if (!$role->hasPermissionTo('create pedido')) {
        $role->givePermissionTo('create pedido');
        info('Permiso "create pedido" asignado al rol vendedor.');
    } else {
        info('El rol vendedor ya tiene el permiso.');
    }
})->dailyAt('05:00');

Schedule::command('zip:storage-app')->dailyAt('23:00');
// Limpiar backups viejos después
Schedule::command('backup:clean')->dailyAt('23:30');
// Crear backup diario
Schedule::command('backup:run --only-db')->dailyAt('00:10');
