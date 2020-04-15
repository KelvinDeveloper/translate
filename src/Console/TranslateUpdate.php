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
        $default = config('translate.default');
        $items = Translate::get([$default]);

        $this->allTexts = [];
        foreach($items as $row) {
            $this->allTexts[$row->$default] = true;
        }

        foreach (config('translate.origin_path') as $path) {
            $path = base_path($this->treatmentPath($path));
            $this->searchTexts($path);
        }

        $translate = new Translate;

        dc('Found '.count($this->storeTexts).' new texts');
        return $translate->insert($this->storeTexts);
    }

    private function searchTexts($path)
    {
        $path = rtrim($path, '/');
        $default = config('translate.default');

        foreach (scandir($path) as $file) {
            if (in_array($file, ['.', '..']))
                continue;
            if (is_dir($path . '/' . $file)) {
                $this->searchTexts($path . '/' . $file);
            } else {
                dc($path.'/'.$file);
                $lines = file($this->treatmentPath($path) . '/' . $file);
                foreach ($lines as $line) {
                    preg_match_all("/_t(\s+)?\((\s+)?('|\")(.*?)('|\")((\s+)?,(\s+)?(.*?))?(\s+)?\)/",
                        $line, $result);


                    if (! is_array($result)) continue;
                    if (! is_array($result[4])) continue;

                    foreach ($result[4] as $text) {
                        if (empty($text))
                            continue;

                        if (!isset($this->allTexts[$text])) {
                            $this->storeTexts[] = [$default => $text, 'created_at' => date('Y-m-d H:i:s')];
                            $this->allTexts[$text] = true;
                        }
                    }
                }
            }
        }
    }

    private function treatmentPath($path)
    {
        return str_finish($path, '/');
    }
}
