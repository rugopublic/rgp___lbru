<?php

namespace App\View\Components\Ui\Form;

use App\Enums\Ui\FormMethod;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Container extends Component
{
    /**
     * The default labels for the form submission button.
     *
     * @var array<string, string>
     */
    public $defaultLabels = [
        FormMethod::GET->value     => 'Получить',
        FormMethod::POST->value    => 'Отправить',
        FormMethod::PUT->value     => 'Сохранить',
        FormMethod::PATCH->value   => 'Изменить',
        FormMethod::DELETE->value  => 'Удалить',
        'reset'                    => 'Сбросить',
    ];

    /**
     * The endpoint to send this form to.
     *
     * @var string
     */
    public string $endpoint;

    /**
     * The identifier for this form for tracking changes.
     *
     * @var string
     */
    public string $identifier;

    /**
     * The list of form labels.
     *
     * @var array<string, string>
     */
    public $labels;

    /**
     * Determines whether to refresh a page after successful submission.
     *
     * @var bool
     */
    public bool $refresh;

    /**
     * Determines whether to use AJAX form sender.
     *
     * @var bool
     */
    public bool $ajax;

    /**
     * Determines whether the form is embedded on the page.
     *
     * @var bool
     */
    public bool $embedded;

    /**
     * Determines whether the form manages a removable object.
     *
     * @var bool
     */
    public bool $removable;

    /**
     * The request method of the form.
     *
     * @var FormMethod
     */
    public FormMethod $method;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $endpoint,
        string $identifier,
        array|null $labels = null,
        bool $refresh = true,
        bool $ajax = true,
        bool $embedded = false,
        bool $removable = true,
        FormMethod $method = FormMethod::POST,
    )
    {
        $this->endpoint = $endpoint;
        $this->identifier = $identifier;
        $this->labels = $labels === null
                        ? $this->defaultLabels
                        : array_replace($this->defaultLabels, $labels);
        $this->refresh = $refresh;
        $this->ajax = $ajax;
        $this->embedded = $embedded;
        $this->removable = $removable;
        $this->method = $method;
    }

    /**
     * Chooses a method valid for the <form> tag.
     *
     * @return string A method valid for the form attribute.
     */
    public function formMethodAttribute(): string
    {
        return $this->method === FormMethod::GET ? 'GET' : 'POST';
    }

    /**
     * Provides a valid label for the form submission button.
     *
     * @return string A label for the form submission button.
     */
    public function submitButtonLabel(): string
    {
        return $this->labels[$this->method->value];
    }

    /**
     * Provides a valid label for the form resetting button.
     *
     * @return string A label for the form resetting button.
     */
    public function resetButtonLabel(): string
    {
        return $this->labels['reset'];
    }

    /**
     * Determines whether a _method field is needed for this form.
     *
     * @return bool Whether to add a method field to the form.
     */
    public function requiresMethodField(): bool
    {
        return $this->method !== FormMethod::GET
            && $this->method !== FormMethod::POST;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.form.container');
    }
}
