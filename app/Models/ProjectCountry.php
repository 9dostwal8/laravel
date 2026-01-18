<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectCountry extends Pivot
{
    protected $table = 'project_countries';
    
    protected $guarded = [];
}