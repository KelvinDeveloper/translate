<?php

namespace Translate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\TranslateClient;

class TranslateController extends Controller
{
    use TranslateStore;

    private $default_language = '';
    private $debug = null;

    public function __construct()
    {
        $this->default_language = config('translate.default');
        $this->debug = config('translate.debug');
    }

    public function getLanguage ()
    {
        return 'pt';
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

    public function translate ($text, $sourceLanguageCode, $targetLanguageCode=false, $forceReturn=true)
    {
        if (! $sourceLanguageCode) $sourceLanguageCode = $this->default_language;

        if ($redis = $this->getTranslateRedis($text, $targetLanguageCode))
            return addslashes($redis);

        if ($aws = $this->getTranslateAws($text, $sourceLanguageCode, $targetLanguageCode))
            return addslashes($aws);

        if ($google = $this->getTranslateGoogle($text, $sourceLanguageCode, $targetLanguageCode))
            return addslashes($google);

        return $forceReturn ? addslashes($text) : '';
    }

    private function getTranslateRedis ($text,  $targetLanguageCode)
    {
        try {
            $redis = \Redis::get("translate.{$targetLanguageCode}.{$text}");
            return $redis;
        } catch (\Exception $e) { return false; }
    }

    private function getTranslateGoogle ($text, $sourceLanguageCode, $targetLanguageCode)
    {
        if (! in_array($targetLanguageCode, config('translate.languages'))) abort(500, "Code {$targetLanguageCode} not supported");

        try {
            $google = TranslateClient::translate($sourceLanguageCode, $targetLanguageCode, $text);
            $this->saveTranslate($text, $google, $sourceLanguageCode, $targetLanguageCode);
            return $google;
        } catch (\Exception $e) {
             if ($this->debug) dd($e->getMessage(), $e->getFile(), $e->getLine());
            return false;
        }
    }

    private function getTranslateAws ($text, $sourceLanguageCode, $targetLanguageCode)
    {
        try {
            $aws = \AWS::createClient('translate');
            $translate = $aws->translateText([
                'SourceLanguageCode'    =>  $sourceLanguageCode,
                'TargetLanguageCode'    =>  $targetLanguageCode,
                'Text'                  =>  $text
            ]);
            $this->saveTranslate($text, $translate['TranslatedText'], $sourceLanguageCode, $targetLanguageCode);
            return $translate['TranslatedText'];
        } catch (\Exception $e) {
            if ($this->debug) dd($e->getMessage(), $e->getFile(), $e->getLine());
            return false;
        }
    }
}