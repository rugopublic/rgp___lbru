<?php

namespace App\View\Components\Ui\Form\Sortable;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Hidehalo\Nanoid\Client as Nanoid;

class Group extends Component
{
    /**
     * The unique identifier for the group node.
     *
     * @var string
     */
    public string $id;

    /**
     * The identifier of the group.
     *
     * @var string
     */
    public string $groupId;

    /**
     * The label of the group.
     *
     * @var string
     */
    public string $label;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $groupId,
        string $label,
    ) {
        $this->id = 'sortable-group-' . (new Nanoid())->generateId();

        $this->groupId = $groupId;
        $this->label = $label;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.form.sortable.group');
    }
}
