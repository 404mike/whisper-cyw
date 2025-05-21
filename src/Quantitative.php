<?php

namespace App;

require_once './vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\FormatTranscript;

class Generate {

    private Environment $twig;
    private array $items = [];

    public function __construct()
    {
        $this->twig = new Environment(new FilesystemLoader('./templates'));
        $this->getAudioTranscript();
        $this->saveOrderedTranscriptions();
        $this->createIndex();
    }

    private function getAudioTranscript()
    {
        $audioFiles = json_decode(file_get_contents('./qualitative/data.json'), true);

        $en_transcriptions = $audioFiles['en'];
        $cy_transcriptions = $audioFiles['cy'];

        foreach($en_transcriptions as $nid) {
            $large_model = $this->formatVTT($nid, 'large');
            $turbo_model = $this->formatVTT($nid, 'turbo');
            $medium_model = $this->formatVTT($nid, 'medium');
            $this->render($nid, 'en', ['large' => $large_model, 'turbo' => $turbo_model, 'medium' => $medium_model]);
        }

        foreach($cy_transcriptions as $nid) {
            $large_model = $this->formatVTT($nid, 'large');
            $turbo_model = $this->formatVTT($nid, 'turbo');
            $medium_model = $this->formatVTT($nid, 'medium');
            $this->render($nid, 'cy', ['large' => $large_model, 'turbo' => $turbo_model, 'medium' => $medium_model]);
        }
    }

    private function formatVTT(int $nid, string $model)
    {
        if(!file_exists('./qualitative/' . $model . '/' . $nid . '.vtt')) {
            return false;
        }
        $transcription = file_get_contents('./qualitative/' . $model . '/' . $nid . '.vtt');
        return FormatTranscript::formatVTTMulti($transcription);
    }

    private function render(string $nid, string $lang, array $transcriptions)
    {

        $arr[$nid] = [];

        $keys = array_keys($transcriptions);
        shuffle($keys);
        $shuffled = [];
        foreach ($keys as $key) {
            $shuffled[$key] = $transcriptions[$key];
        }
        $transcriptions = $shuffled;

        // list keys
        foreach ($transcriptions as $key => $value) {
            $arr[$nid][] = $key;
        }

        $this->items[] = $arr;

        $template = $this->twig->load('template_qualitative.twig');
        $title = '<a target="_blank" href="https://www.peoplescollection.wales/items/'.$nid.'">Transcription for ' . $nid . '</a>';
        $html = $template->render([
            'nid' => $nid,
            'lang' => $lang,
            'title' => $title,
            'transcriptions' => $transcriptions,
            'url' => $this->getAudioUrl($nid),
        ]);
        file_put_contents('./qualitative/dist/' . $nid . '.html', $html);
    }   

    private function saveOrderedTranscriptions()
    {
        file_put_contents('./qualitative/ordered.json', json_encode($this->items, JSON_PRETTY_PRINT));
    }

    private function getAudioUrl(int $nid)
    {
        $audioFiles = json_decode(file_get_contents('audio.json'), true);
        foreach($audioFiles as $audioFile) {
            if($audioFile['nid'] == $nid) {
                return $audioFile['url'];
            }
        }
        return false;
    }

    private function createIndex()
    {
        $template = $this->twig->load('index_qualitative.twig');
        $html = $template->render([
            'items' => $this->items,
        ]);
        // print_r($this->items);
        // die();
        file_put_contents('./qualitative/dist/index.html', $html);
    }
}

new Generate();