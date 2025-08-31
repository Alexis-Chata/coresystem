<?php

use Carbon\Carbon;
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
})->skip(function () {
    // 0 = domingo, 1 = lunes, ..., 6 = sábado
    return Carbon::now()->isSunday();
});

Schedule::command('csv:export-compress 10')->dailyAt('23:00')->onFailure(function () {
    Log::channel('respuesta_envio_sftp')->error("CSV Export 10: Falló la ejecución del comando.");
})->skip(function () {
    // 0 = domingo, 1 = lunes, ..., 6 = sábado
    return Carbon::now()->isSunday();
});

Schedule::call(function () {
    $role = Role::findByName('vendedor', 'web');

    if (!$role->hasPermissionTo('create pedido')) {
        $role->givePermissionTo('create pedido');
        info('Permiso "create pedido" asignado al rol vendedor.');
    } else {
        info('El rol vendedor ya tiene el permiso.');
    }
})->dailyAt('05:00')->skip(function () {
    // 0 = domingo, 1 = lunes, ..., 6 = sábado
    return Carbon::now()->isSunday();
});

Schedule::command('zip:storage-app')->dailyAt('23:00');
// Limpiar backups viejos después
Schedule::command('backup:clean')->dailyAt('23:30');
// Crear backup diario
Schedule::command('backup:run --only-db')->dailyAt('00:00')
    ->then(function () {
        $appName = config('app.name');
        $path = storage_path("app/private/{$appName}");
        exec("chown -R www-data:www-data {$path}");
        exec("chmod -R 755 {$path}");
    });
