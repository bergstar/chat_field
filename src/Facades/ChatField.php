<?php

namespace Toolborg\ChatField\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Toolborg\ChatField\ChatField
 */
class ChatField extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Toolborg\ChatField\ChatField::class;
    }
}
