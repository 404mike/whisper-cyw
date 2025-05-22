<?php

$data = json_decode(file_get_contents('./data.json'), true);
$en_transcriptions = $data['en'];
$cy_transcriptions = $data['cy'];


echo "English Transcriptions:\n";
foreach($en_transcriptions as $nid) {
    echo "https://404mike.github.io/whisper-cyw/qualitative/dist/$nid.html\n";
}

echo "\nWelsh Transcriptions:\n";
foreach($cy_transcriptions as $nid) {
    echo "https://404mike.github.io/whisper-cyw/qualitative/dist/$nid.html\n";
}
echo "\n\n";