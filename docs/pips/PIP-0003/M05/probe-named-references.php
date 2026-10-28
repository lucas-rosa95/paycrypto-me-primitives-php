<?php
declare(strict_types=1);

namespace M05C2Lab;

// Round two reuses the first experiment's structural routine and cases.
// Its assertions run too; suppress only its intermediate JSON report.
ob_start();
require __DIR__ . '/probe-authoring.php';
ob_end_clean();
$baselineChecks = count($checks);

function mapFields(mixed $value, array $required, array $optional = []): array
{
    demand(is_array($value), 'map-required');
    foreach ($value as $key => $_) {
        demand(is_string($key) && in_array($key, [...$required, ...$optional], true), 'unknown-map-field');
    }
    foreach ($required as $key) {
        demand(array_key_exists($key, $value), 'missing-map-field');
    }
    return $value;
}

function textField(mixed $value): string
{
    demand(is_string($value) && $value !== '', 'non-text-field');
    return $value;
}

function namedSource(mixed $value): array
{
    demand(is_array($value) && isset($value['kind']) && is_string($value['kind']), 'reference-kind-required');
    $fields = match ($value['kind']) {
        'input', 'parameter' => ['kind', 'name'],
        'output' => ['kind', 'step', 'port'],
        'field' => ['kind', 'source', 'name'],
        default => throw new Rejected('unknown-reference-kind'),
    };
    $value = mapFields($value, $fields);
    return match ($value['kind']) {
        'input', 'parameter' => [$value['kind'], textField($value['name'])],
        'output' => ['output', textField($value['step']), textField($value['port'])],
        'field' => ['field', namedSource($value['source']), textField($value['name'])],
    };
}

function namedLinks(mixed $value): array
{
    demand(is_array($value) && array_is_list($value), 'binding-list-required');
    return array_map(static function ($entry): array {
        $entry = mapFields($entry, ['port', 'source']);
        return ['port' => textField($entry['port']), 'source' => namedSource($entry['source'])];
    }, $value);
}

function fromAssociative(mixed $value): array
{
    demand(is_array($value) && array_is_list($value), 'flow-list-required');
    return array_map(static function ($entry): array {
        $flow = mapFields($entry, ['id', 'contract', 'steps', 'outputs']);
        $id = textField($flow['id']);
        $contract = textField($flow['contract']);
        $catalogue = contracts();
        demand(isset($catalogue[$contract]), 'unknown-contract');
        demand(is_array($flow['steps']) && array_is_list($flow['steps']), 'step-list-required');
        $steps = array_map(static function ($entry): array {
            $step = mapFields($entry, ['id', 'kind', 'target'], ['inputs', 'parameters']);
            return [
                'id' => textField($step['id']), 'kind' => textField($step['kind']), 'target' => textField($step['target']),
                // Omission represents an empty collection, never a missing required value.
                // Explicit null must fail, so null coalescing is deliberately not used.
                'inputs' => namedLinks(array_key_exists('inputs', $step) ? $step['inputs'] : []),
                'parameters' => namedLinks(array_key_exists('parameters', $step) ? $step['parameters'] : []),
            ];
        }, $flow['steps']);
        return [
            'id' => $id, 'contract' => $contract,
            'inputs' => $catalogue[$contract]['inputs'], 'parameters' => $catalogue[$contract]['parameters'],
            'steps' => $steps, 'outputs' => namedLinks($flow['outputs']),
        ];
    }, $value);
}

$associative = require __DIR__ . '/authoring-lab/associative.php';
$normalized = fromAssociative($associative);
check('associative-normalization-matches-records', static fn (): bool => $normalized === $sources['records']);
foreach ($cases as $index => [$root, $targets, $network]) {
    check('associative-resolved-case-' . $index, static fn (): bool => Snapshot::admit($normalized, $root, $targets, $network)->view() === $plans[$index]);
}
$bad = $associative;
$bad[0]['steps'][0]['inputs'][0]['source']['typo'] = 'x';
rejects('named-reference-unknown-field', 'unknown-map-field', static fn () => fromAssociative($bad));
$bad = $associative;
unset($bad[0]['steps'][1]['inputs'][0]['source']['step']);
rejects('named-reference-missing-step', 'missing-map-field', static fn () => fromAssociative($bad));
$bad = $associative;
$bad[0]['steps'][0]['inputs'][0]['source']['kind'] = 'callback';
rejects('named-reference-unknown-kind', 'unknown-reference-kind', static fn () => fromAssociative($bad));
$bad = $associative;
$bad[0]['steps'][0]['parameters'] = null;
rejects('explicit-null-not-treated-as-omission', 'binding-list-required', static fn () => fromAssociative($bad));
$bad = $associative;
$bad[0]['id'] = 123;
rejects('raw-scalar-not-coerced', 'non-text-field', static fn () => fromAssociative($bad));
$bad = $associative;
$bad[0]['steps'][0]['target'] = static fn () => 'sha256';
rejects('callable-is-not-declaration-data', 'non-text-field', static fn () => fromAssociative($bad));
$bad = $associative;
$bad[0]['steps'] = ['sha' => $bad[0]['steps'][0]];
rejects('indexed-step-map-rejected', 'step-list-required', static fn () => fromAssociative($bad));
$bad = $associative;
$bad[] = $bad[0];
rejects('source-duplicate-flow-retained', 'duplicate-flow', static fn () => Snapshot::admit(fromAssociative($bad), 'hash160', []));
$bad = $associative;
$bad[0]['steps'][] = $bad[0]['steps'][0];
rejects('source-duplicate-step-retained', 'duplicate-step', static fn () => Snapshot::admit(fromAssociative($bad), 'hash160', []));
$bad = $associative;
$bad[0]['steps'][0]['inputs'][] = $bad[0]['steps'][0]['inputs'][0];
rejects('source-duplicate-binding-retained', 'duplicate-binding', static fn () => Snapshot::admit(fromAssociative($bad), 'hash160', []));
$bad = $associative;
unset($bad[0]['steps'][0]['inputs']);
rejects('omission-does-not-supply-required-input', 'missing-binding', static fn () => Snapshot::admit(fromAssociative($bad), 'hash160', []));
$bad = $associative;
$bad[0]['steps'] = array_reverse($bad[0]['steps']);
rejects('source-order-not-repaired', 'unknown-or-future-output', static fn () => Snapshot::admit(fromAssociative($bad), 'hash160', []));
$reordered = $associative;
$reordered[0]['steps'][1]['inputs'][0]['source'] = ['port' => 'digest', 'kind' => 'output', 'step' => 'sha'];
check('named-reference-field-order-immaterial', static fn (): bool => fromAssociative($reordered) === $normalized);
check('overwritten-fixed-field-cannot-be-recovered', static function () use ($associative, $normalized): bool {
    $copy = $associative;
    $copy[0]['steps'][1]['inputs'][0]['source'] = ['kind' => 'output', 'step' => 'wrong', 'step' => 'sha', 'port' => 'digest'];
    return fromAssociative($copy) === $normalized;
});
$alias = 'sha';
$draft = $associative;
$draft[0]['steps'][0]['id'] =& $alias;
$admitted = Snapshot::admit(fromAssociative($draft), 'hash160', []);
$alias = 'changed';
check('associative-caller-alias-detached', static fn (): bool => $admitted->view() === $plans[0]);


require __DIR__ . '/authoring-lab/reference-records.php';

function typedSource(NamedReferences\Input|NamedReferences\Parameter|NamedReferences\StepOutput|NamedReferences\Field $source): array
{
    return match (true) {
        $source instanceof NamedReferences\Input => ['input', textField($source->name)],
        $source instanceof NamedReferences\Parameter => ['parameter', textField($source->name)],
        $source instanceof NamedReferences\StepOutput => ['output', textField($source->step), textField($source->port)],
        $source instanceof NamedReferences\Field => ['field', typedSource($source->source), textField($source->name)],
    };
}

function typedLinks(array $links): array
{
    demand(array_is_list($links), 'binding-list-required');
    return array_map(static function ($link): Link {
        demand($link instanceof NamedReferences\Link, 'typed-link-required');
        return new Link(textField($link->port), typedSource($link->source));
    }, $links);
}

function fromNamedRecords(array $flows): array
{
    demand(array_is_list($flows), 'flow-list-required');
    $drafts = array_map(static function ($flow): Flow {
        demand($flow instanceof Flow, 'flow-record-required');
        demand(array_is_list($flow->steps), 'step-list-required');
        $steps = array_map(static function ($call): Call {
            demand($call instanceof Call, 'call-record-required');
            return new Call(textField($call->id), textField($call->kind), textField($call->target), typedLinks($call->inputs), typedLinks($call->parameters));
        }, $flow->steps);
        return new Flow(textField($flow->id), textField($flow->contract), $steps, typedLinks($flow->outputs));
    }, $flows);
    return fromRecords($drafts);
}

$named = require __DIR__ . '/authoring-lab/named-records.php';
$typedNormalized = fromNamedRecords($named);
check('named-records-normalize-like-associative', static fn (): bool => $typedNormalized === $normalized);
foreach ($cases as $index => [$root, $targets, $network]) {
    check('named-records-resolved-case-' . $index, static fn (): bool => Snapshot::admit($typedNormalized, $root, $targets, $network)->view() === $plans[$index]);
}

// Exercise the actual typed source boundary, not just normalized arrays.
$bad = $named;
$bad[] = $bad[0];
rejects('typed-source-duplicate-flow', 'duplicate-flow', static fn () => Snapshot::admit(fromNamedRecords($bad), 'hash160', []));
$bad = [new Flow('hash160', 'hash160', [...$named[0]->steps, $named[0]->steps[0]], $named[0]->outputs)];
rejects('typed-source-duplicate-step', 'duplicate-step', static fn () => Snapshot::admit(fromNamedRecords($bad), 'hash160', []));
$first = $named[0]->steps[0];
$badCall = new Call($first->id, $first->kind, $first->target, [...$first->inputs, ...$first->inputs]);
$bad = [new Flow('hash160', 'hash160', [$badCall, $named[0]->steps[1]], $named[0]->outputs)];
rejects('typed-source-duplicate-binding', 'duplicate-binding', static fn () => Snapshot::admit(fromNamedRecords($bad), 'hash160', []));
$bad = [new Flow('hash160', 'hash160', ['sha' => $first], $named[0]->outputs)];
rejects('typed-source-indexed-step-map', 'step-list-required', static fn () => fromNamedRecords($bad));
$bad = [new Flow('hash160', 'hash160', array_reverse($named[0]->steps), $named[0]->outputs)];
rejects('typed-source-order-not-repaired', 'unknown-or-future-output', static fn () => Snapshot::admit(fromNamedRecords($bad), 'hash160', []));
$badCall = new Call($first->id, $first->kind, $first->target);
$bad = [new Flow('hash160', 'hash160', [$badCall, $named[0]->steps[1]], $named[0]->outputs)];
rejects('typed-source-omission-no-required-input', 'missing-binding', static fn () => Snapshot::admit(fromNamedRecords($bad), 'hash160', []));

function nativeFailure(string $name, string $class, callable $call): void
{
    check($name, static function () use ($class, $call): bool {
        try { $call(); } catch (\Throwable $error) { return get_class($error) === $class; }
        return false;
    });
}
nativeFailure('typed-reference-unknown-field', \Error::class, static fn () => new NamedReferences\StepOutput(step: 'sha', port: 'digest', typo: 'x'));
nativeFailure('typed-reference-missing-step', \ArgumentCountError::class, static fn () => new NamedReferences\StepOutput(port: 'digest'));
nativeFailure('typed-reference-duplicate-named-field', \Error::class, static fn () => eval('new \\M05C2Lab\\NamedReferences\\StepOutput(step: "x", step: "sha", port: "digest");'));
nativeFailure('typed-reference-strict-scalar', \TypeError::class, static fn () => new NamedReferences\StepOutput(step: 123, port: 'digest'));
nativeFailure('typed-reference-no-callable-source', \TypeError::class, static fn () => new NamedReferences\Link(port: 'bytes', source: static fn () => 'sha'));
nativeFailure('typed-reference-no-null-source', \TypeError::class, static fn () => new NamedReferences\Link(port: 'bytes', source: null));
check('typed-named-field-order-immaterial', static fn (): bool => typedSource(new NamedReferences\StepOutput(port: 'digest', step: 'sha')) === ['output', 'sha', 'digest']);
check('typed-weak-caller-coercion-remains', static function (): bool {
    $value = eval('declare(strict_types=0); return new \\M05C2Lab\\NamedReferences\\StepOutput(step: 123, port: "digest");');
    return typedSource($value) === ['output', '123', 'digest'];
});
check('typed-unpacking-cannot-recover-overwrite', static function (): bool {
    $args = ['step' => 'wrong', 'step' => 'sha', 'port' => 'digest'];
    return typedSource(new NamedReferences\StepOutput(...$args)) === ['output', 'sha', 'digest'];
});
$badCall = new Call('sha', 'capability', 'sha256', [new NamedReferences\Link(port: 'bytes', source: new NamedReferences\StepOutput(step: 'missing', port: 'digest'))]);
$bad = [new Flow('hash160', 'hash160', [$badCall, $named[0]->steps[1]], $named[0]->outputs)];
rejects('typed-shape-does-not-prove-reference-exists', 'unknown-or-future-output', static fn () => Snapshot::admit(fromNamedRecords($bad), 'hash160', []));

echo json_encode([
    'php' => PHP_VERSION, 'baseline_checks' => $baselineChecks,
    'additional_checks' => count($checks) - $baselineChecks,
    'passed' => array_slice($checks, $baselineChecks),
    'source_forms' => ['associative', 'named-records'],
    'declarations_per_form' => count($associative), 'resolved_cases' => count($cases),
    'limits' => [
        'Same trusted fixture catalogue and structural routine as round one; no independent semantic qualification.',
        'Fixed-field overwrites before admission remain invisible. No relaxation of the agreed duplicate-rejection obligation is approved.',
        'The associative PHP fixture contains only literal data, but require is not a sandbox or an approved production loader.',
        'Typed direct-call errors remain native PHP errors; weak callers coerce scalars and unpacked maps can already have lost duplicates.',
        'No cyclic-container handling, full ingress diagnostics, performance or all-intent admission qualification.',
    ],
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
