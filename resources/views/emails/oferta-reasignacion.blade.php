<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #455A64;">
    <div style="max-width: 500px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #0D47A1;">¡Tenemos un cupo disponible!</h2>

        <p>Hola {{ $nombrePaciente }},</p>

        <p>Se ha liberado un cupo que coincide con su solicitud en lista de espera:</p>

        <ul>
            <li><strong>Médico:</strong> {{ $nombreMedico }}</li>
            <li><strong>Sede:</strong> {{ $nombreSede }}</li>
            <li><strong>Hora:</strong> {{ $horaCita }}</li>
        </ul>

        <p style="color: #C62828;">
            <strong>Tiene 15 minutos para confirmar este cupo.</strong>
            Pasado ese tiempo, se ofrecerá a otro paciente.
        </p>

        <a href="{{ $urlAceptar }}"
           style="background: #2E7D32; color: white; padding: 12px 24px;
                  text-decoration: none; border-radius: 4px; display: inline-block;">
            Aceptar Cupo
        </a>
        &nbsp;
        <a href="{{ $urlRechazar }}"
           style="background: #CFD8DC; color: #455A64; padding: 12px 24px;
                  text-decoration: none; border-radius: 4px; display: inline-block;">
            No puedo asistir
        </a>
    </div>
</body>
</html>
