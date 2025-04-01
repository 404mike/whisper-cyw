<?php

function getWikidataImage($qid) {
    $sparqlEndpoint = 'https://query.wikidata.org/sparql';
    $userAgent = 'YourAppName/1.0 (your@email.com)'; // Replace with your app info

    $sparqlQuery = <<<SPARQL
SELECT ?image
WHERE {
  wd:$qid wdt:P18 ?image.
}
LIMIT 1
SPARQL;

    $params = [
        'query' => $sparqlQuery,
        'format' => 'json',
    ];

    $fullUrl = $sparqlEndpoint . '?' . http_build_query($params);

    // Initialize cURL session
    $ch = curl_init($fullUrl);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/sparql-results+json']); // Specify desired response type

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    // Close cURL session
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Check if there are results and extract the image URL
    if (isset($data['results']['bindings']) && !empty($data['results']['bindings'])) {
        // print_r($data);
        return $data['results']['bindings'][0]['image']['value'];
    } else {
        return null; // No image found for the given QID
    }
}

$json = file_get_contents('./entities.json');
$data = json_decode($json, true);

$arr = [];
foreach ($data as $item) {
    // print_r($item);

    if(empty($item)){
        $arr[] = [];
        continue;
    }

    $a = [];
    foreach($item as $k => $v){
        // echo $k . ' => ' . $v . "\n";
        if(!empty($k)) {
            $a[$k] = [
                'name' => $v,
                'image' => getWikidataImage($k),
            ];
        }
    }
    $arr[] = $a;
}

// print_r($arr);
file_put_contents('./entities-images.json', json_encode($arr, JSON_PRETTY_PRINT));
// // Example usage:
// $entityQID = 'Q15'; // Example: Italy
// $imageUrl = getWikidataImage($entityQID);

// if ($imageUrl) {
//     echo "Image URL for QID '" . $entityQID . "': " . $imageUrl . "\n";
//     // echo "Image URL for QID '" . $entityQID . "': " . $imageUrl . "\n";
//     // echo '<img src="' . $imageUrl . '" alt="Image for ' . $entityQID . '">';
// } else {
//     echo "No image found for QID '" . $entityQID . "'.\n";
// }

// ?>