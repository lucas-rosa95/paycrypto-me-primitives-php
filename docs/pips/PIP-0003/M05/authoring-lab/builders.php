<?php
declare(strict_types=1);

namespace M05C2Lab;

// Disposable authoring fixture; never a production declaration or API.
// The same nine declarations are written in each of the three source forms.

return [
    (new Builder('hash160', 'hash160'))
        ->call(
            'sha', 'capability', 'sha256',
            inputs: [new Link('bytes', ['input', 'bytes'])],
        )
        ->call(
            'ripe', 'capability', 'ripemd160',
            inputs: [new Link('bytes', ['output', 'sha', 'digest'])],
        )
        ->export('digest', ['output', 'ripe', 'digest']),
    (new Builder('public-address', 'public-address'))
        ->call(
            'admit', 'capability', 'extended-public-key-admission',
            inputs: [new Link('request', ['input', 'request'])],
        )
        ->call(
            'depth', 'capability', 'remaining-depth',
            inputs: [new Link('node', ['output', 'admit', 'node']), new Link('request', ['input', 'request'])],
        )
        ->call(
            'derive', 'capability', 'ckd-public-path',
            inputs: [new Link('parent', ['output', 'admit', 'node']), new Link('path', ['output', 'depth', 'path'])],
        )
        ->call(
            'keyhash', 'composition', 'hash160',
            inputs: [new Link('bytes', ['field', ['output', 'derive', 'node'], 'publicKey'])],
        )
        ->call(
            'encode', 'intent', 'key-hash-address',
            inputs: [new Link('keyHash', ['output', 'keyhash', 'digest'])],
            parameters: [new Link('network', ['parameter', 'network'])],
        )
        ->call(
            'result', 'capability', 'public-address-result',
            inputs: [new Link('request', ['input', 'request']), new Link('address', ['output', 'encode', 'address'])],
        )
        ->export('result', ['output', 'result', 'result']),
    (new Builder('p2pkh-address', 'key-hash-address'))
        ->call(
            'payload', 'capability', 'p2pkh-payload',
            inputs: [new Link('keyHash', ['input', 'keyHash'])],
            parameters: [new Link('version', ['field', ['parameter', 'network'], 'p2pkhVersion'])],
        )
        ->call(
            'encode', 'capability', 'base58check-encode',
            inputs: [new Link('payload', ['output', 'payload', 'payload'])],
        )
        ->export('address', ['output', 'encode', 'address']),
    (new Builder('nested-witness-address', 'key-hash-address'))
        ->call(
            'script', 'capability', 'witness-v0-keyhash-script',
            inputs: [new Link('keyHash', ['input', 'keyHash'])],
        )
        ->call(
            'scripthash', 'composition', 'hash160',
            inputs: [new Link('bytes', ['output', 'script', 'script'])],
        )
        ->call(
            'payload', 'capability', 'p2sh-payload',
            inputs: [new Link('scriptHash', ['output', 'scripthash', 'digest'])],
            parameters: [new Link('version', ['field', ['parameter', 'network'], 'p2shVersion'])],
        )
        ->call(
            'encode', 'capability', 'base58check-encode',
            inputs: [new Link('payload', ['output', 'payload', 'payload'])],
        )
        ->export('address', ['output', 'encode', 'address']),
    (new Builder('native-witness-address', 'key-hash-address'))
        ->call(
            'encode', 'capability', 'segwit-v0-keyhash-encode',
            inputs: [new Link('program', ['input', 'keyHash'])],
            parameters: [new Link('hrp', ['field', ['parameter', 'network'], 'segwitHrp'])],
        )
        ->export('address', ['output', 'encode', 'address']),
    (new Builder('finish', 'make-result'))
        ->call(
            'end', 'intent', 'make-result',
            inputs: [new Link('address', ['input', 'address'])],
        )
        ->export('result', ['output', 'end', 'result']),
    (new Builder('result-a', 'make-result'))
        ->call(
            'emit', 'capability', 'make-result',
            inputs: [new Link('address', ['input', 'address'])],
        )
        ->export('result', ['output', 'emit', 'result']),
    (new Builder('result-b', 'make-result'))
        ->call(
            'emit', 'capability', 'second-result-fixture',
            inputs: [new Link('address', ['input', 'address'])],
        )
        ->export('result', ['output', 'emit', 'result']),
    (new Builder('pair', 'two-results'))
        ->call(
            'left', 'composition', 'finish',
            inputs: [new Link('address', ['input', 'address'])],
        )
        ->call(
            'right', 'composition', 'finish',
            inputs: [new Link('address', ['input', 'address'])],
        )
        ->export('first', ['output', 'left', 'result'])
        ->export('second', ['output', 'right', 'result']),
];
