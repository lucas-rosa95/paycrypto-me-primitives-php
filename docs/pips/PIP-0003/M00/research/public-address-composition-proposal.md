# Public address composition and invocation proposal

**Status: proposal — not implemented, not a frozen public API.**

This note supports the [consolidated first-capability record](../../PIP-0003.md).
The [current canonical](../../../../canonicals/primitives/paycrypto-primitives-canonical-architecture-reference-v1.8.md)
owns the invariants; this note illustrates possible PHP usage. Bitcoin Cash is
an architectural fitness example, not part of the initial delivery.

## Illustrative composition declarations: Bitcoin and Bitcoin Cash

Canonical section 28A requires checking existing capabilities, definitions and
recomposition before proposing new behavior. A new protocol identity alone does
not justify a new derivation implementation. The following PHP is a prospective
usage sketch, not an executable example or a frozen API. It assumes that a future
admitted requirement has already qualified CashAddr support; this plan does not
deliver that support.

Compare Bitcoin P2PKH with Bitcoin Cash CashAddr P2PKH, with compatible public
key and relative-path semantics. All variables below are explicitly wired
semantic capabilities or validated definitions, never vendor objects.

```php
// Shared capabilities: public derivation and compressed-public-key HASH160.
// Their providers are wired outside these declarations.
$deriveKeyHash = static function (
    ExtendedPublicKey $key,
    RelativePublicPath $path,
) use ($publicDerivation, $hash160): PublicKeyHash {
    $child = $publicDerivation->derive($key, $path);

    return $hash160->fromCompressedPublicKey($child->publicKey());
};

// Two compositions of existing capabilities; no per-coin derivation classes.
$bitcoin = static fn (ExtendedPublicKey $key, RelativePublicPath $path) =>
    $base58CheckP2pkhAddress->fromPublicKeyHash(
        hash: $deriveKeyHash($key, $path),
        definition: $bitcoinMainnetP2pkh,
    );

$bitcoinCash = static fn (ExtendedPublicKey $key, RelativePublicPath $path) =>
    $cashAddrP2pkhAddress->fromPublicKeyHash(
        hash: $deriveKeyHash($key, $path),
        definition: $bitcoinCashMainnetP2pkh,
    );

$btcAddress = $bitcoin($validatedPublicKey, $explicitRelativePath);
$bchAddress = $bitcoinCash($validatedPublicKey, $explicitRelativePath);
```

The common behavior ends at the public-key hash. The terminal capabilities
apply their respective address semantics and delegate standardized encoding to
qualified providers. CashAddr is not a prefix option for Base58Check or Bech32.
If both terminal capabilities already exist, these declarations require no new
algorithm, codec, adapter or derivation capability. If CashAddr is absent, only
that demonstrated divergence requires new support and provider qualification.

The shared derivation does not receive a coin identifier, inspect the terminal
capability or know which composition calls it. The closures illustrate ordinary
composition, not a proposed pipeline framework. Input parsing and validation
precede this sketch; compatibility must be established explicitly and does not
imply interchangeable wallet account policies or automatic network conversion.
For the same admitted key and path, verification must show identical child
public keys and public-key hashes in both compositions, followed by each format's
own address conformance checks. Provider replacement must preserve these results.

## Proposed developer-facing API: declarative definitions and one invocable

Canonical sections 9 and 9.2 already establish declarative definitions, validated
loading and separation of data from behavior. The recommendation here is one
invocable for this admitted public-address derivation operation, backed by the
small compositions illustrated above. This is a proposal for qualification,
not a frozen PHP API or a universal protocol engine.

Keep three responsibilities explicit:

| Responsibility | Contents | Owner of behavior |
|---|---|---|
| Definition source | Network parameters, accepted public-version metadata and supported address profiles. PHP arrays are a suitable initial source format. | No executable behavior; a schema validates supported semantics. |
| Derivation request | Public extended key, explicit relative path, selected validated definition and explicit address policy. | The public operation validates their compatibility. |
| Construction/wiring | Explicitly selected adapters and compositions implementing supported semantic profiles. | Primitives capabilities; no vendor selection in definitions or requests. |

For example, a definition source could contain these address-profile fragments.
They are not complete network definitions: extended-key metadata and all other
required fields must be provided and validated before a definition is usable.
Bitcoin Cash remains a hypothetical future extension in this example.

```php
$addressProfiles = [
    'bitcoin.mainnet' => [
        'p2pkh-base58check' => ['version_hex' => '00'],
    ],
    'bitcoin-cash.mainnet' => [
        'p2pkh-cashaddr' => ['prefix' => 'bitcoincash'],
    ],
];
```

Profile identifiers name supported protocol semantics, not class names, callable
names or vendor methods. The loader validates the relevant profile's schema and
produces an immutable semantic definition. Protocol code consumes that validated
view, not scattered raw array keys. Loading parameters does not implement a
missing profile: CashAddr still needs its own qualified capability and codec.

The corresponding public usage could be:

```php
// $deriveAddress is the same explicitly wired invocable in both calls.
// $definitions contains fully validated definitions, not the fragments above.
$btcAddress = $deriveAddress(new DeriveAddressRequest(
    extendedPublicKey: $xpub,
    path: $relativePath,
    definition: $definitions->get('bitcoin.mainnet'),
    policy: AddressPolicy::P2pkhBase58Check,
));

$bchAddress = $deriveAddress(new DeriveAddressRequest(
    extendedPublicKey: $xpub,
    path: $relativePath,
    definition: $definitions->get('bitcoin-cash.mainnet'),
    policy: AddressPolicy::P2pkhCashAddr,
));
```

The definition lookup above can be a simple validated collection; it does not
require a service locator or plugin registry. Names and signatures remain open.
The invocable validates the request, selects an explicitly wired composition for
the supported semantic policy, derives the public key and constructs the address.
Lower capabilities receive only the semantic inputs they need; the full request,
network catalog and composition selector do not propagate down the stack.

Prefer a small explicit policy-to-composition mapping over coin-name switches,
reflection, configurable method sequences or an arbitrary pipeline interpreter.
The common operation owns orchestration, not algorithms. Share lower capabilities
and behavior even if two terminal compositions have different internal shapes;
do not force all address formats through a public-key-hash-only interface.
The initial three Bitcoin policies must fit this design before acceptance.

Parameter provenance and failures must be unambiguous:

- Key and path are explicit request inputs. Network parameters come from the
  selected validated definition. The explicit policy must be supported by that
  definition and by the wired capabilities.
- Do not merge arbitrary request fields into definitions or accept contradictory
  network, version, prefix and policy overrides. Do not infer a missing policy
  from a key prefix. Check key-version compatibility before derivation.
- Definition loading rejects malformed, missing and unknown fields and invalid
  parameter combinations. Operation admission rejects unsupported policies or
  unavailable capabilities before cryptographic work, with defined failures.
- No definition contains code, adapter classes, dependency versions, fallback
  rules or instructions to allocate indices. Provider wiring is explicit and
  independent of the definition's source representation.
- Adding a compatible network requires definition data and verification fixtures,
  not new behavioral code. Adding an already supported composition requires
  only explicit wiring where necessary. New semantics still require a capability
  and its qualification evidence.

Qualify this proposal against direct composition (the preceding closures).
Prefer the invocable if it keeps public usage simpler while preserving explicit
policy, type clarity and localized errors. Keep direct capability composition
available internally; do not publish a framework of abstractions merely to obtain
one calling syntax. Freeze the exact request, definition representation, policy
mapping and result/error types only at the contract decision gate.

## Pattern vocabulary and selection criterion

Adapter describes provider integration; Strategy can describe interchangeable
address constructions; explicit construction uses dependency injection. A typed
request is a parameter object. The public operation can serve as a facade, but
`__invoke()` itself is only a calling convention. None requires a framework,
class hierarchy, factory layer or separately published interface by default.

Select the smallest design that makes valid inputs, composition, failures and
provider replacement clear. Preserve explicit supported semantics rather than
building a configurable graph interpreter. Evaluate the public operation across
P2PKH, P2SH-P2WPKH and P2WPKH before freezing signatures. The preceding closures
explain reuse; they are not the production implementation or its complete contract.
