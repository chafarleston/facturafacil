
<?php $__env->startSection('title', 'Editar Producto'); ?>
<?php $__env->startSection('page_title', 'Editar Producto'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Producto</h3>
    </div>
    <form method="POST" action="<?php echo e(route('products.update', $product)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>
        <div class="card-body">
            <div class="row">
<div class="col-md-6">
                    <div class="form-group">
                        <label>Código Interno</label>
                        <input type="text" name="codigo" value="<?php echo e($product->codigo); ?>" class="form-control bg-light" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Código de Barras</label>
                        <input type="text" name="codigo_barras" value="<?php echo e($product->codigo_barras); ?>" class="form-control" placeholder="EAN, UPC, etc.">
                    </div>
                </div>
            </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Código SUNAT</label>
                        <div style="position:relative;">
                            <input type="text" id="sunat-search" placeholder="Buscar código SUNAT..." class="form-control" autocomplete="off" value="<?php echo e($product->codigo_sunat ? $product->codigo_sunat . ' - ' . optional(\App\Models\SunatProduct::where('codigo', $product->codigo_sunat)->first())->descripcion : ''); ?>">
                            <input type="hidden" name="codigo_sunat" id="codigo_sunat" value="<?php echo e($product->codigo_sunat); ?>">
                            <div id="sunat-results" class="position-absolute bg-white border rounded mt-1 p-2" style="display:none;z-index:1000;max-height:200px;overflow:auto;width:100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Descripción</label>
                        <input type="text" name="descripcion" value="<?php echo e($product->descripcion); ?>" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="category_id" class="form-control">
                            <option value="">Sin categoría</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php echo e($product->category_id == $category->id ? 'selected' : ''); ?>><?php echo e($category->nombre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" class="form-control" value="<?php echo e($product->stock ?? 0); ?>" min="0">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Destino KDS</label>
                        <select name="kds_destination" class="form-control">
                            <option value="cocina" <?php echo e(($product->kds_destination ?? 'cocina') == 'cocina' ? 'selected' : ''); ?>>KDS Cocina</option>
                            <option value="cocina2" <?php echo e(($product->kds_destination ?? 'cocina') == 'cocina2' ? 'selected' : ''); ?>>KDS Cocina 2</option>
                            <option value="bar" <?php echo e(($product->kds_destination ?? 'cocina') == 'bar' ? 'selected' : ''); ?>>KDS Bar</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Precio Unitario (Con IGV)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">S/</span>
                            </div>
                            <input type="number" id="precio_con_igv" name="precio_con_igv" value="<?php echo e($product->precio); ?>" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Precio Unitario (Sin IGV)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">S/</span>
                            </div>
                            <input type="number" id="precio_sin_igv" name="precio_sin_igv" value="<?php echo e(number_format($product->precio / 1.18, 2)); ?>" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tipo Afectación IGV</label>
                        <select name="tipo_afectacion" class="form-control" required>
                            <option value="GRA" <?php echo e($product->tipo_afectacion == 'GRA' ? 'selected' : ''); ?>>Gravado - 18%</option>
                            <option value="EXO" <?php echo e($product->tipo_afectacion == 'EXO' ? 'selected' : ''); ?>>Exonerado - 0%</option>
                            <option value="INA" <?php echo e($product->tipo_afectacion == 'INA' ? 'selected' : ''); ?>>Inafecto - 0%</option>
                            <option value="EXE" <?php echo e($product->tipo_afectacion == 'EXE' ? 'selected' : ''); ?>>Exportación - 0%</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Unidad de Medida</label>
                        <select name="umedida_codigo" class="form-control">
                            <option value="NIU" <?php echo e($product->umedida_codigo == 'NIU' ? 'selected' : ''); ?>>Unidad (NIU)</option>
                            <option value="KGM" <?php echo e($product->umedida_codigo == 'KGM' ? 'selected' : ''); ?>>Kilogramo (KGM)</option>
                            <option value="GRM" <?php echo e($product->umedida_codigo == 'GRM' ? 'selected' : ''); ?>>Gramo (GRM)</option>
                            <option value="LTR" <?php echo e($product->umedida_codigo == 'LTR' ? 'selected' : ''); ?>>Litro (LTR)</option>
                            <option value="MLT" <?php echo e($product->umedida_codigo == 'MLT' ? 'selected' : ''); ?>>Mililitro (MLT)</option>
                            <option value="MTK" <?php echo e($product->umedida_codigo == 'MTK' ? 'selected' : ''); ?>>Metro cuadrado (MTK)</option>
                            <option value="MTQ" <?php echo e($product->umedida_codigo == 'MTQ' ? 'selected' : ''); ?>>Metro cúbico (MTQ)</option>
                            <option value="HR" <?php echo e($product->umedida_codigo == 'HR' ? 'selected' : ''); ?>>Hora (HR)</option>
                            <option value="D" <?php echo e($product->umedida_codigo == 'D' ? 'selected' : ''); ?>>Día (D)</option>
                            <option value="TNE" <?php echo e($product->umedida_codigo == 'TNE' ? 'selected' : ''); ?>>Tonelada (TNE)</option>
                            <option value="BX" <?php echo e($product->umedida_codigo == 'BX' ? 'selected' : ''); ?>>Caja (BX)</option>
                            <option value="PK" <?php echo e($product->umedida_codigo == 'PK' ? 'selected' : ''); ?>>Paquete (PK)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo e(route('products.show', $product)); ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const IGV_RATE = 1.18;
    const precioSinIgvInput = document.getElementById('precio_sin_igv');
    const precioConIgvInput = document.getElementById('precio_con_igv');
    let syncing = false;

    precioSinIgvInput.addEventListener('input', function() {
        if (syncing) return;
        const sinIgv = parseFloat(this.value) || 0;
        syncing = true;
        precioConIgvInput.value = (sinIgv * IGV_RATE).toFixed(2);
        syncing = false;
    });

    precioConIgvInput.addEventListener('input', function() {
        if (syncing) return;
        const conIgv = parseFloat(this.value) || 0;
        syncing = true;
        precioSinIgvInput.value = (conIgv / IGV_RATE).toFixed(2);
        syncing = false;
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const sunatSearch = document.getElementById('sunat-search');
    const codigoSunat = document.getElementById('codigo_sunat');
    const resultsBox = document.getElementById('sunat-results');
    if (!sunatSearch) return;
    let timeout = null;
    sunatSearch.addEventListener('input', function() {
        const q = this.value.trim();
        if (timeout) clearTimeout(timeout);
        if (q.length < 2) { resultsBox.style.display = 'none'; return; }
        timeout = setTimeout(() => {
            fetch('<?php echo e(route("sunat-products.search")); ?>?query=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(list => {
                    resultsBox.innerHTML = '';
                    if (list.length === 0) { resultsBox.style.display = 'none'; return; }
                    list.forEach(item => {
                        const div = document.createElement('div');
                        div.textContent = item.codigo + ' - ' + item.descripcion;
                        div.className = 'p-2 hover:bg-light cursor-pointer';
                        div.style.cursor = 'pointer';
                        div.onclick = () => {
                            sunatSearch.value = item.codigo + ' - ' + item.descripcion;
                            codigoSunat.value = item.codigo;
                            resultsBox.style.display = 'none';
                        };
                        resultsBox.appendChild(div);
                    });
                    resultsBox.style.display = 'block';
                });
        }, 300);
    });
    sunatSearch.addEventListener('blur', () => {
        setTimeout(() => { resultsBox.style.display = 'none'; }, 200);
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\products\edit.blade.php ENDPATH**/ ?>