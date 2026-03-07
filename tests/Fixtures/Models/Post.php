<?php

namespace Toolborg\ChatField\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Post extends Model
{
    protected $guarded = [];

    public function meta(): HasOne
    {
        return $this->hasOne(PostMeta::class);
    }
}
