# Independent canonical alignment review: First Capability plan

**Reviewed artifact:** [`docs/PLAN-FIRST-CAPABILITY.md` at `83ccd557`](https://github.com/paycrypto-me/paycrypto-me-primitives-php/blob/83ccd5571843eed57726e140e493868f0f152b5f/docs/PLAN-FIRST-CAPABILITY.md), as present on 2026-10-03.
**Authorities at review time:** [Public Architecture v1.1](../canonicals/public-architecture/paycrypto-public-architecture-canonical-v1.1.md) and Primitives v1.6 (historical baseline; see the [current canonical v1.8](../canonicals/primitives/paycrypto-primitives-canonical-architecture-reference-v1.8.md)).
**Supporting research reviewed:** [Current provider research](../research/public-address-provider-research.md), including its stated evidence limits.  
**Review scope:** Shallow inspection of the plan and supporting research against the two canonicals, including whether the plan represents the research accurately. This is not a technical design review, independent validation of package claims or probe outputs, provider qualification, implementation review, or formal Counter-Proof report.

## Result

**Aligned at plan/research level. No canonical conflict or required correction identified.** The plan preserves the accepted boundaries and identifies its extra delivery and verification commitments as plan choices. The research supports the plan's preliminary status and does not claim production selection. Statements about future work are intentions and gates; they do not establish implementation compliance. Full canonical compliance remains contingent on the implementation and independent assessment described by the plan itself.

| Canonical concern | Plan evidence | Assessment |
|---|---|---|
| Concrete need precedes abstractions and dependencies | §§2–4 derive the slice and minimal contracts from public receiving-address derivation; §3 starts provider research from required operations. | Aligned with Primitives §§2, 23, 28 and CP-01. |
| Reuse until actual behavioral divergence | §§2, 4–5 share derivation, key operations and hashing across the three address constructions; the Bitcoin Cash example is explicitly a bounded fitness check. | Aligned with Primitives §§7–8, 18, 28A and CP-02/CP-04. |
| Parameters remain definitions | §§2 and 4 treat network differences as validated data and leave definition format/loading open. | Aligned with Primitives §9 and CP-03. No class hierarchy is prescribed for network identity. |
| Owned semantics and composition, delegated machinery | §§1, 3–5 assign CKDpub and address semantics to Primitives while delegating ECC, hashing and standardized codecs. | Aligned with Primitives §§10–10A, 12–14 and CP-05/CP-10. The plan explicitly discloses older open wording about codec ownership rather than treating it as a new canonical decision. |
| Library-independent contracts and explicit provider replacement | §§4–5 require semantic contracts, adapter containment, deliberate production selection, replacement checks and no silent fallback. | Aligned with Primitives §§24–27 and CP-06/CP-07. The two-library exercise is expressly labeled a **plan-level** gate, not a canonical requirement. |
| Public/read-only scope and domain isolation | §§1–2 and 6–8 keep private material and upper-domain objects out, require local use, and confine delivery to this Primitives library. | Aligned with Public Architecture §§3, 5–6, 10–11 and Primitives §§1, 15, 20 and CP-08/CP-09. Independent use by an External Project does not introduce a PayCrypto.Me Consumer bypass. |
| Evidence, limitations and open decisions | §§3–8 distinguish research from selection, list unresolved decisions, require independent evidence and preserve corrective review history. | Aligned with Public Architecture §§9, 13, 15 and Primitives §§0B, 26, 32–34 and CP-11/CP-12. |

## Research-to-plan consistency

| Research observation | Representation in the plan | Assessment |
|---|---|---|
| Research §§1–2 starts from required D01–D11 operations, but acknowledges that the initial candidate search relied substantially on familiar packages. | Plan §§1 and 3 require operation-first contracts and describe the shortlist as preliminary rather than a broad comparison. | Consistent with Primitives §§23 and 28; no candidate is promoted into architectural authority. |
| Research §§2–4 separates direct candidates, transitive dependencies and reference-only projects. `bitwasp/bitcoin` is excluded from production for this slice; focused `bitwasp/bech32` remains a conditional candidate. | Plan §3 makes the same scoped distinction and calls for footprint and dependency-path evidence. | Consistent with Primitives §§24–25. The exclusion is a slice-level choice, not a new canonical prohibition. |
| Research §§1–2 treats owned CKDpub composition over delegated HMAC/ECC as an initial design option; fixed-width runtime conversions and strict admission still need qualification. | Plan §§1, 3–4 preserves owned protocol semantics, delegated machinery and open contracts. | Consistent with Primitives §§10A, 12–13 and 32. The research does not make a full BIP32 package mandatory. |
| Research §§2, 4–5 reports bounded vendor/API probes, missing complete checked-codec and Bech32 alternatives, no semantic adapter replacement run, no independent ECC oracle and untested PHP 8.1 ECC/GMP execution. | Plan §§3, 5–6 keeps provider qualification, two-library replacement, runtime support and independent evidence open. | Accurate evidence status. Probe success is not treated as CP-07/CP-11 compliance or provider selection. |
| Research §§3–5 presents dated version, requirement and advisory snapshots with express limits and calls for further maintenance, license, dependency and actual integration checks. | Plan §§3–5 requires exact revisions, full dependency/runtime evidence and joint selection before production wiring. | Consistent with Primitives §§23–27 and CP-01/CP-07/CP-11. |

The research's proposed `pack`/`unpack` use for fixed fields is framed as runtime conversion beneath Primitives-owned structure and admission semantics (research §2, D10–D11). That allocation is consistent with Primitives §§10A and 12.1 at this planning level. The eventual implementation must still demonstrate that it has not introduced a locally implemented standardized codec.

## Notes for the executor

1. **No plan correction is required by this review.** Keep the plan's two-library qualification rule identified as an additional delivery commitment. If qualification proves infeasible, revise or explicitly reconsider that commitment with evidence; do not describe it as a CP-07 mandate or claim that an untested alternative passed.
2. **Keep authority boundaries explicit during execution.** The plan's choice to exclude the broad `bitwasp/bitcoin` package from this slice and its choice to ship a standalone library operation are scoped implementation decisions. Neither changes the canonicals' general rules for future capabilities or the PayCrypto.Me functional topology.
3. **Do not promote the research probes or this review to an implementation PASS.** Provider suitability, exact protocol behavior, runtime support, replacement, test independence and actual domain containment are deliberately pending. In particular, the research has not exercised unchanged composition through two semantic adapters, qualified independent ECC evidence, or established PHP 8.1 ECC/GMP support. Assess these against the delivered artifact through the canonical Counter-Proof process.

This review used only the four named repository documents. It treats the provider research's stated observations and limits as documentary evidence, without independently reproducing them or adopting claims from its linked artifacts, PIPs, report protocols, code, or previous conversations.
