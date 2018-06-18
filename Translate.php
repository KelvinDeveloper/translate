<?php

namespace Translate;

use Illuminate\Database\Eloquent\Model;

class Translate extends Model
{
    protected $table = null;
    protected $fillable = null;
    protected $primaryKey = 'id_lang';

    public function __construct()
    {
        $this->table = config('translate.table');
        $this->fillable = array_merge(config('translate.languages'), ['verify']);
    }
}