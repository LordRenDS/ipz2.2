<?php

namespace Ren\App\DTO;

require_once __DIR__ . '/../../vendor/autoload.php';

class UserDTO
{
    public readonly ?bool $admin;

    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $surname = null,
        public ?string $email = null,
        public ?string $password = null,
    ) {}
}
