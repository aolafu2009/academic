<?php

declare(strict_types=1);

/**
 * Create Omise test token and print bill pay payload.
 *
 * Usage:
 * php omise_token_test.php --bill-no=P20260408123456000001 [--api-base-url=http://127.0.0.1:8000] [--access-token=xxx]
 *
 * Optional card args (defaults are Omise test card):
 * --name="JOHN DOE"
 * --number=4242424242424242
 * --exp-month=12
 * --exp-year=2030
 * --security-code=123
 */

const DEFAULT_CARD = [
    'name' => 'JOHN DOE',
    'number' => '4242424242424242',
    'exp_month' => '12',
    'exp_year' => '2030',
    'security_code' => '123',
];

function loadEnvValue(string $key): ?string
{
    $direct = getenv($key);
    if ($direct !== false && $direct !== '') {
        return $direct;
    }

    $envPath = __DIR__.'/.env';
    if (!is_file($envPath)) {
        return null;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return null;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        $parts = explode('=', $trimmed, 2);
        if (count($parts) !== 2) {
            continue;
        }

        if (trim($parts[0]) !== $key) {
            continue;
        }

        $value = trim($parts[1]);
        if ($value === '') {
            return null;
        }

        return trim($value, " \t\n\r\0\x0B\"'");
    }

    return null;
}

function usageAndExit(string $message = ''): never
{
    if ($message !== '') {
        fwrite(STDERR, "Error: {$message}\n\n");
    }

    $usage = <<<TXT
Usage:
  php omise_token_test.php --bill-no=<BILL_NO> [--api-base-url=<URL>] [--access-token=<TOKEN>]

Card options (optional, default Omise test card):
  --name="JOHN DOE"
  --number=4242424242424242
  --exp-month=12
  --exp-year=2030
  --security-code=123

Example:
  php omise_token_test.php --bill-no=P20260408123456000001 --api-base-url=http://127.0.0.1:8000 --access-token=your_jwt
TXT;

    fwrite(STDERR, $usage."\n");
    exit(1);
}

$options = getopt('', [
    'bill-no:',
    'api-base-url::',
    'access-token::',
    'name::',
    'number::',
    'exp-month::',
    'exp-year::',
    'security-code::',
]);

if ($options === false) {
    usageAndExit('Failed to parse CLI options.');
}

$billNo = $options['bill-no'] ?? '';
if (!is_string($billNo) || $billNo === '') {
    usageAndExit('--bill-no is required.');
}

$publicKey = loadEnvValue('OMISE_PUBLIC_KEY');
if ($publicKey === null || $publicKey === '') {
    usageAndExit('OMISE_PUBLIC_KEY not found in env/.env.');
}

$apiBaseUrl = (string) ($options['api-base-url'] ?? 'http://127.0.0.1:8000');
$apiBaseUrl = rtrim($apiBaseUrl, '/');

$card = [
    'name' => (string) ($options['name'] ?? DEFAULT_CARD['name']),
    'number' => (string) ($options['number'] ?? DEFAULT_CARD['number']),
    'expiration_month' => (string) ($options['exp-month'] ?? DEFAULT_CARD['exp_month']),
    'expiration_year' => (string) ($options['exp-year'] ?? DEFAULT_CARD['exp_year']),
    'security_code' => (string) ($options['security-code'] ?? DEFAULT_CARD['security_code']),
];

$postData = [
    'card[name]' => $card['name'],
    'card[number]' => $card['number'],
    'card[expiration_month]' => $card['expiration_month'],
    'card[expiration_year]' => $card['expiration_year'],
    'card[security_code]' => $card['security_code'],
];

$ch = curl_init('https://vault.omise.co/tokens');
if ($ch === false) {
    fwrite(STDERR, "Error: Failed to initialize curl.\n");
    exit(1);
}

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => $publicKey.':',
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
    ],
]);

$raw = curl_exec($ch);
$curlError = curl_error($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($raw === false) {
    fwrite(STDERR, "Error: Failed to call Omise token API. {$curlError}\n");
    exit(1);
}

$body = json_decode($raw, true);
if (!is_array($body)) {
    fwrite(STDERR, "Error: Omise response is not JSON.\nRaw:\n{$raw}\n");
    exit(1);
}

if ($status >= 400 || !isset($body['id'])) {
    fwrite(STDERR, "Error: Omise token creation failed (HTTP {$status}).\n");
    fwrite(STDERR, "Response:\n".json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
    exit(1);
}

$token = (string) $body['id'];
$payPayload = [
    'payment_provider' => 'omise',
    'omise_token' => $token,
];

$payloadJson = json_encode($payPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($payloadJson === false) {
    fwrite(STDERR, "Error: Failed to encode payload JSON.\n");
    exit(1);
}

$accessToken = (string) ($options['access-token'] ?? 'REPLACE_WITH_STUDENT_BEARER_TOKEN');
$payUrl = "{$apiBaseUrl}/api/bills/{$billNo}/pay";

echo "Omise token created successfully:\n";
echo "  token: {$token}\n\n";

echo "Pay API JSON body:\n";
echo $payloadJson."\n\n";

echo "Pay API curl:\n";
echo "curl -X POST '{$payUrl}' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Authorization: Bearer {$accessToken}' \\\n";
echo "  -d '{$payloadJson}'\n";
