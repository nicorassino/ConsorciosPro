<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Resumen financiero</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin: 16px 0 8px; }
        .muted { color: #6b7280; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Resumen financiero mensual</h1>
    <p class="muted">
        Consorcio: {{ $data['consorcio']->nombre }} ·
        Período: {{ $data['periodo']->format('m/Y') }}
    </p>

    <h2>Conciliación</h2>
    <table>
        <tr><th>Concepto</th><th class="right">Monto</th></tr>
        <tr><td>Saldo inicial</td><td class="right">${{ number_format($data['conciliacion']['saldo_inicial'], 2, ',', '.') }}</td></tr>
        <tr><td>Ingresos</td><td class="right">${{ number_format($data['conciliacion']['ingresos'], 2, ',', '.') }}</td></tr>
        <tr><td>Egresos</td><td class="right">${{ number_format($data['conciliacion']['egresos'], 2, ',', '.') }}</td></tr>
        <tr><td>Saldo disponible</td><td class="right">${{ number_format($data['conciliacion']['saldo_disponible'], 2, ',', '.') }}</td></tr>
        <tr><td>Obligaciones pendientes</td><td class="right">${{ number_format($data['conciliacion']['obligaciones_pendientes'], 2, ',', '.') }}</td></tr>
    </table>

    <h2>Estadísticas</h2>
    <table>
        <tr><th>Indicador</th><th class="right">Valor</th></tr>
        <tr><td>Total cobrado</td><td class="right">${{ number_format($data['stats']['total_cobrado'], 2, ',', '.') }}</td></tr>
        <tr><td>Capital cobrado</td><td class="right">${{ number_format($data['stats']['capital_cobrado'], 2, ',', '.') }}</td></tr>
        <tr><td>Interés cobrado</td><td class="right">${{ number_format($data['stats']['interes_cobrado'], 2, ',', '.') }}</td></tr>
        <tr><td>Cobrabilidad</td><td class="right">{{ number_format($data['stats']['cobrabilidad'], 2, ',', '.') }}%</td></tr>
        <tr><td>Pagos registrados</td><td class="right">{{ $data['stats']['pagos_registrados'] }}</td></tr>
    </table>

    <h2>Top deudores</h2>
    <table>
        <tr><th>Unidad</th><th class="right">Saldo</th></tr>
        @forelse ($data['deuda']['deudores']->take(10) as $deudor)
            <tr>
                <td>UF {{ $deudor['unidad'] }}</td>
                <td class="right">${{ number_format($deudor['saldo'], 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="2">Sin deuda pendiente en el período.</td></tr>
        @endforelse
    </table>
</body>
</html>
