<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

/**
 * Params validation
 */
$start = isset($_GET['start']) ? (string) $_GET['start'] : null;
$end = isset($_GET['end']) ? (string) $_GET['end'] : null;

$start = preg_replace('/\D/', '', $start);
$end = preg_replace('/\D/', '', $end);

$errors = [];
$validRange = ['000001', '999999'];

foreach (['start' => $start, 'end' => $end] as $paramName => $param) {
    if (strlen($param) < strlen($validRange[0]) || $param < $validRange[0] || $param > $validRange[1]) {
        $errors[] = "'{$paramName}' parameter should be in range 000001-999999";
    }
}

if ($start >= $end) {
    $errors[] = "'start' parameter should be higher than 'end' parameter";
}

if (!empty($errors)) {
    echo '<pre>';
    echo implode(PHP_EOL, $errors);
    echo '</pre>';
    exit;
}

/**
 * Helpers
 */
function dump($var)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

/**
 * Convert number to digit
 *
 * 101 => 1 + 0 + 1 => 2
 *
 * @param string|int $n
 * @param bool $nonZero
 * @return int
 */
function simplifyNumber($n, bool $nonZero = false):int
{
    $n = (int) $n;

    while ($n > 9) {
        $nParts = str_split($n);
        $n = array_sum($nParts);
    }

    if ($n == 0 && $nonZero) {
        $n = 1;
    }

    return $n;
}

/**
 * Determines how many times every digit from range 1-9 could fit into the passed digit range.
 *
 * Imagine the passed range 100-120. How many times sums from 1 to 9 will occur in that range?
 * 1 - 3 times (100, 109, 118)
 * 2 - 3 times (101, 110, 119)
 * 3 - 3 times (102, 111, 120)
 * 4 - 2 times (103, 112)
 * ...
 *
 * @param string|int $dFrom
 * @param string|int $dTo
 *
 * @return array
 */
function calcSumFrequency($dFrom, $dTo): array
{
    $data = [];
    $slotsCount = (int) $dTo - (int) $dFrom + 1;
    $fillGap = 9;
    $firstSum = simplifyNumber($dFrom);

    for ($i = $firstSum; $i <= $firstSum + 8; $i++) {
        $d = simplifyNumber($i);
        $frequency = ceil($slotsCount / $fillGap);
        $slotsCount -= $frequency;
        $fillGap--;

        $data[$d] = $frequency;
    }

    return $data;
}

/**
 * Main logic
 */
$startLeftPart = substr($start, 0, strlen($start) / 2);
$startRightPart = substr($start, - strlen($start) / 2);
$endLeftPart = substr($end, 0, strlen($end) / 2);
$endRightPart = substr($end, - strlen($end) / 2);
$leftPartsGap = $endLeftPart - $startLeftPart;

/**
 * Lets imagine number range: 555789 to 558111
 *
 * Total lucky tickets count consists of 3 components:
 * 1. Left edge, left part compared to the right "partial thousand" (555 vs (1000 - 789) numbers)
 * 2. Right edge, left part compared to the right "partial thousand" (558 vs 111 numbers)
 * 3. Middle bunch, left parts compared to the "full thousand" (556-557 vs 999 numbers)
 *
 * And the final merge of this counts would be the total lucky tickets count
 */
$luckyTicketsCount = [
    'leftEdge' => 0,
    'rightEdge' => 0,
    'middleEdge' => 0,
];

$leftEdgeNumber = null;
$rightEdgeNumber = null;
$middleEdgeNumbersRange = [];

switch ($leftPartsGap) {
    case 0;
        $leftEdgeNumber = $startLeftPart;
        break;
    case 1;
        $leftEdgeNumber = $startLeftPart;
        $rightEdgeNumber = $endLeftPart;
        break;
    default:
        $leftEdgeNumber = $startLeftPart;
        $rightEdgeNumber = $endLeftPart;

        if ($leftPartsGap == 2) {
            $middleEdgeNumbersRange = [$startLeftPart + 1];
        } else {
            $middleEdgeNumbersRange = [$startLeftPart + 1, $rightEdgeNumber - 1];
        }
}

/**
 * 1. Left edge lucky tickets count calc
 */
$leftEdgeLeftSum = simplifyNumber($leftEdgeNumber);
$leftEdgeRightSumFrequency = calcSumFrequency($startRightPart, 999);

if (!empty($leftEdgeLuckyTicketsCount = $leftEdgeRightSumFrequency[$leftEdgeLeftSum])) {
    $luckyTicketsCount['leftEdge'] = $leftEdgeLuckyTicketsCount;
}

/**
 * 2. Right edge lucky tickets count calc
 */
if ($rightEdgeNumber !== null) {
    $rightEdgeLeftSum = simplifyNumber($rightEdgeNumber);
    $rightEdgeRightSumFrequency = calcSumFrequency(1, $endRightPart);

    if (!empty($rightEdgeLuckyTicketsCount = $rightEdgeRightSumFrequency[$rightEdgeLeftSum])) {
        $luckyTicketsCount['rightEdge'] = $rightEdgeLuckyTicketsCount;
    }
}

/**
 * 3. Middle edge lucky tickets count calc
 */
if (!empty($middleEdgeNumbersRange)) {
    $thousandSumFrequency = calcSumFrequency(1, 999); // base frequency in a digit range 1-999
    $middleEdgeLeftSumFrequency = calcSumFrequency($middleEdgeNumbersRange[0], $middleEdgeNumbersRange[(count($middleEdgeNumbersRange) == 1 ? 0 : 1)]);
    $middleEdgeLeftSumFrequency = array_filter($middleEdgeLeftSumFrequency);

    foreach ($middleEdgeLeftSumFrequency as $sum => $frequency) {
        $luckyTicketsCount['middleEdge'] += $thousandSumFrequency[$sum] * $frequency;
    }
}

/**
 * 4. Final calc of the lucky tickets count
 */
$luckyTicketCountFinal = array_sum($luckyTicketsCount);

echo "Lucky tickets count: {$luckyTicketCountFinal}";
