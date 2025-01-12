<?php

namespace App\Services;

use App\Models\FComprobanteSunat;
use Greenter\Report\XmlUtils;

class EnvioSunatService
{
    public function send(FComprobanteSunat $comprobante)
    {
        $company = $comprobante->sede->empresa;

        $sunat = new SunatService;
        $see = $sunat->getSee($company);
        $invoice = $sunat->getInvoice($comprobante);
        $result = $see->send($invoice);

        $response['xml'] = $see->getFactory()->getLastXml();
        //$response['xml'] = $see->getXmlSigned($invoice);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

        $response['sunatResponse'] = $sunat->sunatResponse($result);

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

        $sunat->generatePdfReport($invoice, $response['hash']);
        return $sunat->getHtmlReport($invoice, $response['hash']);
    }
}
