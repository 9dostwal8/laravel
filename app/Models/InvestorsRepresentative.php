<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestorsRepresentative extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'name' => 'json',
    ];

    protected $table = 'investors_representative';
}
