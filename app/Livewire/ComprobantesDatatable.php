<?php

namespace App\Livewire;

use App\Exports\FComprobanteSunatsExport;
use App\Models\Empresa;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\FComprobanteSunat;
use App\Models\FGuiaSunat;
use App\Models\FSerie;
use App\Services\EnvioSunatService;
use Carbon\Carbon;
use Exception;
use Greenter\Ws\Services\ConsultCdrService;
use Greenter\Ws\Services\SoapClient;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;
use Rappasoft\LaravelLivewireTables\Views\Columns\DateColumn;
use ZipArchive;

class ComprobantesDatatable extends DataTableComponent
{
    // No esta dentro de la documentacion pero esta ayudando para agregar html antes/despues de la tabla
    public function render(): Application|Factory|View
    {
        return view('livewire.comprobantes-datatable-wrapper');
    }

    //protected $model = FComprobanteSunat::class;
    protected ?int $searchFilterDebounce = 1000;
    public ?int $perPage = 50;
    public array $perPageAccepted = [10, 25, 50, 100];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public array $bulkActions = [
        'enviarSeleccionados' => 'Enviar Comprobantes',
    ];

    public $fecha_emision;
    public $fecha_emision_fin;
    public $buscar_search;
    public $estado_envio;
    public $tipoDoc;
    public $tipo_comprobante;

    public function builder(): Builder
    {

        $return =  FComprobanteSunat::query()
            ->when($this->tipoDoc, function ($query, $tipo_doc) {
                $query->where("tipoDoc", $tipo_doc); // Siempre filtra por tipoDoc primero
            })
            ->when($this->fecha_emision, function ($query) {
                $query->whereBetween("fechaEmision", [$this->fecha_emision . ' 00:00:00', $this->fecha_emision_fin . ' 23:59:59']);
            })
            ->when($this->estado_envio, function ($query) {
                $query->where(function ($q) { // Aplicar condición dentro de un subquery
                    $estado = in_array($this->estado_envio, ['aceptado', 'rechazado'])
                        ? $this->estado_envio
                        : 'pendiente';

                    $q->where("estado_cpe_sunat", $estado);
                });
            })
            ->when($this->buscar_search, function ($query) {
                $query->where(function ($q) {
                    $q->orWhere("correlativo", 'like', '%' . $this->buscar_search . '%')
                        ->orWhere("cliente_id", 'like', '%' . $this->buscar_search . '%');
                });
            });
        $sql = vsprintf(str_replace('?', "'%s'", $return->toSql()), $return->getBindings());
        logger()->info($sql);
        //dd($sql);
        return $return;
    }

    public function descargar_comprobantes()
    {
        $inicio = $this->fecha_emision;
        $fin = $this->fecha_emision_fin;
        return Excel::download(new FComprobanteSunatsExport($inicio, $fin), 'Reporte_Comprobantes_' . format_date($inicio) . '_' . format_date($fin) . '.xlsx');
    }

    public function mount()
    {
        $this->fecha_emision = Carbon::now();

        if ($this->fecha_emision->isMonday()) {
            $this->fecha_emision = $this->fecha_emision->subDays(2)->toDateString();
        } else {
            $this->fecha_emision = $this->fecha_emision->subDay()->toDateString();
        }
        $this->fecha_emision_fin = $this->fecha_emision;
    }

    public function enviarSeleccionados()
    {
        foreach ($this->getSelected() as $id) {
            $this->cdr($id);
        }
        $this->clearSelected();

        session()->flash('mensaje', '¡Comprobantes enviados correctamente!');
    }

    public function consulta_cdr($id)
    {
        $comprobante = FComprobanteSunat::find($id);
        $empresa = Empresa::find($comprobante->empresa_id);
        // URL CDR de Producción
        $wsdlUrl = 'https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl';
        $soap = new SoapClient($wsdlUrl);
        $soap->setCredentials($empresa->ruc . $empresa->sol_user, $empresa->sol_pass);

        $service = new ConsultCdrService();
        $service->setClient($soap);

        $rucEmisor = $comprobante->companyRuc;
        $tipoDocumento = $comprobante->tipoDoc; // 01: Factura, 07: Nota de Crédito, 08: Nota de Débito
        $serie = $comprobante->serie;
        $correlativo = $comprobante->correlativo;
        $result = $service->getStatusCdr($rucEmisor, $tipoDocumento, $serie, $correlativo);

        if (!$result->isSuccess()) {
            //var_dump($result->getError());
            logger("consultar_cdr", ["result_getError" => $result->getError()]);
            return;
        }

        $cdr = $result->getCdrResponse();
        if ($cdr === null) {
            logger('CDR no encontrado, el comprobante no ha sido comunicado a SUNAT.');
            return;
        }

        $path_cdrzip = 'invoices/' . $rucEmisor . '-' . $tipoDocumento . '-' . $serie . '-' . $correlativo . '-CDR.zip';
        $path_cdrxml = 'invoices/R-' . $rucEmisor . '-' . $tipoDocumento . '-' . $serie . '-' . $correlativo . '.xml';

        // Guardar el archivo ZIP en el almacenamiento de Laravel
        // $result->getCdrZip() - Contenido binario del ZIP
        Storage::put($path_cdrzip, $result->getCdrZip());

        // Registrar la respuesta CDR en los logs correctamente
        logger("Consulta CDR realizada", ['cdr' => $cdr]);

        $zip = new ZipArchive;
        if ($zip->open(storage_path('app/private/' . $path_cdrzip)) === true) {
            $zip->extractTo(storage_path('app/private/invoices/'));
            $zip->close();
        }
        $comprobante->update([
            'cdrxml'      => $path_cdrxml,
            'cdrbase64'   => base64_encode($result->getCdrZip()),
            'codigo_sunat' => $result->getCdrResponse()->getCode(),
            'mensaje_sunat' => $result->getCdrResponse()->getDescription(),
            'obs'         => $result->getCdrResponse()->getNotes()
        ]);

        if ($comprobante->cdrxml && Storage::exists($comprobante->cdrxml)) {
            return Storage::download($comprobante->cdrxml);
        } else {
            Log::channel('respuesta_envio_sunat')->warning('path_cdrxml', ['El archivo no existe o cdrxml es null.', 'cdrxml' => $comprobante->cdrxml]);
            logger("Archivo no existe, verificar path", ['path_cdrxml' => $comprobante->cdrxml]);
        }
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
        $comprobante = FComprobanteSunat::findOrFail($id);
        //dd($comprobante);
        $service = new EnvioSunatService;
        $service->actualizarEstadoSegunRespuestaSunat($comprobante);
        $comprobante->refresh();
        // 1) Notas de pedido: no van a SUNAT
        if ($comprobante->tipoDoc === "00") {
            $this->dispatch('sweetalert2-notapedido', $comprobante->serie . "-" . $comprobante->correlativo);
            return;
        }

        // 2) Si YA tengo el CDR guardado físicamente, lo descargo y listo
        if ($comprobante->cdrxml && Storage::exists($comprobante->cdrxml)) {
            return Storage::download($comprobante->cdrxml);
        }

        // 3) Si no tengo CDR, lo consulto en SUNAT
        if ($comprobante->codigo_sunat !== null && $comprobante->codigo_sunat !== '') {
            logger("cdr: aceptado sin CDR físico, consultando CDR en SUNAT");
            $consulta_cdr = $this->consulta_cdr($id);
            logger("Resultado de consulta CDR", ['consulta_cdr' => $consulta_cdr]);

            if ($consulta_cdr) {
                return $consulta_cdr;
            }
            // Si no devuelve nada, seguimos abajo (no rompemos)
        }

        // 4) Si nunca se envió (null) o tiene código de error (!== '0'), intento enviar ahora
        logger("cdr: enviando comprobante a SUNAT");
        $envioSunat = new EnvioSunatService;
        $response = $envioSunat->send($comprobante);
        $comprobante->refresh();
        Log::channel('respuesta_envio_sunat')->info('respuesta_sunat', $response['sunatResponse'] ?? []);

        // 5) Después de enviar, intento descargar CDR si ya se generó
        if ($comprobante->cdrxml && Storage::exists($comprobante->cdrxml)) {
            return Storage::download($comprobante->cdrxml);
        }

        // 6) Si aún no hay CDR, como fallback intento consultarlo
        $consulta_cdr = $this->consulta_cdr($id);
        logger("Resultado de consulta CDR post-envio", ['consulta_cdr' => $consulta_cdr]);

        if ($consulta_cdr) {
            return $consulta_cdr;
        }

        Log::channel('respuesta_envio_sunat')
            ->warning('path_cdrxml', ['No se pudo obtener CDR para el comprobante ' . $comprobante->id]);

        return;
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
            return $this->dispatch('sweetalert2-sunatResponse', $mensaje);
        }

        $this->cdr($comprobante->id);
    }

    // Ya no se usa anular() desde el dropdown
    public function anular($id)
    {
        return; //Ya no se usa anular() desde el dropdown
        $comprobante_guia = FComprobanteSunat::find($id);

        $comprobante_guia = $comprobante_guia->id ? $comprobante_guia : FGuiaSunat::find($id);
        $validando = match ($comprobante_guia->tipoDoc) {
            "00" => "nota_pedido",
            "01", "03" => false,
            default => true,
        };
        if ($validando == "nota_pedido") {
            $comprobante_guia->estado_reporte = false;
            $comprobante_guia->save();
            return;
        }
        if ($validando) {
            return;
        }

        $nota_anulacion_operacion = FComprobanteSunat::where('numDocfectado', $comprobante_guia->serie . "-" . $comprobante_guia->correlativo)
            ->where('tipoDoc', '07')
            ->where('codMotivo', '01')
            ->where('desMotivo', 'ANULACION DE LA OPERACION')
            ->exists();
        if ($nota_anulacion_operacion) {
            return;
        }

        $nota_anulacion = FComprobanteSunat::where('numDocfectado', $comprobante_guia->serie . "-" . $comprobante_guia->correlativo)
            ->where('tipoDoc', '07')
            ->exists();
        if ($nota_anulacion) {
            return;
        }

        try {
            Cache::lock('generar_nota', 15)->block(10, function () use ($id) {
                DB::beginTransaction();
                $tipoDoc = "07";
                $comprobante = FComprobanteSunat::with('detalle')->find($id);
                $serie = FSerie::where('f_sede_id', $comprobante->sede_id)->where('serie', 'like', substr($comprobante->serie, 0, 1) . "%")
                    ->whereHas('fTipoComprobante', function ($query) use ($tipoDoc) {
                        $query->where('tipo_comprobante', $tipoDoc);
                    })
                    ->get()->first();
                $serie->correlativo = $serie->correlativo + 1;
                $serie->save();

                $notaSunat = $comprobante->replicate();
                $notaSunat->fill([
                    //"conductor_id" => 10,
                    "ublVersion" => "2.1",
                    "tipoDoc" => $tipoDoc,
                    "tipoDoc_name" => $serie->fTipoComprobante->name,
                    "serie" => $serie->serie,
                    "correlativo" => $serie->correlativo,
                    "fechaEmision" => now(),
                    "tipDocAfectado" => $comprobante->tipoDoc,
                    "numDocfectado" => $comprobante->serie . "-" . $comprobante->correlativo,
                    "codMotivo" => "01",
                    "desMotivo" => "ANULACION DE LA OPERACION",
                    "nombrexml" => null,
                    "xmlbase64" => null,
                    "hash" => null,
                    "cdrxml" => null,
                    "cdrbase64" => null,
                    "codigo_sunat" => null,
                    "mensaje_sunat" => null,
                    "obs" => null,
                    "estado_reporte" => false,
                    "estado_cpe_sunat" => 'pendiente',
                ]);
                $notaSunat->save();
                $notaSunat->detalle()->createMany($comprobante->detalle->toArray());

                $comprobante->estado_reporte = false;
                $comprobante->save();
                //dd($serie, substr($comprobante->serie, 0, 1), $notaSunat, $comprobante->detalle->toArray());
                DB::commit();
            });
        } catch (Exception | LockTimeoutException $e) {
            DB::rollback();
            logger("Error al guardar comprobante nota:", ["error" => $e->getMessage()]);
            //throw $e; // Relanza la excepción si necesitas propagarla
            $this->dispatch("error-guardando-comprobante-nota", "Error al guardar comprobante nota" . "<br>" . $e->getMessage());
            $this->addError("error_guardar", $e->getMessage());
        }
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
            Column::make("Estado Reporte", "estado_reporte")
                ->sortable(),
            Column::make("Id", "id")
                ->hideIf(true),
            DateColumn::make('Fecha Emision', 'fechaEmision')
                ->sortable()
                ->inputFormat('Y-m-d H:i:s')
                ->outputFormat('d-m-Y')
                ->emptyValue('No Disponible'),
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
            Column::make("CodCliente", "cliente_id")
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
            Column::make("TipoDoc", "tipoDoc")
                ->hideIf(true),
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
            Column::make("Estado Reporte", "estado_reporte")
                ->sortable(),
            Column::make("Estado Cpe Sunat", "estado_cpe_sunat")
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
