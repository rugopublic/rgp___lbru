<?php

namespace App\View\Components\Ui\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RichTextarea extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $name,
        public string $id,
        public string $inputId,
        public string $labelledby,
        public ?string $value = null,
    )
    {
        $this->value = $value ?? '';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.form.rich-textarea');
    }
}
