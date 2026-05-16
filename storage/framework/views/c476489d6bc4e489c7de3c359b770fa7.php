
<?php $__env->startSection('title', 'Comprobantes'); ?>
<?php $__env->startSection('page_title', 
    $tipoDocumento == '01' ? 'Facturas' : 
    ($tipoDocumento == '03' ? 'Boletas' : 
    ($tipoDocumento == '07' ? 'Notas de Crédito' : 
    ($tipoDocumento == 'NV' ? 'Notas de Venta' : 'Todos los Comprobantes')))
); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Lista de Comprobantes</h3>
        <div class="card-tools">
          <a href="<?php echo e(route('invoices.create', ['company_id' => $companyId ?? null])); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Generar Comprobante
          </a>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>Documento</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Total</th>
              <th>Estado SUNAT</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($invoice->document_type_name); ?> <?php echo e($invoice->full_number); ?></td>
              <td><?php echo e($invoice->customer?->nombre ?? 'VARIOS'); ?></td>
              <td><?php echo e(date('Y-m-d', strtotime($invoice->fecha_emision))); ?> <?php echo e($invoice->hora_emision ? substr($invoice->hora_emision, 0, 8) : ''); ?></td>
              <td>S/ <?php echo e(number_format($invoice->total, 2)); ?></td>
              <td>
                <?php switch($invoice->sunat_estado):
                  case ('PENDIENTE'): ?><span class="badge badge-warning">Pendiente</span><?php break; ?>
                  <?php case ('ENVIADO'): ?><span class="badge badge-info">Enviado</span><?php break; ?>
                  <?php case ('ACEPTADO'): ?><span class="badge badge-success">Aceptado</span><?php break; ?>
                  <?php case ('RECHAZADO'): ?><span class="badge badge-danger">Rechazado</span><?php break; ?>
                  <?php case ('ANULADO'): ?><span class="badge badge-secondary">Anulado</span><?php break; ?>
                <?php endswitch; ?>
              </td>
              <td>
                <a href="<?php echo e(route('invoices.show', $invoice)); ?>" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                <a href="<?php echo e(route('invoices.pdf', $invoice)); ?>" class="btn btn-secondary btn-xs" target="_blank"><i class="fas fa-file-pdf"></i></a>
                <?php if($invoice->xml_path): ?>
                <a href="<?php echo e(route('invoices.downloadXml', $invoice)); ?>" class="btn btn-primary btn-xs"><i class="fas fa-code"></i></a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" class="text-center">No hay comprobantes</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
        <div class="card-footer"><?php echo e($invoices->links()); ?></div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\invoices\index.blade.php ENDPATH**/ ?>