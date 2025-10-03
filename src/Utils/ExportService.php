<?php

//composer require mpdf/mpdf
//composer require phpoffice/phpspreadsheet

namespace App\Utils;

use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExportService
{
    /**
     * Exporta los datos a PDF.
     */
    public function exportToPdf(string $title, array $data, array $headers = []): Response
    {
        $mpdf = new Mpdf();

        // Definir pie de página con número de página actual y total
        $mpdf->setFooter('Página {PAGENO} de {nb}');

        $html = '<h2>' . htmlspecialchars($title) . '</h2><table border="1" cellpadding="5" cellspacing="0"><thead><tr>';

        // Encabezados
        if (empty($headers) && !empty($data)) {
            $headers = array_keys($data[0]);
        }

        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        // Datos
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . htmlspecialchars($value ?? '') . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        $mpdf->WriteHTML($html);

        return new Response(
            $mpdf->Output('', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . time() . '.pdf"',
            ]
        );
    }


    /**
     * Exporta los datos a Excel.
     */
public function exportToExcel(string $title, array $data, array $headers = []): StreamedResponse
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    if (empty($headers) && !empty($data)) {
        $headers = [];
        $numColumns = count($data[0]);
        for ($i = 0; $i < $numColumns; $i++) {
            $headers[] = 'Col ' . ($i + 1);
        }
    }

    // Escribir encabezados
    foreach ($headers as $colIndex => $header) {
        $cell = Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
        $sheet->setCellValue($cell, $header);
    }

    // Escribir datos (usar array_values para evitar keys string)
    foreach ($data as $rowIndex => $row) {
        $values = array_values($row);
        foreach ($values as $colIndex => $value) {
            $cell = Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 2);
            $sheet->setCellValue($cell, $value ?? '');
        }
    }

    return new StreamedResponse(function () use ($spreadsheet) {
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="' . time() . '.xlsx"',
        'Cache-Control' => 'max-age=0',
    ]);
}


}
