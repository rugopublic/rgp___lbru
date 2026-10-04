<?php

namespace App\View\Components\Ui\Layout\Widgets;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ActionBar extends Component
{
    /**
     * Determines whether the bar is embedded on the page.
     *
     * @var bool
     */
    public bool $embedded;

    /**
     * Create a new component instance.
     */
    public function __construct(
        bool $embedded = false
    )
    {
        $this->embedded = $embedded;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.layout.widgets.action-bar');
    }
}
