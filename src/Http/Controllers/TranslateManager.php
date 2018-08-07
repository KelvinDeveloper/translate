<?php

namespace Translate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Translate\Console\TranslateAuto;
use Translate\Console\TranslateSync;
use Translate\Console\TranslateUpdate;
use Translate\Translate;
use Translate\TranslateVerified;

class TranslateManager extends Controller{

    use TranslateStore;

    public function index ($translate_lang=null)
    {

        if (! $translate_lang) $translate_lang = array_values(config('translate.languages'))[0];

        $verify = (new TranslateVerified)->getData($translate_lang);

        return view('translate.index', compact('translate_lang', 'verify'));
    }

    public function update (Request $request)
    {
        $translate = Translate::find($request->id_lang);
        $update    = $translate->fill($request->all())->save();

        if ($update) {

            $verify = $this->getVerified($request->id_lang, $request->language);

            if (! $verify) {

                $this->createVerified($request->id_lang, $request->language);
            }
        }

        return ['status' => true];
    }

    public function getVerified($id, $language)
    {
        return TranslateVerified::where('id_lang', $id)->where('language', $language)->first();
    }

    public function updateVerified (Request $request)
    {
        if ($request->check == 'true') return ['status' => $this->createVerified($request->id_lang, $request->language)];

        return ['status' => $this->deleteVerified($request->id_lang, $request->language)];
    }

    private function deleteVerified ($id, $language)
    {
        return $this->getVerified($id, $language)->delete();
    }

    private function createVerified ($id, $language)
    {
        return (new TranslateVerified)->fill([
            'id_lang'  => $id,
            'language' => $language
        ])->save();
    }

    public function autoTranslate (Request $request)
    {
        (new TranslateAuto())->handle($request->language);
        return ['status' => true];
    }

    public function updateTexts ()
    {
        (new TranslateUpdate())->handle();
        return ['status' => true];
    }

    public function updateCache ()
    {
        (new TranslateSync())->handle();
        return ['status' => true];
    }
}