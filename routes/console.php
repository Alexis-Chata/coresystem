<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('csv:export-compress 7')->everyMinute()->onFailure(function () {
    Log::channel('respuesta_envio_sftp')->error("CSV Export 7: Falló la ejecución del comando.");
});

Schedule::command('csv:export-compress 10')->everyMinute()->onFailure(function () {
    Log::channel('respuesta_envio_sftp')->error("CSV Export 10: Falló la ejecución del comando.");
});
