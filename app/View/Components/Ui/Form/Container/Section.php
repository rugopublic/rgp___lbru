<?php

namespace App\View\Components\Ui\Form\Container;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Section extends Component
{
    /**
     * The title of this section.
     *
     * @var string
     */
    public string $title;

    /**
     * The subtitle of this section.
     *
     * @var string
     */
    public string $subtitle;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $title,
        string $subtitle = ''
    )
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.form.container.section');
    }
}
