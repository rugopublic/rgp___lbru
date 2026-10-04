<?php

namespace App\View\Components\Ui\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Hidehalo\Nanoid\Client as Nanoid;
use InvalidArgumentException;

class Dropdown extends Component
{
    /**
     * The unique identifier for the select node.
     *
     * @var string
     */
    public string $id;

    /**
     * The name of the dropdown form field.
     *
     * @var string
     */
    public string $name;

    /**
     * The label of the dropdown form field.
     * Null by default.
     *
     * @var string|null
     */
    public ?string $label;

    /**
     * The fallback value of the dropdown (when nothing is selected).
     * Null by default.
     *
     * @var string|null
     */
    public ?string $fallback;

    /**
     * The current value of the dropdown.
     * Null by default.
     *
     * @var string|null
     */
    public ?string $value;

    /**
     * The available options of the dropdown.
     *
     * @var array<string, string>
     */
    public array $options;

    /**
     * Indicates whether this field is required.
     * False by default.
     *
     * @var bool
     */
    public bool $required;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string      $name,
        array       $options,
        ?string     $label          = null,
        ?string     $fallback       = null,
        ?string     $value          = null,
        bool        $required       = false,
    )
    {
        if ($value !== null && !array_key_exists($value, $options)) {
            throw new InvalidArgumentException(
                "The dropdown's value must exist in the available options."
            );
        }

        $this->id       = 'sel-' . (new Nanoid())->generateId();

        $this->fallback = $fallback === null
                            ? 'Выберите значение'
                            : $fallback;

        $this->name     = $name;
        $this->label    = $label;
        $this->value    = $value;
        $this->options  = $options;
        $this->required = $required;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.form.dropdown');
    }
}
