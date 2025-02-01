<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\FGuiaSunat;
use App\Services\EnvioSunatService;
use Illuminate\Support\Facades\Storage;

class FGuiaSunatsDatatable extends DataTableComponent
{
    protected $model = FGuiaSunat::class;
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
        foreach ($this->getSelected() as $id) {
            $this->cdr($id);
        }
        $this->clearSelected();

        session()->flash('mensaje', '¡Comprobantes enviados correctamente!');
    }

    public function pdf($id)
    {
        $comprobante = FGuiaSunat::find($id);
        $envioSunat = new EnvioSunatService;
        $envioSunat->pdf($comprobante);
        return Storage::download(str_replace('.xml', '.pdf', $comprobante->nombrexml));
    }

    public function xml($id)
    {
        $comprobante = FGuiaSunat::find($id);
        $envioSunat = new EnvioSunatService;
        $envioSunat->xml($comprobante);
        //dd(Storage::exists($comprobante->nombrexml));
        return Storage::download($comprobante->nombrexml);
    }


    public function cdr($id)
    {
        $comprobante = FGuiaSunat::find($id);
        if ($comprobante->codigo_sunat === '0') {
            return Storage::download($comprobante->cdrxml);
        }
        if ($comprobante->tipoDoc === "00") {
            $this->dispatch('sweetalert2-notapedido', $comprobante->serie . "-" . $comprobante->correlativo);
            return;
        }
        $envioSunat = new EnvioSunatService;
        $envioSunat->send($comprobante);
        return Storage::download($comprobante->cdrxml);
    }

    public function sunatResponse($id)
    {
        $comprobante = FGuiaSunat::find($id);
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
            return $this->dispatch('sweetalert2-sunatResponse', $mensaje);
        }

        $this->cdr($comprobante->id);
    }

    public function columns(): array
    {
        return [
            Column::make('Action')
                ->label(
                    fn($row, Column $column) => view('livewire.components.dropdown')->with([
                        'id' => $row->id,
                        'codigo_sunat' => $row->codigo_sunat,
                        'tipo_doc' => $row->tipoDoc,
                    ])
                ),
            Column::make("Id", "id")
                ->sortable(),
            Column::make("Version", "version")
                ->sortable(),
            Column::make("TipoDoc", "tipoDoc")
                ->sortable(),
            Column::make("Serie", "serie")
                ->sortable(),
            Column::make("Correlativo", "correlativo")
                ->sortable(),
            Column::make("FechaEmision", "fechaEmision")
                ->sortable(),
            Column::make("CompanyRuc", "companyRuc")
                ->sortable(),
            Column::make("CompanyRazonSocial", "companyRazonSocial")
                ->sortable(),
            Column::make("CompanyNombreComercial", "companyNombreComercial")
                ->sortable(),
            Column::make("CompanyAddressUbigueo", "companyAddressUbigueo")
                ->sortable(),
            Column::make("CompanyAddressDepartamento", "companyAddressDepartamento")
                ->sortable(),
            Column::make("CompanyAddressProvincia", "companyAddressProvincia")
                ->sortable(),
            Column::make("CompanyAddressDistrito", "companyAddressDistrito")
                ->sortable(),
            Column::make("CompanyAddressUrbanizacion", "companyAddressUrbanizacion")
                ->sortable(),
            Column::make("CompanyAddressDireccion", "companyAddressDireccion")
                ->sortable(),
            Column::make("CompanyAddressCodLocal", "companyAddressCodLocal")
                ->sortable(),
            Column::make("ClientTipoDoc", "clientTipoDoc")
                ->sortable(),
            Column::make("ClientNumDoc", "clientNumDoc")
                ->sortable(),
            Column::make("ClientRazonSocial", "clientRazonSocial")
                ->sortable(),
            Column::make("ClientDireccion", "clientDireccion")
                ->sortable(),
            Column::make("CodTraslado", "codTraslado")
                ->sortable(),
            Column::make("ModTraslado", "modTraslado")
                ->sortable(),
            Column::make("FecTraslado", "fecTraslado")
                ->sortable(),
            Column::make("PesoTotal", "pesoTotal")
                ->sortable(),
            Column::make("UndPesoTotal", "undPesoTotal")
                ->sortable(),
            Column::make("LlegadaUbigeo", "llegadaUbigeo")
                ->sortable(),
            Column::make("LlegadaDireccion", "llegadaDireccion")
                ->sortable(),
            Column::make("PartidaUbigeo", "partidaUbigeo")
                ->sortable(),
            Column::make("PartidaDireccion", "partidaDireccion")
                ->sortable(),
            Column::make("Transportista tipoDoc", "transportista_tipoDoc")
                ->sortable(),
            Column::make("Transportista numDoc", "transportista_numDoc")
                ->sortable(),
            Column::make("Transportista rznSocial", "transportista_rznSocial")
                ->sortable(),
            Column::make("Transportista nroMtc", "transportista_nroMtc")
                ->sortable(),
            Column::make("Vehiculo placa", "vehiculo_placa")
                ->sortable(),
            Column::make("Chofer tipoDoc", "chofer_tipoDoc")
                ->sortable(),
            Column::make("Chofer nroDoc", "chofer_nroDoc")
                ->sortable(),
            Column::make("Chofer licencia", "chofer_licencia")
                ->sortable(),
            Column::make("Chofer nombres", "chofer_nombres")
                ->sortable(),
            Column::make("Chofer apellidos", "chofer_apellidos")
                ->sortable(),
            Column::make("Created at", "created_at")
                ->sortable(),
            Column::make("Updated at", "updated_at")
                ->sortable(),
        ];
    }
}
