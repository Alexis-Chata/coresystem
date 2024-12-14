<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos</title>
    <style>
        html{
            margin: 5px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid black;
            padding: 5px 10px;
            text-align: center;
            white-space: nowrap;
        }
        th {
            background-color: #f2f2f2;
        }
        
        .total-row td {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .total-row td.total-label {
            text-align: right;
            padding-right: 10px;
        }
        
        .total-row td.total-value {
            text-align: center;
        }
        
        .empty-row td {
            height: 20px;
            background-color: white;
        }
    </style>
</head>
<body>
    <h2>Reporte de Pedidos</h2>
    <p>Desde {{ $startDate }}, hasta {{ $endDate }}</p>
    <table>
    <thead>
        <tr>
            <th colspan="1">Conductor</th>
            <th rowspan="2">Rutas</th>
            <th rowspan="2">Vendedor</th>
            <th colspan="3">Programa de Carga</th>
            <th colspan="1">Diferencia</th>
        </tr>
        <tr>
            <th>Vehículo</th>
            <th>PESO Kg</th>
            <th>N° CLI</th>
            <th>TOT. IMPORT</th>
            <th>PESO Kg</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pedidosAgrupados as $conductorId => $grupo)
            @php
                $pedidosPorConductor = $grupo['pedidos'];
                $importeTotalConductor = $grupo['importeTotal'];
                $conductor = $pedidosPorConductor->first();
            @endphp
            <tr class="total-row">
                <td colspan="3" class="total-label">TOTALES:</td>
                <td class="total-value">0.00</td>
                <td class="total-value">{{ $grupo['totalClientes'] }}</td>
                <td class="total-value">{{ number_format($importeTotalConductor, 2) }}</td>
                <td></td>
            </tr>
            <tr>
                <td rowspan="{{ $pedidosPorConductor->count() }}" style="text-align: center;">
                    {{ $conductor->conductor_id }} - {{ $conductor->conductor_nombre }}<br><br>
                    {{ $conductor->vehiculo_placa }} {{ $conductor->vehiculo_marca }}<br>
                    {{ $conductor->vehiculo_tonelaje }}t.
                </td>
                @foreach($pedidosPorConductor as $pedido)
                    @if (!$loop->first)
                        <tr>
                    @endif
                        <td style="text-align: left;">{{ $pedido->ruta_id }} - {{ $pedido->ruta_nombre }}</td>
                        <td style="text-align: left;">{{ $pedido->vendedor_id }} - {{ $pedido->vendedor_nombre }}</td>
                        <td>0.00</td>
                        <td>{{ $grupo['clientesPorRuta'][$pedido->ruta_id] }}</td>
                        <td>{{ number_format($pedido->importe_total, 2) }}</td>
                        @if ($loop->first)
                            <td rowspan="{{ $pedidosPorConductor->count() }}">-50,000.00</td>
                        @endif
                    @if (!$loop->first)
                        </tr>
                    @endif
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
