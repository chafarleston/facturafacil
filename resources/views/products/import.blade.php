@extends('layouts.admin')
@section('title', 'Importar Productos')
@section('page_title', 'Importar Productos desde Excel')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Importar Productos</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <h5><i class="icon fas fa-info"></i> Formato del archivo</h5>
            <p>El archivo debe ser Excel (.xlsx, .xls) o CSV. La primera fila debe contener los encabezados de columna.</p>
            <p><strong>Columnas aceptadas:</strong></p>
            <ul class="mb-0">
                <li><strong>codigo</strong> (opcional, se genera automáticamente si está vacío)</li>
                <li><strong>codigo_barras</strong> (opcional)</li>
                <li><strong>descripcion</strong> (obligatorio)</li>
                <li><strong>precio</strong> (opcional, valor por defecto: 0)</li>
                <li><strong>stock</strong> (opcional, valor por defecto: 0)</li>
                <li><strong>tipo_afectacion</strong> (opcional, valores: GRA, EXO, INA, EXE)</li>
                <li><strong>umedida</strong> (opcional, valores: NIU, KGM, LTR, etc.)</li>
                <li><strong>categoria</strong> (opcional, se crea automáticamente si no existe)</li>
                <li><strong>codigo_sunat</strong> (opcional, ej: 53121801, 53121605, 51121701, etc.)</li>
                <li><strong>kds_destination</strong> (opcional, valores: cocina, cocina2, bar)</li>
            </ul>
            <p class="mt-2 mb-0 text-muted" style="font-size:12px;">
                <strong>Destinos KDS:</strong> cocina (KDS Cocina), cocina2 (KDS Cocina 2), bar (KDS Bar)
            </p>
        </div>

        <form method="POST" action="{{ route('products.import.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="company_id" value="{{ $companyId }}">
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Archivo Excel (.xlsx, .xls) o CSV</label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                        @error('file')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('products.index', ['company_id' => $companyId]) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Importar Productos
                </button>
            </div>
        </form>

        <hr>
        <h5>Ejemplo de archivo</h5>
        <table class="table table-bordered table-sm" style="width: auto;">
            <thead class="thead-dark">
                <tr>
                    <th>codigo</th>
                    <th>codigo_barras</th>
                    <th>descripcion</th>
                    <th>precio</th>
                    <th>stock</th>
                    <th>tipo_afectacion</th>
                    <th>umedida</th>
                    <th>categoria</th>
                    <th>codigo_sunat</th>
                    <th>kds_destination</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PROD00001</td>
                    <td>7501234567890</td>
                    <td>Producto de ejemplo</td>
                    <td>100.00</td>
                    <td>50</td>
                    <td>GRA</td>
                    <td>NIU</td>
                    <td>Bebidas</td>
                    <td>53121801</td>
                    <td>cocina</td>
                </tr>
                <tr>
                    <td>PROD00002</td>
                    <td>7501234567891</td>
                    <td>Galletas de chocolate</td>
                    <td>75.50</td>
                    <td>30</td>
                    <td>GRA</td>
                    <td>NIU</td>
                    <td>Alimentos</td>
                    <td>53121605</td>
                    <td>cocina</td>
                </tr>
                <tr>
                    <td>PROD00003</td>
                    <td></td>
                    <td>Jugo de naranja</td>
                    <td>45.00</td>
                    <td>100</td>
                    <td>GRA</td>
                    <td>NIU</td>
                    <td>Bebidas</td>
                    <td>51121701</td>
                    <td>cocina2</td>
                </tr>
                <tr>
                    <td>PROD00004</td>
                    <td></td>
                    <td>Cerveza artesanal</td>
                    <td>35.00</td>
                    <td>60</td>
                    <td>GRA</td>
                    <td>NIU</td>
                    <td>Bebidas</td>
                    <td>51111702</td>
                    <td>bar</td>
                </tr>
            </tbody>
        </table>
        <a href="{{ route('products.import.template') }}" class="btn btn-success btn-sm mt-2">
            <i class="fas fa-download"></i> Descargar plantilla
        </a>
    </div>
</div>
@endsection