<?php

namespace App\View\Components\Ui\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Wizard extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public int $step = 1
    )
    {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.form.wizard');
    }
}
