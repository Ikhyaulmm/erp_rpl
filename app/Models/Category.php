<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $fillable = ['category'];


    public static function updateCategory($category_id, array $data)
    {
        $category = self::find($category_id);
        if (!$category) {
            return null;
        }

        $category->update($data);

        return $category;
    }
}
