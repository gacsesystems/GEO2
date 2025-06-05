<?php

namespace App\Exports;

use App\Services\ReportesService; // Usaremos el servicio para obtener los datos
use App\DTOs\Reportes\RespuestaDetalladaDto; // Para el tipado
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping; // Para transformar cada fila
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;

class RespuestasDetalladasExport implements FromCollection, WithHeadings, WithMapping
{
    protected int $idEncuesta;
    protected array $filtros;
    protected ReportesService $reportesService;

    public function __construct(int $idEncuesta, array $filtros = [])
    {
        $this->idEncuesta = $idEncuesta;
        $this->filtros = $filtros;
        // Resolvemos el servicio desde el contenedor para que sus dependencias también se resuelvan
        $this->reportesService = app(ReportesService::class);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        // El servicio ya devuelve una colección de DTOs
        return $this->reportesService->obtenerRespuestasDetalladas($this->idEncuesta, $this->filtros);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Define las cabeceras de tu archivo CSV/Excel
        return [
            'ID Encuesta Respondida',
            'Correo Encuestado',
            'Fecha Inicio Respuesta',
            'Fecha Fin Respuesta',
            'Tiempo Transcurrido',
            'ID Sección',
            'Nombre Sección',
            'Orden Sección',
            'ID Pregunta',
            'Texto Pregunta',
            'Orden Pregunta',
            'Tipo Pregunta',
            'Respuesta',
            'Fecha Registro Respuesta',
            // Puedes añadir más campos del DTO si lo deseas
            // 'ID Encuesta',
            // 'Nombre Encuesta',
            // 'Alias Cliente',
        ];
    }

    /**
     * @param RespuestaDetalladaDto $respuestaDetalladaDto
     * @return array
     */
    public function map($respuestaDetalladaDto): array
    {
        // $respuestaDetalladaDto ya es un objeto DTO
        return [
            $respuestaDetalladaDto->id_encuesta_respondida,
            $respuestaDetalladaDto->correo_respuesta_encuesta,
            $respuestaDetalladaDto->fecha_inicial_respuesta->format('d/m/Y H:i:s'),
            $respuestaDetalladaDto->fecha_final_respuesta?->format('d/m/Y H:i:s'),
            $respuestaDetalladaDto->tiempo_transcurrido,
            $respuestaDetalladaDto->id_seccion,
            $respuestaDetalladaDto->nombre_seccion,
            $respuestaDetalladaDto->orden_seccion,
            $respuestaDetalladaDto->id_pregunta,
            $respuestaDetalladaDto->texto_pregunta,
            $respuestaDetalladaDto->orden_pregunta_en_seccion,
            $respuestaDetalladaDto->nombre_tipo_pregunta,
            $respuestaDetalladaDto->valor_respuesta_texto_formateado,
            $respuestaDetalladaDto->fecha_registro_respuesta_detalle->format('d/m/Y H:i:s'),
        ];
    }

    public function download($filename, $type = Excel::CSV)
    {
        return Excel::download($this, $filename, $type);
    }
}
