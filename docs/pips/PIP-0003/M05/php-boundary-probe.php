<?php
// Deliberately no strict_types: the caller controls PHP scalar coercion.
// This probes PHP semantics, not a production API or domain implementation.
require __DIR__ . '/php-boundary-callee.php';

function m05LanguageCheck(bool $condition, string $label): void
{
    if (!$condition) {
        throw new RuntimeException('Language control failed: ' . $label);
    }
}

$coerced = [];
$rawAdmission = [];
foreach (['numeric_string' => '7', 'boolean' => true, 'integral_float' => 7.0] as $name => $value) {
    $coerced[$name] = m05TypedIndex($value);
    $rawAdmission[$name] = m05OriginalIndex($value);
}
m05LanguageCheck($coerced === ['numeric_string' => 7, 'boolean' => 1, 'integral_float' => 7], 'strict callee coercion');
m05LanguageCheck($rawAdmission === ['numeric_string' => false, 'boolean' => false, 'integral_float' => false], 'original types');
m05LanguageCheck(m05OriginalIndex(0) && m05OriginalIndex(2147483647), 'integer edges');
m05LanguageCheck(!m05OriginalIndex(-1) && !m05OriginalIndex(2147483648), 'integer bounds');

$external = 'original';
$callerArray = ['value' => &$external];
$aliased = new M05ArrayHolder($callerArray);
$rebuilt = [];
foreach ($callerArray as $key => $value) {
    // This control contains scalar leaves only. A nested source needs
    // reconstruction at every validated scalar leaf, not a shallow copy.
    $rebuilt[$key] = $value;
}
$detached = new M05ArrayHolder($rebuilt);
$external = 'changed';
m05LanguageCheck($aliased->values['value'] === 'changed', 'readonly retains caller reference');
m05LanguageCheck($detached->values['value'] === 'original', 'scalar reconstruction detaches reference');

m05LanguageCheck(ini_get('zend.exception_ignore_args') === '0', 'trace arguments explicitly enabled');
$diagnosticMarker = 'M05_PUBLIC_KEY_DIAGNOSTIC_MARKER';
try {
    m05SensitiveThrow($diagnosticMarker);
} catch (RuntimeException $error) {
    $traceArgument = $error->getTrace()[0]['args'][0] ?? null;
    m05LanguageCheck($traceArgument instanceof SensitiveParameterValue, 'engine redaction wrapper');
    m05LanguageCheck(!str_contains((string) $error, $diagnosticMarker), 'standard trace redaction');
    // Redaction is not erasure: introspection can recover the original value.
    m05LanguageCheck($traceArgument->getValue() === $diagnosticMarker, 'redaction trust limit');
    m05LanguageCheck($error->getPrevious() === null, 'no previous chain');
}

echo json_encode([
    'php' => PHP_VERSION,
    'integer_bytes' => PHP_INT_SIZE,
    'strict_callee_from_weak_caller' => $coerced,
    'mixed_ingress_accepts_wrong_original_type' => $rawAdmission,
    'integer_edge_controls_pass' => true,
    'readonly_array_retains_caller_reference' => true,
    'rebuilt_scalar_snapshot_detaches_reference' => true,
    'standard_sensitive_trace_is_redacted' => true,
    'sensitive_trace_value_remains_introspectable' => true,
    'previous_chain_is_null' => true,
    'source_sha256' => [
        'php-boundary-callee.php' => hash_file('sha256', __DIR__ . '/php-boundary-callee.php'),
        'php-boundary-probe.php' => hash_file('sha256', __FILE__),
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
