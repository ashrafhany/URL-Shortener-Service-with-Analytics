<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    use HasFactory;
    protected $fillable = ['original_url', 'alias'];

    public function logs()
    {
        return $this -> hasMany(UrlLog::class);
    }
}
