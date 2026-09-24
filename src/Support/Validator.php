<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Minimal array validator: define rules, get back an errors map.
 * Not meant to be exhaustive, just enough for a starter kit.
 *
 * @phpstan-type Rules array<string, list<string>>
 */
final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $rules e.g. ['email' => ['required', 'email'], 'name' => ['required', 'min:2']]
     */
    public function __construct(
        private readonly array $data,
        private readonly array $rules,
    ) {
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $value, $rule);

                // Stop at first failure per field to keep messages simple.
                if (isset($this->errors[$field])) {
                    break;
                }
            }
        }

        return $this->errors === [];
    }

    public function fails(): bool
    {
        return !$this->validate();
    }

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        $parameter = null;
        if (str_contains($rule, ':')) {
            [$rule, $parameter] = explode(':', $rule, 2);
        }

        $stringValue = is_string($value) ? $value : (string) ($value ?? '');

        match ($rule) {
            'required' => $this->checkRequired($field, $value),
            'email' => $this->checkEmail($field, $stringValue),
            'min' => $this->checkMin($field, $stringValue, (int) $parameter),
            'max' => $this->checkMax($field, $stringValue, (int) $parameter),
            'confirmed' => $this->checkConfirmed($field, $stringValue),
            default => null,
        };
    }

    private function checkRequired(string $field, mixed $value): void
    {
        $isEmpty = $value === null || $value === '' || (is_string($value) && trim($value) === '');
        if ($isEmpty) {
            $this->errors[$field] = ucfirst($field) . ' is required.';
        }
    }

    private function checkEmail(string $field, string $value): void
    {
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[$field] = 'Please provide a valid email address.';
        }
    }

    private function checkMin(string $field, string $value, int $min): void
    {
        if (mb_strlen($value) < $min) {
            $this->errors[$field] = ucfirst($field) . " must be at least {$min} characters.";
        }
    }

    private function checkMax(string $field, string $value, int $max): void
    {
        if (mb_strlen($value) > $max) {
            $this->errors[$field] = ucfirst($field) . " must be at most {$max} characters.";
        }
    }

    private function checkConfirmed(string $field, string $value): void
    {
        $confirmationField = $field . '_confirmation';
        $confirmation = $this->data[$confirmationField] ?? null;

        if ($value !== (string) ($confirmation ?? '')) {
            $this->errors[$field] = ucfirst($field) . ' confirmation does not match.';
        }
    }
}
