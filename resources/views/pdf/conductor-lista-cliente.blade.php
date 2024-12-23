<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Relación de Documentos</title>
    <style>
        html {
            margin: 5px;
            font-size: 10px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px 10px;
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
            padding-top: 20px;
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
            margin: 170px 5px 80px;
            /* Margen superior e inferior para header y footer */
        }

        .header {
            position: fixed;
            top: -165px;
            /* Ajuste para coincidir con @page margin superior */
            left: 0;
            right: 0;
            height: 110px;
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
    </style>
</head>

<body>
    <table style="width: 100%">
        <tr>
            <td>ROMANI GOLOMIX</td>
            <td class="center">*** RELACION DOCUMENTOS POR CHOFER ***</td>
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

    <table style="width: 100%; border-collapse: collapse">
        <thead>
            <tr>
                <th style="width: ">CODIGO</th>
                <th>NOMBRE y APELLIDOS</th>
                <th>DIRECCION</th>
                <th>DOCUMENTO</th>
                <th>IMPORTE</th>
                <th>COND.</th>
                <th>OBSERVACION</th>
            </tr>
        </thead>
    </table>


    <table style="width: 100%; border-collapse: collapse">
        <thead>
            <tr>
                <th>CODIGO</th>
                <th>NOMBRE y APELLIDOS</th>
                <th>DIRECCION</th>
                <th>DOCUMENTO</th>
                <th>IMPORTE</th>
                <th>COND.</th>
                <th>OBSERVACION</th>
            </tr>
        </thead>
        <tbody>
            <tr class="empty-row">
                <td colspan="7">*** RUTA: 090 : ARMANDO MAYORISTA</td>
            </tr>
            <tr>
                <td>07012297</td>
                <td>CLIENTE GLADIS</td>
                <td>MCD.BRISASPST.259 GOLOSINAS</td>
                <td>NPE 001-0138152</td>
                <td>156.00</td>
                <td>CONT</td>
                <td>..............</td>
            </tr>
            <tr>
                <td>07013214</td>
                <td>VANESSA</td>
                <td>MCDO JOSFEL PSJ 19 PST 46 ABARROTES</td>
                <td>NPE 001-0138153</td>
                <td>96.90</td>
                <td>CONT</td>
                <td>..............</td>
            </tr>
            <tr>
                <td>07012366</td>
                <td>CLIENTE ROCIO</td>
                <td>PRODUCTORES PSJ.GIRALDO PST.11 SANTA ANITA</td>
                <td>NPE 001-0138154</td>
                <td>206.50</td>
                <td>CONT</td>
                <td>..............</td>
            </tr>
            <tr>
                <td>07012380</td>
                <td>ALICIA POMA</td>
                <td>MCD.APROMEC PT.G6</td>
                <td>NPE 001-0138155</td>
                <td>609.75</td>
                <td>CONT</td>
                <td>..............</td>
            </tr>
            <tr>
                <td>07012383</td>
                <td>MARIA VILLAR</td>
                <td>MCD.APROMEC PT.D27</td>
                <td>NPE 001-0138156</td>
                <td>186.00</td>
                <td>CONT</td>
                <td>..............</td>
            </tr>

            <tr class="empty-row">
                <td colspan="7">*** RUTA: 090 : ARMANDO MAYORISTA</td>
            </tr>
            <tr>
                <td>07012297</td>
                <td>CLIENTE GLADIS</td>
                <td>MCD.BRISASPST.259 GOLOSINAS</td>
                <td>NPE 001-0138152</td>
                <td>156.00</td>
                <td>CONT</td>
                <td>..............</td>
            </tr>
            <tr>
                <td>07013214</td>
                <td>VANESSA</td>
                <td>MCDO JOSFEL PSJ 19 PST 46 ABARROTES</td>
                <td>NPE 001-0138153</td>
                <td>96.90</td>
                <td>CONT</td>
                <td>..............</td>
            </tr>
            <tr>
                <td>07012366</td>
                <td>CLIENTE ROCIO</td>
                <td>PRODUCTORES PSJ.GIRALDO PST.11 SANTA ANITA</td>
                <td>NPE 001-0138154</td>
                <td>206.50</td>
                <td>CONT</td>
                <td>..............</td>
            </tr>
            <tr>
                <td>07012380</td>
                <td>ALICIA POMA</td>
                <td>MCD.APROMEC PT.G6</td>
                <td>NPE 001-0138155</td>
                <td>609.75</td>
                <td>CONT</td>
                <td>..............</td>
            </tr>
            <tr>
                <td>07012383</td>
                <td>MARIA VILLAR</td>
                <td>MCD.APROMEC PT.D27</td>
                <td>NPE 001-0138156</td>
                <td>186.00</td>
                <td>CONT</td>
                <td>..............</td>
            </tr>
            <!-- Agrega más filas según sea necesario -->
        </tbody>
    </table>

    <!-- Segunda Tabla -->
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
                <td>058 CAMPOY TOTUS</td>
                <td>Charlie Ponce</td>
                <td>0.00</td>
            </tr>
            <tr>
                <td>060 HUACHIPA</td>
                <td>Hector Rumani</td>
                <td>0.00</td>
            </tr>
            <tr>
                <td>136 SANTA ROSA HUACHIPA</td>
                <td>Dixon Flores</td>
                <td>0.00</td>
            </tr>
        </tbody>
    </table>

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
            <tr>
                <td>058 CAMPOY TOTUS</td>
                <td>177 CERES - ATE</td>
                <td>15</td>
                <td>15</td>
                <td>781.7</td>
            </tr>
            <tr>
                <td>060 HUACHIPA</td>
                <td>090 ARMANDO MAYORISTA</td>
                <td>8</td>
                <td>8</td>
                <td>349.66</td>
            </tr>
            <tr>
                <td>136 SANTA ROSA HUACHIPA</td>
                <td>177 CERES - ATE</td>
                <td>4</td>
                <td>4</td>
                <td>3,992.15</td>
            </tr>
        </tbody>
        <tfoot>
            <td>*** TOTAL</td>
            <td></td>
            <td>27</td>
            <td>27</td>
            <td>5,622.15</td>
        </tfoot>
    </table>

    <!-- Cuarta Tabla -->
    <p>*** RESUMEN X DOCUMENTOS DE VENTA ***</p>
    <table style="margin: auto; border-collapse: collapse">
        <thead></thead>
        <tbody>
            <tr>
                <td>TOTAL CONTADO</td>
                <td>4,773.85</td>
                <td>TOTAL FACTURAS</td>
                <td>1</td>
                <td>71.40</td>
            </tr>
            <tr>
                <td>TOTAL CREDITO</td>
                <td>0.00</td>
                <td>TOTAL FACTURAS</td>
                <td>14</td>
                <td>710.30</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>TOTAL FACTURAS</td>
                <td>8</td>
                <td>3,992.15</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td style="font-weight: bold; font-size: 20px">
                    -------------
                </td>
                <td>TOTAL FACTURAS</td>
                <td>0</td>
                <td>0.00</td>
            </tr>
        </tbody>
        <tfoot>
            <td>TOTAL IMPORTE ==></td>
            <td><span>4,773.85</span></td>
            <td>TOTAL DOCUMENTOS ===></td>
            <td>23</td>
            <td>&nbsp;</td>
        </tfoot>
    </table>

    <!-- FIN DE REPORTE -->
    <p style="text-align: center; padding: 40px 0">
        *** FIN DE REPORTE ***
    </p>
</body>

</html>
