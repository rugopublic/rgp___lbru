<?php

namespace App\View\Components\Ui\Form\Wizard;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Step extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public int $order = 1
    )
    {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.form.wizard.step');
    }
}
