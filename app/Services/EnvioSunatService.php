<?php

namespace App\Services;

use App\Models\FComprobanteSunat;
use Greenter\Report\XmlUtils;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class EnvioSunatService
{
    public function send(FComprobanteSunat $comprobante)
    {
        $this->xml($comprobante);
        $this->pdf($comprobante);

        $company = $comprobante->sede->empresa;

        $sunat = new SunatService;
        $see = $sunat->getSee($company);
        $invoice = $sunat->getInvoice($comprobante);
        $result = $see->send($invoice);

        $response['xml'] = $see->getFactory()->getLastXml();
        //$response['xml'] = $see->getXmlSigned($invoice);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);
        $path = 'invoices/' . $invoice->getName() . '.xml';

        $response['sunatResponse'] = $sunat->sunatResponse($result);
        if ($response['sunatResponse']['success']) {
            Storage::put('invoices/' . $invoice->getName() . '-CDR.zip', base64_decode($response['sunatResponse']["cdrZip"]));
            $zip = new ZipArchive;
            if ($zip->open(storage_path('app/private/invoices/' . $invoice->getName() . '-CDR.zip')) === true) {
                $zip->extractTo(storage_path('app/private/invoices/'));
                $zip->close();
            }

            $comprobante->update(['nombrexml' => $path, 'xmlbase64' => base64_encode(((string) $response['xml'])), 'hash' => $response['hash'], 'cdrbase64' => $response['sunatResponse']['cdrZip'], 'codigo_sunat' => $response['sunatResponse']['cdrResponse']['code'], 'mensaje_sunat' => $response['sunatResponse']['cdrResponse']['description'], 'obs' => $response['sunatResponse']['cdrResponse']['notes']]);
        } else {
            $comprobante->update(['nombrexml' => $path, 'xmlbase64' => base64_encode(((string) $response['xml'])), 'hash' => $response['hash'], 'codigo_sunat' => $response['sunatResponse']['error']['code'], 'mensaje_sunat' => $response['sunatResponse']['error']['message']]);
        }

        return $response;
    }

    public function xml(FComprobanteSunat $comprobante)
    {
        $company = $comprobante->sede->empresa;

        $sunat = new SunatService;
        $see = $sunat->getSee($company);
        $invoice = $sunat->getInvoice($comprobante);

        $response['xml'] = $see->getXmlSigned($invoice);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

        $path = 'invoices/' . $invoice->getName() . '.xml';
        Storage::put($path, $response['xml']);
        $comprobante->update(['nombrexml' => $path, 'hash' => $response['hash']]);
        return $response;
    }

    public function pdf(FComprobanteSunat $comprobante)
    {
        //dd($comprobante->sede->empresa);
        $company = $comprobante->sede->empresa;

        $sunat = new SunatService;
        $see = $sunat->getSee($company);
        $invoice = $sunat->getInvoice($comprobante);

        $response['xml'] = $see->getXmlSigned($invoice);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

        $sunat->generatePdfReport($invoice, $response['hash']); // genera y guarda el pdf de la factura
        return $sunat->getHtmlReport($invoice, $response['hash']); // solo genera HTML de la factura
    }
}
