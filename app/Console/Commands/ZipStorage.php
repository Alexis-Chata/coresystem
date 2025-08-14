<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ZipStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zip:storage-app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zipea la carpeta storage/app (zip.exe requisito necesario)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Obtiene el nombre del sistema desde .env o config
        $appName = config('app.name'); // o env('APP_NAME', 'default')
        $storagePath = storage_path('app');
        $zipName = 'app.zip';

        // Comando para crear el archivo ZIP
        $command = "cd {$storagePath} && zip -r {$zipName} . -x \"private/{$appName}/*\"";

        // Ejecuta el comando
        exec($command, $output, $returnVar);

        // Verifica si fue exitoso
        if ($returnVar === 0) {
            $this->info("Archivo ZIP creado correctamente en: {$storagePath}/{$zipName}");
        } else {
            $this->error("Error al crear el archivo ZIP.");
        }
    }
}
