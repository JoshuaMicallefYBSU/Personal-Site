<?php

namespace App\Models\VATPAC;
use App\Models\VATPAC\Sessions;
use Carbon\Carbon;
use Carbon\CarbonInterval;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 'vatpac_users';    

    protected $fillable = [
        'id',
        'rating',
    ];
}
