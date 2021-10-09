<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body'
    ];

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // public function image_url()
    // {
    //     return Storage::url('images/posts/' . $this->image);
    // }

    public function getImageUrlAttribute()
    {
        return Storage::url('images/posts/' . $this->image);
    }
}
