<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema bloqueado</title>
    <style>
        body{font-family:'Segoe UI',Arial,sans-serif;background:#f8f9fa;color:#212529;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}
        .box{background:#fff;border:1px solid #dee2e6;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:40px;max-width:520px;text-align:center}
        .icon{font-size:64px}
        h1{font-size:24px;margin:16px 0 8px}
        p{color:#6c757d;line-height:1.6}
        .btn{margin-top:20px;display:inline-block;background:#dc3545;color:#fff;border:0;padding:10px 22px;border-radius:6px;font-size:15px;cursor:pointer}
        .btn:hover{background:#bb2d3b}
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">&#128274;</div>
        <h1>Sistema bloqueado</h1>
        <p>El sistema se encuentra bloqueado por falta de pago o venció el soporte activo.<br>Contacte a su proveedor para regularizar su suscripción.</p>
        @auth
            @if(auth()->user()->email === config('app.lock_owner_email', 'rcharles84@gmail.com'))
                <form action="{{ route('system-lock.toggle') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn">Desbloquear sistema</button>
                </form>
            @endif
        @endauth
    </div>
</body>
</html>