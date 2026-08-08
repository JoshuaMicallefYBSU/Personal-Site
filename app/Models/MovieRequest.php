<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieRequest extends Model
{
    protected $fillable = [
        'name',
        'type',
        'title',
        'release_year',
        'all_episodes',
        'episodes',
        'delete_token',
    ];

    protected $casts = [
        'all_episodes' => 'boolean',
    ];
}
