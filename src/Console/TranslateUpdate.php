<?php

namespace Translate\Console;

use Illuminate\Console\Command;
use Translate\Translate;

class TranslateUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procura novos termos a serem traduzidos no sistema.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    private $allTexts = null;
    private $storeTexts = [];

    public function handle()
    {
        $items = Translate::get([config('translate.default')])->toArray();
        $collection = collect($items);

        $this->allTexts = $collection->pluck(config('translate.default'))->toArray();

        foreach (config('translate.origin_path') as $path) {

            $path = base_path($this->treatmentPath($path));
            $this->searchTexts($path);
        }

        $translate = new Translate;

        return $translate->insert($this->storeTexts);
    }

    private function searchTexts($path)
    {
        $path = rtrim($path, '/');

        foreach (scandir($path) as $file) {

            if (in_array($file, ['.', '..'])) continue;
            if (is_dir($path . '/' . $file)) {
                $this->searchTexts($path . '/' . $file);
            } else {
                foreach ($file as $line) {

                    preg_match_all("/_t\(('|\")(.*?)('|\")\)/", $line, $result);

                    if (! is_array($result)) continue;
                    if (! is_array($result[2])) continue;

                    foreach ($result[2] as $text) {

                        if (empty($text)) continue;
                        if (! in_array($text, $this->allTexts)) {

                            $this->storeTexts[] = [config('translate.default') => $text];
                            $this->allTexts[] = $text;
                        }
                    }
                }
            }

            $file = file($this->treatmentPath($path) . '/' . $file);
        }
    }

    private function treatmentPath($path)
    {
        return str_finish($path, '/');
    }
}
