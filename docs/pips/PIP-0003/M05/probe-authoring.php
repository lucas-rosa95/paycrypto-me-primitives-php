<?php
declare(strict_types=1);

namespace M05C2Lab;

// Disposable C2 research. No production API, semantic checker, crypto or executor.
// Three source forms enter one bounded structural admission/expansion routine.
// Source-form and occurrence-scoping comparisons are independent experiments.

final class Rejected extends \RuntimeException {}

function demand(bool $condition, string $reason): void
{
    if (!$condition) {
        throw new Rejected($reason);
    }
}

final readonly class Link
{
    public function __construct(public string $port, public array $source) {}
}

final readonly class Call
{
    public function __construct(
        public string $id,
        public string $kind,
        public string $target,
        public array $inputs = [],
        public array $parameters = [],
    ) {}
}

// A source record is deliberately constructible; it is NOT an admitted flow.
final readonly class Flow
{
    public function __construct(
        public string $id,
        public string $contract,
        public array $steps,
        public array $outputs,
    ) {}
}

final class Builder
{
    private array $steps = [];
    private array $outputs = [];

    public function __construct(private string $id, private string $contract) {}

    public function call(
        string $id, string $kind, string $target,
        array $inputs = [], array $parameters = [],
    ): self {
        // Append: repeated IDs remain observable to common admission.
        $this->steps[] = new Call($id, $kind, $target, $inputs, $parameters);
        return $this;
    }

    public function export(string $port, array $source): self
    {
        $this->outputs[] = new Link($port, $source);
        return $this;
    }

    public function draft(): Flow
    {
        return new Flow($this->id, $this->contract, $this->steps, $this->outputs);
    }
}

function signature(array $inputs, array $parameters, array $outputs): array
{
    return compact('inputs', 'parameters', 'outputs');
}

function contracts(): array
{
    // Fixture-owned catalogue, not an accepted C3 type or lineage system.
    return [
        'hash160' => signature(['bytes' => 'bytes'], [], ['digest' => 'digest20']),
        'public-address' => signature(['request' => 'request'], ['network' => 'network'], ['result' => 'result']),
        'key-hash-address' => signature(['keyHash' => 'digest20'], ['network' => 'network'], ['address' => 'address']),
        'make-result' => signature(['address' => 'address'], [], ['result' => 'result']),
        'two-results' => signature(['address' => 'address'], [], ['first' => 'result', 'second' => 'result']),
    ];
}

function capabilities(): array
{
    return [
        'sha256' => signature(['bytes' => 'bytes'], [], ['digest' => 'digest32']),
        'ripemd160' => signature(['bytes' => 'bytes'], [], ['digest' => 'digest20']),
        'extended-public-key-admission' => signature(['request' => 'request'], [], ['node' => 'node']),
        'remaining-depth' => signature(['node' => 'node', 'request' => 'request'], [], ['path' => 'path']),
        'ckd-public-path' => signature(['parent' => 'node', 'path' => 'path'], [], ['node' => 'node']),
        'public-address-result' => signature(['request' => 'request', 'address' => 'address'], [], ['result' => 'result']),
        'p2pkh-payload' => signature(['keyHash' => 'digest20'], ['version' => 'version'], ['payload' => 'payload']),
        'base58check-encode' => signature(['payload' => 'payload'], [], ['address' => 'address']),
        'witness-v0-keyhash-script' => signature(['keyHash' => 'digest20'], [], ['script' => 'script']),
        'p2sh-payload' => signature(['scriptHash' => 'digest20'], ['version' => 'version'], ['payload' => 'payload']),
        'segwit-v0-keyhash-encode' => signature(['program' => 'digest20'], ['hrp' => 'hrp'], ['address' => 'address']),
        'make-result' => signature(['address' => 'address'], [], ['result' => 'result']),
        'second-result-fixture' => signature(['address' => 'address'], [], ['result' => 'result']),
    ];
}

function fields(mixed $pairs, array $required): array
{
    demand(is_array($pairs) && array_is_list($pairs), 'record-not-list');
    $record = [];
    foreach ($pairs as $pair) {
        demand(is_array($pair) && array_is_list($pair) && count($pair) === 2, 'malformed-field');
        [$name, $value] = $pair;
        demand(is_string($name) && in_array($name, $required, true), 'unknown-field');
        demand(!array_key_exists($name, $record), 'duplicate-field');
        $record[$name] = $value;
    }
    demand(count($record) === count($required), 'missing-field');
    return $record;
}

function ports(array $pairs): array
{
    demand(array_is_list($pairs), 'ports-not-list');
    $ports = [];
    foreach ($pairs as $pair) {
        $port = fields($pair, ['name', 'type']);
        demand(is_string($port['name']) && is_string($port['type']), 'invalid-port');
        demand(!array_key_exists($port['name'], $ports), 'duplicate-port');
        $ports[$port['name']] = $port['type'];
    }
    return $ports;
}

function pairLinks(array $pairs): array
{
    demand(array_is_list($pairs), 'bindings-not-list');
    return array_map(static function ($pair): array {
        return fields($pair, ['port', 'source']);
    }, $pairs);
}

function fromPairs(array $source): array
{
    demand(array_is_list($source), 'flows-not-list');
    $flows = [];
    foreach ($source as $entry) {
        $flow = fields($entry, ['id', 'contract', 'inputs', 'parameters', 'steps', 'outputs']);
        $flow['inputs'] = ports($flow['inputs']);
        $flow['parameters'] = ports($flow['parameters']);
        demand(is_array($flow['steps']) && array_is_list($flow['steps']), 'steps-not-list');
        $flow['steps'] = array_map(static function ($entry): array {
            $call = fields($entry, ['id', 'target', 'inputs', 'parameters']);
            demand(is_array($call['target']) && array_is_list($call['target']) && count($call['target']) === 2, 'target-shape');
            return [
                'id' => $call['id'], 'kind' => $call['target'][0], 'target' => $call['target'][1],
                'inputs' => pairLinks($call['inputs']), 'parameters' => pairLinks($call['parameters']),
            ];
        }, $flow['steps']);
        $flow['outputs'] = pairLinks($flow['outputs']);
        $flows[] = $flow;
    }
    return $flows;
}

function recordLinks(array $links): array
{
    demand(array_is_list($links), 'links-not-list');
    return array_map(static function ($link): array {
        demand($link instanceof Link, 'link-record-required');
        return ['port' => $link->port, 'source' => $link->source];
    }, $links);
}

function fromRecords(array $source): array
{
    demand(array_is_list($source), 'flows-not-list');
    $catalogue = contracts();
    return array_map(static function ($flow) use ($catalogue): array {
        demand($flow instanceof Flow, 'flow-record-required');
        demand(isset($catalogue[$flow->contract]), 'unknown-contract');
        return [
            'id' => $flow->id, 'contract' => $flow->contract,
            'inputs' => $catalogue[$flow->contract]['inputs'],
            'parameters' => $catalogue[$flow->contract]['parameters'],
            'steps' => array_map(static function ($call): array {
                demand($call instanceof Call, 'call-record-required');
                return [
                    'id' => $call->id, 'kind' => $call->kind, 'target' => $call->target,
                    'inputs' => recordLinks($call->inputs), 'parameters' => recordLinks($call->parameters),
                ];
            }, $flow->steps),
            'outputs' => recordLinks($flow->outputs),
        ];
    }, $source);
}

function scalarSnapshot(mixed $value): mixed
{
    if (is_array($value)) {
        $copy = [];
        foreach ($value as $key => $child) {
            $copy[$key] = scalarSnapshot($child);
        }
        return $copy;
    }
    demand(is_string($value), 'non-data-leaf');
    return $value;
}

function indexedFlows(array $source): array
{
    demand(array_is_list($source), 'flows-not-list');
    $flows = [];
    foreach ($source as $flow) {
        demand(is_string($flow['id']) && $flow['id'] !== '', 'invalid-id');
        demand(!isset($flows[$flow['id']]), 'duplicate-flow');
        $flows[$flow['id']] = $flow;
    }
    return $flows;
}

function links(array $entries, array $required): array
{
    demand(array_is_list($entries), 'bindings-not-list');
    $found = [];
    foreach ($entries as $entry) {
        $port = $entry['port'];
        demand(is_string($port) && array_key_exists($port, $required), 'unknown-binding-port');
        demand(!array_key_exists($port, $found), 'duplicate-binding');
        demand(is_array($entry['source']) && array_is_list($entry['source']), 'reference-not-list');
        $found[$port] = $entry['source'];
    }
    demand(count($found) === count($required), 'missing-binding');
    // Record field and binding-entry order do not determine execution order.
    $ordered = [];
    foreach ($required as $port => $_type) {
        $ordered[$port] = $found[$port];
    }
    return $ordered;
}

function source(array $ref, array $inputs, array $parameters, array $earlier): array
{
    $kind = $ref[0] ?? '';
    if (in_array($kind, ['input', 'parameter'], true)) {
        demand(count($ref) === 2 && is_string($ref[1]), 'reference-shape');
        $scope = $kind === 'input' ? $inputs : $parameters;
        demand(array_key_exists($ref[1], $scope), 'unknown-source-port');
        return $scope[$ref[1]];
    }
    if ($kind === 'output') {
        demand(count($ref) === 3 && is_string($ref[1]) && is_string($ref[2]), 'reference-shape');
        demand(isset($earlier[$ref[1]][$ref[2]]), 'unknown-or-future-output');
        return $earlier[$ref[1]][$ref[2]];
    }
    if ($kind === 'field') {
        demand(count($ref) === 3 && is_array($ref[1]) && is_string($ref[2]), 'reference-shape');
        demand(in_array($ref[1][0] ?? '', ['input', 'parameter', 'output'], true), 'nested-projection');
        // Only a fixture field-name gate. NO producer/type/lineage proof is claimed.
        demand(in_array($ref[2], ['publicKey', 'p2pkhVersion', 'p2shVersion', 'segwitHrp'], true), 'unknown-field-projection');
        return ['field', source($ref[1], $inputs, $parameters, $earlier), $ref[2]];
    }
    throw new Rejected('unknown-reference-kind');
}

function pathKey(array $path): string
{
    demand($path !== [] && array_is_list($path), 'empty-occurrence-path');
    foreach ($path as $part) {
        demand(is_string($part) && $part !== '', 'invalid-occurrence-path');
    }
    return json_encode($path, JSON_THROW_ON_ERROR);
}

function expand(
    string $id, array $flows, array $bindings, array &$used, array &$nodes,
    array $at = [], array $inputs = [], array $parameters = [], array $stack = [],
): array {
    demand(isset($flows[$id]), 'unknown-composition');
    demand(!in_array($id, $stack, true), 'composition-cycle');
    $stack[] = $id;
    $flow = $flows[$id];
    $catalogue = contracts();
    demand(isset($catalogue[$flow['contract']]), 'unknown-contract');
    $contract = $catalogue[$flow['contract']];
    demand($flow['inputs'] === $contract['inputs'] && $flow['parameters'] === $contract['parameters'], 'contract-port-mismatch');
    demand(array_is_list($flow['steps']) && $flow['steps'] !== [], 'steps-not-nonempty-list');
    $earlier = [];
    foreach ($flow['steps'] as $call) {
        $local = $call['id'];
        demand(is_string($local) && $local !== '', 'invalid-step-id');
        demand(!array_key_exists($local, $earlier), 'duplicate-step');
        $position = [...$at, $local];
        $kind = $call['kind'];
        $target = $call['target'];
        demand(is_string($target), 'invalid-target');
        if ($kind === 'intent') {
            $key = pathKey($position);
            demand(array_key_exists($key, $bindings), 'missing-occurrence-binding');
            demand(isset($catalogue[$target]), 'unknown-contract');
            $selected = $bindings[$key];
            demand(isset($flows[$selected]) && $flows[$selected]['contract'] === $target, 'selected-contract-mismatch');
            $used[$key] = true;
            $spec = $catalogue[$target];
            $target = $selected;
        } elseif ($kind === 'composition') {
            demand(isset($flows[$target]), 'unknown-composition');
            demand(isset($catalogue[$flows[$target]['contract']]), 'unknown-contract');
            $spec = $catalogue[$flows[$target]['contract']];
        } else {
            demand($kind === 'capability' && isset(capabilities()[$target]), 'unknown-capability');
            $spec = capabilities()[$target];
        }
        $boundInputs = [];
        foreach (links($call['inputs'], $spec['inputs']) as $port => $ref) {
            $boundInputs[$port] = source($ref, $inputs, $parameters, $earlier);
        }
        $boundParameters = [];
        foreach (links($call['parameters'], $spec['parameters']) as $port => $ref) {
            $boundParameters[$port] = source($ref, $inputs, $parameters, $earlier);
        }
        if ($kind === 'capability') {
            $nodes[] = ['at' => $position, 'capability' => $target, 'inputs' => $boundInputs, 'parameters' => $boundParameters];
            $outputs = [];
            foreach ($spec['outputs'] as $port => $_type) {
                $outputs[$port] = ['output', $position, $port];
            }
        } else {
            $outputs = expand($target, $flows, $bindings, $used, $nodes, $position, $boundInputs, $boundParameters, $stack);
        }
        $earlier[$local] = $outputs;
    }
    $exports = [];
    foreach (links($flow['outputs'], $contract['outputs']) as $port => $ref) {
        $exports[$port] = source($ref, $inputs, $parameters, $earlier);
    }
    return $exports;
}

function flattenContext(array $entries, array $parent = []): array
{
    demand(array_is_list($entries), 'context-not-list');
    $flat = [];
    $seen = [];
    foreach ($entries as $entry) {
        demand(is_string($entry['step'] ?? null) && $entry['step'] !== '', 'context-step-required');
        demand(!isset($seen[$entry['step']]), 'duplicate-context-step');
        $seen[$entry['step']] = true;
        demand(array_diff(array_keys($entry), ['step', 'composition', 'children']) === [], 'unknown-context-field');
        $path = [...$parent, $entry['step']];
        if (array_key_exists('composition', $entry)) {
            $flat[] = ['at' => $path, 'composition' => $entry['composition']];
        }
        $children = $entry['children'] ?? [];
        demand(is_array($children) && ($children !== [] || array_key_exists('composition', $entry)), 'empty-context');
        array_push($flat, ...flattenContext($children, $path));
    }
    return $flat;
}

final readonly class Snapshot
{
    private function __construct(private array $plan) {}

    public static function admit(array $source, string $root, array $targets, string $network = 'mainnet'): self
    {
        // The normalized raw arrays are trusted products of these fixture adapters;
        // this is NOT a complete arbitrary-input parser or production admission API.
        $flows = indexedFlows(scalarSnapshot($source));
        demand(isset($flows[$root]) && isset(contracts()[$flows[$root]['contract']]), 'unknown-root');
        $contract = contracts()[$flows[$root]['contract']];
        $bindings = [];
        foreach ($targets as $entry) {
            $key = pathKey($entry['at']);
            demand(!array_key_exists($key, $bindings), 'duplicate-occurrence-binding');
            demand(is_string($entry['composition']), 'invalid-selected-composition');
            $bindings[$key] = $entry['composition'];
        }
        $inputs = [];
        foreach ($contract['inputs'] as $port => $_type) {
            $inputs[$port] = ['input', $port];
        }
        $parameters = [];
        foreach ($contract['parameters'] as $port => $_type) {
            $parameters[$port] = ['parameter', $port, $network];
        }
        $used = $nodes = [];
        $exports = expand($root, $flows, $bindings, $used, $nodes, [], $inputs, $parameters);
        demand(count($used) === count($bindings), 'unused-occurrence-binding');
        return new self(['nodes' => $nodes, 'outputs' => $exports]);
    }

    public function view(): array { return $this->plan; }
    public function __serialize(): array { throw new Rejected('serialization-disabled'); }
    public function __unserialize(array $data): void { throw new Rejected('serialization-disabled'); }
}

$checks = [];
function check(string $name, callable $test): void
{
    global $checks;
    demand($test() === true, 'control-failed:' . $name);
    $checks[] = $name;
}

function rejects(string $name, string $reason, callable $test): void
{
    check($name, static function () use ($reason, $test): bool {
        try { $test(); } catch (Rejected $error) { return $error->getMessage() === $reason; }
        return false;
    });
}

$pairs = require __DIR__ . '/authoring-lab/pairs.php';
$records = require __DIR__ . '/authoring-lab/records.php';
$builders = require __DIR__ . '/authoring-lab/builders.php';
$sources = [
    'pairs' => fromPairs($pairs), 'records' => fromRecords($records),
    'builders' => fromRecords(array_map(static fn (Builder $builder): Flow => $builder->draft(), $builders)),
];
check('three-source-forms-preserve-equal-declarations', static fn (): bool => $sources['pairs'] === $sources['records'] && $sources['records'] === $sources['builders']);
$reorderedPairs = $pairs;
$reorderedPairs[0] = array_reverse($reorderedPairs[0]);
check('pair-record-field-order-is-immaterial', static fn (): bool => Snapshot::admit(fromPairs($reorderedPairs), 'hash160', [])->view() === Snapshot::admit($sources['pairs'], 'hash160', [])->view());

$cases = [['hash160', [], 'mainnet']];
foreach (['mainnet', 'testnet'] as $network) {
    foreach (['p2pkh-address', 'nested-witness-address', 'native-witness-address'] as $terminal) {
        $cases[] = ['public-address', [['at' => ['encode'], 'composition' => $terminal]], $network];
    }
}
$pairTargets = [
    ['at' => ['left', 'end'], 'composition' => 'result-a'],
    ['at' => ['right', 'end'], 'composition' => 'result-b'],
];
$cases[] = ['pair', $pairTargets, 'synthetic'];
$plans = [];
foreach ($cases as $caseIndex => [$root, $targets, $network]) {
    $forms = [];
    foreach ($sources as $name => $source) {
        $forms[$name] = Snapshot::admit($source, $root, $targets, $network)->view();
    }
    check('equivalent-resolved-connections-' . $caseIndex, static fn (): bool => $forms['pairs'] === $forms['records'] && $forms['records'] === $forms['builders']);
    $plans[] = $forms['records'];
}
check('network-parameter-context-retained', static fn (): bool => $plans[1] !== $plans[4]);
check('sibling-exports-keep-distinct-origins', static fn (): bool => $plans[7]['outputs']['first'] !== $plans[7]['outputs']['second']);
$reordered = $sources['records'];
$reordered[count($reordered) - 1]['outputs'] = array_reverse($reordered[count($reordered) - 1]['outputs']);
check('binding-entry-order-is-immaterial', static fn (): bool => Snapshot::admit($reordered, 'pair', $pairTargets, 'synthetic')->view() === $plans[7]);

$context = [
    ['step' => 'left', 'children' => [['step' => 'end', 'composition' => 'result-a']]],
    ['step' => 'right', 'children' => [['step' => 'end', 'composition' => 'result-b']]],
];
check('flat-and-contextual-bindings-resolve-equally', static fn (): bool => Snapshot::admit($sources['records'], 'pair', flattenContext($context))->view() === Snapshot::admit($sources['records'], 'pair', $pairTargets)->view());
rejects('sibling-cannot-borrow-binding', 'missing-occurrence-binding', static fn () => Snapshot::admit($sources['records'], 'pair', [$pairTargets[0]]));
rejects('bare-local-name-does-not-find-nested-occurrence', 'missing-occurrence-binding', static fn () => Snapshot::admit($sources['records'], 'pair', [['at' => ['end'], 'composition' => 'result-a']]));
rejects('duplicate-occurrence-binding', 'duplicate-occurrence-binding', static fn () => Snapshot::admit($sources['records'], 'pair', [...$pairTargets, $pairTargets[0]]));
rejects('unused-occurrence-binding', 'unused-occurrence-binding', static fn () => Snapshot::admit($sources['records'], 'hash160', $pairTargets));
rejects('duplicate-context-sibling', 'duplicate-context-step', static fn () => flattenContext([...$context, $context[0]]));
rejects('unknown-context-field', 'unknown-context-field', static fn () => flattenContext([['step' => 'end', 'composition' => 'result-a', 'typo' => 'x']]));

// Controlled refactoring: insert one fixed wrapper around both occurrences.
$wrapped = $sources['records'];
$pairIndex = array_search('pair', array_column($wrapped, 'id'), true);
$inner = $wrapped[$pairIndex];
$inner['id'] = 'pair-inner';
$wrapped[] = $inner;
$wrapped[$pairIndex]['steps'] = [[
    'id' => 'wrap', 'kind' => 'composition', 'target' => 'pair-inner',
    'inputs' => [['port' => 'address', 'source' => ['input', 'address']]], 'parameters' => [],
]];
$wrapped[$pairIndex]['outputs'] = [
    ['port' => 'first', 'source' => ['output', 'wrap', 'first']],
    ['port' => 'second', 'source' => ['output', 'wrap', 'second']],
];
$flatWrapped = array_map(static fn ($entry): array => ['at' => ['wrap', ...$entry['at']], 'composition' => $entry['composition']], $pairTargets);
$contextWrapped = [['step' => 'wrap', 'children' => $context]];
check('wrapper-refactor-preserves-flat-context-equivalence', static fn (): bool => Snapshot::admit($wrapped, 'pair', $flatWrapped)->view() === Snapshot::admit($wrapped, 'pair', flattenContext($contextWrapped))->view());
rejects('old-flat-paths-fail-after-wrapper', 'missing-occurrence-binding', static fn () => Snapshot::admit($wrapped, 'pair', $pairTargets));
rejects('old-context-also-needs-new-wrapper', 'missing-occurrence-binding', static fn () => Snapshot::admit($wrapped, 'pair', flattenContext($context)));

$badPairs = $pairs;
$badPairs[0][] = ['id', 'other'];
rejects('pair-source-preserves-duplicate-field', 'duplicate-field', static fn () => fromPairs($badPairs));
$badPairs = $pairs;
$badPairs[0][] = ['typo', 'other'];
rejects('pair-source-rejects-unknown-field', 'unknown-field', static fn () => fromPairs($badPairs));
$badPairs = $pairs;
unset($badPairs[0][3]);
rejects('pair-source-requires-explicit-empty-field', 'record-not-list', static fn () => fromPairs($badPairs));
$badPairs = $pairs;
array_splice($badPairs[0], 3, 1);
rejects('pair-source-missing-field-after-reindexing', 'missing-field', static fn () => fromPairs($badPairs));
$badPairs = $pairs;
$badPairs[0][2][1][0][1][1] = 'wrong-type';
rejects('repeated-port-annotation-cannot-redefine-contract', 'contract-port-mismatch', static fn () => Snapshot::admit(fromPairs($badPairs), 'hash160', []));
check('fixed-record-rejects-unknown-named-argument', static function (): bool {
    try { new Flow(id: 'x', contract: 'hash160', steps: [], outputs: [], typo: 'x'); }
    catch (\Error $error) { return str_contains($error->getMessage(), 'Unknown named parameter'); }
    return false;
});
check('fixed-record-rejects-duplicate-named-argument', static function (): bool {
    try { eval('new \\M05C2Lab\\Flow(id: "x", id: "y", contract: "hash160", steps: [], outputs: []);'); }
    catch (\Error $error) { return str_contains($error->getMessage(), 'overwrites previous argument'); }
    return false;
});

foreach ($sources as $name => $source) {
    $bad = $source;
    $bad[] = $bad[0];
    rejects($name . '-duplicate-flow', 'duplicate-flow', static fn () => Snapshot::admit($bad, 'hash160', []));
    $bad = $source;
    $bad[0]['steps'][] = $bad[0]['steps'][0];
    rejects($name . '-duplicate-step', 'duplicate-step', static fn () => Snapshot::admit($bad, 'hash160', []));
    $bad = $source;
    $bad[0]['steps'][0]['inputs'][] = $bad[0]['steps'][0]['inputs'][0];
    rejects($name . '-duplicate-port-binding', 'duplicate-binding', static fn () => Snapshot::admit($bad, 'hash160', []));
    $bad = $source;
    $bad[0]['steps'][0]['inputs'] = [];
    rejects($name . '-omitted-required-input', 'missing-binding', static fn () => Snapshot::admit($bad, 'hash160', []));
    $bad = $source;
    $bad[0]['steps'][0]['parameters'][] = ['port' => 'unexpected', 'source' => ['input', 'bytes']];
    rejects($name . '-extra-parameter', 'unknown-binding-port', static fn () => Snapshot::admit($bad, 'hash160', []));
    $bad = $source;
    $bad[0]['steps'] = array_reverse($bad[0]['steps']);
    rejects($name . '-order-not-repaired', 'unknown-or-future-output', static fn () => Snapshot::admit($bad, 'hash160', []));
    $bad = $source;
    $bad[0]['steps'][0]['inputs'][0]['source'] = ['output', 'missing', 'digest'];
    rejects($name . '-unknown-reference', 'unknown-or-future-output', static fn () => Snapshot::admit($bad, 'hash160', []));
    $bad = $source;
    $bad[0]['steps'][0] = ['id' => 'again', 'kind' => 'composition', 'target' => 'hash160', 'inputs' => [['port' => 'bytes', 'source' => ['input', 'bytes']]], 'parameters' => []];
    rejects($name . '-composition-cycle', 'composition-cycle', static fn () => Snapshot::admit($bad, 'hash160', []));
    $bad = $source;
    $bad[1]['steps'][4]['parameters'] = [];
    rejects($name . '-omitted-required-network', 'missing-binding', static fn () => Snapshot::admit($bad, 'public-address', $cases[1][1]));
    $bad = $source;
    $bad[0]['steps'][0]['inputs'][0]['source'] = ['field', ['field', ['input', 'bytes'], 'publicKey'], 'publicKey'];
    rejects($name . '-nested-field-projection', 'nested-projection', static fn () => Snapshot::admit($bad, 'hash160', []));
}

$duplicateBuilder = clone $builders[0];
$duplicateBuilder->call('sha', 'capability', 'sha256', [new Link('bytes', ['input', 'bytes'])]);
rejects('builder-appends-rather-than-overwrites', 'duplicate-step', static fn () => Snapshot::admit(fromRecords([$duplicateBuilder->draft()]), 'hash160', []));
check('map-overwrite-negative-control', static function (): bool {
    $map = ['id' => 'hash160', 'id' => 'other'];
    return $map === ['id' => 'other'];
});
check('unsafe-builder-index-negative-control', static function (): bool {
    $calls = [];
    $calls['sha'] = 'sha256';
    $calls['sha'] = 'ripemd160';
    return $calls === ['sha' => 'ripemd160'];
});
check('named-unpacking-after-map-loss-negative-control', static function (): bool {
    $args = ['id' => 'first', 'id' => 'survivor', 'contract' => 'hash160', 'steps' => [], 'outputs' => []];
    return (new Flow(...$args))->id === 'survivor';
});
check('weak-caller-scalar-coercion-negative-control', static function (): bool {
    $flow = eval('declare(strict_types=0); return new \\M05C2Lab\\Flow(id: 123, contract: "hash160", steps: [], outputs: []);');
    return $flow->id === '123';
});
check('structural-admission-is-not-semantic-proof-negative-control', static function () use ($sources): bool {
    $wrong = $sources['records'];
    $wrong[0]['steps'][1]['target'] = 'sha256';
    $plan = Snapshot::admit($wrong, 'hash160', [])->view();
    return array_column($plan['nodes'], 'capability') === ['sha256', 'sha256'];
});
check('readonly-array-alias-negative-control', static function (): bool {
    $external = 'original';
    $holder = new class(['value' => &$external]) {
        public function __construct(public readonly array $values) {}
    };
    $external = 'changed';
    return $holder->values['value'] === 'changed';
});

$draft = $sources['records'];
$external = 'sha';
$draft[0]['steps'][0]['id'] =& $external;
$snapshot = Snapshot::admit($draft, 'hash160', []);
$before = $snapshot->view();
$external = 'changed';
check('admitted-snapshot-detaches-caller-reference', static fn (): bool => $snapshot->view() === $before && $draft[0]['steps'][0]['id'] === 'changed');
$view = $snapshot->view();
$view['nodes'][0]['capability'] = 'changed';
check('exported-array-does-not-mutate-snapshot', static fn (): bool => $snapshot->view() === $before);
$builderSnapshot = Snapshot::admit(fromRecords([$builders[0]->draft()]), 'hash160', []);
$builders[0]->call('later', 'capability', 'sha256', [new Link('bytes', ['input', 'bytes'])]);
check('builder-reuse-does-not-mutate-admitted-snapshot', static fn (): bool => $builderSnapshot->view() === $before);
$object = new \stdClass();
$bad = $sources['records'];
$bad[0]['steps'][0]['inputs'][0]['source'][1] = $object;
rejects('mutable-object-rejected-at-snapshot-boundary', 'non-data-leaf', static fn () => Snapshot::admit($bad, 'hash160', []));
check('direct-snapshot-construction-is-unavailable', static function (): bool {
    try { new Snapshot([]); } catch (\Error $error) { return str_contains($error->getMessage(), 'private'); }
    return false;
});
rejects('serialization-cannot-bypass-admission', 'serialization-disabled', static function (): void {
    $class = Snapshot::class;
    unserialize('O:' . strlen($class) . ':"' . $class . '":0:{}');
});

$sourceMetrics = [];
foreach (['pairs', 'records', 'builders'] as $name) {
    $text = file_get_contents(__DIR__ . '/authoring-lab/' . $name . '.php');
    $sourceMetrics[$name] = [
        'fixture_bytes' => strlen($text), 'fixture_lines' => substr_count($text, "\n"),
        'explicit_empty_arrays' => preg_match_all('/\[\]/', $text),
    ];
}
echo json_encode([
    'php' => PHP_VERSION, 'checks' => count($checks), 'passed' => $checks,
    'source_forms' => array_keys($sources), 'resolved_cases_per_form' => count($cases),
    'fixture_metrics' => $sourceMetrics,
    'limits' => [
        'Fixture signatures and adapters are trusted. Only the selected root closure is expanded; this is not complete catalogue or all-intent admission.',
        'The three source forms cover composition declarations; intent/provisioning rows remain driver fixtures and are not a complete three-way authoring comparison.',
        'Connection/parameter/field origins are structural. Semantic types, lineage correctness, protocol admission and actual bytes are not qualified.',
        'No provider or request execution, cryptography, codec, performance benchmark, consumer API or PHP minimum selection.',
        'Fixed-record native argument errors differ from owned validation failures; no final failure normalization is selected.',
        'Reason controls do not qualify author-location diagnostics. Native typed construction also does not preserve original scalar types from a weak caller.',
        'Named-argument unpacking of an already overwritten map remains lossy. Typed records do not solve that ingress problem.',
        'Private construction/serialization controls concern ordinary supported APIs, not hostile reflection or arbitrary code in the process.',
        'Malformed recursive PHP containers, arbitrary object graphs and all source-ingress paths are not qualified by this bounded fixture normalizer.',
        'Fixture size is measured separately from adapter/catalogue/builder machinery; it is not an objective readability score.',
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
