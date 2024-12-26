<?php

namespace App\Livewire;

use App\Models\FComprobanteSunat;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Luecano\NumeroALetras\NumeroALetras;
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
            $comprobantes = FComprobanteSunat::with(['vendedor', 'tipo_doc', 'cliente.padron', 'conductor', 'detalle.producto'])->get();

            $font = Printer::FONT_A;
            if($this->impresora == 'EPSON-TM-U220-Receipt'){
                $font = Printer::FONT_B;
            }
            $connector = new WindowsPrintConnector($this->impresora);
            $printer = new Printer($connector);
            foreach ($comprobantes as $comprobante) {

                $formatter = new NumeroALetras();
                //dd($comprobante->detalle);
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->setTextSize(1, 1);
                // $printer->setLineSpacing(65);
                $printer->setFont($font);
                $printer->text(strtoupper($comprobante->companyRazonSocial));
                $printer->feed();
                $printer->text("RUC: " . $comprobante->companyRuc);
                $printer->feed();
                $printer->text(strtoupper("PUNTO PARTIDA: " . $comprobante->companyAddressDireccion));
                $printer->feed();
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->feed();
                $printer->text("FECHA : " . ($comprobante->fechaEmision));
                $printer->feed();
                $printer->text(strtoupper($comprobante->tipoDoc_name . " " . $comprobante->serie . "-" . str_pad($comprobante->correlativo, 8, "0", STR_PAD_LEFT)));
                $printer->feed();
                $printer->text("--------------------------------");
                $printer->feed();
                $printer->text(strtoupper("COD.CLTE: " . str_pad($comprobante->cliente_id, 8, "0", STR_PAD_LEFT) . " " . $comprobante->tipo_doc->tipo_documento . ": " . $comprobante->clientNumDoc));
                $printer->feed();
                $printer->text("NOMBRE Y APELLIDOS:");
                $printer->feed();
                $printer->text(strtoupper($comprobante->clientRazonSocial));
                $printer->feed();
                $printer->text("DOMICILIO DE ENTREGA:");
                $printer->feed();
                $printer->text(strtoupper($comprobante->clientDireccion));
                $printer->feed();
                $printer->text(strtoupper("VENDEDOR: " . str_pad($comprobante->vendedor_id, 3, "0", STR_PAD_LEFT) . " " . $comprobante->vendedor->name));
                $printer->feed();
                $printer->text("RUTA: " . str_pad($comprobante->ruta_id, 4, "0", STR_PAD_LEFT) . "  SEC.: " . str_pad($comprobante->cliente->padron->nro_secuencia, 5, "0", STR_PAD_LEFT));
                $printer->feed();
                $printer->text("FORMA DE PAGO : CONTADO");
                $printer->feed();
                $printer->text("ARTICULO    CANTIDAD   PRECIO   IMPORTE");
                $printer->feed();
                $printer->text("---------------------------------------");
                $printer->feed();
                $printer->feed();
                foreach ($comprobante->detalle as $detalle) {
                    $printer->text(strtoupper(str_pad($detalle->codProducto, 5, "0", STR_PAD_LEFT) . " " . substr($detalle->descripcion, 0, 34)));
                    $printer->feed();
                    $printer->text("CAJX" . str_pad($detalle->ref_producto_cantidad_cajon, 2, "0", STR_PAD_LEFT) . "    " . str_pad(number_format_punto2($detalle->ref_producto_cant_vendida), 6, " ", STR_PAD_LEFT) . " " . str_pad(number_format($detalle->ref_producto_precio_cajon, 2), 10, " ", STR_PAD_LEFT) . " " . str_pad(number_format(($detalle->mtoValorVenta + $detalle->totalImpuestos), 2), 12, " ", STR_PAD_LEFT));
                    $printer->feed();
                }
                $printer->text("**SON: " . strtoupper($formatter->toInvoice($comprobante->mtoImpVenta, 2, 'SOLES')));
                $printer->feed();
                $printer->text("---------------------------------------");
                $printer->feed();
                $printer->text("NUMERO DE ITEMS = " . $comprobante->detalle->count());
                $printer->feed();
                $printer->text("IMPORTE BRUTO: " . number_format($comprobante->subTotal, 2));
                $printer->feed();
                $printer->text("DESCUENTOS : 0.00");
                $printer->feed();
                $printer->text("IMPORTE TOTAL: " . number_format($comprobante->mtoImpVenta, 2));
                $printer->feed();
                $printer->text(strtoupper("CHOFER: " . str_pad($comprobante->conductor_id, 3, "0", STR_PAD_LEFT) . " " . $comprobante->conductor->name));
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
            }

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
            //dd("error");
            session()->flash('error', 'Error al imprimir: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.imprimir-comprobante');
    }
}
