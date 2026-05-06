<?php

namespace App\Http\Controllers;

use App\Services\SiroApiService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalDashboardController extends Controller
{
    public function index(Request $request, SiroApiService $siroApiService): View
    {
        $context = $this->buildContext($request);

        $coupons = $siroApiService->buildDebtCouponsForUnidad($context['unidad'], CarbonImmutable::today());
        $payments = $context['unidad']->cobranzas()->latest('fecha_pago')->limit(10)->get();

        return view('portal.dashboard', array_merge($context, [
            'coupons' => $coupons,
            'payments' => $payments,
        ]));
    }

    public function notes(Request $request): View
    {
        return view('portal.notes', $this->buildContext($request));
    }

    public function contact(Request $request): View
    {
        return view('portal.contact', $this->buildContext($request));
    }

    private function buildContext(Request $request): array
    {
        $portalUser = $request->user('portal');
        $unidad = $portalUser->unidad()->with('consorcio')->firstOrFail();

        return [
            'portalUser' => $portalUser,
            'unidad' => $unidad,
            'consorcio' => $unidad->consorcio,
        ];
    }
}
