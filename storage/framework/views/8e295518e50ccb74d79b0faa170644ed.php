<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - FacturaFacil by RealComputer SAC</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Source Sans Pro', sans-serif; }
    .login-page { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .login-box { max-width: 400px; }
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="card card-primary card-outline">
    <div class="card-header text-center">
      <a href="/" class="h1"><i class="fas fa-file-invoice"></i> <b>FacturaFacil by RealComputer SAC</b></a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Iniciar Sesión</p>
      <form method="POST" action="/login">
        <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Correo electrónico" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember" name="remember">
              <label for="remember">Recordarme</label>
            </div>
          </div>
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html><?php /**PATH C:\laragon\www\facturafacil\resources\views\auth\login.blade.php ENDPATH**/ ?>