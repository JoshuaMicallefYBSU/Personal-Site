<?php

namespace App\Models\VATPAC;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 'vatpac_users';    

    protected $fillable = [
        'name',
        'rating',
    ];

    public function airportControllerTotals()
    {

    }

    public function totalControllerTotals()
    {
        
    }
}
