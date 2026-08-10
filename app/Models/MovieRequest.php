<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieRequest extends Model
{
    public const STATUS_REQUESTED = 'Requested';
    public const STATUS_AVAILABLE = 'Available';

    protected $fillable = [
        'name',
        'type',
        'title',
        'release_year',
        'all_episodes',
        'episodes',
        'status',
        'delete_token',
        'available_token',
        'discord_message_id',
    ];

    protected $casts = [
        'all_episodes' => 'boolean',
    ];
}
