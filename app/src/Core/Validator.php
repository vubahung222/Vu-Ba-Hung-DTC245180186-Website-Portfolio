<?php
declare(strict_types=1);
namespace Core;

class Validator
{
    private array $errors = [];
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data, array $rules): self
    {
        $v = new self($data);
        foreach ($rules as $field => $ruleStr) {
            $ruleList = explode('|', $ruleStr);
            foreach ($ruleList as $rule) {
                $v->applyRule($field, $rule);
            }
        }
        return $v;
    }

    private function applyRule(string $field, string $rule): void
    {
        $value = $this->data[$field] ?? null;
        [$ruleName, $ruleParam] = array_pad(explode(':', $rule, 2), 2, null);

        switch ($ruleName) {
            case 'required':
                if ($value === null || trim((string)$value) === '') {
                    $this->errors[$field][] = "Trường này là bắt buộc.";
                }
                break;
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "Email không hợp lệ.";
                }
                break;
            case 'min':
                if (strlen((string)$value) < (int)$ruleParam) {
                    $this->errors[$field][] = "Tối thiểu {$ruleParam} ký tự.";
                }
                break;
            case 'max':
                if (strlen((string)$value) > (int)$ruleParam) {
                    $this->errors[$field][] = "Tối đa {$ruleParam} ký tự.";
                }
                break;
            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->errors[$field][] = "Phải là số.";
                }
                break;
            case 'between':
                [$min, $max] = explode(',', $ruleParam);
                $num = (int)$value;
                if ($num < (int)$min || $num > (int)$max) {
                    $this->errors[$field][] = "Phải từ {$min} đến {$max}.";
                }
                break;
            case 'url':
                if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field][] = "URL không hợp lệ.";
                }
                break;
        }
    }

    public function fails(): bool { return !empty($this->errors); }
    public function passes(): bool { return empty($this->errors); }
    public function errors(): array { return $this->errors; }
    public function firstError(string $field): ?string { return $this->errors[$field][0] ?? null; }

    public function validated(): array
    {
        return array_intersect_key($this->data, array_flip(array_keys($this->errors) ?: []));
    }
}
