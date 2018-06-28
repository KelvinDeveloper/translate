<?php

namespace Translate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Translate\Translate;

class TranslateManager extends Controller{

    use TranslateStore;

    public function index ($translate_lang=null)
    {

        if (! $translate_lang) $translate_lang = array_values(config('translate.languages'))[0];

        return view('translate.index', compact('translate_lang'));
    }

    public function update (Request $request)
    {
        $translate = Translate::find($request->id_lang);
        return ['status' => $translate->fill($request->all())->save()];
    }

    public function autoTranslate (Request $request)
    {
        return Artisan::call('translate:auto', [
            'language'  =>  $request->language
        ]);
    }

    public function updateTexts ()
    {
        return Artisan::call('translate:update');
    }

    public function updateCache ()
    {
        return Artisan::call('translate:sync');
    }
}