<?php

namespace App\Services;

use App\Models\Charge;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use SplFileInfo;
use ZipArchive;

class ChargeProcessorService
{
    private Parser $parser;
    private float $exchangeRate;
    private string $invalidPdfPath;

    public function __construct()
    {
        $this->parser = new Parser();
        $this->exchangeRate = config('charges.exchange_rate', 6.96);
        $this->invalidPdfPath = public_path('pdf_no_validos/');
    }

    /**
     * Extrae un archivo ZIP al directorio de PDFs
     */
    public function extractZip(string $zipPath): array
    {
        $zip = new ZipArchive();
        $extractPath = public_path('pdf/');

        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0755, true);
        }

        if ($zip->open($zipPath) !== true) {
            throw new \Exception("No se pudo abrir el archivo ZIP: {$zipPath}");
        }

        $extractedFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'pdf') {
                $extractedFiles[] = $filename;
            }
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Eliminar el ZIP después de extraer
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        return [
            'success' => true,
            'files_count' => count($extractedFiles),
            'files' => $extractedFiles,
        ];
    }

    /**
     * Procesa todos los PDFs en el directorio
     */
    public function process(string $directoryPath): array
    {
        $results = [
            'totals' => [
                'total' => 0,
                'procesados' => 0,
                'eliminados' => 0,
                'existentes' => 0,
                'omitidos' => 0,
                'movidos_no_validos' => 0,
            ],
            'fileCount' => [],
            'data' => [],
            'errors' => [],
            'no_validos' => [],
        ];

        if (!is_dir($directoryPath)) {
            return $results;
        }

        // Crear carpeta para PDFs no válidos si no existe
        if (!is_dir($this->invalidPdfPath)) {
            mkdir($this->invalidPdfPath, 0755, true);
        }

        $files = $this->collectPdfFiles($directoryPath);

        foreach ($files as $file) {
            $dirName = $file->getPath();
            $results['fileCount'][$dirName] ??= [
                'total' => 0,
                'procesados' => 0,
                'eliminados' => 0,
                'no_validos' => 0,
            ];
            $results['fileCount'][$dirName]['total']++;
            $results['totals']['total']++;

            try {
                $result = $this->processFile($file);

                if ($result === 'exists') {
                    $results['totals']['existentes']++;
                    // Eliminar el archivo si ya existe en BD
                    $this->deleteFile($file->getRealPath());
                    $results['fileCount'][$dirName]['eliminados']++;
                    $results['totals']['eliminados']++;
                    continue;
                }

                if ($result === 'skipped') {
                    // Mover a carpeta de no válidos
                    $moved = $this->moveToInvalid($file);
                    if ($moved) {
                        $results['totals']['movidos_no_validos']++;
                        $results['fileCount'][$dirName]['no_validos']++;
                        $results['no_validos'][] = $file->getFilename();
                    }
                    $results['totals']['omitidos']++;
                    continue;
                }

                $results['data'][] = $result;
                $results['fileCount'][$dirName]['procesados']++;
                $results['totals']['procesados']++;

                if ($this->deleteFile($file->getRealPath())) {
                    $results['fileCount'][$dirName]['eliminados']++;
                    $results['totals']['eliminados']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'file' => $file->getRealPath(),
                    'error' => $e->getMessage(),
                ];
                Log::error("Error procesando PDF: {$file->getRealPath()}", [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->cleanEmptyDirectories($directoryPath);

        return $results;
    }

    /**
     * Mueve un archivo a la carpeta de PDFs no válidos
     */
    private function moveToInvalid(SplFileInfo $file): bool
    {
        $sourcePath = $file->getRealPath();
        $relativePath = str_replace(public_path('pdf/'), '', $file->getPath());

        // Crear subdirectorio si es necesario
        $targetDir = $this->invalidPdfPath . $relativePath;
        if (!empty($relativePath) && !is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $this->invalidPdfPath . $relativePath . '/' . $file->getFilename();
        $targetPath = str_replace('//', '/', $targetPath);

        // Si ya existe un archivo con ese nombre, agregar timestamp
        if (file_exists($targetPath)) {
            $pathInfo = pathinfo($targetPath);
            $targetPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . time() . '.' . $pathInfo['extension'];
        }

        if (rename($sourcePath, $targetPath)) {
            Log::info("PDF no válido movido: {$sourcePath} -> {$targetPath}");
            return true;
        }

        Log::warning("No se pudo mover PDF no válido: {$sourcePath}");
        return false;
    }

    /**
     * Recolecta todos los archivos PDF del directorio
     */
    private function collectPdfFiles(string $directoryPath): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directoryPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'pdf') {
                $files[] = clone $file;
            }
        }

        return $files;
    }

    /**
     * Procesa un archivo PDF individual
     */
    private function processFile(SplFileInfo $file): string|Charge
    {
        $filePath = $file->getRealPath();
        $relativeFilePath = str_replace(public_path(), '', $filePath);

        if (Charge::where('RUTA', $relativeFilePath)->exists()) {
            Log::info("Factura ya existe en BD: {$relativeFilePath}");
            return 'exists';
        }

        $pdf = $this->parser->parseFile($filePath);
        $text = $pdf->getText();

        if (strpos($text, 'RECEPCIÓNDEFACTURA') === false) {
            Log::info("PDF sin marca de recepción: {$relativeFilePath}");
            return 'skipped';
        }

        $lines = explode("\n", $text);

        return DB::transaction(fn() => $this->createChargeFromLines($lines, $relativeFilePath));
    }

    /**
     * Crea un registro Charge desde las líneas del PDF
     */
    private function createChargeFromLines(array $lines, string $relativeFilePath): Charge
    {
        $hasReferencia = isset($lines[14]) && strpos($lines[14], 'Referencia') !== false;
        $offset = $hasReferencia ? 0 : 1;

        $dateString = substr($lines[13 + $offset] ?? '', 0, -6);
        $date = Carbon::createFromFormat('d/m/Y', trim($dateString))->startOfDay();

        $mount = $this->extractMount($lines);
        $numericMount = floatval(str_replace(',', '', $mount));

        return Charge::firstOrCreate(
            ['RUTA' => $relativeFilePath],
            [
                'FACTURA' => preg_replace('/\D/', '', $lines[16 + $offset] ?? ''),
                'FECHA' => $date,
                'MES' => $date->translatedFormat('F'),
                'APLICACION' => '',
                'RAZON_SOCIAL' => trim($lines[10] ?? ''),
                'MARCA' => '',
                'CAMPANIA' => '',
                'GRUPO' => '',
                'CONCEPTO' => $this->extractConcepto($lines),
                'CANTIDAD_CONCEPTO' => 1,
                'PRECIO_UNITARIO_EN_BS' => $mount,
                'BS' => $mount,
                'SUS' => number_format($numericMount / $this->exchangeRate, 2),
                'T/C' => (string) $this->exchangeRate,
                'CUENTAS_CONTABILIDAD' => '',
                'CUENTA' => '',
                'CUENTA2' => '',
                'CODIGO_QUITER' => str_replace('Referencia: ', '', $lines[14 + $offset] ?? ''),
                'OBSERVACIONES' => '',
                'NIT' => trim($lines[12 + $offset] ?? ''),
                'OBSERVACIONES2' => str_replace('OBSERVACIONES: ', '', $lines[count($lines) - 4] ?? ''),
                'USUARIO' => str_replace('Usuario:', '', trim(end($lines))),
            ]
        );
    }

    /**
     * Extrae el concepto de las líneas del PDF
     */
    private function extractConcepto(array $lines): string
    {
        $concepto = '';
        for ($i = 23; $i < count($lines); $i++) {
            $lineParts = explode("\t", $lines[$i]);
            if (trim($lineParts[0]) === 'SON:') {
                break;
            }
            $concepto .= $lines[$i] . ' ';
        }
        return trim($concepto);
    }

    /**
     * Extrae el monto de las líneas del PDF
     */
    private function extractMount(array $lines): string
    {
        foreach ([5, 6, 7] as $offset) {
            $value = $lines[count($lines) - $offset] ?? '';
            if (preg_match('/^[0-9,.]+$/', trim($value))) {
                return trim($value);
            }
        }
        return '0';
    }

    /**
     * Elimina un archivo
     */
    private function deleteFile(string $filePath): bool
    {
        if (file_exists($filePath) && is_writable($filePath)) {
            return unlink($filePath);
        }
        Log::warning("No se pudo eliminar archivo: {$filePath}");
        return false;
    }

    /**
     * Limpia directorios vacíos
     */
    private function cleanEmptyDirectories(string $directoryPath): void
    {
        if (!is_dir($directoryPath)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directoryPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $files = glob($file->getPathname() . '/*');
                if (empty($files)) {
                    @rmdir($file->getPathname());
                }
            }
        }
    }
}
