<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrlLog extends Model
{
    use HasFactory;
    protected $fillable = ['url_id'];

    public function url()
    {
        return $this -> belongsTo(url::class);
    }
}
