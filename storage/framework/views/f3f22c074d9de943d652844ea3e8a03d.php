<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Restablecer Contraseña - FacturaFacil by RealComputer SAC</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="card card-primary card-outline">
    <div class="card-header text-center">
      <a href="/" class="h1"><b>FacturaFacil by RealComputer SAC</b></a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Restablecer Contraseña</p>
      <?php if($errors->any()): ?>
      <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
      <?php endif; ?>
      <form method="POST" action="<?php echo e(route('password.store')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="token" value="<?php echo e($token); ?>">
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Email" required>
          <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-envelope"></span></div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Nueva Contraseña" required>
          <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-lock"></span></div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password_confirmation" class="form-control" placeholder="Confirmar Contraseña" required>
          <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-lock"></span></div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Restablecer</button>
          </div>
        </div>
      </form>
      <p class="mt-3 mb-1 text-center">
        <a href="/login">Iniciar Sesión</a>
      </p>
    </div>
  </div>
</div>
</body>
</html><?php /**PATH C:\laragon\www\facturafacil\resources\views\auth\reset-password.blade.php ENDPATH**/ ?>