<?php

class ValidateModelPredictions {

    private $modelPredictions = [];
    private $models = [
        'tiny' => 0,
        'base' => 0,
        'small' => 0,
        'medium' => 0,
        'large' => 0,
        'turbo' => 0,
    ];
    private $numberOfTranscriptions = 0;

    public function __construct() {
        $this->loadModelPredictions();
        $this->loadCSV();
        $this->outPutModelPerformance();
    }

    private function loadCSV()
    {
        $csv = array_map(function($line) {
            return str_getcsv($line, ",", '"', "\\");
        }, file('transcriptions.csv'));

        unset($csv[0]); // Remove the header row

        $this->loopTranscriptions($csv);
    }

    private function loopTranscriptions(array $csv)
    {
        foreach ($csv as $line) {
            $nid = str_replace(['https://404mike.github.io/whisper-cyw/dist/','.html'], '', $line[0]);
            $actualLanguage = $line[2];

            if(!in_array($actualLanguage, ['en', 'cy'])) {
                continue;
            }

            $this->numberOfTranscriptions++;

            $this->getModelPrediction($nid, $actualLanguage);
        }
    }

    private function getModelPrediction(int $nid, string $actualLanguage)
    {
        if (isset($this->modelPredictions[$nid])) {

            $models = $this->modelPredictions[$nid];
            foreach ($models as $model => $prediction) {
                if ($prediction === $actualLanguage) {
                    $this->models[$model]++;
                }
            }
        }
    }

    private function loadModelPredictions()
    {
        $this->modelPredictions = json_decode(file_get_contents('combined_output.json'), true);
    }

    private function outPutModelPerformance()
    {
        $total = $this->numberOfTranscriptions;
        foreach ($this->models as $model => $count) {
            $percentage = round(($count / $total) * 100, 2);
            $numberCorrect = $count;
            echo "$model: $numberCorrect / $total = $percentage%\n";
        }
    }

}

new ValidateModelPredictions();