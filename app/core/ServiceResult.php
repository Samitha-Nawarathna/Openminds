<?php

class ServiceResult {
    private bool $success;
    private array $errors;
    private mixed $data;

    public function __construct(bool $success, array $errors = [], mixed $data = null) {
        $this->success = $success;
        $this->errors = $errors;
        $this->data = $data;
    }

    public static function success(mixed $data = null): self {
        return new self(true, [], $data);
    }

    public static function failure(array $errors): self {
        return new self(false, $errors);
    }

    public function is_success(): bool {
        return $this->success;
    }

    public function get_errors(): array {
        return $this->errors;
    }

    public function get_errors_string(): string {
        return implode(", ", $this->errors).trim(",");
    }

    public function get_data(): mixed {
        return $this->data;
    }

    public function has_errors(): bool {
        return !empty($this->errors);
    }
}