<?php

namespace Translate\Facades;

use Illuminate\Support\Facades\Facade;

class TranslateFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Translate';
    }
}