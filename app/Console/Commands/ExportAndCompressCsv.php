<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExportCsvService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemException;
use ZipArchive;

class ExportAndCompressCsv extends Command
{
    protected $signature = 'csv:export-compress {marcaId}';
    protected $description = 'Genera archivos CSV, los comprime y los deja listos para enviar.';

    public function handle()
    {
        $marcaId = $this->argument('marcaId');

        // Determinar el nombre de la marca para la carpeta
        $marcaNombre = $marcaId == 7 ? 'winter' : ($marcaId == 10 ? 'arcor' : 'marca_' . $marcaId);

        // Crear la carpeta específica para la marca si no existe
        $exportDir = "exports/{$marcaNombre}";

        // Crear la carpeta si no existe
        if (!file_exists(storage_path("app/{$exportDir}"))) {
            mkdir(storage_path("app/{$exportDir}"), 0755, true);
        }

        $this->info("Generando archivos CSV para la marca $marcaId ($marcaNombre)...");

        // Exportar archivos CSV
        $clientesPath = ExportCsvService::exportClientes($marcaId, $exportDir);
        $productosPath = ExportCsvService::exportProductos($marcaId, $exportDir);
        $stockPath = ExportCsvService::exportStock($marcaId, $exportDir);
        $vendedoresPath = ExportCsvService::exportVendedores($marcaId, $exportDir);
        $ventasPath = ExportCsvService::exportVentas($marcaId, $exportDir);
        $rutasPath = ExportCsvService::exportRutas($marcaId, $exportDir);
        //$pedidosPath = ExportCsvService::exportPedidos($marcaId, $exportDir);

        // Crear un archivo ZIP
        $zipPath = storage_path("app/{$exportDir}/data.zip");
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFile(storage_path("app/$clientesPath"), 'clientes.csv');
            $zip->addFile(storage_path("app/$productosPath"), 'productos.csv');
            $zip->addFile(storage_path("app/$stockPath"), 'stock.csv');
            $zip->addFile(storage_path("app/$vendedoresPath"), 'vendedores.csv');
            $zip->addFile(storage_path("app/$ventasPath"), 'ventas.csv');
            $zip->addFile(storage_path("app/$rutasPath"), 'rutas.csv');
            //$zip->addFile(storage_path("app/$pedidosPath"), 'pedidos.csv');
            $zip->close();
            $this->info("Archivos CSV comprimidos correctamente en {$exportDir}/data.zip.");
        } else {
            $this->error("Error al crear el ZIP.");
        }

        Log::channel('respuesta_envio_sftp')->info("Iniciando subida a SFTP...");
        $this->info("Iniciando subida a SFTP...");

        if (!file_exists($zipPath)) {
            $errorMsg = "Error: El archivo no existe en local";
            Log::channel('respuesta_envio_sftp')->error($errorMsg);
            $this->error($errorMsg);
            return;
        }

        // Definir el disco según la marca
        $disks = match ((int) $marcaId) {
            7 => 'sftp_cnch',
            10 => 'sftp_arcor',
            default => 'sftp_prueba',
        };

        if (!$disks) {
            $errorMsg = "El disco no está definido para la marca ID: $marcaId";
            Log::channel('respuesta_envio_sftp')->error($errorMsg);
            $this->error($errorMsg);
            return;
        }

        Log::channel('respuesta_envio_sftp')->info("Usando disco: $disks");
        $this->info("Usando disco: $disks");

        $remotePath = "output.zip";

        try {
            // Intentar subir el archivo
            $resultado = Storage::disk($disks)->putFileAs('', $zipPath, $remotePath);

            if ($resultado) {
                $size = Storage::disk($disks)->size($remotePath);
                $successMsg = "El archivo '$remotePath' se ha subido con éxito. Tamaño: $size bytes.";
                Log::channel('respuesta_envio_sftp')->info($successMsg);
                $this->info($successMsg);
            } else {
                $errorMsg = "Error: No se pudo subir el archivo.";
                Log::channel('respuesta_envio_sftp')->error($errorMsg);
                $this->error($errorMsg);
            }
        } catch (FilesystemException $e) {
            $errorMsg = "Error de conexión con SFTP: " . $e->getMessage();
            Log::channel('respuesta_envio_sftp')->error($errorMsg);
            $this->error($errorMsg);
        } catch (\Exception $e) {
            $errorMsg = "Error inesperado: " . $e->getMessage();
            Log::channel('respuesta_envio_sftp')->error($errorMsg);
            $this->error($errorMsg);
        }
        Log::channel('respuesta_envio_sftp')->info("...");
    }
}
