<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExportCsvService;
use ZipArchive;

class ExportAndCompressCsv extends Command
{
    protected $signature = 'csv:export-compress';
    protected $description = 'Genera archivos CSV, los comprime y los deja listos para enviar.';

    public function handle()
    {
        $this->info("Generando archivos CSV...");

        // Exportar archivos CSV
        $clientesPath = ExportCsvService::exportClientes();
        $productosPath = ExportCsvService::exportProductos();
        $stockPath = ExportCsvService::exportStock();
        $vendedoresPath = ExportCsvService::exportVendedores();
        $ventasPath = ExportCsvService::exportVentas();
        $rutasPath = ExportCsvService::exportRutas();
        $pedidosPath = ExportCsvService::exportPedidos();

        // Crear un archivo ZIP
        $zipPath = storage_path('app/exports/data.zip');
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
            $this->info("Archivos CSV comprimidos correctamente en data.zip.");
        } else {
            $this->error("Error al crear el ZIP.");
        }
    }
}
