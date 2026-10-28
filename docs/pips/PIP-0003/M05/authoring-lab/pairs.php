<?php
declare(strict_types=1);

namespace M05C2Lab;

// Disposable authoring fixture; never a production declaration or API.
// The same nine declarations are written in each of the three source forms.

return [
    [
        ['id', 'hash160'],
        ['contract', 'hash160'],
        ['inputs', [[['name', 'bytes'], ['type', 'bytes']]]],
        ['parameters', []],
        ['steps', [
            [
                ['id', 'sha'],
                ['target', ['capability', 'sha256']],
                ['inputs', [[['port', 'bytes'], ['source', ['input', 'bytes']]]]],
                ['parameters', []],
            ],
            [
                ['id', 'ripe'],
                ['target', ['capability', 'ripemd160']],
                ['inputs', [[['port', 'bytes'], ['source', ['output', 'sha', 'digest']]]]],
                ['parameters', []],
            ],
        ]],
        ['outputs', [[['port', 'digest'], ['source', ['output', 'ripe', 'digest']]]]],
    ],
    [
        ['id', 'public-address'],
        ['contract', 'public-address'],
        ['inputs', [[['name', 'request'], ['type', 'request']]]],
        ['parameters', [[['name', 'network'], ['type', 'network']]]],
        ['steps', [
            [
                ['id', 'admit'],
                ['target', ['capability', 'extended-public-key-admission']],
                ['inputs', [[['port', 'request'], ['source', ['input', 'request']]]]],
                ['parameters', []],
            ],
            [
                ['id', 'depth'],
                ['target', ['capability', 'remaining-depth']],
                ['inputs', [[['port', 'node'], ['source', ['output', 'admit', 'node']]], [['port', 'request'], ['source', ['input', 'request']]]]],
                ['parameters', []],
            ],
            [
                ['id', 'derive'],
                ['target', ['capability', 'ckd-public-path']],
                ['inputs', [[['port', 'parent'], ['source', ['output', 'admit', 'node']]], [['port', 'path'], ['source', ['output', 'depth', 'path']]]]],
                ['parameters', []],
            ],
            [
                ['id', 'keyhash'],
                ['target', ['composition', 'hash160']],
                ['inputs', [[['port', 'bytes'], ['source', ['field', ['output', 'derive', 'node'], 'publicKey']]]]],
                ['parameters', []],
            ],
            [
                ['id', 'encode'],
                ['target', ['intent', 'key-hash-address']],
                ['inputs', [[['port', 'keyHash'], ['source', ['output', 'keyhash', 'digest']]]]],
                ['parameters', [[['port', 'network'], ['source', ['parameter', 'network']]]]],
            ],
            [
                ['id', 'result'],
                ['target', ['capability', 'public-address-result']],
                ['inputs', [[['port', 'request'], ['source', ['input', 'request']]], [['port', 'address'], ['source', ['output', 'encode', 'address']]]]],
                ['parameters', []],
            ],
        ]],
        ['outputs', [[['port', 'result'], ['source', ['output', 'result', 'result']]]]],
    ],
    [
        ['id', 'p2pkh-address'],
        ['contract', 'key-hash-address'],
        ['inputs', [[['name', 'keyHash'], ['type', 'digest20']]]],
        ['parameters', [[['name', 'network'], ['type', 'network']]]],
        ['steps', [
            [
                ['id', 'payload'],
                ['target', ['capability', 'p2pkh-payload']],
                ['inputs', [[['port', 'keyHash'], ['source', ['input', 'keyHash']]]]],
                ['parameters', [[['port', 'version'], ['source', ['field', ['parameter', 'network'], 'p2pkhVersion']]]]],
            ],
            [
                ['id', 'encode'],
                ['target', ['capability', 'base58check-encode']],
                ['inputs', [[['port', 'payload'], ['source', ['output', 'payload', 'payload']]]]],
                ['parameters', []],
            ],
        ]],
        ['outputs', [[['port', 'address'], ['source', ['output', 'encode', 'address']]]]],
    ],
    [
        ['id', 'nested-witness-address'],
        ['contract', 'key-hash-address'],
        ['inputs', [[['name', 'keyHash'], ['type', 'digest20']]]],
        ['parameters', [[['name', 'network'], ['type', 'network']]]],
        ['steps', [
            [
                ['id', 'script'],
                ['target', ['capability', 'witness-v0-keyhash-script']],
                ['inputs', [[['port', 'keyHash'], ['source', ['input', 'keyHash']]]]],
                ['parameters', []],
            ],
            [
                ['id', 'scripthash'],
                ['target', ['composition', 'hash160']],
                ['inputs', [[['port', 'bytes'], ['source', ['output', 'script', 'script']]]]],
                ['parameters', []],
            ],
            [
                ['id', 'payload'],
                ['target', ['capability', 'p2sh-payload']],
                ['inputs', [[['port', 'scriptHash'], ['source', ['output', 'scripthash', 'digest']]]]],
                ['parameters', [[['port', 'version'], ['source', ['field', ['parameter', 'network'], 'p2shVersion']]]]],
            ],
            [
                ['id', 'encode'],
                ['target', ['capability', 'base58check-encode']],
                ['inputs', [[['port', 'payload'], ['source', ['output', 'payload', 'payload']]]]],
                ['parameters', []],
            ],
        ]],
        ['outputs', [[['port', 'address'], ['source', ['output', 'encode', 'address']]]]],
    ],
    [
        ['id', 'native-witness-address'],
        ['contract', 'key-hash-address'],
        ['inputs', [[['name', 'keyHash'], ['type', 'digest20']]]],
        ['parameters', [[['name', 'network'], ['type', 'network']]]],
        ['steps', [
            [
                ['id', 'encode'],
                ['target', ['capability', 'segwit-v0-keyhash-encode']],
                ['inputs', [[['port', 'program'], ['source', ['input', 'keyHash']]]]],
                ['parameters', [[['port', 'hrp'], ['source', ['field', ['parameter', 'network'], 'segwitHrp']]]]],
            ],
        ]],
        ['outputs', [[['port', 'address'], ['source', ['output', 'encode', 'address']]]]],
    ],
    [
        ['id', 'finish'],
        ['contract', 'make-result'],
        ['inputs', [[['name', 'address'], ['type', 'address']]]],
        ['parameters', []],
        ['steps', [
            [
                ['id', 'end'],
                ['target', ['intent', 'make-result']],
                ['inputs', [[['port', 'address'], ['source', ['input', 'address']]]]],
                ['parameters', []],
            ],
        ]],
        ['outputs', [[['port', 'result'], ['source', ['output', 'end', 'result']]]]],
    ],
    [
        ['id', 'result-a'],
        ['contract', 'make-result'],
        ['inputs', [[['name', 'address'], ['type', 'address']]]],
        ['parameters', []],
        ['steps', [
            [
                ['id', 'emit'],
                ['target', ['capability', 'make-result']],
                ['inputs', [[['port', 'address'], ['source', ['input', 'address']]]]],
                ['parameters', []],
            ],
        ]],
        ['outputs', [[['port', 'result'], ['source', ['output', 'emit', 'result']]]]],
    ],
    [
        ['id', 'result-b'],
        ['contract', 'make-result'],
        ['inputs', [[['name', 'address'], ['type', 'address']]]],
        ['parameters', []],
        ['steps', [
            [
                ['id', 'emit'],
                ['target', ['capability', 'second-result-fixture']],
                ['inputs', [[['port', 'address'], ['source', ['input', 'address']]]]],
                ['parameters', []],
            ],
        ]],
        ['outputs', [[['port', 'result'], ['source', ['output', 'emit', 'result']]]]],
    ],
    [
        ['id', 'pair'],
        ['contract', 'two-results'],
        ['inputs', [[['name', 'address'], ['type', 'address']]]],
        ['parameters', []],
        ['steps', [
            [
                ['id', 'left'],
                ['target', ['composition', 'finish']],
                ['inputs', [[['port', 'address'], ['source', ['input', 'address']]]]],
                ['parameters', []],
            ],
            [
                ['id', 'right'],
                ['target', ['composition', 'finish']],
                ['inputs', [[['port', 'address'], ['source', ['input', 'address']]]]],
                ['parameters', []],
            ],
        ]],
        ['outputs', [[['port', 'first'], ['source', ['output', 'left', 'result']]], [['port', 'second'], ['source', ['output', 'right', 'result']]]]],
    ],
];
