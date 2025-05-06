<?php

class GenerateCSV
{
    public function __construct()
    {
        $filename = 'transcript_output.csv';
        $data = $this->getData();

        $this->generate($filename, $data);
    }

    private function getData()
    {
        $files = glob('../data/*.txt');
        $data = [];
        foreach ($files as $file) {
            $fileData = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $nid = str_replace(['.txt','.lang'], '', basename($file));
            if (!empty($fileData)) { // Add check for non-empty $fileData
                $lang = $fileData[0];
                $data[$lang][] = $nid;
            }
        }
        return $data;
    }

    public function generate(string $filepath, array $data)
    {
        $output = fopen($filepath, 'w');

        if ($output === false) {
            die("Could not open file for writing: " . $filepath . "\n");
        }

        foreach ($data as $lang => $nids) {
            foreach ($nids as $nid) {
                // $url = "https://www.peoplescollection.wales/items/{$nid}";
                $url = "https://404mike.github.io/whisper-cyw/dist/{$nid}.html";
                fputcsv($output, [$url, $lang], ',', '"', '\\');
            }
        }

        fclose($output);
    }
}

new GenerateCSV();