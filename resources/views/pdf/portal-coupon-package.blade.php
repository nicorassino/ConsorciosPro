<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cupón e informe</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .box { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; margin-bottom: 12px; }
        .title { font-size: 16px; font-weight: bold; margin-bottom: 8px; }
        .muted { color: #4b5563; }
    </style>
</head>
<body>
    <div class="box">
        <div class="title">Cupón SIRO - {{ $consorcio->nombre }}</div>
        <div>Unidad: {{ $unidad->numero }} | Período: {{ $coupon['periodo'] }}</div>
        <div>1er vencimiento: ${{ number_format($coupon['monto_primer_vto'], 2, ',', '.') }}</div>
        <div>2do vencimiento: ${{ number_format($coupon['monto_segundo_vto'], 2, ',', '.') }}</div>
        <div class="muted">Código de pago: {{ $coupon['codigo_pago_electronico'] }} | Nro SIRO: {{ $coupon['nro_cupon_siro'] }}</div>
        <div class="muted">Cuenta recaudadora: {{ $consorcio->nro_cuenta_bancaria ?: 'No informada' }} (CBU oculto por política SRS)</div>
    </div>

    <div class="box">
        <div class="title">Informe económico editable</div>
        <div>{!! nl2br(e($economicBody)) !!}</div>
    </div>

    <div class="box">
        <div class="title">Cuerpo administrativo</div>
        <div>{!! nl2br(e($administrativeBody)) !!}</div>
    </div>

    <p class="muted">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</p>
</body>
</html>
