<?php
declare(strict_types=1);

// Research-only language controls, loaded by a deliberately weak caller.
function m05TypedIndex(int $index): int
{
    return $index;
}

function m05OriginalIndex(mixed $index): bool
{
    return is_int($index) && $index >= 0 && $index <= 2147483647;
}

final readonly class M05ArrayHolder
{
    public function __construct(public array $values) {}
}

function m05SensitiveThrow(#[SensitiveParameter] mixed $input): never
{
    throw new RuntimeException('fixed_reason');
}
