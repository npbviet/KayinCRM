<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Webkul\Product\Contracts\ProductImage as ProductImageContract;

class ProductImage extends Model implements ProductImageContract
{
    protected $table = 'product_images';

    protected $fillable = [
        'product_id',
        'path',
        'is_thumbnail',
        'sort_order',
    ];

    protected $casts = [
        'is_thumbnail' => 'boolean',
        'sort_order'   => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(\Webkul\Product\Models\Product::class);  
    }

    public function getUrl(): string  
    {
        return $this->path ? url('storage/' . $this->path) : '';
    }
    
}