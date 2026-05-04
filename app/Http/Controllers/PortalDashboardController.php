<?php

namespace App\Http\Controllers;

use App\Services\SiroApiService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalDashboardController extends Controller
{
    public function __invoke(Request $request, SiroApiService $siroApiService): View
    {
        $portalUser = $request->user('portal');
        $unidad = $portalUser->unidad()->with('consorcio')->firstOrFail();
        $consorcio = $unidad->consorcio;

        $coupons = $siroApiService->buildDebtCouponsForUnidad($unidad, CarbonImmutable::today());
        $payments = $unidad->cobranzas()->latest('fecha_pago')->limit(10)->get();

        return view('portal.dashboard', [
            'portalUser' => $portalUser,
            'unidad' => $unidad,
            'consorcio' => $consorcio,
            'coupons' => $coupons,
            'payments' => $payments,
        ]);
    }
}
