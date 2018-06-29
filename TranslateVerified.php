<?php

namespace Translate;

use Illuminate\Database\Eloquent\Model;

class TranslateVerified extends Model
{
    protected $table = 'translations_verified';
    protected $fillable = ['id_lang', 'language'];

    public function getData ($translate_lang)
    {
        $data = $this->where('language', $translate_lang)->get(['id_lang', 'language'])->implode('id_lang', ',');
        return explode(',', $data);
    }
}