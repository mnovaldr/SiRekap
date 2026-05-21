<?php
function testCleanSdgs(mixed $raw): ?string
{
    if ($raw === null || trim((string) $raw) === '') return null;

    $s = trim((string) $raw);

    // Pecah nilai multi (separator: semicolon, koma, &, kata "dan"/"and", atau lookahead untuk "sdgs")
    $parts = preg_split('/[;,&]|\bdan\b|\band\b|(?=\bsdgs?\s*\d)/i', $s);
    $parts = array_filter(array_map('trim', $parts), fn($p) => $p !== '');

    if (empty($parts)) return null;

    $normalized = [];
    foreach ($parts as $part) {
        $part = trim($part);

        // Format "SDGS X" atau "SDGs X" atau "sdgsx"
        if (preg_match('/^sdgs?\s*(\d{1,2})$/i', $part, $m)) {
            $num = (int) $m[1];
            if ($num >= 1 && $num <= 17) {
                $normalized[] = 'SDGS ' . $num;
            } else {
                $normalized[] = 'SDGS ' . $num;
            }
            continue;
        }

        // Hanya angka: "4" → "SDGS 4"
        if (preg_match('/^\d{1,2}$/', $part)) {
            $normalized[] = 'SDGS ' . (int) $part;
            continue;
        }

        // Format tidak dikenali — simpan apa adanya agar data tidak hilang
        if (!empty($part)) {
            $normalized[] = $part;
        }
    }

    if (empty($normalized)) return null;

    $result = implode('; ', array_unique($normalized));

    return mb_substr($result, 0, 255);
}

$testCases = [
    "SDGS 4",
    "SDGs 8",
    "4",
    "sdgs4",
    "SDGS 4; SDGS 5; SDGS 18",
    "4, 5, 10",
    "SDGS 1, 2, 3",
    "SDGS 4 dan SDGS 5",
    "SDGS 1, SDGS 2, SDGS 3",
    "SDGS 1;2;3",
    "SDGS 4, 8,12",
    "",
    null
];

foreach ($testCases as $case) {
    $result = testCleanSdgs($case);
    echo "Input: " . var_export($case, true) . " => Output: " . var_export($result, true) . "\n";
}
