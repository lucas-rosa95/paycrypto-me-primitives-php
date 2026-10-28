<?php
declare(strict_types=1);

namespace M05C2Lab;

use M05C2Lab\NamedReferences\{Input, Parameter, StepOutput, Field, Link};

// Exploratory named-reference records; no approved API or executed capability.
return [
    new Flow(
        id: 'hash160', contract: 'hash160',
        steps: [
            new Call(
                id: 'sha', kind: 'capability', target: 'sha256',
                inputs: [
                    new Link(port: 'bytes', source: new Input(name: 'bytes')),
                ],
            ),
            new Call(
                id: 'ripe', kind: 'capability', target: 'ripemd160',
                inputs: [
                    new Link(port: 'bytes', source: new StepOutput(step: 'sha', port: 'digest')),
                ],
            ),
        ],
        outputs: [
            new Link(port: 'digest', source: new StepOutput(step: 'ripe', port: 'digest')),
        ],
    ),
    new Flow(
        id: 'public-address', contract: 'public-address',
        steps: [
            new Call(
                id: 'admit', kind: 'capability', target: 'extended-public-key-admission',
                inputs: [
                    new Link(port: 'request', source: new Input(name: 'request')),
                ],
            ),
            new Call(
                id: 'depth', kind: 'capability', target: 'remaining-depth',
                inputs: [
                    new Link(port: 'node', source: new StepOutput(step: 'admit', port: 'node')),
                    new Link(port: 'request', source: new Input(name: 'request')),
                ],
            ),
            new Call(
                id: 'derive', kind: 'capability', target: 'ckd-public-path',
                inputs: [
                    new Link(port: 'parent', source: new StepOutput(step: 'admit', port: 'node')),
                    new Link(port: 'path', source: new StepOutput(step: 'depth', port: 'path')),
                ],
            ),
            new Call(
                id: 'keyhash', kind: 'composition', target: 'hash160',
                inputs: [
                    new Link(port: 'bytes', source: new Field(source: new StepOutput(step: 'derive', port: 'node'), name: 'publicKey')),
                ],
            ),
            new Call(
                id: 'encode', kind: 'intent', target: 'key-hash-address',
                inputs: [
                    new Link(port: 'keyHash', source: new StepOutput(step: 'keyhash', port: 'digest')),
                ],
                parameters: [
                    new Link(port: 'network', source: new Parameter(name: 'network')),
                ],
            ),
            new Call(
                id: 'result', kind: 'capability', target: 'public-address-result',
                inputs: [
                    new Link(port: 'request', source: new Input(name: 'request')),
                    new Link(port: 'address', source: new StepOutput(step: 'encode', port: 'address')),
                ],
            ),
        ],
        outputs: [
            new Link(port: 'result', source: new StepOutput(step: 'result', port: 'result')),
        ],
    ),
    new Flow(
        id: 'p2pkh-address', contract: 'key-hash-address',
        steps: [
            new Call(
                id: 'payload', kind: 'capability', target: 'p2pkh-payload',
                inputs: [
                    new Link(port: 'keyHash', source: new Input(name: 'keyHash')),
                ],
                parameters: [
                    new Link(port: 'version', source: new Field(source: new Parameter(name: 'network'), name: 'p2pkhVersion')),
                ],
            ),
            new Call(
                id: 'encode', kind: 'capability', target: 'base58check-encode',
                inputs: [
                    new Link(port: 'payload', source: new StepOutput(step: 'payload', port: 'payload')),
                ],
            ),
        ],
        outputs: [
            new Link(port: 'address', source: new StepOutput(step: 'encode', port: 'address')),
        ],
    ),
    new Flow(
        id: 'nested-witness-address', contract: 'key-hash-address',
        steps: [
            new Call(
                id: 'script', kind: 'capability', target: 'witness-v0-keyhash-script',
                inputs: [
                    new Link(port: 'keyHash', source: new Input(name: 'keyHash')),
                ],
            ),
            new Call(
                id: 'scripthash', kind: 'composition', target: 'hash160',
                inputs: [
                    new Link(port: 'bytes', source: new StepOutput(step: 'script', port: 'script')),
                ],
            ),
            new Call(
                id: 'payload', kind: 'capability', target: 'p2sh-payload',
                inputs: [
                    new Link(port: 'scriptHash', source: new StepOutput(step: 'scripthash', port: 'digest')),
                ],
                parameters: [
                    new Link(port: 'version', source: new Field(source: new Parameter(name: 'network'), name: 'p2shVersion')),
                ],
            ),
            new Call(
                id: 'encode', kind: 'capability', target: 'base58check-encode',
                inputs: [
                    new Link(port: 'payload', source: new StepOutput(step: 'payload', port: 'payload')),
                ],
            ),
        ],
        outputs: [
            new Link(port: 'address', source: new StepOutput(step: 'encode', port: 'address')),
        ],
    ),
    new Flow(
        id: 'native-witness-address', contract: 'key-hash-address',
        steps: [
            new Call(
                id: 'encode', kind: 'capability', target: 'segwit-v0-keyhash-encode',
                inputs: [
                    new Link(port: 'program', source: new Input(name: 'keyHash')),
                ],
                parameters: [
                    new Link(port: 'hrp', source: new Field(source: new Parameter(name: 'network'), name: 'segwitHrp')),
                ],
            ),
        ],
        outputs: [
            new Link(port: 'address', source: new StepOutput(step: 'encode', port: 'address')),
        ],
    ),
    new Flow(
        id: 'finish', contract: 'make-result',
        steps: [
            new Call(
                id: 'end', kind: 'intent', target: 'make-result',
                inputs: [
                    new Link(port: 'address', source: new Input(name: 'address')),
                ],
            ),
        ],
        outputs: [
            new Link(port: 'result', source: new StepOutput(step: 'end', port: 'result')),
        ],
    ),
    new Flow(
        id: 'result-a', contract: 'make-result',
        steps: [
            new Call(
                id: 'emit', kind: 'capability', target: 'make-result',
                inputs: [
                    new Link(port: 'address', source: new Input(name: 'address')),
                ],
            ),
        ],
        outputs: [
            new Link(port: 'result', source: new StepOutput(step: 'emit', port: 'result')),
        ],
    ),
    new Flow(
        id: 'result-b', contract: 'make-result',
        steps: [
            new Call(
                id: 'emit', kind: 'capability', target: 'second-result-fixture',
                inputs: [
                    new Link(port: 'address', source: new Input(name: 'address')),
                ],
            ),
        ],
        outputs: [
            new Link(port: 'result', source: new StepOutput(step: 'emit', port: 'result')),
        ],
    ),
    new Flow(
        id: 'pair', contract: 'two-results',
        steps: [
            new Call(
                id: 'left', kind: 'composition', target: 'finish',
                inputs: [
                    new Link(port: 'address', source: new Input(name: 'address')),
                ],
            ),
            new Call(
                id: 'right', kind: 'composition', target: 'finish',
                inputs: [
                    new Link(port: 'address', source: new Input(name: 'address')),
                ],
            ),
        ],
        outputs: [
            new Link(port: 'first', source: new StepOutput(step: 'left', port: 'result')),
            new Link(port: 'second', source: new StepOutput(step: 'right', port: 'result')),
        ],
    ),
];
