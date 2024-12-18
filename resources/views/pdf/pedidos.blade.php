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
            padding-top: 20px;
        }

        .total-row td.total-label {
            text-align: left;
            padding-right: 10px;
        }

        .total-row td.total-value {
            text-align: center;
        }

        .empty-row td {
            height: 20px;
            background-color: white;
        }

        /*Tabla Encabezado*/
        table:nth-of-type(1) td {
            border: none;
        }
        table:nth-of-type(1) td:nth-child(1) {
            text-align: left;
            width: 20%;
        }
        .center {
            text-align: center;
            width: 60%;
        }
        .right {
            text-align: right;
            width: 20%;
        }
    </style>
</head>
<body>
    <table style="width: 100%">
        <tr>
            <td>ROMANI GOLOMIX</td>
            <td class="center">
                *** REPORTE DE PEDIDOS ***
            </td>
            <td class="right">Hora:</td>
        </tr>
        <tr>
            <td>DIVISION GALLETA</td>
            <td class="center">Desde {{ $startDate }}, hasta {{ $endDate }}</td>
            <td class="right">{{ now()->format('H:i:s') }}</td>
        </tr>
    </table>

    <!-- Tabla Principal -->
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
                <td colspan="3" class="total-label">
                    @if($conductor->conductor_id)
                        {{ $conductor->conductor_id }} - {{ $conductor->conductor_nombre }}
                        {{ $conductor->vehiculo_placa }} {{ $conductor->vehiculo_marca }}
                        {{ $conductor->vehiculo_tonelaje }}t.
                    @else
                        <span>Sin asignar Conductor.</span>
                    @endif
                    <span style="float: right;">TOTALES:</span>
                </td>
                <td class="total-value">0.00</td>
                <td class="total-value">{{ $grupo['totalClientes'] }}</td>
                <td class="total-value">{{ number_format($importeTotalConductor, 2) }}</td>
                <td></td>
            </tr>
            <tr>
                <td rowspan="{{ $pedidosPorConductor->unique('ruta_id')->count() }}" style="text-align: center;">
                    @if($conductor->conductor_id)
                        {{ $conductor->conductor_id }} - {{ $conductor->conductor_nombre }}<br><br>
                        {{ $conductor->vehiculo_placa }} {{ $conductor->vehiculo_marca }}<br>
                        {{ $conductor->vehiculo_tonelaje }}t.
                    @else
                        <span>Sin asignar<br>Conductor.</span>
                    @endif
                </td>
                @foreach($pedidosPorConductor->unique('ruta_id') as $pedido)
                    @if (!$loop->first)
                        <tr>
                    @endif
                        <td style="text-align: left;">{{ $pedido->ruta_id }} - {{ $pedido->ruta_nombre }}</td>
                        <td style="text-align: left;">{{ $pedido->vendedor_id }} - {{ $pedido->vendedor_nombre }}</td>
                        <td>0.00</td>
                        <td>{{ $grupo['clientesPorRuta'][$pedido->ruta_id] }}</td>
                        <td>{{ number_format($pedido->importe_total, 2) }}</td>
                        @if ($loop->first)
                            <td rowspan="{{ $pedidosPorConductor->unique('ruta_id')->count() }}">-50,000.00</td>
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
