
<?php $__env->startSection('title', 'Ver Comprobante'); ?>
<?php $__env->startSection('page_title', 'Ver Comprobante'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><?php echo e($invoice->document_type_name); ?> <?php echo e($invoice->full_number); ?></h3>
                <?php if($invoice->tipo_documento == 'NV'): ?>
                <span class="badge bg-warning ml-2">Nota de Venta</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">F. Emisión</span>
                                <span class="info-box-number"><?php echo e(date('Y-m-d', strtotime($invoice->fecha_emision))); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">H. Emisión</span>
                                <span class="info-box-number"><?php echo e($invoice->hora_emision ? substr($invoice->hora_emision, 0, 8) : ''); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-user"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Cliente</span>
                                <span class="info-box-number"><?php echo e($invoice->customer->nombre); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-secondary"><i class="fas fa-id-card"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Documento</span>
                                <span class="info-box-number"><?php echo e($invoice->customer->documento_tipo == '1' ? 'DNI' : 'RUC'); ?>: <?php echo e($invoice->customer->documento_numero); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-money-bill"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Método de Pago</span>
                                <span class="info-box-number"><?php echo e($invoice->metodo_pago ?? 'EFECTIVO'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-<?php echo e($invoice->sunat_estado == 'ACEPTADO' || $invoice->sunat_estado == 'ENVIADO' ? 'success' : 'danger'); ?>"><i class="fas fa-cloud-upload-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Estado SUNAT</span>
                                <span class="info-box-number"><?php echo e($invoice->sunat_estado); ?> <?php if($invoice->sunat_code): ?>(<?php echo e($invoice->sunat_code); ?>)<?php endif; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if($invoice->sunat_description): ?>
                <div class="alert alert-info mt-3">
                    <?php echo e($invoice->sunat_description); ?>

                </div>
                <?php endif; ?>

                <?php if($invoice->isNotaVenta()): ?>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-info-circle"></i> Nota de Venta - NV no envía a SUNAT. Este documento es para ventas internas.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Items</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->codigo); ?></td>
                    <td><?php echo e($item->descripcion); ?></td>
                    <td class="text-right"><?php echo e($item->cantidad); ?></td>
                    <td class="text-right">S/ <?php echo e(number_format($item->precio_unitario, 2)); ?></td>
                    <td class="text-right">S/ <?php echo e(number_format($item->precio_venta, 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <div class="row justify-content-end">
            <div class="col-md-4">
                <table class="table table-sm">
                    <tr>
                        <td class="text-right"><strong>Subtotal:</strong></td>
                        <td class="text-right">S/ <?php echo e(number_format($invoice->gravado, 2)); ?></td>
                    </tr>
                    <tr>
                        <td class="text-right"><strong>IGV:</strong></td>
                        <td class="text-right">S/ <?php echo e(number_format($invoice->igv, 2)); ?></td>
                    </tr>
                    <tr>
                        <td class="text-right"><strong>Total:</strong></td>
                        <td class="text-right"><strong>S/ <?php echo e(number_format($invoice->total, 2)); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if($invoice->creditNote): ?>
<div class="card mt-3">
    <div class="card-header bg-warning">
        <h3 class="card-title">Nota de Crédito Generada</h3>
    </div>
    <div class="card-body">
        <p><strong>Documento:</strong> <?php echo e($invoice->creditNote->full_number); ?></p>
        <p><strong>Estado SUNAT:</strong> <span class="text-<?php echo e($invoice->creditNote->sunat_estado == 'ACEPTADO' ? 'success' : 'danger'); ?>"><?php echo e($invoice->creditNote->sunat_estado); ?></span></p>
        <a href="<?php echo e(route('invoices.show', $invoice->creditNote)); ?>" class="btn btn-warning btn-sm">Ver Nota de Crédito</a>
    </div>
</div>
<?php endif; ?>

<?php if($invoice->tipo_documento == '07' && $invoice->originalInvoice): ?>
<div class="card mt-3">
    <div class="card-header bg-info">
        <h3 class="card-title">Documento Modificado</h3>
    </div>
    <div class="card-body">
        <p><strong>Factura/Boleta original:</strong> <?php echo e($invoice->originalInvoice->full_number); ?></p>
        <a href="<?php echo e(route('invoices.show', $invoice->originalInvoice)); ?>" class="btn btn-info btn-sm">Ver Documento Original</a>
    </div>
</div>
<?php endif; ?>

<div class="mt-3">
    <a href="<?php echo e(route('invoices.pdf', $invoice)); ?>" target="_blank" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Ver PDF</a>
    <a href="<?php echo e(route('invoices.ticket', $invoice)); ?>" target="_blank" class="btn btn-orange"><i class="fas fa-print"></i> Ticket (80mm)</a>
    
    <?php if($invoice->xml_firmado): ?>
    <a href="<?php echo e(route('invoices.downloadXml', $invoice)); ?>" class="btn btn-secondary"><i class="fas fa-download"></i> XML</a>
    <?php endif; ?>
    
    <?php if($invoice->cdr_path || $invoice->sunat_estado == 'ACEPTADO'): ?>
    <a href="<?php echo e(route('invoices.downloadCdr', $invoice)); ?>" class="btn btn-purple"><i class="fas fa-download"></i> CDR</a>
    <?php endif; ?>
    
    <?php if($invoice->sunat_estado == 'ACEPTADO' && !$invoice->credit_note_id && $invoice->tipo_documento != '07'): ?>
    <a href="<?php echo e(route('invoices.creditNoteForm', $invoice)); ?>" class="btn btn-warning"><i class="fas fa-minus-circle"></i> Nota de Crédito</a>
    <?php endif; ?>
    
    <?php if($invoice->sunat_estado != 'ACEPTADO' && $invoice->sunat_estado != 'ENVIADO'): ?>
    <a href="<?php echo e(route('invoices.send', $invoice)); ?>" class="btn btn-success"><i class="fas fa-paper-plane"></i> Enviar a SUNAT</a>
    <?php endif; ?>
    
    <?php if($invoice->sunat_estado == 'ACEPTADO' && $invoice->tipo_documento != '07'): ?>
    <form action="<?php echo e(route('invoices.destroy', $invoice)); ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de dar de baja este documento en SUNAT?');">
        <?php echo csrf_field(); ?>
        <?php echo method_field('DELETE'); ?>
        <button type="submit" class="btn btn-danger"><i class="fas fa-power-off"></i> Dar de Baja</button>
    </form>
    <?php endif; ?>
    
    <a href="<?php echo e(route('invoices.index')); ?>" class="btn btn-secondary">Volver</a>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\invoices\show.blade.php ENDPATH**/ ?>