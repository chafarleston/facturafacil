<?php $company = App\Models\Company::getMainCompany(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h3 { text-align: center; margin: 0; }
        .sub { text-align: center; color: #666; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; font-size: 10px; }
        th { background: #333; color: #fff; text-align: center; }
        .sum td { font-weight: bold; background: #eee; }
        .faltas td { background: #ffe6e6; }
        .tardanza td { background: #fff3cd; }
    </style>
</head>
<body>
    <h3>{{ $company->razon_social }}</h3>
    <div class="sub">REPORTE DE ASISTENCIA · {{ strtoupper($rango) }} · {{ $range['inicio']->format('d/m/Y') }} al {{ $range['fin']->format('d/m/Y') }}</div>

    @foreach($report['trabajadores'] as $t)
    <table>
        <tr>
            <th>DNI</th><th>Trabajador</th><th>Cargo</th><th>Faltas</th><th>Tardanzas</th><th>Descuento total</th>
        </tr>
        <tr>
            <td>{{ $t['dni'] }}</td>
            <td>{{ $t['nombre'] }}</td>
            <td>{{ $t['cargo'] ?? '-' }}</td>
            <td>{{ $t['tot_faltas'] }}</td>
            <td>{{ $t['tot_tardanzas'] }}</td>
            <td>S/ {{ number_format($t['tot_descuento'], 2) }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <th>Fecha</th><th>Estado</th><th>Entrada 1</th><th>Salida 1</th><th>Entrada 2</th><th>Salida 2</th><th>Tardanza</th><th>Descuento</th>
        </tr>
        @foreach($t['dias'] as $dia)
        <tr class="{{ in_array($dia['estado'], ['FALTA', 'FALTA_GRAVE']) ? 'faltas' : ($dia['estado'] == 'TARDANZA' ? 'tardanza' : '') }}">
            <td>{{ $dia['fecha'] }}</td>
            <td>{{ $dia['estado_label'] }}</td>
            <td>{{ $dia['entrada_1'] }}</td>
            <td>{{ $dia['salida_1'] }}</td>
            <td>{{ $dia['entrada_2'] }}</td>
            <td>{{ $dia['salida_2'] }}</td>
            <td>{{ $dia['tardanza_min'] }} min</td>
            <td>S/ {{ number_format($dia['descuento'], 2) }}</td>
        </tr>
        @endforeach
    </table>
    @endforeach
</body>
</html>
