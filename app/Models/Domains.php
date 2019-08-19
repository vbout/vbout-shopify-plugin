<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Domains extends Model

{
    protected $fillable = ['shop_url', 'domain'];
    protected $appends = ['remaining_days'];


}