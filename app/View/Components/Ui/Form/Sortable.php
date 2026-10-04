<?php

namespace App\View\Components\Ui\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Hidehalo\Nanoid\Client as Nanoid;

class Sortable extends Component
{
    /**
     * The unique identifier for the sortable node.
     *
     * @var string
     */
    public string $id;

    /**
     * The endpoint used by this sortable to fetch data.
     * Defaults to null.
     *
     * @var string|null
     */
    public ?string $endpoint;

    /**
     * Determines whether to disallow the out-of-group items.
     * Defaults to false.
     *
     * @var bool
     */
    public bool $grouped;

    /**
     * The name of the form value.
     *
     * @var string
     */
    public string $name;

    /**
     * The initial value of the sortable.
     * Defaults to null.
     *
     * @var array|null
     */
    public ?array $value;

    /**
     * The label of a sortable.
     * Defaults to null.
     *
     * @var string|null
     */
    public ?string $label;

    /**
     * Indicates whether this field is required.
     * False by default.
     *
     * @var bool
     */
    public bool $required;

    /**
     * Determines whether the add-group button is displayed.
     *
     * @var bool
     */
    public bool $button;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $name,
        ?array $value = null,
        ?string $endpoint = null,
        ?string $label = null,
        bool $required = false,
        bool $button = true,
        bool $grouped = false,
    ) {
        $this->id = 'sortable-' . (new Nanoid())->generateId();

        $this->endpoint = $endpoint;
        $this->name = $name;
        $this->value = $value;
        $this->label = $label;
        $this->required = $required;
        $this->button = $button;
        $this->grouped = $grouped;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.form.sortable');
    }
}
