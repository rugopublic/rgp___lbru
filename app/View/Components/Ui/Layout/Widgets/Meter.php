<?php

namespace App\View\Components\Ui\Layout\Widgets;

use Closure;
use Hidehalo\Nanoid\Client as Nanoid;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

class Meter extends Component
{
    /**
     * The unique identifier for the inner meter.
     *
     * @var string
     */
    public string $id;

    /**
     * Whether to display relative or absolute progress.
     * `true`: Display relative progress.
     * `false`: Display absolute progress.
     *
     * False by default.
     *
     * @var bool
     */
    public bool $relative;

    /**
     * The minimum value for the meter.
     * Must be nonnegative.
     * 0 by default.
     *
     * @var int
     */
    public int $min;

    /**
     * The maximum value for the meter.
     * Must be greater than the minimum value.
     * 100 by default.
     *
     * @var int
     */
    public int $max;

    /**
     * The current value for the meter.
     * 0 by default.
     *
     * @var int
     */
    public int $value;

    /**
     * Create a new component instance.
     */
    public function __construct(
        int     $value    = 0,
        int     $min      = 0,
        int     $max      = 100,
        bool    $relative = false
    )
    {
        if($min < 0) {
            throw new InvalidArgumentException("The meter's minimum value must be nonnegative.");
        } elseif($max <= $min) {
            throw new InvalidArgumentException("The meter's maximum value must be greater than its minimum value.");
        } elseif($value < $min) {
            throw new InvalidArgumentException("The meter's value must not be less than its minimum value.");
        } elseif($value > $max) {
            throw new InvalidArgumentException("The meter's value must not be more than its maximum value.");
        }

        $this->value    = $value;
        $this->min      = $min;
        $this->max      = $max;
        $this->relative = $relative;

        $this->id       = 'meter-' . (new Nanoid())->generateId();
    }

    /**
     * Provides percentage value of progress.
     *
     * @param int $precision The required precision. Defaults to 2.
     *
     * @return float
     */
    public function percentageValue(int $precision = 2): float
    {
        return round(
            100 * ($this->value - $this->min) / ($this->max - $this->min),
            max(0, $precision),
            PHP_ROUND_HALF_DOWN
        );
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.layout.widgets.meter');
    }
}
