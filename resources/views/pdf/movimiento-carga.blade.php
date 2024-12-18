<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Tabla de Productos</title>
        <style>
            html {
                font-size: 11px;
                margin: 5px;
                padding: 0;
            }
            body {
                font-family: Arial, Helvetica, sans-serif;
                margin: 0;
                padding: 0;
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
                border-bottom: 1px dashed;
            }
            .marca {
                font-weight: bold;
                text-align: left;
                padding-top: 20px;
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
        </style>
    </head>
    <body>
        <table style="width: 100%">
            <tr>
                <td>ROMANI GOLOMIX</td>
                <td class="center">
                    *** PLANILLA DE CARGA No.: 0000019991 ***
                </td>
                <td class="right">PAG.N°: 0001</td>
            </tr>
            <tr>
                <td>DIVISION GALLETA</td>
                <td class="center">Fecha Emisión del : 03/08/2024</td>
                <td class="right">02/08/2024</td>
            </tr>
            <tr>
                <td>SPVR68</td>
                <td></td>
                <td class="right">16:35:52</td>
            </tr>
        </table>
        <p>*** C.D.: 07: JICAMARCA - SJL <strong>Chofer:</strong> 006 - KIMI</p>
        <p>Vehículo: 006 HD78 FORLAND Placa: ARP-911 Capac.Tn.: 50.00</p>
        <p>Fecha Movimiento: 16/12/2024 - Fecha Liquidación 17/12/2024</p>
        <table>
            <thead>
                <tr>
                    <th rowspan="2">CÓDIGO</th>
                    <th rowspan="2">DESCRIPCIÓN ARTÍCULO</th>
                    <th rowspan="2">PRESENTA.</th>
                    <th colspan="3">CARGA</th>
                    <th colspan="2">DESCARGA</th>
                </tr>
                <tr>
                    <th>BULTOS</th>
                    <th>UNID</th>
                    <th>IMPORTE</th>
                    <th>BULTOS</th>
                    <th>UNID</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="marca" colspan="8">MARCA: 001 MOLITALIA</td>
                </tr>
                <tr>
                    <td class="codigo">000-000-0133</td>
                    <td class="descripcion">CADONAZO EXTRA CJ X6DIS X12UNI</td>
                    <td>CAJAX6</td>
                    <td>0</td>
                    <td>1</td>
                    <td>11.90</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="codigo">000-000-0135</td>
                    <td class="descripcion">CAR.BONAMI CJX18BOLX10UNI</td>
                    <td>CAJAX18</td>
                    <td>1</td>
                    <td>0</td>
                    <td>129.60</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="codigo">000-000-0146</td>
                    <td class="descripcion">CHAPULIN STD CJX25BOLX25UNI</td>
                    <td>CAJAX25</td>
                    <td>1</td>
                    <td>0</td>
                    <td>182.50</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="codigo">000-000-0147</td>
                    <td class="descripcion">CHOCMAN CJX14PAKX6UNI</td>
                    <td>CAJAX14</td>
                    <td>1</td>
                    <td>0</td>
                    <td>58.80</td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
            <tfoot>
                <!-- TOTALES -->
                <tr>
                    <td colspan="3" style="text-align: left">*** TOTALES :</td>
                    <td>175</td>
                    <td>186</td>
                    <td>11202.57</td>
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
                        -----------<br /><span style="padding-right: 15px"
                            >ALMACEN</span
                        >
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
