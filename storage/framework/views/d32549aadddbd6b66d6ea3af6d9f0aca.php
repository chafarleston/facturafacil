<?php $__env->startSection('title', 'Impresoras'); ?>
<?php $__env->startSection('page_title', 'Asignación de Impresoras'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <?php if(!$serverRunning): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Servidor de impresión no disponible. Ejecuta <code>php C:\laragon\www\print-server\print-server.php</code>
        </div>
        <?php else: ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Servidor de impresión conectado.
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Puntos de Impresión</h3>
                <div class="card-tools">
                    <button class="btn btn-info btn-sm" onclick="document.getElementById('detectModal').style.display='flex'">
                        <i class="fas fa-search"></i> Detectar y asignar impresoras
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Punto</th>
                            <th>Impresora</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><strong><?php echo e($slot->name); ?></strong></td>
                            <td><?php echo e($slot->printer_name ?? ($slot->ip_address ? $slot->ip_address.':'.$slot->port : '—')); ?></td>
                            <td><span class="badge badge-<?php echo e($slot->type === 'local' ? 'info' : 'warning'); ?>"><?php echo e($slot->type === 'local' ? 'Local' : 'Red'); ?></span></td>
                            <td><span class="badge badge-<?php echo e($slot->active ? 'success' : 'secondary'); ?>"><?php echo e($slot->active ? 'Activo' : 'Inactivo'); ?></span></td>
                            <td>
                                <button class="btn btn-xs btn-primary" onclick="document.getElementById('editSlot<?php echo e($slot->id); ?>').style.display='flex'">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($serverRunning && count($availablePrinters) > 0): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Impresoras detectadas en este equipo</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php $__currentLoopData = $availablePrinters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4 col-sm-6 mb-2">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <i class="fas fa-print fa-2x mr-3 text-info"></i>
                            <strong><?php echo e($p['name']); ?></strong>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>


<div class="qty-overlay" id="detectModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:25px; border-radius:10px; min-width:450px; max-width:90%;">
        <h5><i class="fas fa-search"></i> Asignar impresora</h5>
        <form method="POST" action="<?php echo e(route('printers.detect.post')); ?>">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>Punto de impresión</label>
                <select name="slot_id" class="form-control" required>
                    <?php $__currentLoopData = $slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($slot->id); ?>"><?php echo e($slot->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label>Impresora detectada</label>
                <select name="printer_name" class="form-control" required>
                    <option value="">Seleccionar...</option>
                    <?php $__currentLoopData = $availablePrinters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($p['name']); ?>"><?php echo e($p['name']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('detectModal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Asignar</button>
            </div>
        </form>
    </div>
</div>


<?php $__currentLoopData = $slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="qty-overlay" id="editSlot<?php echo e($slot->id); ?>" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:25px; border-radius:10px; min-width:400px; max-width:90%;">
        <h5><i class="fas fa-edit"></i> <?php echo e($slot->name); ?></h5>
        <form method="POST" action="<?php echo e(route('printers.update', $slot)); ?>">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="form-group">
                <label>Nombre en Windows</label>
                <input type="text" name="printer_name" class="form-control" value="<?php echo e($slot->printer_name); ?>" placeholder="Ej: EPSON TM-T20III">
            </div>
            <div class="form-group">
                <label>O impresora de red</label>
                <div class="row">
                    <div class="col-8"><input type="text" name="ip_address" class="form-control" placeholder="IP" value="<?php echo e($slot->ip_address); ?>"></div>
                    <div class="col-4"><input type="number" name="port" class="form-control" placeholder="Puerto" value="<?php echo e($slot->port); ?>"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="type" class="form-control">
                            <option value="local" <?php echo e($slot->type == 'local' ? 'selected' : ''); ?>>Local</option>
                            <option value="network" <?php echo e($slot->type == 'network' ? 'selected' : ''); ?>>Red</option>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Activo</label>
                        <select name="active" class="form-control">
                            <option value="1" <?php echo e($slot->active ? 'selected' : ''); ?>>Sí</option>
                            <option value="0" <?php echo e(!$slot->active ? 'selected' : ''); ?>>No</option>
                        </select>
                    </div>
                </div>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editSlot<?php echo e($slot->id); ?>').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<style>
.qty-overlay { display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center; }
.qty-popup { background:white; padding:20px; border-radius:10px; min-width:300px; max-width:90%; }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\admin\printers\index.blade.php ENDPATH**/ ?>