<?php

namespace App\View\Components\Ui\Form\Sortable;

use Closure;
use Hidehalo\Nanoid\Client as Nanoid;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Item extends Component
{
    /**
     * The unique identifier for the item node.
     *
     * @var string
     */
    public string $id;

    /**
     * The value of the item.
     *
     * @var string
     */
    public string $value;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $value,
    ) {
        $this->id = 'sortable-item-' . (new Nanoid())->generateId();

        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.form.sortable.item');
    }
}
