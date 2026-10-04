<?php

namespace App\View\Components\Ui\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Hidehalo\Nanoid\Client as Nanoid;
use InvalidArgumentException;

class Typeahead extends Component
{
    /**
     * The unique identifier for the select node.
     *
     * @var string
     */
    public string $id;

    /**
     * The endpoint to send search queries from this typeahead to.
     * Defaults to null.
     *
     * @var string|null
     */
    public ?string $endpoint;

    /**
     * The currently selected value of a typeahead.
     * Defaults to null.
     *
     * @var string|null
     */
    public ?string $value;

    /**
     * The initial options.
     * Defaults to an empty array.
     *
     * @var array<int, array<string, string>>
     */
    public array $options;

    /**
     * The name of the form value.
     *
     * @var string
     */
    public string $name;

    /**
     * The label of a typeahead.
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
     * Create a new component instance.
     */
    public function __construct(
        string $name,
        array $options = [],
        ?string $endpoint = null,
        ?string $value = null,
        ?string $label = null,
        bool $required = false,
    ) {
        $this->options = collect($options)
            ->map(
                function ($option, $key): array {
                    if (
                        is_array($option)
                        && array_key_exists('value', $option)
                        && array_key_exists('label', $option)
                    ) {
                        return [
                            'value' => (string) $option['value'],
                            'label' => (string) $option['label'],
                        ];
                    }

                    return [
                        'value' => (string) $key,
                        'label' => (string) $option,
                    ];
                }
            )
            ->values()
            ->all();

        if ($endpoint === null && $value !== null) {
            $exists = collect($this->options)->contains(
                fn (array $option): bool => $option['value'] === $value
            );

            if (!$exists) {
                throw new InvalidArgumentException(
                    "The selected typeahead value '{$value}' was not found in the options."
                );
            }
        }

        $this->id       = 'typahead-' . (new Nanoid())->generateId();

        $this->name = $name;
        $this->endpoint = $endpoint;
        $this->value = $value;
        $this->label = $label;
        $this->required = $required;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.form.typeahead');
    }
}
