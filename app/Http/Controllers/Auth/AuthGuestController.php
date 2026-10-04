<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class AuthGuestController extends Controller
{

    /**
     * Builds the listing response and loads selector/filter data.
     * @return View
     */
    public function index(): View
    {
        return view('frontend.auth.index');
    }
}
