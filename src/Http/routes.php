<?php

Route::group(['prefix' => config('translate.url_path', 'translate')], function () {
    Route::post('/', 'Translate\Http\Controllers\TranslateController@httpTranslate');
    Route::get('js/{lang}', 'Translate\Http\Controllers\TranslateController@getJavascript');
    Route::group(['prefix' => 'manager'], function () {
        Route::post('update', 'Translate\Http\Controllers\TranslateManager@update');
        Route::post('auto_translate', 'Translate\Http\Controllers\TranslateManager@autoTranslate');
        Route::post('update_texts', 'Translate\Http\Controllers\TranslateManager@updateTexts');
        Route::post('update_cache', 'Translate\Http\Controllers\TranslateManager@updateCache');
        Route::post('verified', 'Translate\Http\Controllers\TranslateManager@updateVerified');
    });
});