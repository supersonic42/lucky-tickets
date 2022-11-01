<?php

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

if (!empty($errors)) {
    echo '<pre>';
    echo implode(PHP_EOL, $errors);
    echo '</pre>';
    exit;
}
