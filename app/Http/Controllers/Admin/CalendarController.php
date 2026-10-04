<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Public\PublicationService;
use Illuminate\Http\Request;

final class CalendarController extends Controller
{

    /**
     * Initializes the calendar controller instance and wires its required dependencies.
     *
     * @param PublicationService $publication
     * @return void
     */
    public function __construct(private readonly PublicationService $publication) {}

    /**
     * Builds the listing response and loads selector/filter data.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));
        $year = (int) $request->integer('year', now()->year);
        $from = now()->setDate($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth()->endOfDay();
        $events = $this->publication->calendar($from, $to);
        $months = [1=>'Январь',2=>'Февраль',3=>'Март',4=>'Апрель',5=>'Май',6=>'Июнь',7=>'Июль',8=>'Август',9=>'Сентябрь',10=>'Октябрь',11=>'Ноябрь',12=>'Декабрь'];
        return view('admin.calendar.index', compact('events','month','year','months'));
    }
}
