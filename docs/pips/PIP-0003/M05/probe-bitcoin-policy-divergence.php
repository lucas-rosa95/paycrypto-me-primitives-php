<?php

declare(strict_types=1);

/**
 * Synthetic C1 binding-mechanism probe for Issue #41. PHP 8.3+.
 * Trusted in-memory fixture syntax, symbolic contracts and call traces only.
 * No production C2 schema, C3 lineage proof, C6 executor, crypto or codec.
 * The admission fixture is hypothetical: current O02 does not admit P2TR.
 */

final class FitnessFailure extends RuntimeException
{
    public function __construct(string $reason, public readonly array $trace = [])
    {
        parent::__construct($reason);
    }
}

function fitnessRequire(bool $condition, string $reason): void
{
    if (!$condition) {
        throw new FitnessFailure($reason);
    }
}

function fitnessContract(array $inputs, array $outputs, array $parameters = []): array
{
    return ['inputs' => $inputs, 'parameters' => $parameters, 'outputs' => $outputs];
}

function fitnessStep(string $id, string $kind, string $target,
    array $inputs, array $parameters = []): array
{
    return compact('id', 'kind', 'target', 'inputs', 'parameters');
}

function fitnessFixture(bool $withTaproot): array
{
    $network = ['network' => 'network-definition'];
    $contracts = [
        'public-address' => fitnessContract(['request' => 'admitted-request'],
            ['result' => 'public-result'], $network),
        'admission' => fitnessContract(['request' => 'admitted-request'],
            ['node' => 'admitted-public-node']),
        'depth' => fitnessContract(['node' => 'admitted-public-node'],
            ['path' => 'checked-relative-path']),
        'derive' => fitnessContract(['node' => 'admitted-public-node',
            'path' => 'checked-relative-path'], ['node' => 'derived-public-node']),
        'derived-node-address' => fitnessContract(['node' => 'derived-public-node'],
            ['address' => 'address-for-intent'], $network),
        // Symbolic whole boundary only; not a proposed production hash capability.
        'hash-fixture' => fitnessContract(['node' => 'derived-public-node'],
            ['digest' => 'derived-key-hash']),
        'key-hash-address' => fitnessContract(['hash' => 'derived-key-hash'],
            ['address' => 'address-for-intent'], $network),
        'make-result' => fitnessContract(['address' => 'address-for-intent'],
            ['result' => 'public-result']),
    ];
    $capabilities = [];
    foreach (['admission', 'depth', 'derive', 'hash-fixture', 'make-result'] as $id) {
        $capabilities[$id] = ['contract' => $id];
    }
    $input = static fn(string $name): array => ['input', $name];
    $output = static fn(string $step, string $port): array => ['output', $step, $port];
    $params = ['network' => ['parameter', 'network']];
    $compositions = [
        'bitcoin-public-address' => [
            'contract' => 'public-address',
            'steps' => [
                fitnessStep('admit', 'capability', 'admission', ['request' => $input('request')]),
                fitnessStep('depth', 'capability', 'depth', ['node' => $output('admit', 'node')]),
                fitnessStep('derive', 'capability', 'derive', [
                    'node' => $output('admit', 'node'), 'path' => $output('depth', 'path')]),
                fitnessStep('address', 'intent', 'derived-node-address',
                    ['node' => $output('derive', 'node')], $params),
                fitnessStep('result', 'capability', 'make-result',
                    ['address' => $output('address', 'address')]),
            ],
            'outputs' => ['result' => $output('result', 'result')],
        ],
        'keyhash-route' => [
            'contract' => 'derived-node-address',
            'policies' => ['p2pkh', 'p2sh-p2wpkh', 'p2wpkh'],
            'steps' => [
                fitnessStep('keyhash', 'capability', 'hash-fixture', ['node' => $input('node')]),
                fitnessStep('encode', 'intent', 'key-hash-address',
                    ['hash' => $output('keyhash', 'digest')], $params),
            ],
            'outputs' => ['address' => $output('encode', 'address')],
        ],
    ];
    $policies = [
        'p2pkh' => ['keyhash-route', 'p2pkh-terminal'],
        'p2sh-p2wpkh' => ['keyhash-route', 'nested-terminal'],
        'p2wpkh' => ['keyhash-route', 'witness-v0-terminal'],
    ];
    foreach ($policies as $policy => [, $terminal]) {
        $capabilities[$terminal] = ['contract' => 'key-hash-address', 'policies' => [$policy]];
        $compositions[$terminal] = [
            'contract' => 'key-hash-address', 'policies' => [$policy],
            'steps' => [fitnessStep('encode', 'capability', $terminal,
                ['hash' => $input('hash')], $params)],
            'outputs' => ['address' => $output('encode', 'address')],
        ];
    }
    if ($withTaproot) {
        $contracts['taproot-output'] = fitnessContract(['node' => 'derived-public-node'],
            ['key' => 'taproot-output-key']);
        $contracts['taproot-key-address'] = fitnessContract(['key' => 'taproot-output-key'],
            ['address' => 'address-for-intent'], $network);
        $capabilities['taproot-output-fixture'] = ['contract' => 'taproot-output'];
        $capabilities['witness-v1-fixture'] = [
            'contract' => 'taproot-key-address', 'policies' => ['p2tr-keypath']];
        $compositions['taproot-route'] = [
            'contract' => 'derived-node-address', 'policies' => ['p2tr-keypath'],
            'steps' => [
                fitnessStep('output-key', 'capability', 'taproot-output-fixture',
                    ['node' => $input('node')]),
                fitnessStep('encode', 'capability', 'witness-v1-fixture',
                    ['key' => $output('output-key', 'key')], $params),
            ],
            'outputs' => ['address' => $output('encode', 'address')],
        ];
        // A fixed single encoder needs no extra selectable junction or wrapper.
        $policies['p2tr-keypath'] = ['taproot-route', null];
    }
    $intents = [];
    foreach (['mainnet', 'testnet'] as $networkId) {
        foreach ($policies as $policy => [$route, $terminal]) {
            $bindings = [['at' => ['address'], 'composition' => $route]];
            if ($terminal !== null) {
                $bindings[] = ['at' => ['address', 'encode'], 'composition' => $terminal];
            }
            $intents[] = [
                'network' => $networkId, 'policy' => $policy,
                'root' => 'bitcoin-public-address',
                'bindings' => $bindings,
            ];
        }
    }
    return compact('contracts', 'capabilities', 'compositions', 'intents');
}

/**
 * A deliberately small compiler of trusted symbolic fixtures.
 * Resolution uses target kind, local ports and scoped occurrence paths.
 * No candidate, policy, stage name or fixed nesting depth is coded here.
 * Occurrence-path authoring is a probe candidate, not an accepted C2 format.
 */
final class FitnessCompiler
{
    private array $bindings = [];
    private array $used = [];
    private array $plan = [];
    private string $policy = '';

    public function __construct(private readonly array $model) {}

    public function compile(array $intent): array
    {
        $this->bindings = $this->used = $this->plan = [];
        $this->policy = $intent['policy'];
        foreach ($intent['bindings'] as $binding) {
            $at = implode('/', $binding['at']);
            fitnessRequire($at !== '' && !isset($this->bindings[$at]), 'duplicate-binding');
            $this->bindings[$at] = $binding['composition'];
        }
        $root = $this->model['compositions'][$intent['root']] ?? null;
        fitnessRequire(is_array($root), 'missing-reference');
        $contract = $this->model['contracts'][$root['contract']];
        $inputs = $parameters = [];
        foreach ($contract['inputs'] as $port => $role) {
            $inputs[$port] = ['role' => $role, 'origin' => '@input/' . $port];
        }
        foreach ($contract['parameters'] as $port => $role) {
            $parameters[$port] = ['role' => $role, 'origin' => '@parameter/' . $port];
        }
        $outputs = $this->expand($intent['root'], '', $inputs, $parameters, []);
        fitnessRequire(count($this->used) === count($this->bindings), 'unused-binding');
        return ['steps' => $this->plan, 'outputs' => $outputs];
    }

    private function ports(array $values, array $expected): void
    {
        $actualNames = array_keys($values);
        $expectedNames = array_keys($expected);
        sort($actualNames);
        sort($expectedNames);
        fitnessRequire($actualNames === $expectedNames, 'ports');
        foreach ($expected as $port => $role) {
            fitnessRequire($values[$port]['role'] === $role, 'incompatible-port');
        }
    }

    private function sources(array $references, array $inputs,
        array $parameters, array $earlier): array
    {
        $values = [];
        foreach ($references as $port => $reference) {
            $value = match ($reference[0]) {
                'input' => $inputs[$reference[1]] ?? null,
                'parameter' => $parameters[$reference[1]] ?? null,
                'output' => $earlier[$reference[1]][$reference[2]] ?? null,
                default => null,
            };
            fitnessRequire(is_array($value), 'invalid-source');
            $values[$port] = $value;
        }
        return $values;
    }

    private function expand(string $id, string $path, array $inputs,
        array $parameters, array $stack): array
    {
        fitnessRequire(!in_array($id, $stack, true), 'cycle');
        $composition = $this->model['compositions'][$id] ?? null;
        fitnessRequire(is_array($composition), 'missing-reference');
        fitnessRequire(!isset($composition['policies'])
            || in_array($this->policy, $composition['policies'], true), 'incompatible-policy');
        $contract = $this->model['contracts'][$composition['contract']];
        $this->ports($inputs, $contract['inputs']);
        $this->ports($parameters, $contract['parameters']);
        $earlier = [];
        foreach ($composition['steps'] as $step) {
            $local = $step['id'];
            fitnessRequire($local !== '' && !str_contains($local, '/')
                && !isset($earlier[$local]), 'duplicate-step');
            $at = $path === '' ? $local : $path . '/' . $local;
            $arguments = $this->sources($step['inputs'], $inputs, $parameters, $earlier);
            $params = $this->sources($step['parameters'], $inputs, $parameters, $earlier);
            $kind = $step['kind'];
            if ($kind === 'intent') {
                fitnessRequire(isset($this->bindings[$at]), 'missing-binding');
                $target = $this->bindings[$at];
                $selected = $this->model['compositions'][$target] ?? null;
                fitnessRequire(is_array($selected), 'missing-reference');
                fitnessRequire($selected['contract'] === $step['target'], 'target-contract');
                $this->used[$at] = true;
            } else {
                $target = $step['target'];
            }
            if ($kind === 'composition' || $kind === 'intent') {
                $earlier[$local] = $this->expand($target, $at, $arguments, $params, [...$stack, $id]);
                continue;
            }
            fitnessRequire($kind === 'capability', 'target-kind');
            $capability = $this->model['capabilities'][$target] ?? null;
            fitnessRequire(is_array($capability), 'missing-reference');
            fitnessRequire(!isset($capability['policies'])
                || in_array($this->policy, $capability['policies'], true), 'incompatible-policy');
            $capContract = $this->model['contracts'][$capability['contract']];
            $this->ports($arguments, $capContract['inputs']);
            $this->ports($params, $capContract['parameters']);
            $produced = [];
            foreach ($capContract['outputs'] as $port => $role) {
                $produced[$port] = ['role' => $role, 'origin' => $at . ':' . $port];
            }
            $this->plan[] = [
                'at' => $at, 'capability' => $target, 'inputs' => $arguments,
                'parameters' => $params, 'outputs' => $produced,
            ];
            $earlier[$local] = $produced;
        }
        $outputs = $this->sources($composition['outputs'], $inputs, $parameters, $earlier);
        $this->ports($outputs, $contract['outputs']);
        return $outputs;
    }
}

function fitnessAssemble(array $model): array
{
    $plans = [];
    $compiler = new FitnessCompiler($model);
    foreach ($model['intents'] as $intent) {
        $key = $intent['network'] . '/' . $intent['policy'];
        fitnessRequire(!isset($plans[$key]), 'duplicate-intent');
        $plans[$key] = $compiler->compile($intent);
    }
    return $plans;
}

function fitnessRun(array $plan, ?string $failAt = null): array
{
    $trace = $values = [];
    // Deterministic test tokens, not runtime protocol values or default results.
    // A declared external origin receives a fixture token; missing internal
    // producers are errors. No cryptography or provider behavior is simulated.
    foreach ($plan['steps'] as $step) {
        foreach ([...$step['inputs'], ...$step['parameters']] as $source) {
            if (str_starts_with($source['origin'], '@')) {
                $values[$source['origin']] = ['fixture-input' => $source['origin']];
            }
        }
    }
    foreach ($plan['steps'] as $step) {
        $trace[] = $step['at'];
        if ($step['at'] === $failAt) {
            throw new FitnessFailure('injected', $trace);
        }
        $resolve = static function (array $source) use (&$values): array {
            fitnessRequire(isset($values[$source['origin']]), 'missing-producer');
            return $values[$source['origin']];
        };
        $inputs = array_map($resolve, $step['inputs']);
        $parameters = array_map($resolve, $step['parameters']);
        foreach ($step['outputs'] as $port => $output) {
            $values[$output['origin']] = [
                'capability' => $step['capability'], 'port' => $port,
                'inputs' => $inputs, 'parameters' => $parameters,
            ];
        }
    }
    $results = [];
    foreach ($plan['outputs'] as $port => $source) {
        fitnessRequire(isset($values[$source['origin']]), 'missing-producer');
        $results[$port] = $values[$source['origin']];
    }
    return ['trace' => $trace, 'outputs' => $plan['outputs'], 'values' => $results];
}

/** Reconstruct the old flat, three-policy declaration in the same fixture grammar. */
function fitnessFlatFixture(): array
{
    $model = fitnessFixture(false);
    $root = &$model['compositions']['bitcoin-public-address'];
    array_splice($root['steps'], 3, 1, [
        fitnessStep('keyhash', 'capability', 'hash-fixture', ['node' => ['output', 'derive', 'node']]),
        fitnessStep('encode', 'intent', 'key-hash-address',
            ['hash' => ['output', 'keyhash', 'digest']], ['network' => ['parameter', 'network']]),
    ]);
    $root['steps'][5]['inputs']['address'] = ['output', 'encode', 'address'];
    unset($root, $model['compositions']['keyhash-route']);
    foreach ($model['intents'] as &$intent) {
        $intent['bindings'] = [[
            'at' => ['encode'], 'composition' => $intent['bindings'][1]['composition'],
        ]];
    }
    unset($intent);
    return $model;
}

/** Independent policy-flow roots; no required Bitcoin-wide execution shell. */
function fitnessIndependentRootsFixture(): array
{
    $model = fitnessFixture(true);
    $oldRoot = $model['compositions']['bitcoin-public-address'];
    $model['contracts']['admitted-public-derivation'] = fitnessContract(
        ['request' => 'admitted-request'], ['node' => 'derived-public-node']);
    // Shared composition, not a capability combining several responsibilities.
    $model['compositions']['bitcoin-public-derivation'] = [
        'contract' => 'admitted-public-derivation',
        'steps' => array_slice($oldRoot['steps'], 0, 3),
        'outputs' => ['node' => ['output', 'derive', 'node']],
    ];
    $prepare = fitnessStep('prepare', 'composition', 'bitcoin-public-derivation',
        ['request' => ['input', 'request']]);
    $network = ['network' => ['parameter', 'network']];
    $model['compositions']['bitcoin-keyhash-public-address'] = [
        'contract' => 'public-address', 'policies' => ['p2pkh', 'p2sh-p2wpkh', 'p2wpkh'],
        'steps' => [
            $prepare,
            fitnessStep('keyhash', 'capability', 'hash-fixture',
                ['node' => ['output', 'prepare', 'node']]),
            fitnessStep('encode', 'intent', 'key-hash-address',
                ['hash' => ['output', 'keyhash', 'digest']], $network),
            fitnessStep('result', 'capability', 'make-result',
                ['address' => ['output', 'encode', 'address']]),
        ],
        'outputs' => ['result' => ['output', 'result', 'result']],
    ];
    $model['compositions']['bitcoin-taproot-public-address'] = [
        'contract' => 'public-address', 'policies' => ['p2tr-keypath'],
        'steps' => [
            $prepare,
            fitnessStep('output-key', 'capability', 'taproot-output-fixture',
                ['node' => ['output', 'prepare', 'node']]),
            fitnessStep('encode', 'capability', 'witness-v1-fixture',
                ['key' => ['output', 'output-key', 'key']], $network),
            fitnessStep('result', 'capability', 'make-result',
                ['address' => ['output', 'encode', 'address']]),
        ],
        'outputs' => ['result' => ['output', 'result', 'result']],
    ];
    // Remove both earlier shapes: neither independent root can call them.
    unset($model['compositions']['bitcoin-public-address'],
        $model['compositions']['keyhash-route'], $model['compositions']['taproot-route']);
    foreach ($model['intents'] as &$intent) {
        if ($intent['policy'] === 'p2tr-keypath') {
            $intent['root'] = 'bitcoin-taproot-public-address';
            $intent['bindings'] = [];
        } else {
            $intent['root'] = 'bitcoin-keyhash-public-address';
            $intent['bindings'] = [[
                'at' => ['encode'], 'composition' => $intent['bindings'][1]['composition'],
            ]];
        }
    }
    unset($intent);
    return $model;
}

/** Compare the complete producer graph independently of composition-local names. */
function fitnessNormalizedPlan(array $plan): array
{
    $origins = [];
    foreach ($plan['steps'] as $index => $step) {
        foreach ($step['outputs'] as $port => $source) {
            $origins[$source['origin']] = $index . ':' . $port;
        }
    }
    $normalize = static function (array $source) use ($origins): array {
        if (!str_starts_with($source['origin'], '@')) {
            fitnessRequire(isset($origins[$source['origin']]), 'missing-producer');
            $source['origin'] = $origins[$source['origin']];
        }
        return $source;
    };
    $steps = [];
    foreach ($plan['steps'] as $step) {
        unset($step['at']);
        foreach (['inputs', 'parameters', 'outputs'] as $field) {
            $step[$field] = array_map($normalize, $step[$field]);
        }
        $steps[] = $step;
    }
    return ['steps' => $steps, 'outputs' => array_map($normalize, $plan['outputs'])];
}

function fitnessReject(array $model, string $reason): void
{
    try {
        fitnessAssemble($model);
    } catch (FitnessFailure $failure) {
        fitnessRequire($failure->getMessage() === $reason,
            'unexpected:' . $failure->getMessage() . ':expected:' . $reason);
        return;
    }
    throw new FitnessFailure('accepted-invalid:' . $reason);
}

function fitnessRenameReference(array $reference): array
{
    if ($reference[0] === 'output') {
        $reference[1] = 'renamed-' . $reference[1];
    }
    return $reference;
}

$baseline = fitnessAssemble(fitnessFixture(false));
$flat = fitnessAssemble(fitnessFlatFixture());
foreach ($flat as $key => $plan) {
    fitnessRequire(fitnessNormalizedPlan($plan) === fitnessNormalizedPlan($baseline[$key]),
        'flat-migration-connections');
    fitnessRequire(fitnessRun($plan)['values'] === fitnessRun($baseline[$key])['values'],
        'flat-migration-values');
}
$model = fitnessFixture(true);
$plans = fitnessAssemble($model);
fitnessRequire(count($baseline) === 6 && count($plans) === 8, 'intent-count');
foreach ($baseline as $key => $plan) {
    fitnessRequire($plans[$key] === $plan, 'existing-policy-changed');
}

$abortChecks = 0;
foreach ($plans as $key => $plan) {
    $calls = array_column($plan['steps'], 'capability');
    $isTaproot = str_ends_with($key, '/p2tr-keypath');
    fitnessRequire(in_array('taproot-output-fixture', $calls, true) === $isTaproot
        && in_array('hash-fixture', $calls, true) === !$isTaproot, 'selected-route');
    fitnessRequire(fitnessRun($plan)['outputs']['result']['role'] === 'public-result', 'complete-result');
    foreach (array_column($plan['steps'], 'at') as $index => $failAt) {
        try {
            fitnessRun($plan, $failAt);
            throw new FitnessFailure('failure-did-not-abort');
        } catch (FitnessFailure $failure) {
            fitnessRequire($failure->getMessage() === 'injected'
                && $failure->trace === array_slice(array_column($plan['steps'], 'at'), 0, $index + 1),
                'failure-continuation');
            ++$abortChecks;
        }
    }
}

// Rename every local step, source reference and selected occurrence path.
$renamed = $model;
foreach ($renamed['compositions'] as &$composition) {
    foreach ($composition['steps'] as &$step) {
        $step['id'] = 'renamed-' . $step['id'];
        $step['inputs'] = array_map('fitnessRenameReference', $step['inputs']);
        $step['parameters'] = array_map('fitnessRenameReference', $step['parameters']);
    }
    unset($step);
    $composition['outputs'] = array_map('fitnessRenameReference', $composition['outputs']);
}
unset($composition);
foreach ($renamed['intents'] as &$intent) {
    foreach ($intent['bindings'] as &$binding) {
        $binding['at'] = array_map(static fn(string $id): string => 'renamed-' . $id, $binding['at']);
    }
    unset($binding);
}
unset($intent);
foreach (fitnessAssemble($renamed) as $key => $plan) {
    fitnessRequire(array_column($plan['steps'], 'capability')
        === array_column($plans[$key]['steps'], 'capability'), 'name-coupling');
}

// Extra early and late selection layers stress the same compiler. These
// neutral layers are synthetic, not recommended production wrappers or steps.
$nested = $model;
$nested['compositions']['test-outer'] = [
    'contract' => 'public-address',
    'steps' => [fitnessStep('begin', 'intent', 'public-address',
        ['request' => ['input', 'request']], ['network' => ['parameter', 'network']])],
    'outputs' => ['result' => ['output', 'begin', 'result']],
];
$nested['compositions']['test-finish'] = [
    'contract' => 'make-result',
    'steps' => [fitnessStep('end', 'intent', 'make-result', ['address' => ['input', 'address']])],
    'outputs' => ['result' => ['output', 'end', 'result']],
];
$nested['compositions']['test-result'] = [
    'contract' => 'make-result',
    'steps' => [fitnessStep('emit', 'capability', 'make-result', ['address' => ['input', 'address']])],
    'outputs' => ['result' => ['output', 'emit', 'result']],
];
$nested['compositions']['bitcoin-public-address']['steps'][4]['kind'] = 'composition';
$nested['compositions']['bitcoin-public-address']['steps'][4]['target'] = 'test-finish';
foreach ($nested['intents'] as &$intent) {
    $intent['root'] = 'test-outer';
    foreach ($intent['bindings'] as &$binding) {
        array_unshift($binding['at'], 'begin');
    }
    unset($binding);
    $intent['bindings'][] = ['at' => ['begin'], 'composition' => 'bitcoin-public-address'];
    $intent['bindings'][] = ['at' => ['begin', 'result', 'end'], 'composition' => 'test-result'];
}
unset($intent);
foreach (fitnessAssemble($nested) as $key => $plan) {
    fitnessRequire(array_column($plan['steps'], 'capability')
        === array_column($plans[$key]['steps'], 'capability'), 'fixed-depth-coupling');
    fitnessRun($plan);
}

// The same reusable declaration occurs twice, with distinct local bindings.
// Both results are exported; these are not unused Bitcoin-policy branches.
$repeated = $nested;
$repeated['contracts']['two-results'] = fitnessContract(
    ['address' => 'address-for-intent'], ['first' => 'public-result', 'second' => 'public-result']);
$repeated['capabilities']['second-result-fixture'] = ['contract' => 'make-result'];
$repeated['compositions']['test-second-result'] = [
    'contract' => 'make-result',
    'steps' => [fitnessStep('emit', 'capability', 'second-result-fixture',
        ['address' => ['input', 'address']])],
    'outputs' => ['result' => ['output', 'emit', 'result']],
];
$repeated['compositions']['test-pair'] = [
    'contract' => 'two-results',
    'steps' => [
        fitnessStep('left', 'composition', 'test-finish', ['address' => ['input', 'address']]),
        fitnessStep('right', 'composition', 'test-finish', ['address' => ['input', 'address']]),
    ],
    'outputs' => ['first' => ['output', 'left', 'result'], 'second' => ['output', 'right', 'result']],
];
$repeated['intents'] = [[
    'network' => 'synthetic', 'policy' => 'synthetic', 'root' => 'test-pair',
    'bindings' => [
        ['at' => ['left', 'end'], 'composition' => 'test-result'],
        ['at' => ['right', 'end'], 'composition' => 'test-second-result'],
    ],
]];
$pair = fitnessAssemble($repeated)['synthetic/synthetic'];
fitnessRequire(array_column($pair['steps'], 'capability')
    === ['make-result', 'second-result-fixture'], 'occurrence-capture');
fitnessRequire($pair['outputs']['first']['origin'] !== $pair['outputs']['second']['origin'],
    'output-capture');
fitnessRun($pair);
$missingSibling = $repeated;
array_pop($missingSibling['intents'][0]['bindings']);
fitnessReject($missingSibling, 'missing-binding');

$cases = [
    'duplicate-intent' => static function (array &$m): void { $m['intents'][] = $m['intents'][0]; },
    'duplicate-binding' => static function (array &$m): void {
        $m['intents'][0]['bindings'][] = $m['intents'][0]['bindings'][0];
    },
    'missing-binding' => static function (array &$m): void {
        // Bare "encode" cannot identify the nested occurrence address/encode.
        $m['intents'][0]['bindings'][1]['at'] = ['encode'];
    },
    'unused-binding' => static function (array &$m): void {
        $m['intents'][0]['bindings'][] = ['at' => ['unused'], 'composition' => 'p2pkh-terminal'];
    },
    'missing-reference' => static function (array &$m): void {
        $m['intents'][0]['bindings'][0]['composition'] = 'absent';
    },
    'target-contract' => static function (array &$m): void {
        $m['intents'][0]['bindings'][0]['composition'] = 'p2pkh-terminal';
    },
    'incompatible-policy' => static function (array &$m): void {
        $m['intents'][3]['bindings'][0]['composition'] = 'keyhash-route';
    },
    'ports' => static function (array &$m): void {
        unset($m['compositions']['bitcoin-public-address']['steps'][2]['inputs']['path']);
    },
    'incompatible-port' => static function (array &$m): void {
        $m['compositions']['bitcoin-public-address']['steps'][2]['inputs']['node']
            = ['output', 'depth', 'path'];
    },
    'invalid-source' => static function (array &$m): void {
        $m['compositions']['bitcoin-public-address']['steps'][1]['inputs']['node']
            = ['output', 'derive', 'node'];
    },
    'duplicate-step' => static function (array &$m): void {
        $m['compositions']['bitcoin-public-address']['steps'][2]['id'] = 'depth';
    },
    'cycle' => static function (array &$m): void {
        // An intent-bound cycle exists only in the last advertised profile.
        $m['compositions']['test-cycle'] = [
            'contract' => 'derived-node-address',
            'steps' => [fitnessStep('again', 'intent', 'derived-node-address',
                ['node' => ['input', 'node']], ['network' => ['parameter', 'network']])],
            'outputs' => ['address' => ['output', 'again', 'address']],
        ];
        $m['intents'][7]['bindings'][0]['composition'] = 'test-cycle';
        $m['intents'][7]['bindings'][1] = [
            'at' => ['address', 'again'], 'composition' => 'test-cycle'];
    },
];
foreach ($cases as $reason => $change) {
    $mutant = $model;
    $change($mutant);
    fitnessReject($mutant, $reason);
}
// Export and lexical-scope checks share the same errors as ordinary ports/sources.
$mutant = $model;
$mutant['compositions']['bitcoin-public-address']['outputs']['result'] = ['output', 'derive', 'node'];
fitnessReject($mutant, 'incompatible-port');
$mutant = $model;
$mutant['compositions']['keyhash-route']['steps'][0]['inputs']['node'] = ['output', 'derive', 'node'];
fitnessReject($mutant, 'invalid-source');

// User-diagram counter-proof: independent flows share a resource, diverge,
// reuse another compatible resource, then finish in their own context.
// Neutral domain contracts only: these are NOT Ethereum/Cardano/Solana models.
$topology = ['contracts' => [], 'capabilities' => [], 'compositions' => [], 'intents' => []];
foreach ([
    'whole' => ['fixture-request', 'fixture-result'],
    'prepare' => ['fixture-request', 'normalized-fixture'],
    'common-a' => ['normalized-fixture', 'first-checkpoint'],
    'middle' => ['first-checkpoint', 'resumed-fixture'],
    'common-b' => ['resumed-fixture', 'finished-fixture'],
    'finish' => ['finished-fixture', 'fixture-result'],
] as $id => [$inputRole, $outputRole]) {
    $topology['contracts'][$id] = fitnessContract(['value' => $inputRole], ['value' => $outputRole]);
}
foreach (['common-a', 'common-b'] as $id) {
    $topology['capabilities'][$id] = ['contract' => $id];
}
foreach (['x', 'y', 'z'] as $id) {
    $topology['capabilities']['middle-' . $id] = ['contract' => 'middle'];
    $topology['compositions']['middle-' . $id] = [
        'contract' => 'middle',
        'steps' => [fitnessStep('work', 'capability', 'middle-' . $id, ['value' => ['input', 'value']])],
        'outputs' => ['value' => ['output', 'work', 'value']],
    ];
}
$topologyChecks = $topologyAborts = 0;
foreach (['alpha' => ['x', 'y'], 'beta' => ['y'], 'gamma' => ['z']] as $candidate => $routes) {
    $topology['capabilities'][$candidate . '-prepare'] = ['contract' => 'prepare'];
    $topology['capabilities'][$candidate . '-finish'] = ['contract' => 'finish'];
    $topology['compositions'][$candidate] = [
        'contract' => 'whole',
        'steps' => [
            fitnessStep('prepare', 'capability', $candidate . '-prepare', ['value' => ['input', 'value']]),
            fitnessStep('first', 'capability', 'common-a', ['value' => ['output', 'prepare', 'value']]),
            fitnessStep('diverge', 'intent', 'middle', ['value' => ['output', 'first', 'value']]),
            fitnessStep('resume', 'capability', 'common-b', ['value' => ['output', 'diverge', 'value']]),
            fitnessStep('finish', 'capability', $candidate . '-finish', ['value' => ['output', 'resume', 'value']]),
        ],
        'outputs' => ['value' => ['output', 'finish', 'value']],
    ];
    // Each candidate assembles its own advertised intents; shared catalogue
    // resources do not transfer execution or values between candidates.
    $topology['intents'] = [];
    foreach ($routes as $route) {
        $topology['intents'][] = [
            'network' => 'fixture-network', 'policy' => $route, 'root' => $candidate,
            'bindings' => [['at' => ['diverge'], 'composition' => 'middle-' . $route]],
        ];
    }
    foreach (fitnessAssemble($topology) as $key => $plan) {
        $route = substr($key, strrpos($key, '/') + 1);
        $expected = [$candidate . '-prepare', 'common-a', 'middle-' . $route,
            'common-b', $candidate . '-finish'];
        fitnessRequire(array_column($plan['steps'], 'capability') === $expected, 'topology-route');
        $token = ['fixture-input' => '@input/value'];
        foreach ($expected as $capability) {
            $token = ['capability' => $capability, 'port' => 'value',
                'inputs' => ['value' => $token], 'parameters' => []];
        }
        fitnessRequire(fitnessRun($plan)['values'] === ['value' => $token], 'topology-value-flow');
        foreach (array_column($plan['steps'], 'at') as $index => $at) {
            try {
                fitnessRun($plan, $at);
                throw new FitnessFailure('failure-did-not-abort');
            } catch (FitnessFailure $failure) {
                fitnessRequire($failure->getMessage() === 'injected'
                    && $failure->trace === array_slice(array_column($plan['steps'], 'at'), 0, $index + 1),
                    'topology-failure-continuation');
                ++$topologyAborts;
            }
        }
        ++$topologyChecks;
    }
}
// A different operation can start and finish at compatible local contracts.
// It needs no Bitcoin request, derivation, HASH160, network or public-result shell.
$topology['compositions']['delta'] = [
    'contract' => 'common-b',
    'steps' => [fitnessStep('only', 'capability', 'common-b', ['value' => ['input', 'value']])],
    'outputs' => ['value' => ['output', 'only', 'value']],
];
$topology['intents'] = [[
    'network' => 'fixture-network', 'policy' => 'unrelated', 'root' => 'delta', 'bindings' => [],
]];
$delta = fitnessAssemble($topology)['fixture-network/unrelated'];
fitnessRequire(array_column($delta['steps'], 'capability') === ['common-b']
    && fitnessRun($delta)['outputs']['value']['role'] === 'finished-fixture', 'universal-root');
++$topologyChecks;
// Same nominal PHP representation cannot replace an incompatible semantic input.
$wrongRoot = $topology;
$wrongRoot['compositions']['delta']['contract'] = 'whole';
fitnessReject($wrongRoot, 'incompatible-port');
// A later common resource cannot silently bypass the selected middle producer.
$wrongWire = $topology;
$wrongWire['compositions']['delta'] = $topology['compositions']['gamma'];
$wrongWire['compositions']['delta']['steps'][3]['inputs']['value'] = ['output', 'first', 'value'];
$wrongWire['intents'][0]['bindings'] = [['at' => ['diverge'], 'composition' => 'middle-z']];
fitnessReject($wrongWire, 'incompatible-port');

// Current direction: the intent selects a complete independent policy flow.
// The same compiler expands shared resources; it acquires no policy dispatch.
$independent = fitnessIndependentRootsFixture();
$independentPlans = fitnessAssemble($independent);
$independentAborts = 0;
fitnessRequire(count($independentPlans) === 8, 'independent-intent-count');
foreach ($independentPlans as $key => $plan) {
    fitnessRequire(fitnessNormalizedPlan($plan) === fitnessNormalizedPlan($plans[$key])
        && fitnessRun($plan)['values'] === fitnessRun($plans[$key])['values'],
        'independent-flow-connections');
    foreach (array_column($plan['steps'], 'at') as $index => $at) {
        try {
            fitnessRun($plan, $at);
            throw new FitnessFailure('failure-did-not-abort');
        } catch (FitnessFailure $failure) {
            fitnessRequire($failure->getMessage() === 'injected'
                && $failure->trace === array_slice(array_column($plan['steps'], 'at'), 0, $index + 1),
                'independent-failure-continuation');
            ++$independentAborts;
        }
    }
}
$wrongPolicyRoot = $independent;
$wrongPolicyRoot['intents'][3]['root'] = 'bitcoin-keyhash-public-address';
fitnessReject($wrongPolicyRoot, 'incompatible-policy');
$wrongKey = $independent;
$wrongKey['compositions']['bitcoin-taproot-public-address']['steps'][2]['inputs']['key']
    = ['output', 'prepare', 'node'];
fitnessReject($wrongKey, 'incompatible-port');

// This relation is not exercised by the admission trace fixture above.
$currentMainnetXpubPolicies = ['p2pkh'];
fitnessRequire(!in_array('p2tr-keypath', $currentMainnetXpubPolicies, true), 'current-o02');
$hypotheticalMainnetXpubPolicies = [...$currentMainnetXpubPolicies, 'p2tr-keypath'];
fitnessRequire(in_array('p2pkh', $hypotheticalMainnetXpubPolicies, true)
    && in_array('p2tr-keypath', $hypotheticalMainnetXpubPolicies, true), 'hypothetical-relation');

echo "binding mechanism: 6 baseline plans preserved in 8-intent extension; renamed steps and early/middle/late selections pass\n";
echo 'failures: ' . $abortChecks . ' exact-prefix aborts; '
    . (count($cases) + 3) . " invalid fixtures rejected before execution\n";
echo "scope: repeated declaration occurrences retain independent bindings and exported origins\n";
echo "migration: 6 reconstructed flat plans preserve normalized connections and deterministic token results\n";
echo 'topology: ' . $topologyChecks . ' independent-root plans; ' . $topologyAborts
    . " exact-prefix aborts; 2 incompatible semantic reuse fixtures rejected\n";
echo 'policy roots: 8 plans preserve graphs/token results without the old Bitcoin root; '
    . $independentAborts . " exact-prefix aborts; 2 incompatible root/input fixtures rejected\n";
echo "limits: symbolic role catalogue and hypothetical admission only; O02 still rejects P2TR; no Taproot correctness claim\n";
