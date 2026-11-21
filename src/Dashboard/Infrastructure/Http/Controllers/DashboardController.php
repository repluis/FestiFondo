<?php

namespace Src\Dashboard\Infrastructure\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Src\Dashboard\Application\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $service) {}

    /** Inicio: portada pública para invitados, resumen con datos reales para usuarios. */
    public function home(): View
    {
        if (!Auth::check()) {
            return view('welcome');
        }

        return view('Dashboard.home', $this->service->home());
    }

    /** Dashboard: gráficas y tablas completas (solo usuarios con sesión). */
    public function index(): View
    {
        return view('Dashboard.index', $this->service->dashboard());
    }
}
