<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #455A64;">
    <div style="max-width: 500px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #0D47A1;">Bienvenido a MEDIQ+</h2>

        <p>Se ha creado su cuenta de acceso como personal médico en el sistema
           de gestión de citas de Clínica TuSalud.</p>

        <p><strong>Correo:</strong> {{ $correo }}</p>
        <p><strong>Contraseña temporal:</strong> {{ $passwordTemporal }}</p>

        <p style="color: #C62828;">
            Por seguridad, deberá cambiar esta contraseña en su primer inicio de sesión.
        </p>

        <a href="{{ $urlActivacion }}"
           style="background: #2E7D32; color: white; padding: 12px 24px;
                  text-decoration: none; border-radius: 4px; display: inline-block;">
            Activar mi cuenta
        </a>

        <p style="font-size: 12px; color: #90A4AE; margin-top: 20px;">
            Este enlace expira en 24 horas.
        </p>
    </div>
</body>
</html>