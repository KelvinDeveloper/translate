<?php

namespace Translate\Console;

use Illuminate\Console\Command;

class TranslateSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza em cache todos os dados gravados em banco';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    protected $drivers = ['redis'];

    public function handle()
    {
        if (! in_array(config('translate.drive'), $this->drivers)) return abort(500, 'Driver not supported');

        $default  = config('translate.default');

        $driver = "Translate\Console\Drivers\Translate" . config('translate.drive');
        $driver = new $driver;

        foreach (\Translate\Translate::get() as $item) {

            $defaultText = $item->{$default};

            foreach (config('translate.languages') as $language) {

                $languageDB  = str_replace('-', '_', strtolower($language));

                if (empty($item->{$languageDB})) continue;
                if ($language == $default) continue;

                $driver->store("translate.{$language}.{$defaultText}", $item->{$languageDB});
            }
        }
    }
}
