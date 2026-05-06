<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PortalLayout extends Component
{
    public function __construct(
        public ?string $active = null,
        public ?string $title = null,
    ) {}

    public function render(): View
    {
        return view('layouts.portal-app');
    }
}
