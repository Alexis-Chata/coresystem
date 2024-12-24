<?php

namespace App\Livewire;

use App\Models\FComprobanteSunat;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class ImprimirComprobante extends Component
{
    #[Rule('required')]
    public $impresora;

    public function imprimir()
    {
        $this->validate();
        try {
            $nombre_impresora_compartida = "POS-80C-1";
            $connector = new WindowsPrintConnector($this->impresora);
            $printer = new Printer($connector);

            $comprobante = FComprobanteSunat::with(['tipo_documento', 'cliente'])->find(1);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(1, 1);
            // $printer->setLineSpacing(65);
            $printer->setFont(Printer::FONT_A);
            $printer->text($comprobante->companyRazonSocial);
            $printer->feed();
            $printer->text("RUC: " . $comprobante->companyRuc);
            $printer->feed();
            $printer->text("PUNTO PARTIDA: " . $comprobante->companyAddressDireccion);
            $printer->feed();
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->feed();
            $printer->text("FECHA : " . ($comprobante->fechaEmision));
            $printer->feed();
            $printer->text($comprobante->tipoDoc_name . " " . $comprobante->serie . "-" . str_pad($comprobante->correlativo, 8, "0", STR_PAD_LEFT));
            $printer->feed();
            $printer->text("--------------------------------");
            $printer->feed();
            $printer->text("COD.CLTE: ".str_pad($comprobante->cliente_id, 8, "0", STR_PAD_LEFT  )." DNI: ".$comprobante->clientNumDoc);
            $printer->feed();
            $printer->text("NOMBRE Y APELLIDOS:");
            $printer->feed();
            $printer->text($comprobante->clientRazonSocial);
            $printer->feed();
            $printer->text("DOMICILIO DE ENTREGA:");
            $printer->feed();
            $printer->text($comprobante->clientDireccion);
            $printer->feed();
            $printer->text("VENDEDOR: 010 LEAO DO SANTOS");
            $printer->feed();
            $printer->text("RUTA: 046 MODU: 00046 SEC.:0212");
            $printer->feed();
            $printer->text("FORMA DE PAGO : CONTADO");
            $printer->feed();
            $printer->text("ARTICULO CANTIDAD PRECIO IMPORTE");
            $printer->feed();
            $printer->text("---------------------------------------");
            $printer->feed();
            $printer->feed();
            $printer->text("0000000066 CHOCOSODA CJX32PACKX6UNI");
            $printer->feed();
            $printer->text("CAJX32     0.01    211.20       6.60");
            $printer->feed();
            $printer->text("0000000092 TRIDENT MENTA CJX30DISX18UNI");
            $printer->feed();
            $printer->text("CAJX30      0.01    615.00     20.50");
            $printer->feed();
            $printer->text("0000000093 TRIDENT MORA CJX30DISX18UNI");
            $printer->feed();
            $printer->text("CAJX30      0.01    615.00     20.50");
            $printer->feed();
            $printer->text("0000000096 TRIDENT ZANDIA CJX30DISX18UN");
            $printer->feed();
            $printer->text("CAJX30      0.01    615.00     20.50");
            $printer->feed();
            $printer->text("0000000097 VAINILLA FIELD CJX24PACKX6UN");
            $printer->feed();
            $printer->text("CAJX24      0.01    132.00     5.50");
            $printer->feed();
            $printer->text("0000000103 GALLETA OREO CJX28PAK");
            $printer->feed();
            $printer->text("CAJX28      0.02    148.40     10.60");
            $printer->feed();
            $printer->text("0000000130 AGUA LIGHT CJX12PAKX6UNI");
            $printer->feed();
            $printer->text("CAJX12      0.01    46.80     3.90");
            $printer->feed();
            $printer->text("0000000139 CEREAL BAR CHIPS CJX8DISX12U");
            $printer->feed();
            $printer->text("CAJX8      0.01    54.40     6.80");
            $printer->feed();
            $printer->text("0000000179 NIK FAMILIAR CHOCOLATCJX6PKX");
            $printer->feed();
            $printer->text("CAJX6      0.01    43.20     7.20");
            $printer->feed();
            $printer->text("0000000180 NIK FAMILIAR FRESA CJX6PKX6U");
            $printer->feed();
            $printer->text("CAJX6      0.01    43.20     7.20");
            $printer->feed();
            $printer->text("0000000181 NIK FAMILIAR VAINILL CJX6PKX");
            $printer->feed();
            $printer->text("CAJX6      0.01    43.20     7.20");
            $printer->feed();
            $printer->text("0000000187 SODA LIGHT CJX12PAKX6UNI");
            $printer->feed();
            $printer->text("CAJX12      0.01    46.80      3.90");
            $printer->feed();
            $printer->text("0000000270 BLACKOUT CHOCOLATE CJX32UNI");
            $printer->feed();
            $printer->text("CAJX32      0.04    24.00      3.00");
            $printer->feed();
            $printer->text("0000000271 BLACKOUT MENTA CJX32UNI");
            $printer->feed();
            $printer->text("CAJX32      0.04    24.00       3.00");
            $printer->feed();
            $printer->text("0000000386 TENTACIËN CHOCOLATE CJX8PAKX");
            $printer->feed();
            $printer->text("CAJX8      0.01    40.00        5.00");
            $printer->feed();
            $printer->text("0000000597 PICARAS CLASICA CJX20PAKX8UN");
            $printer->feed();
            $printer->text("CAJX20      0.01    132.00       6.60");
            $printer->feed();
            $printer->text("**SON: CIENTO TREINTA Y OCHO Y 00/100 SOLES");
            $printer->feed();
            $printer->text("---------------------------------------");
            $printer->feed();
            $printer->text("NUMERO DE ITEMS = 16");
            $printer->feed();
            $printer->text("IMPORTE BRUTO: 138.00");
            $printer->feed();
            $printer->text("DESCUENTOS : 0.00");
            $printer->feed();
            $printer->text("IMPORTE TOTAL: 138.00");
            $printer->feed();
            $printer->text("CHOFER:004 CRISTIAN");
            $printer->feed();
            $printer->feed();
            $printer->text("REPRESENTACION IMPRESA DE BOLETA ELECTRONICA");
            $printer->feed();
            $printer->text("AUTORIZADO MEDIANTE RESOLUCION");
            $printer->feed();
            $printer->text("NRO.:340-2017/SUNAT");
            $printer->feed();
            $printer->text("VB");
            $printer->feed();

            $printer->feed();
            $printer->feed();
            $printer->cut();

            /*
            Por medio de la impresora mandamos un pulso.
            Esto es útil cuando la tenemos conectada
            por ejemplo a un cajón
            */
            $printer->pulse();

            /*
            Para imprimir realmente, tenemos que "cerrar"
            la conexión con la impresora. Recuerda incluir esto al final de todos los archivos
            */
            $printer->close();
        } catch (\Exception $e) {
            // Manejo de errores
            session()->flash('error', 'Error al imprimir: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.imprimir-comprobante');
    }
}
