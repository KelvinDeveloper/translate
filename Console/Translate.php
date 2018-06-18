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
    protected $signature = 'translate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

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

                $translate = (new TranslateController)->translate($defaultText, $default, $language);
                $item->{$languageDB} = $translate;
            }

            $item->save();
        }
    }
}
