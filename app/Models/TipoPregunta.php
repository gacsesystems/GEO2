<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperTipoPregunta
 */
class TipoPregunta extends Model
{
    use HasFactory;
    public const NOMBRE_VALORACION              = 'Valoración';
    public const NOMBRE_VALOR_NUMERICO          = 'Valor numérico';
    public const NOMBRE_BOOLEANO                = 'Booleano (Sí/No)';
    public const NOMBRE_TEXTO_CORTO             = 'Texto corto';
    public const NOMBRE_TEXTO_LARGO             = 'Texto largo';
    public const NOMBRE_OPCION_UNICA            = 'Opción múltiple (única respuesta)';
    public const NOMBRE_LISTA_DESPLEGABLE       = 'Lista desplegable (única respuesta)';
    public const NOMBRE_SELECCION_MULTIPLE      = 'Selección múltiple (varias respuestas)';
    public const NOMBRE_FECHA                   = 'Fecha';
    public const NOMBRE_HORA                    = 'Hora';

    protected $table = 'tipos_pregunta';
    protected $primaryKey = 'id_tipo_pregunta';
    public $timestamps = false; // Catálogo fijo
    protected $fillable = [
        'nombre',
        'descripcion',
        'requiere_opciones',
        'permite_min_max_numerico',
        'permite_min_max_fecha',
        'es_seleccion_multiple'
    ];

    protected $casts = [
        'requiere_opciones'           => 'boolean',
        'permite_min_max_numerico'    => 'boolean',
        'permite_min_max_fecha'       => 'boolean',
        'es_seleccion_multiple'       => 'boolean',
    ];

    /**
     * Relación: un tipo de pregunta tiene muchas Preguntas.
     */
    public function preguntas(): HasMany
    {
        return $this->hasMany(Pregunta::class, 'id_tipo_pregunta', 'id_tipo_pregunta');
    }

    // ===========================
    // Helper methods (toggle logic)
    // ===========================

    /**
     * ¿Este tipo de pregunta requiere opciones (p. ej. lista u opción)?
     */
    public function requiereOpciones(): bool
    {
        return $this->requiere_opciones;
    }

    /**
     * ¿Este tipo permite definir valores numéricos con mínimo/máximo?
     */
    public function permiteMinMaxNumerico(): bool
    {
        return $this->permite_min_max_numerico;
    }

    /**
     * ¿Este tipo permite definir valores de fecha con mínimo/máximo?
     */
    public function permiteMinMaxFecha(): bool
    {
        return $this->permite_min_max_fecha;
    }

    /**
     * ¿Este tipo es una selección múltiple (varias respuestas)?
     */
    public function esSeleccionMultiple(): bool
    {
        return $this->es_seleccion_multiple;
    }

    /**
     * ¿Este tipo es “opción única” o “lista desplegable”? (requiere opciones, pero NO es “selección múltiple”)
     */
    public function esOpcionUnica(): bool
    {
        return $this->requiere_opciones && ! $this->es_seleccion_multiple;
    }

    /**
     * ¿Este tipo es “lista desplegable (única respuesta)”?
     */
    public function esListaDesplegable(): bool
    {
        return $this->nombre === self::NOMBRE_LISTA_DESPLEGABLE;
    }

    /**
     * ¿Este tipo es “Opción múltiple (única respuesta)”?
     */
    public function esOpcionMultipleUnica(): bool
    {
        return $this->nombre === self::NOMBRE_OPCION_UNICA;
    }

    /**
     * ¿Este tipo es “Selección múltiple (varias respuestas)”?
     */
    public function esOpcionMultipleMultiple(): bool
    {
        return $this->nombre === self::NOMBRE_SELECCION_MULTIPLE;
    }

    /**
     * ¿Este tipo es de valor numérico (float/int)?
     */
    public function esValorNumerico(): bool
    {
        return $this->nombre === self::NOMBRE_VALOR_NUMERICO
            || $this->nombre === self::NOMBRE_VALORACION;
    }

    /**
     * ¿Este tipo es booleano (Sí/No)?
     */
    public function esBooleano(): bool
    {
        return $this->nombre === self::NOMBRE_BOOLEANO;
    }
}
