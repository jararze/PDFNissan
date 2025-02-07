<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use Carbon\Carbon;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Smalot\PdfParser\Parser;

class ChargeController extends Controller
{

    public function index()
    {
        return view('control.indez');
    }

    /**
     * Display a listing of the resource.
     */
    public function generate()
    {
        $directoryPath = public_path('pdf/');

        if (!is_dir($directoryPath)) {
            abort(500, 'Directory not found');
        }

        $dirIterator = new RecursiveDirectoryIterator($directoryPath);
        $iterator = new RecursiveIteratorIterator($dirIterator);

        $parser = new Parser();
        $data = [];
        $fileCount = [];
        $totals = ['total' => 0, 'procesados' => 0, 'eliminados' => 0];

        foreach ($iterator as $file) {
            $dirName = $file->getPath();
            if ($file->isFile() && strtolower($file->getExtension()) === "pdf") {
                if (!isset($fileCount[$dirName])) {
                    $fileCount[$dirName] = ['total' => 0, 'procesados' => 0, 'eliminados' => 0];
                }
                $fileCount[$dirName]['total']++;
                $totals['total']++;
            }
        }

        foreach ($iterator as $file) {
            $dirName = $file->getPath();
            if ($file->isFile() && strtolower($file->getExtension()) === "pdf") {
                $filePath = $file->getRealPath();
                $relativeFilePath = str_replace(public_path(), '', $filePath);
                if (!file_exists($filePath)) {
                    abort(500, 'File not found : '.$filePath);
                }

                $existsInDatabase = Charge::where('RUTA', $relativeFilePath)->exists();

                if (!$existsInDatabase) {
                    $pdf = $parser->parseFile($filePath);
                    $text = $pdf->getText();


                    if (strpos($text, 'RECEPCIÓNDEFACTURA') !== false) {
                        $lines = explode("\n", $text);
                        $data[] = $this->getDataFromLines($lines, $relativeFilePath);
                        $fileCount[$dirName]['procesados']++;
                        $totals['procesados']++;
                        if (is_writable($filePath)) {
                            unlink($filePath);
                            $fileCount[$dirName]['eliminados']++;
                            $totals['eliminados']++;
                        } else {
                            abort(500, 'File cannot be deleted : '.$filePath);
                        }
                    }
                }else{
                    echo "la factura existe en la base de datos ". $relativeFilePath . "<br>";
                }


            }
        }

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $files = glob($file->getPathname().'/*');
                if (empty($files)) {
                    rmdir($file->getPathname());
                }
            }
        }

        return view('index', ['data' => $data, 'fileCount' => $fileCount, 'totals' => $totals]);

    }

    private function getPossibleMount($lines, $positions)
    {
        foreach ($positions as $position) {
            $possible_mount = array_slice($lines, -$position, 1);
            if (preg_match("/^[0-9,.]+$/", $possible_mount[0])) {
                return $possible_mount;
            }
        }
        return [0];
    }

    private function getDataFromLines($lines, $relativeFilePath)
    {
        $stringConcat = '';
        for ($i = 23; $i < count($lines); $i++) {
            $lineParts = explode("\t", $lines[$i]);
            if (trim($lineParts[0]) === 'SON:') {
                break;
            }
            $stringConcat .= $lines[$i]." ";
        }
        $dateSubstring = '';

        if (strpos($lines[14], 'Referencia') !== false) {
            $dateSubstring = substr($lines[13], 0, -6);
            $facturaMonto = preg_replace('/\D/', '', $lines[16]);
            $codigoQuiter = $lines[14];
            $nit = $lines[12];
            $razon_social = $lines[10];
        } else {
            $dateSubstring = substr($lines[14], 0, -6);
            $facturaMonto = preg_replace('/\D/', '', $lines[17]);
            $codigoQuiter = $lines[15];
            $nit = $lines[13];
            $razon_social = $lines[10];
        }
        $date = Carbon::createFromFormat('d/m/Y', $dateSubstring)->startOfDay();

        setlocale(LC_TIME, 'Spanish');
        $monthInSpanish = $date->formatLocalized('%B');

        $positions = [5, 6, 7];

        $mount = $this->getPossibleMount($lines, $positions);
        $us = str_replace(",", "", end($mount));

        $slicedArray = array_slice($lines, -4, 1);
        $observations = str_replace('OBSERVACIONES: ', '', end($slicedArray));

        return Charge::firstOrCreate(
            ['RUTA' => $relativeFilePath],
            [
                'FACTURA' => $facturaMonto,
                'FECHA' => $date,
                'MES' => $monthInSpanish,
                'APLICACION' => '',
                'RAZON_SOCIAL' => $razon_social,
                'MARCA' => '',
                'CAMPANIA' => '',
                'GRUPO' => '',
                'CONCEPTO' => $stringConcat,
                'CANTIDAD_CONCEPTO' => 1,
                'PRECIO_UNITARIO_EN_BS' => end($mount),
                'BS' => end($mount),
                'SUS' => number_format($us / 6.96, 2),
                'T/C' => '6.96',
                'CUENTAS_CONTABILIDAD' => '',
                'CUENTA' => '',
                'CUENTA2' => '',
                'CODIGO_QUITER' => str_replace("Referencia: ", "", $codigoQuiter),
                'OBSERVACIONES' => '',
                'NIT' => $nit,
                'OBSERVACIONES2' => $observations,
                'USUARIO' => str_replace("Usuario:", "", end($lines)),
                'RUTA' => $relativeFilePath,
            ]);
    }
}
