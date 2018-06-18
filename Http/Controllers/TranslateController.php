<?php

namespace Translate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\TranslateClient;

class TranslateController extends Controller
{
    use TranslateStore;

    private $default_language = '';

    public function __construct()
    {
        $this->default_language = config('translate.default');
    }

    public function httpTranslate (Request $request)
    {
        $this->validate($request, [
            'Text'                  =>  'required',
            'TargetLanguageCode'    =>  'required'
        ]);

        if ($request->SourceLanguageCode)
            $this->default_language = $request->SourceLanguageCode;

        return $this->translate($request->Text, $this->default_language, $request->TargetLanguageCode);
    }

    public function translate ($text, $sourceLanguageCode, $targetLanguageCode)
    {
        if ($redis = $this->getTranslateRedis($text, $targetLanguageCode))
            return $redis;

        if ($google = $this->getTranslateGoogle($text, $sourceLanguageCode, $targetLanguageCode))
            return $google;

        return $text;
    }

    private function getTranslateRedis ($text,  $targetLanguageCode)
    {
        try {
            $redis = \Redis::get("translate.{$targetLanguageCode}", $text);
            return $redis;
        } catch (\Exception $e) { return false; }
        return false;
    }

    private function getTranslateGoogle ($text, $sourceLanguageCode, $targetLanguageCode)
    {
        if (! in_array($targetLanguageCode, config('translate.languages'))) abort(500, "Code {$targetLanguageCode} not supported");

        try {
            $google = TranslateClient::translate($sourceLanguageCode, $targetLanguageCode, $text);
            $this->saveTranslate($text, $google, $sourceLanguageCode, $targetLanguageCode);
            return $google;
        } catch (\Exception $e) { return false; }
    }
}