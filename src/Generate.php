<?php

namespace App;

require_once './vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\FormatTranscript;

class Generate {

    private Environment $twig;
    private array $items = [];
    private array $languages = [];
    private array $langkeys = [];

    public function __construct()
    {
        $this->setLangKeys();
        $this->twig = new Environment(new FilesystemLoader('./templates'));
        $this->getAudioTranscript();
        $this->createIndexPage();
    }

    private function setLangKeys()
    {
        $json = file_get_contents('./src/langkeys.json');
        $this->langkeys = json_decode($json, true);
    }

    private function getAudioTranscript()
    {
        $audioFiles = json_decode(file_get_contents('./audio.json'), true);

        foreach($audioFiles as $audioFile) {
            $nid = $audioFile['nid'];
            $url = $audioFile['url'];
            $title = $audioFile['title'];
            $this->getTranscriptionData($nid, $url, $title);
        }
    }

    private function getTranscriptionData(string $nid, string $url, string $title)
    {
        if(!file_exists('./data/' . $nid . '.vtt')) {
            return false;
        }

        $lang = file_get_contents('./data/' . $nid . '.lang.txt');
        $this->setLanaguages($lang);
        $transcription = file_get_contents('./data/' . $nid . '.vtt');
        $this->render($nid, $lang, $transcription, $url, $title);
        $this->items[] = [
            'nid' => $nid,
            'url' => $url,
            'title' => $title,
            'lang' => $lang
        ];
    }

    private function getLangKey(string $lang)
    {
        return $this->langkeys[$lang] ?? $lang;
    } 

    private function setLanaguages(string $lang)
    {
        if(!in_array($lang, $this->languages)) {
            $this->languages[] = $lang;
        }
    } 

    private function render(string $nid, string $lang, string $transcription, string $url, string $title)
    {
        $template = $this->twig->load('template.twig');
        $title = '<a target="_blank" href="https://www.peoplescollection.wales/items/'.$nid.'">Transcription for ' . $nid . '</a>';
        $content = FormatTranscript::formatVTT($transcription);
        $output = $template->render([
            'title' => $title, 
            'content' => $content,
            'url' => $this->formatAudioUrl($url),
            'title' => $title,
            'lang' => $lang,
            'nid' => $nid,
        ]);

        file_put_contents('./dist/'.$nid.'.html', $output);
    }

    private function formatAudioUrl(string $url)
    {
        $url = str_replace('#', '%23', $url);
        return $url;
    }

    private function createIndexPage()
    {
        shuffle($this->items);
        $template = $this->twig->load('index.twig');
        $title = 'Transcriptions';
        $output = $template->render([
            'data' => $this->items,
            'count' => count($this->items),
            'languages' => $this->updateLangKeys()
        ]);

        file_put_contents('./dist/index.html', $output);
    }

    private function updateLangKeys()
    {
        $arr = [];

        foreach($this->languages as $lang) {
            $arr[$lang] = $this->getLangKey($lang);
        }

        return $arr;
    }
}

new Generate();