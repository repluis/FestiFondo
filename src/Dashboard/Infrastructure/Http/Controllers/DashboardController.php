<?php

namespace Src\Dashboard\Infrastructure\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    /** Transparencia financiera pública de una campaña (nombres y total pagado sin mora). */
    public function transparency(string $uuid): View
    {
        $data = Str::isUuid($uuid) ? $this->service->transparency($uuid) : null;

        abort_if($data === null, 404);

        return view('Dashboard.transparency', $data);
    }

    /** Dashboard: gráficas y tablas completas (solo usuarios con sesión). */
    public function index(): View
    {
        return view('Dashboard.index', $this->service->dashboard());
    }
}
