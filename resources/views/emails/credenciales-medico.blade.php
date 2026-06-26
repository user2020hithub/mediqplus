<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #455A64;">
    <div style="max-width: 500px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #0D47A1;">Bienvenido a MEDIQ+ TuSalud</h2>

        <p>Hola {{ $nombreMedico }},</p>

        <p>Se ha creado su cuenta de médico. Utilice las siguientes credenciales para su primer acceso:</p>

        <ul>
            <li><strong>Correo:</strong> {{ $correo }}</li>
            <li><strong>Contraseña temporal:</strong> {{ $passwordTemporal }}</li>
        </ul>

        <p style="color: #C62828;">
            <strong>Por seguridad, cambie su contraseña después del primer inicio de sesión.</strong>
        </p>

        <a href="{{ $urlLogin }}"
           style="background: #0D47A1; color: white; padding: 12px 24px;
                  text-decoration: none; border-radius: 4px; display: inline-block;">
            Iniciar sesión
        </a>
    </div>
</body>
</html>
