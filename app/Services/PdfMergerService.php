<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

class PdfMergerService
{
    public function merge(array $archivos, string $rutaSalida): string
    {
        $pdf = new Fpdi();

        foreach ($archivos as $archivo) {
            $paginas = $pdf->setSourceFile($archivo);
            for ($i = 1; $i <= $paginas; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }
        }

        $pdf->Output('F', $rutaSalida);
        // Verifica si el archivo se creó correctamente
        if (!file_exists($rutaSalida)) {
            throw new \Exception("Error al crear el archivo PDF: $rutaSalida");
        }
        // Retorna la ruta del archivo PDF generado
        return $rutaSalida;
    }
}
