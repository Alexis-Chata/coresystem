<?php

namespace App\Services;

use App\Models\Empresa;
use DateTime;
use Greenter\Api;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Driver;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Despatch\Vehicle;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Report\HtmlReport;
use Greenter\Report\PdfReport;
use Greenter\Report\Resolver\DefaultTemplateResolver;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Facades\Storage;

class SunatService
{
    public function getSee($company)
    {
        $see = new See();
        $see->setCertificate(Storage::get($company->cert_path));
        $see->setService($company->production ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);
        $see->setClaveSOL($company->ruc, $company->sol_user, $company->sol_pass);
        //dd($company->cert_path, $company->production, $company->ruc, $company->sol_user, $company->sol_pass);
        return $see;
    }

    public function getSeeApi($company)
    {
        $api = new Api($company->production ? [
            'auth' => 'https://api-seguridad.sunat.gob.pe/v1',
            'cpe' => 'https://api-cpe.sunat.gob.pe/v1',
        ] : [
            'auth' => 'https://gre-test.nubefact.com/v1',
            'cpe' => 'https://gre-test.nubefact.com/v1',
        ]);

        $api->setBuilderOptions(
            [
                'strict_variables' => true,
                'optimizations' => 0,
                'debug' => true,
                'cache' => false,
            ]
        )->setApiCredentials(
            $company->production ? $company->client_id : 'test-85e5b0ae-255c-4891-a595-0b98c65c9854',
            $company->production ? $company->client_secret : 'test-Hty/M6QshYvPgItX2P0+Kw=='
        )->setClaveSOL(
            $company->ruc,
            $company->production ? $company->sol_user : 'MODDATOS',
            $company->production ? $company->sol_pass : 'MODDATOS'
        )->setCertificate(Storage::get($company->cert_path));

        return $api;
    }

    public function getInvoice($data)
    {

        //dd($data->mtoOperGravadas, $data->mtoOperExoneradas, $data->mtoOperInafectas, $data->mtoOperExportacion, $data->mtoOperGratuitas);
        return (new Invoice())
            ->setUblVersion($data->ublVersion ?? '2.1')
            ->setTipoOperacion($data->tipoOperacion ?? null) // Venta - Catalog. 51
            ->setTipoDoc($data->tipoDoc ?? null) // Factura - Catalog. 01
            ->setSerie($data->serie ?? null)
            ->setCorrelativo($data->correlativo ?? null)
            ->setFechaEmision(new DateTime($data->fechaEmision ?? null)) // Zona horaria: Lima
            ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
            ->setTipoMoneda($data->tipoMoneda ?? "PEN") // Sol - Catalog. 02
            ->setCompany($this->getCompany($data))
            ->setClient($this->getClient($data))

            //Mto Operaciones
            ->setMtoOperGravadas($data->mtoOperGravadas ?? null)
            ->setMtoOperExoneradas($data->mtoOperExoneradas ?? null)
            ->setMtoOperInafectas($data->mtoOperInafectas ?? null)
            ->setMtoOperExportacion($data->mtoOperExportacion ?? null)
            ->setMtoOperGratuitas($data->mtoOperGratuitas ?? null)

            //Impuestos
            ->setMtoIGV($data->mtoIGV)
            ->setMtoIGVGratuitas($data->mtoIGVGratuitas)
            ->setIcbper($data->icbper)
            ->setTotalImpuestos($data->totalImpuestos)

            //Totales
            ->setValorVenta($data->valorVenta)
            ->setSubTotal($data->subTotal)
            ->setRedondeo($data->redondeo)
            ->setMtoImpVenta($data->mtoImpVenta)

            //Productos
            ->setDetails($this->getDetails($data->detalle))

            //Leyendas
            ->setLegends($this->getLegends([
                [
                    'code' => $data->legendsCode, // '1000'
                    'value' => $data->legendsValue
                ]
            ]));
    }

    public function getNote($data)
    {
        return (new Note())
            ->setUblVersion($data->ublVersion ?? '2.1')
            ->setTipoDoc($data->tipoDoc ?? null) // Factura - Catalog. 01
            ->setSerie($data->serie ?? null)
            ->setCorrelativo($data->correlativo ?? null)
            ->setFechaEmision(new DateTime($data->fechaEmision ?? null)) // Zona horaria: Lima
            ->setTipDocAfectado($data->tipDocAfectado ?? null) // DNI - Catalog. 06
            ->setNumDocfectado($data->numDocfectado ?? null)
            ->setCodMotivo($data->codMotivo ?? null) // Catalog. 04
            ->setDesMotivo($data->desMotivo ?? null)
            ->setTipoMoneda($data->tipoMoneda ?? null) // Sol - Catalog. 02
            ->setCompany($this->getCompany($data))
            ->setClient($this->getClient($data))

            //Mto Operaciones
            ->setMtoOperGravadas($data->mtoOperGravadas ?? null)
            ->setMtoOperExoneradas($data->mtoOperExoneradas ?? null)
            ->setMtoOperInafectas($data->mtoOperInafectas ?? null)
            ->setMtoOperExportacion($data->mtoOperExportacion ?? null)
            ->setMtoOperGratuitas($data->mtoOperGratuitas ?? null)

            //Impuestos
            ->setMtoIGV($data->mtoIGV)
            ->setMtoIGVGratuitas($data->mtoIGVGratuitas)
            ->setIcbper($data->icbper)
            ->setTotalImpuestos($data->totalImpuestos)

            //Totales
            ->setValorVenta($data->valorVenta)
            ->setSubTotal($data->subTotal)
            ->setRedondeo($data->redondeo)
            ->setMtoImpVenta($data->mtoImpVenta)

            //Productos
            ->setDetails($this->getDetails($data->detalle))

            //Leyendas
            ->setLegends($this->getLegends([
                [
                    'code' => $data->legendsCode, // '1000'
                    'value' => $data->legendsValue
                ]
            ]));
    }

    public function getDespatch($data)
    {
        return (new Despatch)
            ->setVersion($data->version ?? '2022')
            ->setTipoDoc($data->tipoDoc ?? '09') // Guia de Remision - Catalog. 09
            ->setSerie($data->serie ?? null)
            ->setCorrelativo($data->correlativo ?? null)
            ->setFechaEmision(new DateTime($data->fechaEmision ?? null)) // Zona horaria: Lima
            ->setCompany($this->getCompany($data))
            ->setDestinatario($this->getClient($data))
            ->setEnvio($this->getEnvio($data))
            ->setDetails($this->getDespatchDetails($data->detalle));;
    }

    public function getEnvio($data)
    {
        $shipment = (new Shipment())
            ->setCodTraslado($data->codTraslado ?? null) // Catalog. 20 ( '01' - Venta | '14' - Venta sujeta a confirmación del comprador )
            ->setDesTraslado($data->desTraslado ?? null) // Catalog. 20 ( '01' - Venta | '14' - Venta sujeta a confirmación del comprador )
            ->setModTraslado($data->modTraslado ?? null) // Catalog. 18 ( '01' - Transporte PUBLICO | '02' - Transporte PRIVADO )
            ->setFecTraslado(new DateTime($data->fecTraslado ?? null))
            ->setPesoTotal($data->pesoTotal ?? null)
            ->setUndPesoTotal($data->undPesoTotal ?? null)
            ->setLlegada(new Direction($data->llegadaUbigeo, $data->llegadaDireccion))
            ->setPartida(new Direction($data->partidaUbigeo, $data->partidaDireccion));

        if ($data->modTraslado == '01') { // Transporte PUBLICO (Empresa aparte que debe de tener un codigo de autorizacion por el ministerio de transporte "nroMtc")
            $shipment->setTransportista($this->getTransportista($data));
        }

        if ($data->modTraslado == '02') { // Transporte PRIVADO (Cuando transportas con tu propia movilidad)
            $shipment->setVehiculo($this->getVehiculo($data));
            $shipment->setChoferes($this->getChoferes($data));
        }

        return $shipment;
    }

    public function getTransportista($data)
    {
        return (new Transportist)
            ->setTipoDoc($data->transportista_tipoDoc ?? null)
            ->setNumDoc($data->transportista_numDoc ?? null)
            ->setRznSocial($data->transportista_rznSocial ?? null)
            ->setNroMtc($data->transportista_nroMtc ?? null)
        ;
    }

    public function getVehiculo($data)
    {
        $vehiculo = (new Vehicle())
            ->setPlaca($data->vehiculo_placa ?? null);

        // ESTA PENDIENTE POR IMPLEMENTAR ( VEHIVULO SECUNDARIO )
        // $vehiculoSecundario = (new Vehicle())
        // ->setPlaca($data->placaSecundaria ?? null)
        // ;

        // $vehiculo->setSecundarios([$vehiculoSecundario]);

        return $vehiculo;
    }

    public function getChoferes($data)
    {
        $drivers = [];
        // ESTA PENDIENTE POR IMPLEMENTAR ( CHOFERES SECUNDARIO )

        $drivers[] = (new Driver)
            ->setTipo('Principal')
            ->setTipoDoc($data->chofer_tipoDoc ?? null)
            ->setNroDoc($data->chofer_nroDoc ?? null)
            ->setLicencia($data->chofer_licencia ?? null)
            ->setNombres($data->chofer_nombres ?? null)
            ->setApellidos($data->chofer_apellidos ?? "...");
        return $drivers;
    }

    public function getDespatchDetails($details){
        $green_details = [];

        foreach ($details as $detail) {
            $green_details[] = (new DespatchDetail())
            ->setCantidad($detail->cantidad)
            ->setUnidad($detail->unidad)
            ->setDescripcion($detail->descripcion)
            ->setCodigo($detail->codigo)
            ;
        }

        return $green_details;
    }

    public function getCompany($company)
    {
        return (new Company())
            ->setRuc($company->companyRuc ?? null)
            ->setRazonSocial($company->companyRazonSocial ?? null)
            ->setNombreComercial($company->companyNombreComercial ?? null)
            ->setAddress($this->getAddress($company) ?? null);
    }

    public function getClient($client)
    {
        return (new Client())
            ->setTipoDoc($client->clientTipoDoc ?? null) // DNI - Catalog. 06
            ->setNumDoc($client->clientNumDoc ?? null)
            ->setRznSocial($client->clientRazonSocial ?? null)
            ->setAddress((new Address())->setDireccion($client->clientDireccion) ?? null);
    }

    public function getAddress($address)
    {
        return (new Address())
            ->setUbigueo($address->companyAddressUbigueo ?? null)
            ->setDepartamento($address->companyAddressDepartamento ?? null)
            ->setProvincia($address->companyAddressProvincia ?? null)
            ->setDistrito($address->companyAddressDistrito ?? null)
            ->setUrbanizacion($address->companyAddressUrbanizacion ?? null)
            ->setDireccion($address->companyAddressDireccion ?? null)
            ->setCodLocal($address->companyAddressCodLocal ?? "0000"); // Codigo de establecimiento asignado por SUNAT, 0000 por defecto.

    }

    public function getDetails($details)
    {
        $green_details = [];

        foreach ($details as $detail) {
            $green_details[] = (new SaleDetail())
                ->setCodProducto($detail->codProducto ?? null)
                ->setUnidad($detail->unidad ?? null) // Unidad - Catalog. 03
                ->setCantidad($detail->cantidad ?? null)
                ->setMtoValorUnitario($detail->mtoValorUnitario ?? null)
                ->setMtoValorGratuito($detail->mtoValorGratuito ?? null)
                ->setDescripcion($detail->descripcion ?? null)
                ->setMtoBaseIgv($detail->mtoBaseIgv ?? null)
                ->setPorcentajeIgv($detail->porcentajeIgv ?? null) // 18%
                ->setIgv($detail->igv ?? null)
                ->setFactorIcbper($detail->factorIcbper ?? null) // 0.3%
                ->setIcbper($detail->icbper ?? null)
                ->setTipAfeIgv($detail->tipAfeIgv ?? null) // Gravado Op. Onerosa - Catalog. 07
                ->setTotalImpuestos($detail->totalImpuestos ?? null) // Suma de impuestos en el detalle
                ->setMtoValorVenta($detail->mtoValorVenta ?? null)
                ->setMtoPrecioUnitario($detail->mtoPrecioUnitario ?? null);
        }
        return $green_details;
    }

    public function getLegends($legends)
    {
        $green_legends = [];

        foreach ($legends as $legend) {
            $green_legends[] = (new Legend())
                ->setCode($legend['code'] ?? null) // Monto en letras - Catalog. 52
                ->setValue($legend['value'] ?? null);
        }

        return $green_legends;
    }

    public function sunatResponse($result)
    {

        $response['success'] = $result->isSuccess();

        // Verificamos que la conexión con SUNAT fue exitosa.
        if (!$response['success']) {

            $response['error'] = [
                'code' => $result->getError()->getCode(),
                'message' => $result->getError()->getMessage()
            ];

            return $response;
        }

        $response['cdrZip'] = base64_encode($result->getCdrZip());

        $cdr = $result->getCdrResponse();

        $response['cdrResponse'] = [
            'code' => (int)$cdr->getCode(),
            'description' => $cdr->getDescription(),
            'notes' => $cdr->getNotes()
        ];

        return $response;
    }

    public function getHtmlReport($invoice, $hash)
    {
        $report = new HtmlReport();

        $resolver = new DefaultTemplateResolver();
        $report->setTemplate($resolver->getTemplate($invoice));

        $ruc = $invoice->getCompany()->getRuc();
        $company = Empresa::where('ruc', $ruc)->first();

        $params = [
            'system' => [
                'logo' => Storage::get($company->logo_path), // Logo de Empresa
                'hash' => $hash, // Valor Resumen
            ],
            'user' => [
                //'header'     => 'Telf: <b>(01) 123375</b>', // Texto que se ubica debajo de la dirección de empresa
                'extras'     => [
                    // Leyendas adicionales
                    ['name' => 'CONDICION DE PAGO', 'value' => 'Contado'],
                    //['name' => 'VENDEDOR', 'value' => 'GITHUB SELLER'],
                ],
                //'footer' => '<p>Nro Resolucion: <b>0180050000932/SUNAT</b></p>'
            ]
        ];

        return $report->render($invoice, $params);
    }

    public function generatePdfReport($invoice, $hash)
    {
        $htmlReport = new HtmlReport();

        $resolver = new DefaultTemplateResolver();
        $htmlReport->setTemplate($resolver->getTemplate($invoice));

        $report = new PdfReport($htmlReport);
        // Options: Ver mas en https://wkhtmltopdf.org/usage/wkhtmltopdf.txt
        $report->setOptions([
            'no-outline',
            'viewport-size' => '1280x1024',
            'page-width' => '21cm',
            'page-height' => '29.7cm',
        ]);
        $report->setBinPath(env('WKHTML_PDF_PATH'));

        $ruc = $invoice->getCompany()->getRuc();
        $company = Empresa::where('ruc', $ruc)->first();

        $params = [
            'system' => [
                'logo' => Storage::get($company->logo_path), // Logo de Empresa
                'hash' => $hash, // Valor Resumen
            ],
            'user' => [
                //'header'     => 'Telf: <b>(01) 123375</b>', // Texto que se ubica debajo de la dirección de empresa
                'extras'     => [
                    // Leyendas adicionales
                    ['name' => 'CONDICION DE PAGO', 'value' => 'Contado'],
                    //['name' => 'VENDEDOR', 'value' => 'SELLER'],
                ],
                //'footer' => '<p>Nro Resolucion: <b>0180050000932/SUNAT</b></p>'
            ]
        ];

        $pdf = $report->render($invoice, $params);

        $path = 'invoices/' . $invoice->getName() . '.pdf';
        Storage::put($path, $pdf);
    }
}
