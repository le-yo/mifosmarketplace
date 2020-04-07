<?php

namespace Modules\MifosHelper\Entities;

use Illuminate\Database\Eloquent\Model;

class MifosRequestLog extends Model
{
    protected $fillable = ['slug','content'];
}
