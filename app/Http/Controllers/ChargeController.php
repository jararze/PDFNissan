<?php

namespace App\Http\Controllers;

use App\Exports\ChargesExport;
use App\Services\ChargeProcessorService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ChargeController extends Controller
{
    public function __construct(
        private ChargeProcessorService $processor
    ) {}

    /**
     * Página principal con formulario de upload
     */
    public function index()
    {
        $pdfDirectory = public_path('pdf/');
        $hasPendingFiles = false;
        $pendingCount = 0;

        if (is_dir($pdfDirectory)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($pdfDirectory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && strtolower($file->getExtension()) === 'pdf') {
                    $pendingCount++;
                }
            }
            $hasPendingFiles = $pendingCount > 0;
        }

        return view('charges.index', [
            'hasPendingFiles' => $hasPendingFiles,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Sube y extrae un archivo ZIP
     */
    public function upload(Request $request)
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:102400', // 100MB max
        ], [
            'zip_file.required' => 'Debes seleccionar un archivo ZIP.',
            'zip_file.mimes' => 'El archivo debe ser un ZIP.',
            'zip_file.max' => 'El archivo no puede superar los 100MB.',
        ]);

        try {
            $file = $request->file('zip_file');
            $zipPath = $file->storeAs('temp', 'upload_' . time() . '.zip');
            $fullZipPath = storage_path('app/' . $zipPath);

            $result = $this->processor->extractZip($fullZipPath);

            return redirect()->route('charges.index')
                ->with('success', "ZIP extraído correctamente. {$result['files_count']} archivos PDF encontrados.");

        } catch (\Exception $e) {
            return redirect()->route('charges.index')
                ->with('error', 'Error al extraer ZIP: ' . $e->getMessage());
        }
    }

    /**
     * Procesa los PDFs y muestra resultados
     */
    public function generate()
    {
        $directoryPath = public_path('pdf/');

        if (!is_dir($directoryPath)) {
            return redirect()->route('charges.index')
                ->with('warning', 'No existe el directorio de PDFs. Sube un archivo ZIP primero.');
        }

        try {
            $results = $this->processor->process($directoryPath);

            if ($results['totals']['total'] === 0) {
                return redirect()->route('charges.index')
                    ->with('warning', 'No se encontraron archivos PDF para procesar.');
            }

            return view('charges.results', $results);

        } catch (\Exception $e) {
            return redirect()->route('charges.index')
                ->with('error', 'Error al procesar: ' . $e->getMessage());
        }
    }

    /**
     * Lista todas las facturas procesadas
     */
    public function list(Request $request)
    {
        $query = \App\Models\Charge::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('RAZON_SOCIAL', 'like', "%{$search}%")
                    ->orWhere('NIT', 'like', "%{$search}%")
                    ->orWhere('FACTURA', 'like', "%{$search}%")
                    ->orWhere('CONCEPTO', 'like', "%{$search}%");
            });
        }

        if ($request->filled('mes')) {
            $query->where('MES', $request->input('mes'));
        }

        $charges = $query->orderBy('FECHA', 'desc')->paginate(20);

        return view('charges.list', [
            'charges' => $charges,
            'search' => $request->input('search'),
            'mes' => $request->input('mes'),
        ]);
    }

    /**
     * Exporta las facturas a Excel
     */
    public function export(Request $request)
    {
        $search = $request->input('search');
        $mes = $request->input('mes');

        $filename = 'facturas_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new ChargesExport($search, $mes), $filename);
    }
}
