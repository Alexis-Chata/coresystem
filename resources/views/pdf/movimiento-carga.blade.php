<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tabla de Productos</title>
    <style>
        html {
            font-size: 12px;
            /* margin: 5px; */
            /* padding: 0; */
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            /* margin: 0;
            padding: 0; */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-family: monospace;
        }

        th,
        td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
        }

        td {
            border: none;
            border-bottom: 0.5px solid;
        }

        .marca {
            font-weight: bold;
            text-align: left;
            padding-top: 15px;
        }

        .codigo,
        .descripcion {
            text-align: left;
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

        /*Tabla Footer*/
        tfoot td {
            border: none;
        }

        tfoot tr:nth-child(3) td {
            height: 60px;
        }

        @page {
            margin: 130px 5px 80px;
            /* Margen superior e inferior para header y footer */
        }

        .header {
            position: fixed;
            top: -125px;
            /* Ajuste para coincidir con @page margin superior */
            left: 0;
            right: 0;
            /* height: 110px; */
        }

        .footer {
            position: fixed;
            bottom: -80px;
            /* Ajuste para coincidir con @page margin inferior */
            left: 0;
            right: 0;
            height: 60px;
            font-size: 10px;
            line-height: 1.5;
        }

        /* Evitar cortes incómodos en el contenido */
        .content2 {
            page-break-inside: avoid;
        }
        .pagenum:before {
        content: counter(page);
        }
    </style>
</head>

<body>
    <!-- Encabezado fijo -->
    <div class="header">
        <table style="width: 100%">
            <tr>
                <td>GOLOMIX</td>
                <td class="center">
                    *** PLANILLA DE CARGA No.: {{ str_pad($movimiento->id, 10, '0', STR_PAD_LEFT) }} ***
                </td>
                <td class="right">PAG.N°: <span class="pagenum"></span></td>
            </tr>
            <tr>
                <td>DIVISION GALLETA</td>
                <td class="center">Fecha Emisión del : {{ $movimiento->created_at->format('d/m/Y') }}</td>
                <td class="right">{{ now()->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td class="right">{{ now()->format('h:i:s A') }}</td>
            </tr>
        </table>
        <p style="padding: 0 0 2px 0; margin:0 0 2px 0;">*** C.D.: 07: JICAMARCA - SJL <strong>Chofer:</strong>
            {{ $movimiento->conductor->id }} -
            {{ $movimiento->conductor->name }}</p>
        <p style="padding: 0 0 2px 0; margin:0 0 2px 0;">Vehículo: {{ $movimiento->vehiculo->id }}
            {{ $movimiento->vehiculo->modelo }} &nbsp;&nbsp;&nbsp; Placa:
            {{ $movimiento->vehiculo->placa }} &nbsp;&nbsp;&nbsp; Capac.Tn.:
            {{ number_format_punto2($movimiento->vehiculo->tonelaje_maximo) }}</p>
        <p style="padding: 2px 0 2px 0; margin:2px 0 2px 0;">Fecha Movimiento:
            {{ $movimiento->created_at->format('d/m/Y') }} - Fecha Liquidación:
            {{ format_date($movimiento->fecha_liquidacion) }}</p>
    </div>

    <div class="footer">
        <span></span>
        Página <span class="pagenum"></span> de <span class="total-pages"></span>
    </div>

    <!-- Contenido dinámico -->
    <table id="contenido" style="font-family: monospace; width: 90%">
        <thead>
            <tr style="background-color: rgba(211, 211, 211, 0.5);">
                <th rowspan="2">CÓDIGO</th>
                <th rowspan="2" style="width: 260px">DESCRIPCIÓN ARTÍCULO</th>
                <th rowspan="2">PRESENTA.</th>
                <th colspan="3">CARGA</th>
                <th colspan="2">DESCARGA</th>
            </tr>
            <tr style="background-color: rgba(211, 211, 211, 0.5);">
                <th>BULTOS</th>
                <th>UNID</th>
                <th>IMPORTE</th>
                <th>BULTOS</th>
                <th>UNID</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detallesAgrupados as $key => $detalles)
                <tr>
                    <td class="marca" colspan="8" style="border-bottom: 0.5px solid;">MARCA:
                        {{ str_pad($key, 3, '0', STR_PAD_LEFT) }}
                        {{ $marca->find($key)->name }}</td>
                </tr>
                @foreach ($detalles as $detalle)
                    <tr>
                        <td class="codigo" style="border-bottom: 0.5px solid; padding: 8px 4px; width: 65px">
                            000-{{ str_pad($detalle->producto_id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="descripcion" style="border-bottom: 0.5px solid; padding: 8px 4px; width: 260px">
                            {{ $detalle->producto->name }}</td>
                        <td style="border-bottom: 0.5px solid; padding: 8px 14px; text-align: left">
                            CAJAX{{ $detalle->producto->cantidad }}</td>
                        <td
                            style="border-bottom: 0.5px solid; padding: 8px 4px; padding-right: 16px; width: 50px; text-align: right; font-weight: bold;">
                            {{ $detalle->cantidad_bultos }}</td>
                        <td
                            style="border-bottom: 0.5px solid; padding: 8px 4px; padding-left: 16px;width: 30px;text-align: left;">
                            {{ $detalle->cantidad_unidades }}</td>
                        <td style="border-bottom: 0.5px solid; padding: 8px 4px; width: 80px">
                            {{ $detalle->precio_venta_total }}</td>
                        <td style="border-bottom: 0.5px solid; padding: 8px 4px; width: 40px"></td>
                        <td style="border-bottom: 0.5px solid; padding: 8px 4px; width: 40px"></td>
                    </tr>
                @endforeach
            @empty
            @endforelse
        </tbody>
        <tfoot>
            <!-- TOTALES -->
            <tr>
                <td colspan="3" style="text-align: left">*** TOTALES :</td>
                <td style="padding-right: 16px; text-align: right; font-weight: bold">{{ $movimiento->movimientoDetalles->sum('cantidad_bultos') }}</td>
                <td style="padding-left: 16px; text-align: left;">{{ $movimiento->movimientoDetalles->sum('cantidad_unidades') }}</td>
                <td>{{ number_format_punto2($movimiento->movimientoDetalles->sum('precio_venta_total')) }}</td>
                {{-- <td>999,999.99</td> --}}
                <td></td>
                <td></td>
            </tr>
            <!-- TOTAL PESO KG -->
            <tr>
                <td class="codigo">TOTAL PESO KG</td>
                <td colspan="7" style="text-align: left">0.00</td>
            </tr>
            <!-- Firma -->
            <tr>
                <td colspan="2" style="text-align: right">
                    -----------<br /><span style="padding-right: 15px">ALMACEN</span>
                </td>
                <td colspan="6" style="text-align: left">
                    -----------<br />
                    <span style="padding-left: 15px">CHOFER</span>
                </td>
            </tr>
        </tfoot>
    </table>
    <!-- FIN DE REPORTE -->
    <p style="text-align: center; padding-bottom;: 40px">
        *** FIN DE REPORTE ***
    </p>
</body>

</html>
