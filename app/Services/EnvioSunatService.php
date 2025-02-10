<?php

namespace App\Services;

use App\Models\FComprobanteSunat;
use App\Models\FGuiaSunat;
use Greenter\Report\XmlUtils;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class EnvioSunatService
{
    public function send(FComprobanteSunat|FGuiaSunat $comprobante)
    {
        $this->xml($comprobante);
        //$this->pdf($comprobante);

        $company = $comprobante->sede->empresa;

        $sunat = new SunatService;
        //$see = $sunat->getSee($company);

        $invoice = match ($comprobante->tipoDoc) {
            "01", "03" => $sunat->getInvoice($comprobante),
            "07", "08" => $sunat->getNote($comprobante),
            "09" => $sunat->getDespatch($comprobante),
            default => null,
        };
        if ($invoice === null) {
            return;
        }

        $see = match ($comprobante->tipoDoc) {
            "09" => $sunat->getSeeApi($company),
            default => $sunat->getSee($company),
        };

        $result = $see->send($invoice);

        if ($comprobante->tipoDoc === "09") {
            $ticket = $result->getTicket();
            $result = $see->getStatus($ticket);
            $response['xml'] = $see->getLastXml();
        }
        else{
            $response['xml'] = $see->getFactory()->getLastXml();
        }

        //$response['xml'] = $see->getXmlSigned($invoice);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);
        $response['sunatResponse'] = $sunat->sunatResponse($result);

        $path = 'invoices/' . $invoice->getName() . '.xml';
        if ($response['sunatResponse']['success']) {
            $path_cdrzip = 'invoices/' . $invoice->getName() . '-CDR.zip';
            Storage::put($path_cdrzip, base64_decode($response['sunatResponse']["cdrZip"]));
            $zip = new ZipArchive;
            if ($zip->open(storage_path('app/private/' . $path_cdrzip)) === true) {
                $zip->extractTo(storage_path('app/private/invoices/'));
                $zip->close();
            }
            $path_cdrxml = 'invoices/R-' . $invoice->getName() . '.xml';
            $comprobante->update(['nombrexml' => $path, 'xmlbase64' => base64_encode(((string) $response['xml'])), 'hash' => $response['hash'], 'cdrxml' => $path_cdrxml, 'cdrbase64' => $response['sunatResponse']['cdrZip'], 'codigo_sunat' => $response['sunatResponse']['cdrResponse']['code'], 'mensaje_sunat' => $response['sunatResponse']['cdrResponse']['description'], 'obs' => $response['sunatResponse']['cdrResponse']['notes']]);
        } else {
            $comprobante->update(['nombrexml' => $path, 'xmlbase64' => base64_encode(((string) $response['xml'])), 'hash' => $response['hash'], 'codigo_sunat' => $response['sunatResponse']['error']['code'], 'mensaje_sunat' => $response['sunatResponse']['error']['message']]);
        }

        return $response;
    }

    public function xml(FComprobanteSunat|FGuiaSunat $comprobante)
    {
        $company = $comprobante->sede->empresa;

        $sunat = new SunatService;
        $see = $sunat->getSee($company);

        $invoice = match ($comprobante->tipoDoc) {
            "00", "01", "03" => $sunat->getInvoice($comprobante),
            "07", "08" => $sunat->getNote($comprobante),
            "09" => $sunat->getDespatch($comprobante),
            default => null,
        };
        if ($invoice === null) {
            return;
        }

        $response['xml'] = $see->getXmlSigned($invoice);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

        $path = 'invoices/' . $invoice->getName() . '.xml';
        Storage::put($path, $response['xml']);
        $comprobante->update(['nombrexml' => $path, 'hash' => $response['hash']]);
        return $response;
    }

    public function pdf(FComprobanteSunat|FGuiaSunat $comprobante)
    {
        $this->xml($comprobante);
        //dd($comprobante->sede->empresa);
        $company = $comprobante->sede->empresa;

        $sunat = new SunatService;
        $see = $sunat->getSee($company);

        $invoice = match ($comprobante->tipoDoc) {
            "00", "01", "03" => $sunat->getInvoice($comprobante),
            "07", "08" => $sunat->getNote($comprobante),
            "09" => $sunat->getDespatch($comprobante),
            default => null,
        };
        if ($invoice === null) {
            return;
        }

        $response['xml'] = $see->getXmlSigned($invoice);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

        $sunat->generatePdfReport($invoice, $response['hash']); // genera y guarda el pdf de la factura
        return $sunat->getHtmlReport($invoice, $response['hash']); // solo genera HTML de la factura
    }
}
