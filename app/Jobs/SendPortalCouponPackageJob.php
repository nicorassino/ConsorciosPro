<?php

namespace App\Jobs;

use App\Models\PortalUser;
use App\Services\PortalPdfComposerService;
use App\Services\SiroApiService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendPortalCouponPackageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $portalUserId,
        public string $economicBody,
        public ?string $administrativeBody = null,
    ) {
    }

    public function handle(SiroApiService $siroApiService, PortalPdfComposerService $composer): void
    {
        $portalUser = PortalUser::query()->with('unidad.consorcio')->find($this->portalUserId);
        if (! $portalUser) {
            return;
        }

        $coupon = $siroApiService
            ->buildDebtCouponsForUnidad($portalUser->unidad, CarbonImmutable::today())
            ->first();

        if (! $coupon) {
            return;
        }

        $path = $composer->composeAndStore(
            $portalUser,
            $coupon,
            $this->economicBody,
            $this->administrativeBody ?? (string) $portalUser->unidad->consorcio->nota
        );

        Mail::raw('Se adjunta su cupón SIRO y el informe económico.', function ($message) use ($portalUser, $path): void {
            $message->to($portalUser->email, $portalUser->nombre)
                ->subject('Cupón SIRO e informe económico')
                ->attach(storage_path('app/'.$path));
        });
    }
}
