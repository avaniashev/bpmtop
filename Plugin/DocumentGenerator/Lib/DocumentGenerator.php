<?php
App::uses('PlaceholderFiller', 'DocumentGenerator.Lib');
class DocumentGenerator
{
    public function generate($templateFileName, $resultFileName, $data){
        $zip = new ZipArchive();
        copy($templateFileName, $resultFileName);
        if ($zip->open($resultFileName)){
            $docFile = 'word/document.xml';
            $doc = $zip->getFromName($docFile);
            $doc = (new PlaceholderFiller())->fill($doc, $data);
            $zip->addFromString($docFile, $doc);
            $zip->close();
        }
    }
}