<?php

$json = file_get_contents('./1306286.json');
$data = json_decode($json, true);

// echo count($data);


$arr = [];

$types = [];
$allowed_types = ['PERSON','LOC','NORP','ORG','GPE'];

foreach($data as $k => $line) {

    // print_r($line);

    $a = [];
    echo "Line $k\n";
    foreach($line as $key => $entity) {
        // // print_r($entity);
        $ent = $entity[1];
        $str = $entity[0];


        if(in_array($ent, $allowed_types)) {
            // $types[$ent] = $str;
            $qid = getWikidataQID($str);

            // echo $ent . ' - ' . $str . ' - ' . $qid . "\n";
            $a[$qid] = $str;
        }



        // $qid = "TEST";
        // $a[$qid] = $str;

    }

    $arr[] = $a;
    sleep(1);

    // print_r($arr);

    // die();

}


file_put_contents('./entities.json', json_encode($arr, JSON_PRETTY_PRINT));

function getWikidataQID($searchQuery) {
    $endpointUrl = 'https://www.wikidata.org/w/api.php';

    $params = [
        'action' => 'wbsearchentities',
        'format' => 'json',
        'language' => 'en', // You can change the language code if needed
        'search' => $searchQuery,
        'limit' => 1, // We only need the top result
    ];

    $fullUrl = $endpointUrl . '?' . http_build_query($params);

    // Initialize cURL session
    $ch = curl_init($fullUrl);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'YourAppName/1.0 (your@email.com)'); // Replace with your app info

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

    // Check if the request was successful and if there are any results
    if (isset($data['success']) && $data['success'] === 1 && isset($data['search']) && !empty($data['search'])) {
        // Extract the QID from the first result
        return $data['search'][0]['id'];
    } else {
        return null; // No QID found for the given search query
    }
}

// // Example usage:
// $search = 'London';
// $qid = getWikidataQID($search);

// if ($qid) {
//     echo "The QID for '" . $search . "' is: " . $qid . "\n";
// } else {
//     echo "No QID found for '" . $search . "'.\n";
// }

// $search = 'Albert Einstein';
// $qid = getWikidataQID($search);

// if ($qid) {
//     echo "The QID for '" . $search . "' is: " . $qid . "\n";
// } else {
//     echo "No QID found for '" . $search . "'.\n";
// }

// $search = 'This is a very unlikely search term';
// $qid = getWikidataQID($search);

// if ($qid) {
//     echo "The QID for '" . $search . "' is: " . $qid . "\n";
// } else {
//     echo "No QID found for '" . $search . "'.\n";
// }

?>