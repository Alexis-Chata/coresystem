<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExportCsvService;
use Illuminate\Support\Facades\Storage;
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
        $pedidosPath = ExportCsvService::exportPedidos($marcaId, $exportDir);

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
            $zip->addFile(storage_path("app/$pedidosPath"), 'pedidos.csv');
            $zip->close();
            $this->info("Archivos CSV comprimidos correctamente en {$exportDir}/data.zip.");
        } else {
            $this->error("Error al crear el ZIP.");
        }

        $this->info("iniciando subida a SFTP...");

        $fechaHora = date('Ymd_His'); // Formato: DíaMesAño_HoraMinutoSegundo
        $marcaNombre = preg_replace('/[^A-Za-z0-9]/', '_', $marcaNombre);
        $remotePath = "data.zip"; // Ruta en el servidor SFTP con el nombre del archivo
        //$remotePath = "data_{$marcaNombre}_{$fechaHora}.zip"; // Ruta en el servidor SFTP con el nombre del archivo

        // Verificar si el archivo local existe
        if (file_exists($zipPath)) {
            $contenido = file_get_contents($zipPath);
        } else {
            $this->error('Error: El archivo no existe en local');
            return;
        }

        $disks = match ((int) $marcaId) {
            7 => 'sftp_cnch', // sftp_prueba
            10 => 'sftp_arcor', // sftp_prueba
            default => null,
        };

        if ($disks === null) {
            $this->error("El disco no está definido para la marca ID: $marcaId");
            return;
        }
        $this->info("Usando disco: $disks");

        $this->info("Subiendo archivo a: {$disks}/{$remotePath}");
        // Subir el archivo
        Storage::disk($disks)->put($remotePath, $contenido);

        $fullPath = Storage::disk($disks)->path($remotePath);
        $this->info("Ruta completa del archivo en SFTP: $fullPath");

        if (Storage::disk($disks)->exists($remotePath)) {
            $size = Storage::disk($disks)->size($remotePath);
            $this->info("El archivo '$remotePath' se ha subido con éxito. Tamaño: $size bytes.");
        } else {
            $this->error("Error: El archivo no se encuentra en el SFTP.");
        }

        // Verificar si el archivo fue subido
        //$archivos = Storage::disk($disks)->files('.');
        //$this->info("Archivos en el SFTP: " . implode(', ', $archivos));

        $this->info("Archivo subido correctamente " . $remotePath);
    }
}
