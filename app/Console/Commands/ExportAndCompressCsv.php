<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExportCsvService;
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
    }
}
