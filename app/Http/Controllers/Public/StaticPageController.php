<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class StaticPageController extends Controller
{

    /**
     * Handles the about HTTP action for the static page controller resource.
     * @return View
     */
    public function about(): View
    {
        return view('frontend.pages.about');
    }

    /**
     * Handles the eula HTTP action for the static page controller resource.
     * @return View
     */
    public function eula(): View
    {
        return view('frontend.pages.eula');
    }

    /**
     * Handles the privacy HTTP action for the static page controller resource.
     * @return View
     */
    public function privacy(): View
    {
        return view('frontend.pages.pp');
    }

    /**
     * Handles the help HTTP action for the static page controller resource.
     * @return View
     */
    public function help(): View
    {
        return view('frontend.help.index');
    }

    /**
     * Handles the payments HTTP action for the static page controller resource.
     * @return View
     */
    public function payments(): View
    {
        return view('frontend.help.payments');
    }

    /**
     * Handles the refunds HTTP action for the static page controller resource.
     * @return View
     */
    public function refunds(): View
    {
        return view('frontend.help.refunds');
    }
}
