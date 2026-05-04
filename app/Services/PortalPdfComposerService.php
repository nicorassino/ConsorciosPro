<?php

namespace App\Services;

use App\Models\PortalUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortalPdfComposerService
{
    public function composeAndStore(PortalUser $portalUser, array $coupon, string $economicBody, string $administrativeBody): string
    {
        $html = view('pdf.portal-coupon-package', [
            'portalUser' => $portalUser,
            'unidad' => $portalUser->unidad,
            'consorcio' => $portalUser->unidad->consorcio,
            'coupon' => $coupon,
            'economicBody' => $economicBody,
            'administrativeBody' => $administrativeBody,
            'generatedAt' => CarbonImmutable::now(),
        ])->render();

        $baseName = 'cupon_'.Str::slug($portalUser->unidad->consorcio->nombre).'_'.$coupon['periodo'].'_'.$portalUser->id;

        $domPdfFacade = 'Barryvdh\\DomPDF\\Facade\\Pdf';

        if (class_exists($domPdfFacade)) {
            $binary = $domPdfFacade::loadHTML($html)->output();
            $path = "portal/cupones/{$baseName}.pdf";
            Storage::disk('local')->put($path, $binary);

            return $path;
        }

        $path = "portal/cupones/{$baseName}.html";
        Storage::disk('local')->put($path, $html);

        return $path;
    }
}
