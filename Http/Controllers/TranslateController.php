<?php

namespace Translate\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedicated\GoogleTranslate\Translator;
use Illuminate\Http\Request;

class TranslateController extends Controller
{
    use TranslateStore;

    private $default_language = '';
    private $debug            = null;
    private $cache_driver     = null;
    private $translate_driver = null;

    public function __construct()
    {
        $this->default_language = config('translate.default', 'en');
        $this->debug = config('translate.debug', false);
        $cache_driver = "Translate\Console\Drivers\Translate" . config('translate.cache_driver');
        $this->cache_driver     = new $cache_driver;
        $this->translate_driver = config('translate.translate_driver');
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

        if ($redis = $this->getTranslate($text, $targetLanguageCode))
            return $redis;

        foreach ($this->translate_driver as $driver) {

            try {
                if ($translate = $this->{'getTranslate' . ucfirst(strtolower(trim($driver)))}($text, $sourceLanguageCode, $targetLanguageCode))
                    return $translate;
            } catch (\Exception $e) {
                if ($this->debug) var_dump($e->getMessage(), $e->getFile(), $e->getLine());
            }
        }

        return $forceReturn ? addslashes($text) : '';
    }

    private function getTranslate ($text,  $targetLanguageCode)
    {
        try {
            $translate = $this->cache_driver->get("translate.{$targetLanguageCode}.{$text}");
            return $translate;
        } catch (\Exception $e) {
            if ($this->debug) var_dump($e->getMessage(), $e->getFile(), $e->getLine());
        }
    }

    private function getTranslateGoogle ($text, $sourceLanguageCode, $targetLanguageCode)
    {
        if (! in_array($targetLanguageCode, config('translate.languages'))) abort(500, "Code {$targetLanguageCode} not supported");

        try {
            $google = new Translator;
            $google->setSourceLang($sourceLanguageCode);
            $google->setTargetLang($targetLanguageCode);
            $translate = $google->translate($text);
            $this->saveTranslate($text, $translate, $sourceLanguageCode, $targetLanguageCode);
            return $translate;
        } catch (\Exception $e) {
             if ($this->debug) var_dump($e->getMessage(), $e->getFile(), $e->getLine());
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
            if ($this->debug) var_dump($e->getMessage(), $e->getFile(), $e->getLine());
            return false;
        }
    }
}