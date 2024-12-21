<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pagos</title>
    <style type="text/css">
        * {
            font-family: sans-serif;
        }

        @page {
            margin-top: 1.5cm;
            margin-bottom: 0.85cm;
            margin-left: 0.5cm;
            margin-right: 0.5cm;
            border: 5px solid blue;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 20px;
        }

        table thead tr th,
        tbody tr td {
            word-wrap: break-word;
            padding: 5px;
        }

        table thead tr th {
            font-size: 10pt;
        }

        tbody tr td {
            font-size: 9pt;
        }

        .encabezado {
            width: 100%;
        }

        .logo img {
            position: absolute;
            width: 200px;
            height: 90px;
            top: -20px;
            left: -20px;
        }

        h2.titulo {
            color: #05568e;
            width: 450px;
            margin: auto;
            margin-top: 15px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14pt;
        }

        .texto {
            color: #05568e;
            width: 400px;
            text-align: center;
            margin: auto;
            margin-top: 15px;
            font-weight: bold;
            font-size: 1.1em;
        }

        .fecha {
            width: 400px;
            text-align: center;
            margin: auto;
            margin-top: 15px;
            font-weight: normal;
            font-size: 0.85em;
        }

        .txt_filtro {
            width: 800px;
            text-align: center;
            margin: auto;
            margin-top: 15px;
            font-weight: normal;
            font-size: 0.85em;
        }

        .centreado {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .txt-lg {
            font-size: 12pt;
        }

        .justificado {
            text-align: justify;
        }

        .completo {
            background: rgb(47, 255, 151);
        }

        .pendiente {
            background: rgb(255, 131, 131);
        }
    </style>
</head>

<body>
    <div class="encabezado">
        <h4 class="texto">PAGOS</h4>
        <h4 class="fecha">Expedido: {{ date('d-m-Y') }}</h4>
        <h4 class="fecha">(Expresado en {{ $moneda_principal->descripcion }})</h4>
        @if ($o_cliente)
            <h4 class="txt_filtro">Filtrado por Cliente<br> {{ $o_cliente->nombre }}</h4>
        @endif
        @if ($o_proyecto || $o_trabajo)
            <h4 class="txt_filtro">Filtrado por Proyecto/Trabajo<br />
                {{ $o_proyecto->alias }}<br>{{ $o_proyecto->nombre }}
            </h4>
        @endif

        @if ($filtro == 'estado_trabajo' && $estado_trabajo != 'todos')
            <h4 class="txt_filtro">Filtrado por estado de trabajo<br /> {{ $estado_trabajo }}</h4>
        @endif
        @if ($filtro == 'estado_pago' && $estado_pago != 'todos')
            <h4 class="txt_filtro">Filtrado por estado de pago<br /> {{ $estado_pago }}</h4>
        @endif
        @if ($filtro_fecha != 'todos')
            <h4 class="txt_filtro">Filtrado por fechas<br />Del {{ date('d/m/Y', strtotime($fecha_ini)) }} al
                {{ date('d/m/Y', strtotime($fecha_fin)) }}
            </h4>
        @endif
    </div>
    <table border="1">
        <thead>
            <tr>
                <th width="4%">N°</th>
                <th>Fecha Pago</th>
                <th>Proyecto</th>
                <th>Trabajo</th>
                <th>Cliente</th>
                <th>Foto Comprobante</th>
                <th>Archivo Comprobante</th>
                <th>Descripción Archivos</th>
                <th>Monto {{ $moneda_principal->nombre }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $cont = 1;
                $total_cancelado = 0;
            @endphp
            @foreach ($pagos as $pago)
                <tr>
                    <td class="centreado">{{ $cont++ }}</td>
                    <td>{{ $pago->fecha_pago }}</td>
                    <td class="centreado">{{ $pago->trabajo->proyecto->nombre }}<br /><span
                            class="bold">({{ $pago->trabajo->proyecto->alias }})</span></td>
                    <td>{!! $pago->trabajo->descripcion !!}</td>
                    <td>{{ $pago->cliente->nombre }}</td>
                    <td>{{ $pago->foto_comprobante ? 'SI' : 'NO' }}</td>
                    <td>{{ $pago->archivo_comprobante ? 'SI' : 'NO' }}</td>
                    <td>{{ $pago->descripcion_archivo }}</td>
                    <td class="centreado">{{ $pago->monto }}</td>
                </tr>
                @php
                    $total_cancelado += $pago->monto;
                @endphp
            @endforeach
            <tr>
                <td class="bold txt-lg derecha" colspan="8">TOTAL</td>
                <td class="bold txt-lg centreado">{{ number_format($total_cancelado, 2, '.', ' ') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
