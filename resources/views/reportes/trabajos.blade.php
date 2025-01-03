<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Trabajos</title>
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
            font-size: 9pt;
        }

        tbody tr td {
            font-size: 7pt;
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
        <h4 class="texto">LISTA DE TRABAJOS</h4>
        <h4 class="fecha">Expedido: {{ date('d-m-Y') }}</h4>
        <h4 class="fecha">(Expresado en {{ $moneda_principal->descripcion }})</h4>
        @if ($o_cliente)
            <h4 class="txt_filtro">Filtrado por Cliente<br> {{ $o_cliente->nombre }}</h4>
        @endif
        @if ($o_proyecto || $o_trabajo)
            <h4 class="txt_filtro">Filtrado por Proyecto/Trabajo<br />
                {{ $o_proyecto->alias }} - {{ $o_proyecto->nombre }}
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
                <th width="10%">Proyecto</th>
                <th>Cliente</th>
                <th width="14%">Descripción</th>
                <th>Fecha Inicio</th>
                <th width="4%">Plazo Días</th>
                <th>Fecha Entrega</th>
                <th>Estado/Trabajo</th>
                <th>Fecha de Envío</th>
                <th>Fecha de conclusión</th>
                <th width="9%">Costo</th>
                <th width="9%">Cancelado</th>
                <th width="9%">Saldo</th>
                <th>Estado/Pago</th>
            </tr>
        </thead>
        <tbody>
            @php
                $cont = 1;
                $total_saldo = 0;
                $total_cancelado = 0;
                $total_costo = 0;
                $total_enviados = 0;
                $total_procesos = 0;
                $total_concluidos = 0;
                $total_pago_completos = 0;
                $total_pago_pendientes = 0;
            @endphp
            @foreach ($trabajos as $trabajo)
                @php
                    if ($trabajo->estado_pago == 'PENDIENTE') {
                        $total_pago_pendientes++;
                    }
                    if ($trabajo->estado_pago == 'COMPLETO') {
                        $total_pago_completos++;
                    }
                    if ($trabajo->estado_trabajo == 'EN PROCESO') {
                        $total_procesos++;
                    }
                    if ($trabajo->estado_trabajo == 'CONCLUIDO') {
                        $total_concluidos++;
                    }
                    if ($trabajo->estado_trabajo == 'ENVIADO') {
                        $total_enviados++;
                    }
                @endphp
                <tr>
                    <td class="centreado">{{ $cont++ }}</td>
                    <td class="centreado">{{ $trabajo->proyecto->nombre }}<br /><span
                            class="bold">({{ $trabajo->proyecto->alias }})</span></td>
                    <td>{{ $trabajo->cliente->nombre }}</td>
                    <td>{!! $trabajo->descripcion !!}</td>
                    <td>{{ $trabajo->fecha_inicio }}</td>
                    <td>{{ $trabajo->dias_plazo }}</td>
                    <td>{{ $trabajo->fecha_entrega }}</td>
                    <td>{{ $trabajo->estado_trabajo }}</td>
                    <td>{{ $trabajo->fecha_envio }}</td>
                    <td>{{ $trabajo->fecha_conclusion }}</td>
                    <td class="centreado">{{ number_format($trabajo->costo, 2, '.', ',') }}</td>
                    <td class="centreado">{{ number_format($trabajo->cancelado, 2, '.', ',') }}
                    </td>
                    <td class="centreado">{{ number_format($trabajo->saldo, 2, '.', ',') }}</td>
                    <td class="{{ mb_strtolower($trabajo->estado_pago) }}">{{ $trabajo->estado_pago }}</td>
                </tr>
                @php
                    $total_saldo += $trabajo->saldo;
                    $total_cancelado += $trabajo->cancelado;
                    $total_costo += $trabajo->costo;
                @endphp
            @endforeach
            <tr>
                <td class="bold txt-lg" colspan="10">TOTALES</td>
                <td class="bold txt-lg">{{ number_format($total_costo, 2, '.', ',') }}</td>
                <td class="bold txt-lg">{{ number_format($total_cancelado, 2, '.', ',') }}</td>
                <td class="bold txt-lg">{{ number_format($total_saldo, 2, '.', ',') }}</td>
                <td class="bold txt-lg"></td>
            </tr>
        </tbody>
    </table>
    <table border="1">
        <thead>
            <tr>
                <th colspan="8">
                    RESUMEN
                </th>
            </tr>
            <tr>
                <th>COSTOS</th>
                <th>CANCELADO</th>
                <th>SALDO</th>
                <th>EN PROCESO</th>
                <th>ENVIADOS</th>
                <th>CONCLUIDOS</th>
                <th>PAGOS COMPLETOS</th>
                <th>PAGOS PENDIENTES</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="centreado txt-lg">{{ number_format($total_costo, 2, '.', ',') }}</td>
                <td class="centreado txt-lg">{{ number_format($total_cancelado, 2, '.', ',') }}</td>
                <td class="centreado txt-lg">{{ number_format($total_saldo, 2, '.', ',') }}</td>
                <td class="centreado txt-lg">{{ $total_procesos }}</td>
                <td class="centreado txt-lg">{{ $total_enviados }}</td>
                <td class="centreado txt-lg">{{ $total_concluidos }}</td>
                <td class="centreado txt-lg">{{ $total_pago_completos }}</td>
                <td class="centreado txt-lg">{{ $total_pago_pendientes }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
