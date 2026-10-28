<?php
declare(strict_types=1);

// Exploratory data-only declaration; not an approved authoring API.
// Fixed fields are maps; occurrences and bindings remain ordered lists.

return [
    [
        'id' => 'hash160',
        'contract' => 'hash160',
        'steps' => [
            [
                'id' => 'sha',
                'kind' => 'capability',
                'target' => 'sha256',
                'inputs' => [['port' => 'bytes', 'source' => ['kind' => 'input', 'name' => 'bytes']]],
            ],
            [
                'id' => 'ripe',
                'kind' => 'capability',
                'target' => 'ripemd160',
                'inputs' => [
                    [
                        'port' => 'bytes',
                        'source' => ['kind' => 'output', 'step' => 'sha', 'port' => 'digest'],
                    ],
                ],
            ],
        ],
        'outputs' => [['port' => 'digest', 'source' => ['kind' => 'output', 'step' => 'ripe', 'port' => 'digest']]],
    ],
    [
        'id' => 'public-address',
        'contract' => 'public-address',
        'steps' => [
            [
                'id' => 'admit',
                'kind' => 'capability',
                'target' => 'extended-public-key-admission',
                'inputs' => [['port' => 'request', 'source' => ['kind' => 'input', 'name' => 'request']]],
            ],
            [
                'id' => 'depth',
                'kind' => 'capability',
                'target' => 'remaining-depth',
                'inputs' => [
                    [
                        'port' => 'node',
                        'source' => ['kind' => 'output', 'step' => 'admit', 'port' => 'node'],
                    ],
                    ['port' => 'request', 'source' => ['kind' => 'input', 'name' => 'request']],
                ],
            ],
            [
                'id' => 'derive',
                'kind' => 'capability',
                'target' => 'ckd-public-path',
                'inputs' => [
                    [
                        'port' => 'parent',
                        'source' => ['kind' => 'output', 'step' => 'admit', 'port' => 'node'],
                    ],
                    [
                        'port' => 'path',
                        'source' => ['kind' => 'output', 'step' => 'depth', 'port' => 'path'],
                    ],
                ],
            ],
            [
                'id' => 'keyhash',
                'kind' => 'composition',
                'target' => 'hash160',
                'inputs' => [
                    [
                        'port' => 'bytes',
                        'source' => [
                            'kind' => 'field',
                            'source' => ['kind' => 'output', 'step' => 'derive', 'port' => 'node'],
                            'name' => 'publicKey',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'encode',
                'kind' => 'intent',
                'target' => 'key-hash-address',
                'inputs' => [
                    [
                        'port' => 'keyHash',
                        'source' => ['kind' => 'output', 'step' => 'keyhash', 'port' => 'digest'],
                    ],
                ],
                'parameters' => [['port' => 'network', 'source' => ['kind' => 'parameter', 'name' => 'network']]],
            ],
            [
                'id' => 'result',
                'kind' => 'capability',
                'target' => 'public-address-result',
                'inputs' => [
                    ['port' => 'request', 'source' => ['kind' => 'input', 'name' => 'request']],
                    [
                        'port' => 'address',
                        'source' => ['kind' => 'output', 'step' => 'encode', 'port' => 'address'],
                    ],
                ],
            ],
        ],
        'outputs' => [['port' => 'result', 'source' => ['kind' => 'output', 'step' => 'result', 'port' => 'result']]],
    ],
    [
        'id' => 'p2pkh-address',
        'contract' => 'key-hash-address',
        'steps' => [
            [
                'id' => 'payload',
                'kind' => 'capability',
                'target' => 'p2pkh-payload',
                'inputs' => [['port' => 'keyHash', 'source' => ['kind' => 'input', 'name' => 'keyHash']]],
                'parameters' => [
                    [
                        'port' => 'version',
                        'source' => [
                            'kind' => 'field',
                            'source' => ['kind' => 'parameter', 'name' => 'network'],
                            'name' => 'p2pkhVersion',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'encode',
                'kind' => 'capability',
                'target' => 'base58check-encode',
                'inputs' => [
                    [
                        'port' => 'payload',
                        'source' => ['kind' => 'output', 'step' => 'payload', 'port' => 'payload'],
                    ],
                ],
            ],
        ],
        'outputs' => [['port' => 'address', 'source' => ['kind' => 'output', 'step' => 'encode', 'port' => 'address']]],
    ],
    [
        'id' => 'nested-witness-address',
        'contract' => 'key-hash-address',
        'steps' => [
            [
                'id' => 'script',
                'kind' => 'capability',
                'target' => 'witness-v0-keyhash-script',
                'inputs' => [['port' => 'keyHash', 'source' => ['kind' => 'input', 'name' => 'keyHash']]],
            ],
            [
                'id' => 'scripthash',
                'kind' => 'composition',
                'target' => 'hash160',
                'inputs' => [
                    [
                        'port' => 'bytes',
                        'source' => ['kind' => 'output', 'step' => 'script', 'port' => 'script'],
                    ],
                ],
            ],
            [
                'id' => 'payload',
                'kind' => 'capability',
                'target' => 'p2sh-payload',
                'inputs' => [
                    [
                        'port' => 'scriptHash',
                        'source' => ['kind' => 'output', 'step' => 'scripthash', 'port' => 'digest'],
                    ],
                ],
                'parameters' => [
                    [
                        'port' => 'version',
                        'source' => [
                            'kind' => 'field',
                            'source' => ['kind' => 'parameter', 'name' => 'network'],
                            'name' => 'p2shVersion',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'encode',
                'kind' => 'capability',
                'target' => 'base58check-encode',
                'inputs' => [
                    [
                        'port' => 'payload',
                        'source' => ['kind' => 'output', 'step' => 'payload', 'port' => 'payload'],
                    ],
                ],
            ],
        ],
        'outputs' => [['port' => 'address', 'source' => ['kind' => 'output', 'step' => 'encode', 'port' => 'address']]],
    ],
    [
        'id' => 'native-witness-address',
        'contract' => 'key-hash-address',
        'steps' => [
            [
                'id' => 'encode',
                'kind' => 'capability',
                'target' => 'segwit-v0-keyhash-encode',
                'inputs' => [['port' => 'program', 'source' => ['kind' => 'input', 'name' => 'keyHash']]],
                'parameters' => [
                    [
                        'port' => 'hrp',
                        'source' => [
                            'kind' => 'field',
                            'source' => ['kind' => 'parameter', 'name' => 'network'],
                            'name' => 'segwitHrp',
                        ],
                    ],
                ],
            ],
        ],
        'outputs' => [['port' => 'address', 'source' => ['kind' => 'output', 'step' => 'encode', 'port' => 'address']]],
    ],
    [
        'id' => 'finish',
        'contract' => 'make-result',
        'steps' => [
            [
                'id' => 'end',
                'kind' => 'intent',
                'target' => 'make-result',
                'inputs' => [['port' => 'address', 'source' => ['kind' => 'input', 'name' => 'address']]],
            ],
        ],
        'outputs' => [['port' => 'result', 'source' => ['kind' => 'output', 'step' => 'end', 'port' => 'result']]],
    ],
    [
        'id' => 'result-a',
        'contract' => 'make-result',
        'steps' => [
            [
                'id' => 'emit',
                'kind' => 'capability',
                'target' => 'make-result',
                'inputs' => [['port' => 'address', 'source' => ['kind' => 'input', 'name' => 'address']]],
            ],
        ],
        'outputs' => [['port' => 'result', 'source' => ['kind' => 'output', 'step' => 'emit', 'port' => 'result']]],
    ],
    [
        'id' => 'result-b',
        'contract' => 'make-result',
        'steps' => [
            [
                'id' => 'emit',
                'kind' => 'capability',
                'target' => 'second-result-fixture',
                'inputs' => [['port' => 'address', 'source' => ['kind' => 'input', 'name' => 'address']]],
            ],
        ],
        'outputs' => [['port' => 'result', 'source' => ['kind' => 'output', 'step' => 'emit', 'port' => 'result']]],
    ],
    [
        'id' => 'pair',
        'contract' => 'two-results',
        'steps' => [
            [
                'id' => 'left',
                'kind' => 'composition',
                'target' => 'finish',
                'inputs' => [['port' => 'address', 'source' => ['kind' => 'input', 'name' => 'address']]],
            ],
            [
                'id' => 'right',
                'kind' => 'composition',
                'target' => 'finish',
                'inputs' => [['port' => 'address', 'source' => ['kind' => 'input', 'name' => 'address']]],
            ],
        ],
        'outputs' => [
            ['port' => 'first', 'source' => ['kind' => 'output', 'step' => 'left', 'port' => 'result']],
            ['port' => 'second', 'source' => ['kind' => 'output', 'step' => 'right', 'port' => 'result']],
        ],
    ],
];
