<?php

if (! function_exists('_t')) {

    function _t($text) {
        $Translate = new \Translate\Http\Controllers\TranslateController;
        return $Translate->translate($text, null, $Translate->getLanguage());
    }
}