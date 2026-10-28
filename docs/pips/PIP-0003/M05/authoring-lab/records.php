<?php
declare(strict_types=1);

namespace M05C2Lab;

// Disposable authoring fixture; never a production declaration or API.
// The same nine declarations are written in each of the three source forms.

return [
    new Flow(
        id: 'hash160', contract: 'hash160',
        steps: [
            new Call(
                id: 'sha', kind: 'capability', target: 'sha256',
                inputs: [new Link('bytes', ['input', 'bytes'])],
            ),
            new Call(
                id: 'ripe', kind: 'capability', target: 'ripemd160',
                inputs: [new Link('bytes', ['output', 'sha', 'digest'])],
            ),
        ],
        outputs: [new Link('digest', ['output', 'ripe', 'digest'])],
    ),
    new Flow(
        id: 'public-address', contract: 'public-address',
        steps: [
            new Call(
                id: 'admit', kind: 'capability', target: 'extended-public-key-admission',
                inputs: [new Link('request', ['input', 'request'])],
            ),
            new Call(
                id: 'depth', kind: 'capability', target: 'remaining-depth',
                inputs: [new Link('node', ['output', 'admit', 'node']), new Link('request', ['input', 'request'])],
            ),
            new Call(
                id: 'derive', kind: 'capability', target: 'ckd-public-path',
                inputs: [new Link('parent', ['output', 'admit', 'node']), new Link('path', ['output', 'depth', 'path'])],
            ),
            new Call(
                id: 'keyhash', kind: 'composition', target: 'hash160',
                inputs: [new Link('bytes', ['field', ['output', 'derive', 'node'], 'publicKey'])],
            ),
            new Call(
                id: 'encode', kind: 'intent', target: 'key-hash-address',
                inputs: [new Link('keyHash', ['output', 'keyhash', 'digest'])],
                parameters: [new Link('network', ['parameter', 'network'])],
            ),
            new Call(
                id: 'result', kind: 'capability', target: 'public-address-result',
                inputs: [new Link('request', ['input', 'request']), new Link('address', ['output', 'encode', 'address'])],
            ),
        ],
        outputs: [new Link('result', ['output', 'result', 'result'])],
    ),
    new Flow(
        id: 'p2pkh-address', contract: 'key-hash-address',
        steps: [
            new Call(
                id: 'payload', kind: 'capability', target: 'p2pkh-payload',
                inputs: [new Link('keyHash', ['input', 'keyHash'])],
                parameters: [new Link('version', ['field', ['parameter', 'network'], 'p2pkhVersion'])],
            ),
            new Call(
                id: 'encode', kind: 'capability', target: 'base58check-encode',
                inputs: [new Link('payload', ['output', 'payload', 'payload'])],
            ),
        ],
        outputs: [new Link('address', ['output', 'encode', 'address'])],
    ),
    new Flow(
        id: 'nested-witness-address', contract: 'key-hash-address',
        steps: [
            new Call(
                id: 'script', kind: 'capability', target: 'witness-v0-keyhash-script',
                inputs: [new Link('keyHash', ['input', 'keyHash'])],
            ),
            new Call(
                id: 'scripthash', kind: 'composition', target: 'hash160',
                inputs: [new Link('bytes', ['output', 'script', 'script'])],
            ),
            new Call(
                id: 'payload', kind: 'capability', target: 'p2sh-payload',
                inputs: [new Link('scriptHash', ['output', 'scripthash', 'digest'])],
                parameters: [new Link('version', ['field', ['parameter', 'network'], 'p2shVersion'])],
            ),
            new Call(
                id: 'encode', kind: 'capability', target: 'base58check-encode',
                inputs: [new Link('payload', ['output', 'payload', 'payload'])],
            ),
        ],
        outputs: [new Link('address', ['output', 'encode', 'address'])],
    ),
    new Flow(
        id: 'native-witness-address', contract: 'key-hash-address',
        steps: [
            new Call(
                id: 'encode', kind: 'capability', target: 'segwit-v0-keyhash-encode',
                inputs: [new Link('program', ['input', 'keyHash'])],
                parameters: [new Link('hrp', ['field', ['parameter', 'network'], 'segwitHrp'])],
            ),
        ],
        outputs: [new Link('address', ['output', 'encode', 'address'])],
    ),
    new Flow(
        id: 'finish', contract: 'make-result',
        steps: [
            new Call(
                id: 'end', kind: 'intent', target: 'make-result',
                inputs: [new Link('address', ['input', 'address'])],
            ),
        ],
        outputs: [new Link('result', ['output', 'end', 'result'])],
    ),
    new Flow(
        id: 'result-a', contract: 'make-result',
        steps: [
            new Call(
                id: 'emit', kind: 'capability', target: 'make-result',
                inputs: [new Link('address', ['input', 'address'])],
            ),
        ],
        outputs: [new Link('result', ['output', 'emit', 'result'])],
    ),
    new Flow(
        id: 'result-b', contract: 'make-result',
        steps: [
            new Call(
                id: 'emit', kind: 'capability', target: 'second-result-fixture',
                inputs: [new Link('address', ['input', 'address'])],
            ),
        ],
        outputs: [new Link('result', ['output', 'emit', 'result'])],
    ),
    new Flow(
        id: 'pair', contract: 'two-results',
        steps: [
            new Call(
                id: 'left', kind: 'composition', target: 'finish',
                inputs: [new Link('address', ['input', 'address'])],
            ),
            new Call(
                id: 'right', kind: 'composition', target: 'finish',
                inputs: [new Link('address', ['input', 'address'])],
            ),
        ],
        outputs: [new Link('first', ['output', 'left', 'result']), new Link('second', ['output', 'right', 'result'])],
    ),
];
