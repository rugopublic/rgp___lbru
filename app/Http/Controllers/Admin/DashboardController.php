<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\View\View;

final class DashboardController extends Controller
{

    /**
     * Initializes the dashboard controller instance and wires its required dependencies.
     *
     * @param DashboardService $dashboard
     * @return void
     */
    public function __construct(private readonly DashboardService $dashboard)
    {
    }

    /**
     * Builds the listing response and loads selector/filter data.
     * @return View
     */
    public function index(): View
    {
        return view('admin.dashboard.index', $this->dashboard->overview());
    }
}
