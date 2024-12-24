<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Relación de Documentos</title>
    <style>
        html {
            /* margin: 5px; */
            font-size: 10px;
        }

        body {
            font-family: Arial, sans-serif;
            /* margin: 0;
            padding: 0; */
        }

        table {
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 2px 8px;
            /* text-align: center; */
            white-space: nowrap;
        }

        th {
            background-color: #f2f2f2;
        }

        td {
            border: none;
        }

        /*Tabla Encabezado*/
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

        /*Primera tabla*/
        table:nth-of-type(2) td:nth-child(2),
        table:nth-of-type(2) td:nth-child(3) {
            text-align: left;
        }

        .empty-row td {
            height: 10px;
            background-color: white;
            text-align: left;
            position: relative;
            padding-top: 15px;
        }

        /*Segunda Tabla*/
        table.dashed {
            margin: 0 0 50px;
        }

        table:nth-of-type(2)+p {
            margin: 40px 0 15px;
        }

        .dashed th,
        .dashed td {
            text-align: left;
        }

        .dashed th {
            border: none;
            border-bottom: 2px dashed;
            padding-bottom: 12px;
            background: none;
        }

        .dashed th:nth-child(2) {
            min-width: 200px;
        }

        /*Tercera Tabla*/
        table:nth-of-type(3)+p {
            margin: 40px 0 15px;
            text-align: center;
        }

        table:nth-of-type(4) td:nth-child(1) {
            text-align: left;
        }

        table:nth-of-type(4) td:nth-child(3),
        table:nth-of-type(4) td:nth-child(4),
        table:nth-of-type(4) td:nth-child(5) {
            text-align: right;
        }

        table:nth-of-type(4) tfoot tr:nth-child(1) td:nth-child(1) {
            text-align: right;
        }

        /*Cuarta Tabla*/
        table:nth-of-type(4)+p {
            margin: 40px 0 15px;
            text-align: center;
        }

        table:nth-of-type(5) td {
            text-align: left;
            position: relative;
            padding-right: 25px;
        }

        table:nth-of-type(5) td:nth-child(2),
        table:nth-of-type(5) td:nth-child(4),
        table:nth-of-type(5) td:nth-child(5) {
            text-align: right;
        }

        /*Primera Columna*/
        table:nth-of-type(5) tr:nth-child(1) td:nth-child(1)::after,
        table:nth-of-type(5) tr:nth-child(2) td:nth-child(1)::after {
            content: ": S/.";
            position: absolute;
            right: 0;
        }

        table:nth-of-type(5) tfoot tr:nth-child(1) td:nth-child(1)::after {
            content: "S/.";
            position: absolute;
            right: 0;
        }

        /*Tercera Columna*/
        table:nth-of-type(5) td:nth-child(3)::after {
            content: ":";
            position: absolute;
            right: 0;
        }

        @page {
            margin: 90px 5px 20px;
            /* Margen superior e inferior para header y footer */
        }

        .header {
            position: fixed;
            top: -85px;
            /* Ajuste para coincidir con @page margin superior */
            left: 0;
            right: 0;
            /* height: 110px; */
        }

        .footer {
            position: fixed;
            bottom: -20px;
            /* Ajuste para coincidir con @page margin inferior */
            left: 0;
            right: 0;
            /* height: 60px; */
            font-size: 10px;
            line-height: 1.5;
        }
        .pagenum:before {
        content: counter(page);
    }
    </style>
</head>

<body>
    <div class="header">
        <table style="width: 100%">
            <tr>
                <td>ROMANI GOLOMIX</td>
                <td class="center">*** RELACION DOCUMENTOS POR CHOFER ***</td>
                <td class="right">Página <span class="pagenum"></span> de <span class="total-pages"></span></td>
            </tr>
            <tr>
                <td>DIVISION GALLETA</td>
                <td class="center">Fecha Emisión del : {{ $movimiento->fecha_movimiento }}</td>
                <td class="right">{{ now()->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>SPVR68</td>
                <td></td>
                <td class="right">{{ now()->format('h:i:s A') }}</td>
            </tr>
        </table>
        <p>*** C.D.: 07: JICAMARCA - SJL <strong>Chofer:</strong> {{ $movimiento->conductor_id }} - {{ $movimiento->conductor->name }}</p>

    </div>
    <div class="footer">
        <span></span>
        Página <span class="pagenum"></span> de <span class="total-pages"></span>
    </div>

    <table style="width: 100%; border-collapse: collapse">
        <thead>
            <tr>
                <th style="width: 1.00cm">CODIGO</th>
                <th style="width: 3.00cm">NOMBRE y APELLIDOS</th>
                <th style="width: 6.80cm">DIRECCION</th>
                <th style="width: 1.90cm">DOCUMENTO</th>
                <th style="width: 1.20cm">IMPORTE</th>
                <th style="border: 1px solid black;padding: 5px 10px;white-space: nowrap;">OBSERVACION</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($comprobantes_rutas->groupBy('ruta_id') as $key => $comprobantes)
                <tr class="empty-row">
                    <td colspan="7">*** RUTA: {{ str_pad($key, 3, '0', STR_PAD_LEFT) }} :
                        {{ $rutas->find($key)->name }}
                    </td>
                </tr>
                @foreach ($comprobantes as $comprobante)
                    <tr>
                        <td>{{ str_pad($comprobante->cliente_id, 7, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $comprobante->clientRazonSocial }}</td>
                        <td>{{ $comprobante->clientDireccion }}</td>
                        <td>{{ $comprobante->serie }} - {{ str_pad($comprobante->correlativo, 8, '0', STR_PAD_LEFT) }}
                        </td>
                        <td style="text-align: right;">{{ number_format($comprobante->mtoImpVenta, 2) }}</td>
                        <td>{{ $comprobante->pedido_obs ?? '..............' }}</td>
                    </tr>
                @endforeach
            @endforeach
            <!-- Agrega más filas según sea necesario -->
        </tbody>
    </table>

    <!-- Segunda Tabla -->
    @if (false)
        <p>*** RESUMEN ARTICULOS PROMOCIONALES ***</p>
        <table class="dashed">
            <thead>
                <tr>
                    <th>CODIGO</th>
                    <th>DESCRIPCION</th>
                    <th>CANTIDAD</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>058</td>
                    <td>Boni.globopop</td>
                    <td>10.00</td>
                </tr>
                <tr>
                    <td>060 </td>
                    <td>Boni.picaras</td>
                    <td>30.00</td>
                </tr>
                <tr>
                    <td>136</td>
                    <td>Boni.Zuko</td>
                    <td>20.00</td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Tercera Tabla -->
    <p>*** RESUMEN PREVENDEDOR - RUTAS ***</p>
    <table style="width: 100%; border-collapse: collapse">
        <thead>
            <tr>
                <th>NOMBRE y APELLIDOS</th>
                <th>R U T A S</th>
                <th>TOTAL PDV</th>
                <th>TOTAL DOC.</th>
                <th>IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($comprobantes_rutas->groupBy('vendedor_id') as $vendedor_id => $comprobantes)
                @foreach ($comprobantes->groupBy('ruta_id') as $ruta_id => $comprobante)
                    <tr>
                        <td>{{ $vendedor_id }} - {{ $vendedores->find($vendedor_id)->name }}</td>
                        <td>{{ $ruta_id }} - {{ $rutas->find($ruta_id)->name }}</td></td>
                        <td>{{ $comprobante->unique('cliente_id')->count(); }}</td>
                        <td>{{ $comprobante->count() }}</td>
                        <td>{{ number_format($comprobante->sum('mtoImpVenta'), 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <td>*** TOTAL</td>
            <td></td>
            <td>{{ $comprobantes_rutas->unique('cliente_id')->count() }}</td>
            <td>{{ $comprobantes_rutas->count() }}</td>
            <td>{{ number_format($comprobantes_rutas->sum('mtoImpVenta'), 2) }}</td>
        </tfoot>
    </table>

    <!-- Cuarta Tabla -->
    <p>*** RESUMEN X DOCUMENTOS DE VENTA ***</p>
    <table style="margin: auto; border-collapse: collapse">
        <thead></thead>
        <tbody>
            <tr>
                <td>TOTAL CONTADO</td>
                <td>{{ $comprobantes_rutas->sum('mtoImpVenta') }}</td>
                <td>TOTAL FACTURAS</td>
                <td>{{ $comprobantes_rutas->where('tipoDoc', '01')->count() }}</td>
                <td>{{ $comprobantes_rutas->where('tipoDoc', '01')->sum('mtoImpVenta') }}</td>
            </tr>
            <tr>
                <td>TOTAL CREDITO</td>
                <td>0.00</td>
                <td>TOTAL BOLETAS</td>
                <td>{{ $comprobantes_rutas->where('tipoDoc', '03')->count() }}</td>
                <td>{{ $comprobantes_rutas->where('tipoDoc', '03')->sum('mtoImpVenta') }}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>TOTAL NOTA PEDIDO</td>
                <td>{{ $comprobantes_rutas->where('tipoDoc', '00')->count() }}</td>
                <td>{{ $comprobantes_rutas->where('tipoDoc', '00')->sum('mtoImpVenta') }}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td style="font-weight: bold; font-size: 20px">
                    -------------
                </td>
                <td style="font-weight: bold; font-size: 20px">
                    -------------
                </td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
        <tfoot>
            <td>TOTAL IMPORTE ==></td>
            <td><span>{{ $comprobantes_rutas->sum('mtoImpVenta') }}</span></td>
            <td>TOTAL DOCUMENTOS ===></td>
            <td>{{ $comprobantes_rutas->count() }}</td>
            <td>{{ $comprobantes_rutas->sum('mtoImpVenta') }}</td>
        </tfoot>
    </table>

    <!-- FIN DE REPORTE -->
    <p style="text-align: center; padding: 40px 0">
        *** FIN DE REPORTE ***
    </p>
</body>

</html>
