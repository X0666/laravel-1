<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;

class ProductGallery extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_galleries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'products_id',
        'url',
    ];

    public function getUrlAttribute($url)
    {
        return config('app.url') . Storage::url($url);
    }
}
