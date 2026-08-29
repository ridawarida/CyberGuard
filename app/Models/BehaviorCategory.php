<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BehaviorCategory extends Model
{
    protected $table = 'behavior_categories';

    protected $fillable = [
        'name',
        'description',
    ];
}
