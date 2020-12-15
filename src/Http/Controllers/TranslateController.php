<?php

namespace Translate\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedicated\GoogleTranslate\Translator;
use Illuminate\Http\Request;
use Translate\Translate;

class TranslateController extends Controller
{
    use TranslateStore;

    static $default_language = '';
    private $debug            = null;
    private $cache_driver     = null;
    private $translate_driver = null;


    static $lang = [];
    static $lastdate = null;


    public function __construct()
    {
        self::$default_language = config('translate.default', 'en');
        $this->debug = config('translate.debug', false);
        $cache_driver = "Translate\Console\Drivers\Translate" . ucfirst(config('translate.cache_driver'));
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
            self::$default_language = $request->SourceLanguageCode;

        return $this->translate($request->Text, self::$default_language, $request->TargetLanguageCode);
    }


    public static function cacheLangs($target) {
        if (self::$lastdate === null) {
            self::$lastdate = Translate::orderBy('updated_at', 'DESC')
                ->first(['updated_at'])->updated_at->format('U');
        }

        // sem cache para o idioma padrao
        if (self::$default_language === $target)
            return;

        $file = storage_path('app/'.$target.'.php');
        if (!isset(self::$lang[$target]) && (!file_exists($file) || filemtime($file) < self::$lastdate)) {
            $default = self::$default_language;
            $instance = new Translate;
            $langs = \DB::connection($instance->getConnection()->getName())
                ->table($instance->getTable())
                ->where($target,'!=','')
                ->orderBy('updated_at')
                ->get([$default, $target]);

            $php = '<?php \Translate\Http\Controllers\TranslateController::$lang[\''.$target.'\'] = [';
            self::$lang[$target] = [];
            foreach($langs as $row) {
                self::$lang[$target][$row->$default] = $row->$target;

                $php .= "'".addslashes($row->$default)."' => '".addslashes($row->$target)."',";
            }

            $php .= ']; ';

            file_put_contents($file, $php);
        } else if (!isset(self::$lang[$target])) {
            require($file);
        }

    }

    public static function translateFromCache($text, $args = null, $target = null) {
        if ($target === null)
            $target = self::getLanguage();
        
        if (self::$default_language !== $target) {
            self::cacheLangs($target);
            $text = isset(self::$lang[$target][$text]) ? self::$lang[$target][$text] : $text;
        }
        
        if (is_array($args)) {
            return self::variableTreatment($text, $args);
        }
        return $text;
    }

    public static function setLanguage($lang)
    {
        if (!in_array($lang, config('translate.principal_languages'))) {
            $lang = config('translate.default');
        }

        return self::setCookie('locale', $lang);
    }

    public static function getLanguage()
    {
        $lang = '';
        if (self::hasCookie('locale'))
            $lang = self::getCookie('locale');
        else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $thisLocale = str_replace('-', '_', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

            foreach (config('translate.languages') as $lang) {
                if ($lang != config('translate.default') && strstr($thisLocale, $lang) == true) {
                    $lang = self::setCookie('locale', $lang);
                    break;
                }
            }
        } else {
            $lang = config('translate.default');
        }

        if (!in_array($lang, config('translate.principal_languages'))) {
            $lang = config('translate.default');
            self::setLanguage($lang);
        }

        return $lang;
    }

    public function translate ($text, $targetLanguageCode, $sourceLanguageCode=false, $forceReturn=true, $getCache=true)
    {
        if (! $sourceLanguageCode) $sourceLanguageCode =self::$default_language;
        if ($targetLanguageCode == $sourceLanguageCode) return $text;

        if ($getCache && $redis = $this->getTranslate($text, $targetLanguageCode))
            return $redis;

        foreach ($this->translate_driver as $driver) {

            try {
                if ($translate = $this->{'getTranslate' . ucfirst(strtolower(trim($driver)))}($text, $sourceLanguageCode, $targetLanguageCode))
                    return $translate;
            } catch (\Exception $e) {
                if ($this->debug) var_dump($e->getMessage(), $e->getFile(), $e->getLine());
            }
        }

        return $forceReturn ? $text : '';
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

    public static function variableTreatment ($text, $args)
    {
        preg_match_all("/{(.*?)}/", $text, $result);
        return str_replace($result[0], $args, $text);
    }

    public function getJavascript ($lang)
    {
        $json = [];
        foreach (\Translate::groupBy(self::$default_language)->get([self::$default_language, $lang])->toArray() as $row) {
            $json[$row[self::$default_language]] = $row[$lang];
        }
        return response('var Lang = ' . json_encode($json))->header('Content-Type', 'application/javascript');
    }

    public function getTranslateGoogle ($text, $sourceLanguageCode, $targetLanguageCode)
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

    public function getTranslateAws ($text, $sourceLanguageCode, $targetLanguageCode)
    {
        try {
            $aws = new \Aws\Translate\TranslateClient([
                'region' => 'us-east-1',
                'version' => 'latest',
                'credentials' => [
                    'key'    => config('translate.credentials.aws.key'),
                    'secret' => config('translate.credentials.aws.secret') ,
                ]
            ]);
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

    public static function setCookie ($name, $value, $time = false, $path = '/', $domain = '', $secure = null, $httpOnly = null) {
        if (is_null($secure) && env('SESSION_SECURE_COOKIE')) {
            $secure = env('SESSION_SECURE_COOKIE');
        }

        if (is_null($httpOnly) && env('SESSION_HTTP_ONLY_COOKIE')) {
            $httpOnly = env('SESSION_HTTP_ONLY_COOKIE');
        }
        else if (is_null($httpOnly)) {
            $httpOnly = false;
        }

        if (! $time) {

            $time = time() + 60 * 60 * 24 * 30;
        }

        self::deleteCookie($name);

        @setcookie($name, $value, $time, $path, $domain, $secure, $httpOnly);
        return $_COOKIE[$name] = $value;
    }

    public static function deleteCookie($key, $path = '/')
    {
        try {
            unset($_COOKIE[$key]);
            return setcookie($key, null, - 1, $path);
        } catch (\Exception $e) { return $key; }
    }

    public static function getCookie($name)
    {
        try {
            if (self::hasCookie($name)) return $_COOKIE[$name];
            return false;
        } catch (\Exception $e) { return $name; }
    }

    public static function hasCookie ($name) {
        try {
            return isset($_COOKIE[$name]);
        } catch (\Exception $e) { return $name; }
    }
}
