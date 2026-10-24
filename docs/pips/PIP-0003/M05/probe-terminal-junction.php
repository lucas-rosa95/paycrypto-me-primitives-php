<?php

declare(strict_types=1);

/**
 * Synthetic C1 junction comparison for Issue #41. Run with PHP 8.3+.
 * No provider, cryptography, codec, public API, or proposed production loader.
 */

final class ProbeFailure extends RuntimeException {}

function fixture(string $form): array
{
    $policies = [
        'p2pkh' => ['id' => 'p2pkh-address', 'parameter' => 'p2pkh-version'],
        'p2sh-p2wpkh' => ['id' => 'nested-witness-address', 'parameter' => 'p2sh-version'],
        'p2wpkh' => ['id' => 'native-witness-address', 'parameter' => 'segwit-hrp'],
    ];
    $terminals = [];
    $intents = [];
    foreach ($policies as $policy => $spec) {
        $terminals[$spec['id']] = [
            'contract' => 'key-hash-address',
            'input' => 'derived-public-key-hash',
            'output' => 'address-for-admitted-intent',
            'policy' => $policy,
            'parameter' => $spec['parameter'],
        ];
        foreach (['mainnet', 'testnet'] as $network) {
            $binding = ['network' => $network, 'policy' => $policy,
                'composition' => 'bitcoin-public-address'];
            if ($form === 'slot') {
                $binding['slots'] = [['slot' => 'terminal', 'composition' => $spec['id']]];
            } else {
                $binding['targets'] = [['step' => 'encode', 'composition' => $spec['id']]];
            }
            $intents[] = $binding;
        }
    }

    return [
        'candidate' => 'bitcoin',
        'form' => $form,
        'composition' => [
            'id' => 'bitcoin-public-address',
            'slots' => $form === 'slot' ? ['terminal' => 'key-hash-address'] : [],
            'steps' => [
                ['id' => 'admit', 'target' => 'extended-public-key-admission'],
                ['id' => 'depth', 'target' => 'remaining-depth'],
                ['id' => 'derive', 'target' => 'ckd-public-path'],
                ['id' => 'keyhash', 'target' => 'hash160',
                    'input' => 'derive.node.publicKey',
                    'outputRole' => 'derived-public-key-hash'],
                ['id' => 'encode',
                    'target' => $form === 'slot' ? 'slot:terminal' : 'intent-step:encode',
                    ...($form === 'direct' ? ['requires' => 'key-hash-address'] : []),
                    'input' => 'keyhash.digest',
                    'inputRole' => 'derived-public-key-hash',
                    'parameter' => 'admitted-network'],
                ['id' => 'result', 'target' => 'public-address-result',
                    'input' => 'encode.address'],
            ],
        ],
        'terminals' => $terminals,
        'intents' => $intents,
    ];
}

function rejectUnless(bool $condition, string $reason): void
{
    if (!$condition) {
        throw new ProbeFailure($reason);
    }
}

/** Validate the same local semantic obligations for either source form. */
function assemble(array $model): array
{
    $form = $model['form'];
    $composition = $model['composition'];
    $steps = $composition['steps'];
    rejectUnless($composition['id'] === 'bitcoin-public-address', 'candidate-composition');
    rejectUnless(array_column($steps, 'id') ===
        ['admit', 'depth', 'derive', 'keyhash', 'encode', 'result'], 'ordered-route');
    rejectUnless($steps[3]['input'] === 'derive.node.publicKey'
        && $steps[3]['outputRole'] === 'derived-public-key-hash', 'hash-origin');
    rejectUnless($steps[4]['input'] === 'keyhash.digest'
        && $steps[4]['inputRole'] === $steps[3]['outputRole']
        && $steps[4]['parameter'] === 'admitted-network'
        && $steps[5]['input'] === 'encode.address', 'semantic-connection');
    rejectUnless($form === 'slot' || $form === 'direct', 'form');
    if ($form === 'slot') {
        rejectUnless(array_keys($composition['slots']) === ['terminal']
            && $steps[4]['target'] === 'slot:terminal', 'slot-declaration');
        $requiredContract = $composition['slots']['terminal'];
    } else {
        rejectUnless($composition['slots'] === []
            && $steps[4]['target'] === 'intent-step:encode', 'direct-target');
        $requiredContract = $steps[4]['requires'] ?? null;
    }
    rejectUnless($requiredContract === 'key-hash-address', 'junction-contract');

    $expected = [
        'p2pkh' => ['p2pkh-address', 'p2pkh-version'],
        'p2sh-p2wpkh' => ['nested-witness-address', 'p2sh-version'],
        'p2wpkh' => ['native-witness-address', 'segwit-hrp'],
    ];
    $plans = [];
    foreach ($model['intents'] as $binding) {
        rejectUnless(($binding['composition'] ?? null) === $composition['id'], 'wrong-composition');
        $network = $binding['network'] ?? null;
        $policy = $binding['policy'] ?? null;
        rejectUnless(in_array($network, ['mainnet', 'testnet'], true)
            && isset($expected[$policy]), 'unknown-intent');
        $key = $network . '/' . $policy;
        rejectUnless(!isset($plans[$key]), 'duplicate-intent');
        if ($form === 'slot') {
            rejectUnless(isset($binding['slots']) && !isset($binding['targets']), 'slot-binding');
            rejectUnless(count($binding['slots']) === 1, 'slot-binding-duplicate');
            rejectUnless(($binding['slots'][0]['slot'] ?? null) === 'terminal', 'slot-binding');
            $terminalId = $binding['slots'][0]['composition'] ?? null;
        } else {
            rejectUnless(isset($binding['targets']) && !isset($binding['slots']), 'direct-binding');
            rejectUnless(count($binding['targets']) === 1, 'direct-binding-duplicate');
            rejectUnless(($binding['targets'][0]['step'] ?? null) === 'encode', 'direct-binding');
            $terminalId = $binding['targets'][0]['composition'] ?? null;
        }
        $terminal = $model['terminals'][$terminalId] ?? null;
        rejectUnless($terminal !== null, 'missing-terminal');
        rejectUnless($terminal['contract'] === $requiredContract
            && $terminal['input'] === $steps[4]['inputRole']
            && $terminal['output'] === 'address-for-admitted-intent',
            'incompatible-terminal');
        rejectUnless($terminal['policy'] === $policy
            && $terminalId === $expected[$policy][0], 'wrong-policy-terminal');
        rejectUnless($terminal['parameter'] === $expected[$policy][1], 'wrong-parameter-role');
        $plans[$key] = $terminalId;
    }
    rejectUnless(count($plans) === 6, 'incomplete-intents');
    return $plans;
}

/** Execute symbolic markers only; a thrown failure must stop the trace. */
function invoke(array $model, array $plans, string $network, string $policy, ?string $failAt): array
{
    $key = $network . '/' . $policy;
    rejectUnless(isset($plans[$key]), 'unadmitted-intent');
    $trace = [];
    foreach ($model['composition']['steps'] as $step) {
        $target = $step['id'] === 'encode' ? $plans[$key] : $step['id'];
        $trace[] = $target;
        if ($target === $failAt) {
            throw new ProbeFailure('abort:' . implode(',', $trace));
        }
    }
    return $trace;
}

function expectRejected(array $model, string $reason): void
{
    try {
        assemble($model);
    } catch (ProbeFailure $failure) {
        rejectUnless($failure->getMessage() === $reason,
            'unexpected:' . $failure->getMessage() . ':expected:' . $reason);
        return;
    }
    throw new ProbeFailure('accepted-invalid:' . $reason);
}

foreach (['slot', 'direct'] as $form) {
    $base = fixture($form);
    $plans = assemble($base);
    $trace = invoke($base, $plans, 'testnet', 'p2sh-p2wpkh', null);
    rejectUnless($trace === ['admit', 'depth', 'derive', 'keyhash',
        'nested-witness-address', 'result'], 'selected-route');
    foreach (['keyhash', 'nested-witness-address'] as $failAt) {
        try {
            invoke($base, $plans, 'testnet', 'p2sh-p2wpkh', $failAt);
            throw new ProbeFailure('failure-did-not-abort');
        } catch (ProbeFailure $failure) {
            rejectUnless(str_starts_with($failure->getMessage(), 'abort:')
                && !str_contains($failure->getMessage(), ',result'), 'failure-continuation');
        }
    }

    $cases = [];
    $cases['incomplete-intents'] = static function (array &$m): void { array_pop($m['intents']); };
    $cases['duplicate-intent'] = static function (array &$m): void { $m['intents'][] = $m['intents'][0]; };
    $cases['missing-terminal'] = static function (array &$m) use ($form): void {
        if ($form === 'slot') {
            $m['intents'][0]['slots'][0]['composition'] = 'absent';
        } else {
            $m['intents'][0]['targets'][0]['composition'] = 'absent';
        }
    };
    $cases[$form === 'slot' ? 'slot-binding-duplicate' : 'direct-binding-duplicate'] =
        static function (array &$m) use ($form): void {
            $field = $form === 'slot' ? 'slots' : 'targets';
            $m['intents'][0][$field][] = $m['intents'][0][$field][0];
        };
    $cases[$form === 'slot' ? 'slot-binding' : 'direct-binding'] =
        static function (array &$m) use ($form): void {
            unset($m['intents'][0][$form === 'slot' ? 'slots' : 'targets']);
        };
    $cases['incompatible-terminal'] = static function (array &$m): void {
        $m['terminals']['p2pkh-address']['input'] = 'script-hash';
    };
    $cases['wrong-policy-terminal'] = static function (array &$m) use ($form): void {
        if ($form === 'slot') {
            $m['intents'][0]['slots'][0]['composition'] = 'nested-witness-address';
        } else {
            $m['intents'][0]['targets'][0]['composition'] = 'nested-witness-address';
        }
    };
    $cases['wrong-parameter-role'] = static function (array &$m): void {
        $m['terminals']['p2pkh-address']['parameter'] = 'p2sh-version';
    };
    $cases['hash-origin'] = static function (array &$m): void {
        $m['composition']['steps'][3]['outputRole'] = 'unqualified-20-byte-digest';
    };
    $cases['semantic-connection'] = static function (array &$m): void {
        $m['composition']['steps'][4]['input'] = 'admit.node.publicKey';
    };
    $cases['junction-contract'] = static function (array &$m) use ($form): void {
        if ($form === 'slot') {
            $m['composition']['slots']['terminal'] = 'any-address';
        } else {
            $m['composition']['steps'][4]['requires'] = 'any-address';
        }
    };
    foreach ($cases as $reason => $change) {
        $mutant = $base;
        $change($mutant);
        expectRejected($mutant, $reason);
    }
    echo $form . ': six admitted intents; selected route and abort; '
        . count($cases) . " invalid fixtures rejected\n";
}
