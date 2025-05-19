<?php

$data = array_map(function($line) {
            return str_getcsv($line, ",", '"', "\\");
        }, file('transcriptions.csv'));
unset($data[0]); // Remove the header row

$en = [];
$cy = [];

foreach ($data as $key => $val) {
    $nid = str_replace(['https://404mike.github.io/whisper-cyw/dist/','.html'], '', $val[0]);
    $actualLanguage = $val[2];
    if(!in_array($actualLanguage, ['en', 'cy'])) {
        continue;
    }
    if ($actualLanguage === 'en') {
        $en[$nid] = $val[1];
    } else {
        $cy[$nid] = $val[1];
    }
}

$randomEn = array_rand($en, 40);
$randomCy = array_rand($cy, 40);

$arr = [
    'en' => $randomEn,
    'cy' => $randomCy,
];

file_put_contents('randomSelection.json', json_encode($arr, JSON_PRETTY_PRINT));