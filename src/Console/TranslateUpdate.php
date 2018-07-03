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
        $this->allTexts = Translate::get([config('translate.default')])->implode(config('translate.default'), ',');
        $this->allTexts = explode(',', $this->allTexts);

        foreach (config('translate.origin_path') as $path) {

            $path = base_path($this->treatmentPath($path));
            $this->searchTexts($path);
        }

        $translate = new Translate;
        return $translate->insert($this->storeTexts);
    }

    private function searchTexts($path)
    {
        foreach (scandir($path) as $file) {

            if (in_array($file, ['.', '..'])) continue;
            if (is_dir($path . $file)) $this->searchTexts($path . $file);

            $file = file($this->treatmentPath($path) . $file);

            foreach ($file as $line) {

                preg_match("/_t\(('|\")(.*?)('|\")\)/", $line, $result);

                if (! is_array($result)) continue;
                if (empty($result[2])) continue;

                $text = $result[2];

                if (! in_array($text, $this->allTexts)) {

                    $this->storeTexts[] = [config('translate.default') => $text];
                }
            }
        }
    }

    private function treatmentPath($path)
    {
        return str_finish($path, '/');
    }
}
