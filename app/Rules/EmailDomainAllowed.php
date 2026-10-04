<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class EmailDomainAllowed implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param string $attribute Field name
     * @param mixed $value Field value
     * @param Closure $fail Callback to trigger when validation fails.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $whitelist = collect(config('email.allowed_domains', []));

        $domain = Str::of($value)
            ->trim()
            ->lower()
            ->afterLast('@')
            ->toString();

        if( $whitelist->doesntContainStrict($domain) ) {
            $fail('validation.email_domain_not_allowed');
        }
    }
}
