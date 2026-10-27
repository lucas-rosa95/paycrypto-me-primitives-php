<?php

declare(strict_types=1);

/**
 * Synthetic C1 fitness probe: two Bitcoin policy junctions, including P2TR.
 * Run with PHP 8.3+. No cryptography, codec, provider, production loader,
 * admission decision, or proposed C2 source format is implemented here.
 */

final class DivergenceProbeFailure extends RuntimeException {}

function divergenceRequire(bool $condition, string $reason): void
{
    if (!$condition) {
        throw new DivergenceProbeFailure($reason);
    }
}

function divergenceFixture(): array
{
    $policies = [
        'p2pkh' => ['route' => 'keyhash-route', 'terminal' => 'p2pkh-address'],
        'p2sh-p2wpkh' => ['route' => 'keyhash-route', 'terminal' => 'nested-address'],
        'p2wpkh' => ['route' => 'keyhash-route', 'terminal' => 'witness-v0-address'],
        'p2tr-keypath' => ['route' => 'taproot-route', 'terminal' => 'witness-v1-address'],
    ];
    $bindings = [];
    foreach (['mainnet', 'testnet'] as $network) {
        foreach ($policies as $policy => $selection) {
            $bindings[] = ['network' => $network, 'policy' => $policy, ...$selection];
        }
    }

    return [
        'candidate' => 'bitcoin',
        'shared' => [
            ['id' => 'admit', 'output' => 'admitted-public-node'],
            ['id' => 'depth', 'input' => 'admit.node'],
            ['id' => 'derive', 'input' => 'admit.node', 'output' => 'derived-public-node'],
            ['id' => 'address', 'input' => 'derive.node',
                'requires' => 'derived-node-address', 'target' => 'intent:route'],
            ['id' => 'result', 'input' => 'address.address'],
        ],
        'routes' => [
            'keyhash-route' => [
                'input' => 'derived-public-node', 'contract' => 'derived-node-address',
                'steps' => [
                    ['id' => 'keyhash', 'input' => 'node.publicKey',
                        'output' => 'derived-public-key-hash'],
                    ['id' => 'encode', 'input' => 'keyhash.digest',
                        'requires' => 'key-hash-address', 'target' => 'intent:terminal'],
                ],
                'output' => 'address-for-admitted-intent',
            ],
            'taproot-route' => [
                'input' => 'derived-public-node', 'contract' => 'derived-node-address',
                'steps' => [
                    ['id' => 'output-key', 'input' => 'node.publicKey',
                        'output' => 'taproot-output-key'],
                    ['id' => 'encode', 'input' => 'output-key.key',
                        'requires' => 'taproot-output-key-address',
                        'target' => 'intent:terminal'],
                ],
                'output' => 'address-for-admitted-intent',
            ],
        ],
        'terminals' => [
            'p2pkh-address' => ['contract' => 'key-hash-address',
                'input' => 'derived-public-key-hash', 'parameter' => 'p2pkh-version',
                'output' => 'address-for-admitted-intent'],
            'nested-address' => ['contract' => 'key-hash-address',
                'input' => 'derived-public-key-hash', 'parameter' => 'p2sh-version',
                'output' => 'address-for-admitted-intent'],
            'witness-v0-address' => ['contract' => 'key-hash-address',
                'input' => 'derived-public-key-hash', 'parameter' => 'segwit-hrp',
                'output' => 'address-for-admitted-intent'],
            'witness-v1-address' => ['contract' => 'taproot-output-key-address',
                'input' => 'taproot-output-key', 'parameter' => 'segwit-hrp',
                'output' => 'address-for-admitted-intent'],
        ],
        'policies' => $policies,
        'bindings' => $bindings,
        // The current O02 allowlist has xpub/tpub only for P2PKH. A future
        // BIP86 profile would add an overlapping version/policy relation.
        'currentAdmission' => [
            'mainnet' => ['xpub' => ['p2pkh']],
            'testnet' => ['tpub' => ['p2pkh']],
        ],
    ];
}

/** Bounded checks of this fixture, not a general declaration validator. */
function divergenceAssemble(array $model): array
{
    divergenceRequire($model['candidate'] === 'bitcoin', 'candidate');
    $shared = $model['shared'];
    divergenceRequire(array_column($shared, 'id') ===
        ['admit', 'depth', 'derive', 'address', 'result'], 'shared-order');
    divergenceRequire($shared[3]['input'] === 'derive.node'
        && $shared[3]['requires'] === 'derived-node-address'
        && $shared[3]['target'] === 'intent:route'
        && $shared[4]['input'] === 'address.address', 'shared-junction');

    $routeInputs = [
        'keyhash-route' => ['keyhash', 'derived-public-key-hash',
            'keyhash.digest', 'key-hash-address'],
        'taproot-route' => ['output-key', 'taproot-output-key',
            'output-key.key', 'taproot-output-key-address'],
    ];
    foreach ($routeInputs as $id => [$firstStep, $firstOutput, $secondInput, $contract]) {
        $route = $model['routes'][$id] ?? null;
        divergenceRequire(is_array($route)
            && $route['input'] === 'derived-public-node'
            && $route['contract'] === $shared[3]['requires']
            && $route['output'] === 'address-for-admitted-intent', 'route-contract');
        divergenceRequire(array_column($route['steps'], 'id') === [$firstStep, 'encode']
            && $route['steps'][0]['input'] === 'node.publicKey'
            && $route['steps'][0]['output'] === $firstOutput
            && $route['steps'][1]['input'] === $secondInput
            && $route['steps'][1]['requires'] === $contract
            && $route['steps'][1]['target'] === 'intent:terminal', 'route-connection');
    }
    divergenceRequire(count($model['routes']) === 2, 'route-catalogue');

    $plans = [];
    foreach ($model['bindings'] as $binding) {
        $network = $binding['network'] ?? null;
        $policy = $binding['policy'] ?? null;
        $expected = $model['policies'][$policy] ?? null;
        divergenceRequire(in_array($network, ['mainnet', 'testnet'], true)
            && is_array($expected), 'unknown-intent');
        $key = $network . '/' . $policy;
        divergenceRequire(!isset($plans[$key]), 'duplicate-intent');
        divergenceRequire($binding['route'] === $expected['route']
            && $binding['terminal'] === $expected['terminal'], 'wrong-policy-route');
        $route = $model['routes'][$binding['route']] ?? null;
        $terminal = $model['terminals'][$binding['terminal']] ?? null;
        divergenceRequire(is_array($route) && is_array($terminal), 'missing-target');
        divergenceRequire($terminal['contract'] === $route['steps'][1]['requires']
            && $terminal['input'] === $route['steps'][0]['output']
            && $terminal['output'] === $route['output'],
            'incompatible-target');
        $plans[$key] = [$binding['route'], $binding['terminal']];
    }
    divergenceRequire(count($plans) === 8, 'incomplete-intents');
    return $plans;
}

/** Selection is data-driven at both junctions; no policy branch in execution. */
function divergenceInvoke(array $model, array $plans, string $network,
    string $policy, ?string $failAt = null): array
{
    $plan = $plans[$network . '/' . $policy] ?? null;
    divergenceRequire(is_array($plan), 'unadmitted-intent');
    [$routeId, $terminalId] = $plan;
    $trace = [];
    foreach ($model['shared'] as $step) {
        if ($step['id'] === 'address') {
            foreach ($model['routes'][$routeId]['steps'] as $routeStep) {
                $id = $routeStep['id'] === 'encode' ? $terminalId : $routeStep['id'];
                $trace[] = $id;
                if ($id === $failAt) {
                    throw new DivergenceProbeFailure('abort:' . implode(',', $trace));
                }
            }
            continue;
        }
        $trace[] = $step['id'];
        if ($step['id'] === $failAt) {
            throw new DivergenceProbeFailure('abort:' . implode(',', $trace));
        }
    }
    return $trace;
}

function divergenceRejects(array $model, string $reason): void
{
    try {
        divergenceAssemble($model);
    } catch (DivergenceProbeFailure $failure) {
        divergenceRequire($failure->getMessage() === $reason,
            'unexpected:' . $failure->getMessage() . ':expected:' . $reason);
        return;
    }
    throw new DivergenceProbeFailure('accepted-invalid:' . $reason);
}

function divergenceAdmits(array $admission, string $network,
    string $version, string $policy): bool
{
    return in_array($policy, $admission[$network][$version] ?? [], true);
}

$model = divergenceFixture();
$plans = divergenceAssemble($model);
divergenceRequire(divergenceInvoke($model, $plans, 'mainnet', 'p2wpkh') ===
    ['admit', 'depth', 'derive', 'keyhash', 'witness-v0-address', 'result'],
    'current-policy-trace');
divergenceRequire(divergenceInvoke($model, $plans, 'mainnet', 'p2tr-keypath') ===
    ['admit', 'depth', 'derive', 'output-key', 'witness-v1-address', 'result'],
    'taproot-policy-trace');
foreach (['derive', 'output-key', 'witness-v1-address'] as $failAt) {
    try {
        divergenceInvoke($model, $plans, 'mainnet', 'p2tr-keypath', $failAt);
        throw new DivergenceProbeFailure('failure-did-not-abort');
    } catch (DivergenceProbeFailure $failure) {
        divergenceRequire(str_starts_with($failure->getMessage(), 'abort:')
            && !str_contains($failure->getMessage(), ',result'), 'failure-continuation');
    }
}

$mutant = $model;
$mutant['bindings'][3]['route'] = 'keyhash-route';
divergenceRejects($mutant, 'wrong-policy-route');
$mutant = $model;
$mutant['routes']['taproot-route']['steps'][0]['id'] = 'keyhash';
divergenceRejects($mutant, 'route-connection');
$mutant = $model;
$mutant['terminals']['witness-v1-address']['input'] = 'derived-public-key-hash';
divergenceRejects($mutant, 'incompatible-target');
$mutant = $model;
$mutant['bindings'][] = $mutant['bindings'][0];
divergenceRejects($mutant, 'duplicate-intent');
$mutant = $model;
array_pop($mutant['bindings']);
divergenceRejects($mutant, 'incomplete-intents');

divergenceRequire(divergenceAdmits($model['currentAdmission'],
    'mainnet', 'xpub', 'p2pkh')
    && !divergenceAdmits($model['currentAdmission'],
        'mainnet', 'xpub', 'p2tr-keypath'), 'current-o02-rejection');
$hypotheticalAdmission = $model['currentAdmission'];
$hypotheticalAdmission['mainnet']['xpub'][] = 'p2tr-keypath';
divergenceRequire(divergenceAdmits($hypotheticalAdmission,
    'mainnet', 'xpub', 'p2tr-keypath')
    && divergenceAdmits($hypotheticalAdmission,
        'mainnet', 'xpub', 'p2pkh'),
    'hypothetical-overlap');

echo "bitcoin: two nested policy junctions; current and Taproot traces, abort and five invalid fixtures checked\n";
echo "admission: current O02 xpub/P2TR rejection; hypothetical overlapping version/policy relation only\n";
