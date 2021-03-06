<?php

namespace Translate\Console;

use Illuminate\Console\Command;
use Translate\Http\Controllers\TranslateController;

class TranslateAuto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:auto {language?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traduz todos os termos gravados no banco para todos os idiomas listados no config/translate.php';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle($lang=false)
    {
        $default   = config('translate.default');
        if (! $lang) {
            $languages = $this->argument('language') ? [$this->argument('language')] : config('translate.languages');
        } else {
            $languages = [$lang];
        }

        foreach (\Translate\Translate::get() as $item) {

            $defaultText = $item->{$default};

            foreach ($languages as $language) {

                $languageDB  = str_replace('-', '_', strtolower($language));

                if (! empty($item->{$languageDB})) continue;
                if ($language == $default) continue;

                $translate = (new TranslateController)->translate($defaultText, $language, $default, false, false);
                $item->{$languageDB} = $translate;
            }

            $item->save();
        }
    }
}
