<!doctype html>
<html lang="es">
<body>
    <h1>Nuevo mensaje de contacto</h1>
    <p><strong>Nombre:</strong> {{ $contactMessage->name }}</p>
    <p><strong>Correo:</strong> {{ $contactMessage->email }}</p>
    @if ($contactMessage->phone)<p><strong>Teléfono:</strong> {{ $contactMessage->phone }}</p>@endif
    <p><strong>Asunto:</strong> {{ $contactMessage->subject }}</p>
    <p><strong>Mensaje:</strong></p>
    <p style="white-space: pre-wrap">{{ $contactMessage->message }}</p>
</body>
</html>
