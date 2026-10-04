<?php

namespace App\View\Components\Ui\Form;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Str;

class Input extends Component
{
    public string $name;

    public ?string $placeholder;

    public ?string $id;

    public ?string $label;

    public mixed $value;

    public string $size;

    public ?int $maxlength;

    public ?string $regexp;

    public ?string $hint;

    public bool $meter;

    public bool $required;

    public bool $disabled;

    public bool $readonly;

    public bool $autofocus;

    /**
     * Initializes the input instance and wires its required dependencies.
     *
     * @param string $name
     * @param ?string $placeholder
     * @param ?string $id
     * @param ?string $label
     * @param mixed $value
     * @param string $size
     * @param ?int $maxlength
     * @param ?string $regexp
     * @param ?string $hint
     * @param bool $meter
     * @param bool $required
     * @param bool $disabled
     * @param bool $readonly
     * @param bool $autofocus
     * @return void
     */
    public function __construct(
        string $name,
        ?string $placeholder = null,
        ?string $id = null,
        ?string $label = null,
        mixed $value = null,
        string $size = 'md',
        ?int $maxlength = null,
        ?string $regexp = null,
        ?string $hint = null,
        bool $meter = false,
        bool $required = false,
        bool $disabled = false,
        bool $readonly = false,
        bool $autofocus = false,
    ) {
        $this->name = $name;

        $this->placeholder = $placeholder;

        $this->id = $id ?: $this->makeId($name);

        $this->label = $label;

        /*
         * После неудачной отправки формы Laravel должен вернуть
         * пользователю именно введённое им значение.
         */
        $this->value = old($name, $value);

        $this->size = in_array($size, ['sm', 'md', 'lg'], true)
            ? $size
            : 'md';

        $this->maxlength = $maxlength;

        $this->regexp = $regexp;

        $this->hint = $hint;

        $this->meter = $meter && $maxlength !== null;

        $this->required = $required;

        $this->disabled = $disabled;

        $this->readonly = $readonly;

        $this->autofocus = $autofocus;
    }

    /**
     * Handles render for the input.
     * @return View
     */
    public function render(): View
    {
        return view('components.ui.form.input');
    }

    /**
     * Handles make id for the input.
     *
     * @param string $name
     * @return string
     */
    protected function makeId(string $name): string
    {
        $id = str_replace(
            ['[', ']', '.', '_'],
            '-',
            $name
        );

        $id = Str::slug($id, '-');

        return $id !== ''
            ? $id
            : 'ui-input-' . Str::random(8);
    }
}
