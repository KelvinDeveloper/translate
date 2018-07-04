<?php

namespace Translate;

use Illuminate\Database\Eloquent\Model;
use Translate\Http\Controllers\TranslateController;

class Translate extends Model
{
    protected $table = null;
    protected $fillable = null;
    protected $primaryKey = 'id_lang';

    public function __construct()
    {
        $this->table = config('translate.table');
        $this->fillable = array_merge(config('translate.languages'));
    }

    public static function changeLanguage ($lang)
    {
        $lang = strtolower(trim($lang));

        if (strstr($lang, '_') || strstr($lang, '-')) {
            $lang = str_replace('-', '_', $lang);
            $lang = explode('_', $lang);
            $lang = $lang[0];
        }

        if (!in_array($lang, config('translate.languages'))) return abort(500, 'Code Language not supported.');

        return (new TranslateController)->setCookie('locale', $lang, 2628000, '/');
    }

    public static function getLocale()
    {
        return (new TranslateController)->getLanguage();
    }
}