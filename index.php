<?php

ini_set('memory_limit', '-1');

require 'vendor/autoload.php';

use App\Block;
use App\Image;
use App\Point;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;

$imagepath = './assets/00036731140067.2.jpg';

$client = new ImageAnnotatorClient(['credentials' => './vision-xxxx-xxxxxx.json']);
$response = $client->annotateImage(fopen($imagepath, 'r'), [Type::DOCUMENT_TEXT_DETECTION]);
$document = $response->getFullTextAnnotation();
$blocks = [];

foreach ($document->getPages() as $page) {
    foreach ($page->getBlocks() as $block) {
        $text = "";
        $box = Block::make(...array_map(
            fn ($vertex) => Point::make($vertex->getX(), $vertex->getY()),
            iterator_to_array($block->getBoundingBox()->getVertices()->getIterator()),
        ));

        foreach ($block->getParagraphs() as $paragraph) {
            foreach ($paragraph->getWords() as $word) {
                foreach ($word->getSymbols() as $symbol) {
                    $text .= $symbol->getText();
                }
                $text .= " ";
            }
            $text .= "\n";
        }

        $blocks[] = [
            'box' => $box,
            'text' => trim($text),
        ];
    }
}

$client->close();

$image = Image::makeFromJPG($imagepath);

foreach ($blocks as $index => $block) {
    var_dump($block['text']);
    $image
        //->paintBlock($block, 0, 0, 255)
        ->cropBlock($block['box'])
        ->saveJPG(sprintf('assets/dump/block-%s.jpg', $index));
}

echo "Done" . PHP_EOL;