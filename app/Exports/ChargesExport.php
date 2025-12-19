<?php

namespace App\Exports;

use App\Models\Charge;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class ChargesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    use Exportable;

    protected ?string $search;
    protected ?string $mes;

    public function __construct(?string $search = null, ?string $mes = null)
    {
        $this->search = $search;
        $this->mes = $mes;
    }

    public function query()
    {
        $query = Charge::query();

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('RAZON_SOCIAL', 'like', "%{$search}%")
                    ->orWhere('NIT', 'like', "%{$search}%")
                    ->orWhere('FACTURA', 'like', "%{$search}%")
                    ->orWhere('CONCEPTO', 'like', "%{$search}%");
            });
        }

        if ($this->mes) {
            $query->where('MES', $this->mes);
        }

        return $query->orderBy('FECHA', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'FACTURA',
            'FECHA',
            'MES',
            'APLICACIÓN',
            'RAZÓN SOCIAL',
            'MARCA',
            'CAMPAÑA',
            'GRUPO',
            'CONCEPTO',
            'CANTIDAD',
            'PRECIO UNIT. BS',
            'TOTAL BS',
            'TOTAL $US',
            'T/C',
            'CTAS. CONTABILIDAD',
            'CUENTA',
            'CUENTA 2',
            'CÓDIGO QUITER',
            'OBSERVACIONES',
            'NIT',
            'OBSERVACIONES 2',
            'USUARIO',
        ];
    }

    public function map($charge): array
    {
        return [
            $charge->id,
            $charge->FACTURA,
            $charge->FECHA ? Carbon::parse($charge->FECHA)->format('d/m/Y') : '',
            $charge->MES,
            $charge->APLICACION,
            $charge->RAZON_SOCIAL,
            $charge->MARCA,
            $charge->CAMPANIA,
            $charge->GRUPO,
            $charge->CONCEPTO,
            $charge->CANTIDAD_CONCEPTO,
            $charge->PRECIO_UNITARIO_EN_BS,
            $charge->BS,
            $charge->SUS,
            $charge->{'T/C'},
            $charge->CUENTAS_CONTABILIDAD,
            $charge->CUENTA,
            $charge->CUENTA2,
            $charge->CODIGO_QUITER,
            $charge->OBSERVACIONES,
            $charge->NIT,
            $charge->OBSERVACIONES2,
            $charge->USUARIO,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // Estilo del encabezado
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'], // Azul
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Altura del encabezado
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Bordes para toda la tabla
        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Alternar colores de filas (zebra)
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3F4F6'], // Gris claro
                    ],
                ]);
            }
        }

        // Alinear columnas numéricas a la derecha
        $numericColumns = ['K', 'L', 'M', 'N', 'O']; // CANTIDAD, PRECIOS, TOTALES, T/C
        foreach ($numericColumns as $col) {
            $sheet->getStyle("{$col}2:{$col}{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ]);
        }

        // Formato de moneda para columnas de dinero
        $sheet->getStyle("L2:N{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        // Congelar primera fila
        $sheet->freezePane('A2');

        return [];
    }

    public function title(): string
    {
        return 'Facturas';
    }
}
