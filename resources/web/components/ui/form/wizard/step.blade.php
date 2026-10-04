@aware(['step' => 1])

@if ($step === $order)
    <div class="step-container">
        {{ $slot }}
    </div>
@endif
