<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shop extends Model
{
    protected $fillable = ['shop_url', 'token'];
    protected $appends = ['remaining_days'];

    public function getRemainingDaysAttribute()
    {
        return $this->created_at->diffInDays(Carbon::now());
    }
}