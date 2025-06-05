<?php

namespace App\Exports;

use App\Services\ReportesService;
use App\DTOs\Reportes\ResumenPreguntaDto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Para ajustar automáticamente el ancho de las columnas
use Illuminate\Support\Collection;

class ResumenPreguntasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected int $idEncuesta;
    protected array $filtros;
    protected ReportesService $reportesService;

    // Para manejar un número variable de opciones como columnas
    protected array $maxOpcionesHeaders = [];
    protected bool $headersCalculados = false;

    public function __construct(int $idEncuesta, array $filtros = [])
    {
        $this->idEncuesta = $idEncuesta;
        $this->filtros = $filtros;
        $this->reportesService = app(ReportesService::class);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        $resumen = $this->reportesService->obtenerResumenPorPregunta($this->idEncuesta, $this->filtros);

        // Pre-calcular las cabeceras de opciones si no se han calculado
        if (!$this->headersCalculados && $resumen->isNotEmpty()) {
            $maxOpciones = 0;
            foreach ($resumen as $item) {
                if ($item->resumen_opciones && count($item->resumen_opciones) > $maxOpciones) {
                    $maxOpciones = count($item->resumen_opciones);
                }
            }
            for ($i = 1; $i <= $maxOpciones; $i++) {
                $this->maxOpcionesHeaders[] = "Opción {$i} Texto";
                $this->maxOpcionesHeaders[] = "Opción {$i} Conteo";
                $this->maxOpcionesHeaders[] = "Opción {$i} %";
            }
            $this->headersCalculados = true;
        }
        return $resumen;
    }

    public function headings(): array
    {
        // Recalcular cabeceras si la colección está vacía (FromCollection se llama antes que headings a veces)
        if (!$this->headersCalculados) {
            $this->collection(); // Llama a collection para asegurar que maxOpcionesHeaders se calcule
        }

        $baseHeaders = [
            'ID Pregunta',
            'Texto Pregunta',
            'Tipo Pregunta',
            'Sección',
            'Orden Sección',
            'Orden Pregunta',
            'Total Respuestas Pregunta',
            'Valor Promedio',
            'Valor Mínimo',
            'Valor Máximo',
            'Nulos/No Aplica',
        ];
        return array_merge($baseHeaders, $this->maxOpcionesHeaders);
    }

    /**
     * @param ResumenPreguntaDto $item
     * @return array
     */
    public function map($item): array
    {
        $filaBase = [
            $item->id_pregunta,
            $item->texto_pregunta,
            $item->nombre_tipo_pregunta,
            $item->nombre_seccion,
            $item->orden_seccion,
            $item->orden_pregunta_en_seccion,
            $item->total_respuestas_para_pregunta,
            $item->valor_promedio,
            $item->valor_minimo,
            $item->valor_maximo,
            $item->conteo_nulos_o_no_aplica,
        ];

        $opcionesData = [];
        if ($item->resumen_opciones) {
            foreach ($item->resumen_opciones as $opcion) {
                $opcionesData[] = $opcion->texto_opcion;
                $opcionesData[] = $opcion->conteo;
                $opcionesData[] = round($opcion->porcentaje, 2) . '%';
            }
        }

        // Rellenar con celdas vacías si hay menos opciones que el máximo
        $numMaxColumnasOpcion = count($this->maxOpcionesHeaders);
        $numColumnasOpcionActual = count($opcionesData);

        if ($numColumnasOpcionActual < $numMaxColumnasOpcion) {
            $opcionesData = array_pad($opcionesData, $numMaxColumnasOpcion, '');
        }

        return array_merge($filaBase, $opcionesData);
    }
}
