<?php

namespace Translate\Http\Controllers;


use Translate\Translate;

trait TranslateStore
{
    private function saveTranslate($sourceLanguageText, $targetLanguageText, $sourceLanguageCode, $targetLanguageCode)
    {
        $this->saveTranslateDatabase($sourceLanguageText, $targetLanguageText, $sourceLanguageCode, $targetLanguageCode);
        $this->saveTranslateRedis($sourceLanguageText, $targetLanguageText, $targetLanguageCode);
    }

    private function saveTranslateDatabase($sourceLanguageText, $targetLanguageText, $sourceLanguageCode, $targetLanguageCode)
    {
        $sourceLanguageCode = str_replace('-', '_', strtolower($sourceLanguageCode));
        $targetLanguageCode = str_replace('-', '_', strtolower($targetLanguageCode));

        $Translate = Translate::where($sourceLanguageCode, $sourceLanguageText)->first();
        if (! $Translate) $Translate = new Translate;

        $Translate->{$sourceLanguageCode} = $sourceLanguageText;
        $Translate->{$targetLanguageCode} = $targetLanguageText;

        $Translate->save();
    }

    private function saveTranslateRedis($sourceLanguageText, $targetLanguageText, $targetLanguageCode)
    {
        return \Redis::set("translate.{$targetLanguageCode}.{$sourceLanguageText}", $targetLanguageText);
    }
}