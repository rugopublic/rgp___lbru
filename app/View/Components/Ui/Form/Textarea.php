<?php

namespace App\View\Components\Ui\Form;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Textarea extends Component
{

    /**
     * Initializes the textarea instance and wires its required dependencies.
     *
     * @param ?string $name
     * @param ?string $id
     * @param ?string $label
     * @param ?string $value
     * @param ?string $placeholder
     * @param ?string $hint
     * @param ?int $maxlength
     * @param ?string $pattern
     * @param string $size
     * @param int $minRows
     * @param int $maxRows
     * @param string $resize
     * @param bool $required
     * @param bool $disabled
     * @param bool $readonly
     * @param bool $autofocus
     * @param ?string $autocomplete
     * @param bool $meter
     * @param bool $ripple
     * @param bool $autosize
     * @param ?int $rows
     * @return void
     */
    public function __construct(
        public ?string $name = null,
        public ?string $id = null,
        public ?string $label = null,
        public ?string $value = null,
        public ?string $placeholder = null,
        public ?string $hint = null,
        public ?int $maxlength = null,
        public ?string $pattern = null,
        public string $size = 'md',
        public int $minRows = 1,
        public int $maxRows = 8,
        public string $resize = 'none',
        public bool $required = false,
        public bool $disabled = false,
        public bool $readonly = false,
        public bool $autofocus = false,
        public ?string $autocomplete = null,
        public bool $meter = true,
        public bool $ripple = true,
        public bool $autosize = true,
        public ?int $rows = null,
    ) {
        $this->id ??= $this->name;

        $this->minRows = max(1, $this->minRows);

        if ($this->rows !== null) {
            $this->rows = max(1, $this->rows);
            $this->minRows = $this->rows;
            $this->maxRows = max($this->rows, $this->maxRows);
        } else {
            $this->maxRows = max($this->minRows, $this->maxRows);
        }

        if (!in_array($this->size, ['sm', 'md', 'lg'], true)) {
            $this->size = 'md';
        }

        if (!in_array($this->resize, ['none', 'vertical', 'both', 'horizontal'], true)) {
            $this->resize = 'none';
        }
    }

    /**
     * Handles render for the textarea.
     * @return View
     */
    public function render(): View
    {
        return view('components.ui.form.textarea');
    }
}
