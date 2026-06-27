// Orden importante: el CSS de Bootstrap debe ir ANTES del CSS propio
// (app.css), para que las variables --bs-* que sobrescribimos ahi
// tengan prioridad sobre los valores por defecto de Bootstrap.

import 'bootstrap/dist/css/bootstrap.min.css';   // CSS de Bootstrap (NUEVO - faltaba)
import 'bootstrap';                                // JS de Bootstrap (modales, dropdowns, etc.)
import '../css/app.css';                           // Variables y clases propias de MEDIQ+
import '@fortawesome/fontawesome-free/css/all.min.css';
