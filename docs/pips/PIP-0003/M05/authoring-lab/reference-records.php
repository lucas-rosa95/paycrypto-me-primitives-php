<?php
declare(strict_types=1);

namespace M05C2Lab\NamedReferences;

// Research-only closed reference variants. Construction executes no flow.
final readonly class Input
{
    public function __construct(public string $name) {}
}
final readonly class Parameter
{
    public function __construct(public string $name) {}
}
final readonly class StepOutput
{
    public function __construct(public string $step, public string $port) {}
}
final readonly class Field
{
    public function __construct(public Input|Parameter|StepOutput|Field $source, public string $name) {}
}
final readonly class Link
{
    public function __construct(public string $port, public Input|Parameter|StepOutput|Field $source) {}
}
