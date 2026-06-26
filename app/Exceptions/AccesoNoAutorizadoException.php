<?php

namespace App\Exceptions;

use Exception;

// Excepción específica para intentos de acceso horizontal (IDOR).
// Se separa de ReservaException porque el Controller debe responder
// con HTTP 403 (Forbidden), no con un mensaje de error de negocio normal.
class AccesoNoAutorizadoException extends Exception {}
