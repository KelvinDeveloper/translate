<?php

namespace Translate\Console;

use Illuminate\Console\Command;
use Translate\Http\Controllers\TranslateController;

class Translate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:auto';

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
    public function handle()
    {
        $default  = config('translate.default');

        foreach (\Translate\Translate::get() as $item) {

            $defaultText = $item->{$default};

            foreach (config('translate.languages') as $language) {

                $languageDB  = str_replace('-', '_', strtolower($language));

                if (! empty($item->{$languageDB})) continue;
                if ($language == $default) continue;

                $translate = (new TranslateController)->translate($defaultText, $default, $language, false);
                $item->{$languageDB} = $translate;
            }

            $item->save();
        }
    }
}
