<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $default  = str_replace('-', '_', strtolower(config('translate.default')));

        Schema::create('translations', function (Blueprint $table) use ($default) {

            $table->engine = 'MyISAM';

            $table->increments('id_lang');
            $table->text($default);
            foreach (config('translate.languages') as $language) {

                $language = str_replace('-', '_', strtolower($language));

                if ($language == config('translate.default')) continue;

                $table->text($language)->nullable();
            }
            $table->integer('verify')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('translations');
    }
}
