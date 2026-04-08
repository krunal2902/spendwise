<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Generate a CSV file download from a collection or query.
     *
     * @param string $filename
     * @param array $headers
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Support\Collection $data
     * @param callable $mapRow A callback function to format a data row into an array.
     * @return StreamedResponse
     */
    public function exportToCsv(string $filename, array $headers, $data, callable $mapRow): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($headers, $data, $mapRow) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write headers
            fputcsv($handle, $headers);

            // Chunk data if it's a query builder to save memory
            if ($data instanceof \Illuminate\Database\Eloquent\Builder || $data instanceof \Illuminate\Database\Query\Builder) {
                $data->chunk(500, function ($chunk) use ($handle, $mapRow) {
                    foreach ($chunk as $item) {
                        fputcsv($handle, $mapRow($item));
                    }
                });
            } else {
                // If it's a Collection or array
                foreach ($data as $item) {
                    fputcsv($handle, $mapRow($item));
                }
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * Generate a PDF download from a Blade view.
     *
     * @param string $viewPath
     * @param array $data
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public function exportToPdf(string $viewPath, array $data, string $filename)
    {
        $pdf = Pdf::loadView($viewPath, $data);
        
        return $pdf->download($filename);
    }
}
