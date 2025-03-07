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
        $this->createIndexPage();
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
        $transcription = file_get_contents('./data/' . $nid . '.vtt');
        $this->render($nid, $lang, $transcription, $url, $title);
        $this->items[] = [
            'nid' => $nid,
            'url' => $url,
            'title' => $title,
            'lang' => $lang,
        ];
    }

    private function render(string $nid, string $lang, string $transcription, string $url, string $title)
    {
        $template = $this->twig->load('template.twig');
        $title = 'Transcription for ' . $nid;
        $content = FormatTranscript::formatVTT($transcription);
        $output = $template->render([
            'title' => $title, 
            'content' => $content,
            'url' => $url,
            'title' => $title,
            'lang' => $lang,
        ]);

        file_put_contents('./dist/'.$nid.'.html', $output);
    }

    private function createIndexPage()
    {
        $template = $this->twig->load('index.twig');
        $title = 'Transcriptions';
        $output = $template->render([
            'data' => $this->items,
        ]);

        file_put_contents('./dist/index.html', $output);
    }
}

new Generate();