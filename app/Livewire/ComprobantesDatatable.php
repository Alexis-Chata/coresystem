<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\FComprobanteSunat;
use App\Services\EnvioSunatService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Rappasoft\LaravelLivewireTables\Views\Columns\DateColumn;

class ComprobantesDatatable extends DataTableComponent
{
    protected $model = FComprobanteSunat::class;
    protected ?int $searchFilterDebounce = 1000;

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public array $bulkActions = [
        'enviarSeleccionados' => 'Enviar Comprobantes',
    ];

    public function enviarSeleccionados()
    {
        foreach ($this->getSelected() as $index => $id) {
            $this->cdr($id);
        }
        $this->clearSelected();

        session()->flash('mensaje', '¡Comprobantes enviados correctamente!');
    }


    public function query()
    {
        return FComprobanteSunat::query()->select('id'); // Asegúrate de incluir 'id'
    }

    public function pdf($id)
    {
        $comprobante = FComprobanteSunat::find($id);
        $envioSunat = new EnvioSunatService;
        $envioSunat->pdf($comprobante);
        return Storage::download(str_replace('.xml', '.pdf', $comprobante->nombrexml));
    }

    public function xml($id)
    {
        $comprobante = FComprobanteSunat::find($id);
        $envioSunat = new EnvioSunatService;
        $envioSunat->xml($comprobante);
        //dd(Storage::exists($comprobante->nombrexml));
        return Storage::download($comprobante->nombrexml);
    }

    public function cdr($id)
    {
        $comprobante = FComprobanteSunat::find($id);
        if ($comprobante->codigo_sunat === '0') {
            return Storage::download($comprobante->cdrxml);
        }
        $envioSunat = new EnvioSunatService;
        $envioSunat->send($comprobante);
        return Storage::download($comprobante->cdrxml);
    }

    public function sunatResponse($id)
    {
        $comprobante = FComprobanteSunat::find($id);
        if ($comprobante->codigo_sunat !== null) {
            $mensaje = "
            <div class='w-full text-xl space-y-4'>
                <div class='flex justify-between'>
                    {$comprobante->tipoDoc_name}
                    <span
                        class='outline-none inline-flex justify-center items-center group rounded-md text-white bg-primary dark:bg-primary-700 gap-x-1 text-base font-semibold px-2.5 py-0.5'>
                        {$comprobante->serie} - {$comprobante->correlativo}
                    </span>
                </div>
                <div class='flex'>";
            if ($comprobante->codigo_sunat === '0') {
                $mensaje .= "<svg class='w-6 h-6 text-green-500 mr-2' fill='currentColor' xmlns='http://www.w3.org/2000/svg' width='24'
                        height='24' viewBox='0 0 24 24'>
                        <path fill-rule='evenodd' clip-rule='evenodd'
                            d='M19.916 4.62592C20.2607 4.85568 20.3538 5.32134 20.124 5.66598L11.124 19.166C10.9994 19.3529 10.7975 19.4742 10.5739 19.4963C10.3503 19.5184 10.1286 19.4392 9.96967 19.2803L3.96967 13.2803C3.67678 12.9874 3.67678 12.5125 3.96967 12.2196C4.26256 11.9267 4.73744 11.9267 5.03033 12.2196L10.3834 17.5727L18.876 4.83393C19.1057 4.48929 19.5714 4.39616 19.916 4.62592Z'>
                        </path>
                    </svg>
                    Enviado a SUNAT";
            } else {
                $mensaje .= "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='currentColor' class='size-6 text-red-400'>
                    <path fill-rule='evenodd' d='M5.47 5.47a.75.75 0 0 1 1.06 0L12 10.94l5.47-5.47a.75.75 0 1 1 1.06 1.06L13.06 12l5.47 5.47a.75.75 0 1 1-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 0 1-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 0 1 0-1.06Z' clip-rule='evenodd' />
                    </svg>

                    Error al Enviar a SUNAT";
            }
            $mensaje .= "</div>";
            if ($comprobante->codigo_sunat === '0') {
                $mensaje .= "
                <div class='flex justify-between'>
                    Estado:
                    <span
                        class='outline-none inline-flex justify-center items-center group rounded-md text-white bg-primary dark:bg-primary-700 gap-x-1 text-base font-semibold px-2.5 py-0.5'>
                        ACEPTADO
                    </span>
                </div>";
            }

            $mensaje .= "<div class='flex justify-between'>
                    Código:
                    <span
                        class='outline-none inline-flex justify-center items-center group rounded-md text-white bg-primary dark:bg-primary-700 gap-x-1 text-base font-semibold px-2.5 py-0.5'>
                        {$comprobante->codigo_sunat}
                    </span>
                </div>
                <div class='whitespace-normal'>
                    {$comprobante->mensaje_sunat}
                </div>";
            if ($comprobante->obs !== "[]") {
                $mensaje .= "<div class='whitespace-normal bg-yellow-100'>
                    <div class='w-full'>
                        <p class='font-semibold'>Observaciones:</p>
                        <p class='font-semibold py-2'>(Corregir estas observaciones en siguientes emisiones)</p>
                        <ul>
                            <li>{$comprobante->obs}</li>
                        </ul>
                    </div>

                </div>";
            }
            $mensaje .= "</div>";
            return $this->dispatch('padron-deleted', $mensaje);
        }

        $envioSunat = new EnvioSunatService;
        $envioSunat->send($comprobante);
    }

    public function anular($id)
    {
        dd($id);
    }

    public function columns(): array
    {
        return [
            Column::make('Action')
                ->label(
                    fn($row, Column $column) => view('livewire.components.dropdown')->with([
                        'id' => $row->id,
                        'codigo_sunat' => $row->codigo_sunat,
                    ])
                ),
            Column::make("Id", "id")
                ->hideIf(true),
            DateColumn::make('Fecha Emision', 'fechaEmision')
                ->sortable()
                ->inputFormat('Y-m-d H:i:s')
                ->outputFormat('d-m-Y')
                ->emptyValue('Not Found'),
            Column::make("TipoDoc name", "tipoDoc_name")
                ->sortable()
                ->searchable(),
            Column::make("Serie", "serie")
                ->sortable()
                ->searchable(),
            Column::make("Correlativo", "correlativo")
                ->sortable(),
            Column::make("ClientTipoDoc", "tipo_doc.tipo_documento")
                ->sortable(),
            Column::make("ClientNumDoc", "clientNumDoc")
                ->sortable()
                ->searchable(),
            Column::make("ClientRazonSocial", "clientRazonSocial")
                ->sortable()
                ->searchable(),
            Column::make("ClientDireccion", "clientDireccion")
                ->sortable()
                ->searchable(),
            Column::make("MtoImpVenta", "mtoImpVenta")
                ->sortable()
                ->searchable(),
            // Column::make("Ruta", "ruta.name")
            //     ->sortable()
            //     ->searchable(),
            // Column::make("Vendedor", "vendedor.name")
            //     ->sortable()
            //     ->searchable(),
            // Column::make("Conductor", "conductor.name")
            //     ->sortable()
            //     ->searchable(),
            // Column::make("Cliente", "cliente.razon_social")
            //     ->sortable()
            //     ->searchable(),
            // Column::make("Movimiento id", "movimiento_id")
            //     ->sortable(),
            // Column::make("Pedido id", "pedido_id")
            //     ->sortable(),
            // Column::make("Pedido obs", "pedido_obs")
            //     ->sortable(),
            // Column::make("Sede", "sede.name")
            //     ->sortable(),
            // Column::make("UblVersion", "ublVersion")
            //     ->sortable(),
            // Column::make("TipoDoc", "tipoDoc")
            //     ->sortable(),
            // Column::make("TipoOperacion", "tipoOperacion")
            //     ->sortable(),
            // Column::make("FormaPagoTipo", "formaPagoTipo")
            //     ->sortable(),
            // Column::make("TipoMoneda", "tipoMoneda")
            //     ->sortable(),
            Column::make("CompanyRuc", "companyRuc")
                ->sortable(),
            Column::make("CompanyRazonSocial", "companyRazonSocial")
                ->sortable(),
            // Column::make("CompanyNombreComercial", "companyNombreComercial")
            //     ->sortable(),
            // Column::make("CompanyAddressUbigueo", "companyAddressUbigueo")
            //     ->sortable(),
            // Column::make("CompanyAddressDepartamento", "companyAddressDepartamento")
            //     ->sortable(),
            // Column::make("CompanyAddressProvincia", "companyAddressProvincia")
            //     ->sortable(),
            // Column::make("CompanyAddressDistrito", "companyAddressDistrito")
            //     ->sortable(),
            // Column::make("CompanyAddressUrbanizacion", "companyAddressUrbanizacion")
            //     ->sortable(),
            Column::make("CompanyAddressDireccion", "companyAddressDireccion")
                ->sortable(),
            Column::make("CompanyAddressCodLocal", "companyAddressCodLocal")
                ->sortable(),
            // Column::make("MtoOperGravadas", "mtoOperGravadas")
            //     ->sortable(),
            // Column::make("MtoOperInafectas", "mtoOperInafectas")
            //     ->sortable(),
            // Column::make("MtoOperExoneradas", "mtoOperExoneradas")
            //     ->sortable(),
            // Column::make("MtoIGV", "mtoIGV")
            //     ->sortable(),
            // Column::make("MtoBaseIsc", "mtoBaseIsc")
            //     ->sortable(),
            // Column::make("MtoISC", "mtoISC")
            //     ->sortable(),
            // Column::make("Icbper", "icbper")
            //     ->sortable(),
            // Column::make("TotalImpuestos", "totalImpuestos")
            //     ->sortable(),
            // Column::make("ValorVenta", "valorVenta")
            //     ->sortable(),
            // Column::make("SubTotal", "subTotal")
            //     ->sortable(),
            // Column::make("Redondeo", "redondeo")
            //     ->sortable(),
            // Column::make("LegendsCode", "legendsCode")
            //     ->sortable(),
            // Column::make("LegendsValue", "legendsValue")
            //     ->sortable(),
            Column::make("TipDocAfectado", "tipDocAfectado")
                ->sortable(),
            Column::make("NumDocfectado", "numDocfectado")
                ->sortable(),
            Column::make("CodMotivo", "codMotivo")
                ->sortable(),
            Column::make("DesMotivo", "desMotivo")
                ->sortable(),
            Column::make("Nombrexml", "nombrexml")
                ->sortable(),
            Column::make("Hash", "hash")
                ->sortable(),
            Column::make("Cdrxml", "cdrxml")
                ->sortable(),
            Column::make("Codigo sunat", "codigo_sunat")
                ->sortable(),
            Column::make("Mensaje sunat", "mensaje_sunat")
                ->sortable(),
            Column::make("Obs", "obs")
                ->sortable(),
            // Column::make("Empresa id", "empresa_id")
            //     ->sortable(),
            DateColumn::make("Created at", "created_at")
                ->sortable()
                ->inputFormat('Y-m-d H:i:s')
                ->outputFormat('d-m-Y H:i:s')
                ->emptyValue('Not Found'),
            DateColumn::make("Updated at", "updated_at")
                ->sortable()
                ->inputFormat('Y-m-d H:i:s')
                ->outputFormat('d-m-Y H:i:s')
                ->emptyValue('Not Found'),
        ];
    }
}
