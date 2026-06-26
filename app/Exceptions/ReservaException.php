<?php

namespace App\Exceptions;

use Exception;

/**
 * Excepción de NEGOCIO (regla de negocio violada), distinta de una
 * excepción TÉCNICA (fallo de conexión, etc.). El Controller la captura
 * por separado para poder mostrar el mensaje exacto al usuario sin
 * exponer ningún detalle interno de infraestructura.
 */
class ReservaException extends Exception {}
