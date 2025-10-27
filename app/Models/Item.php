<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class Item extends Model
{
    use HasFactory;

    protected $table;
    protected $fillable = [
        'product_id', 'sku', 'item_name', 'measurement_unit',
        'avg_base_price', 'selling_price', 'purchase_unit',
        'sell_unit', 'stock_unit'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Pakai konfigurasi tabel dari config/db_constants.php
        $this->table = config('db_constants.table.item', 'items');
        $this->fillable = array_values(config('db_constants.column.item') ?? $this->fillable);
    }

    // Relasi berdasarkan SKU ke PurchaseOrderDetail
    public function purchaseOrderDetails()
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'product_id', 'sku');
    }

    // Relasi ke MeasurementUnit
    public function unit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'measurement_unit', 'id');
    }

    // Ambil semua item
    public function getItem()
    {
        return self::all();
    }

    // Ambil semua item dengan pencarian opsional
    public static function getAllItems($search = null)
    {
        $query = self::with('unit');

        if ($search) {
            if (is_numeric($search)) {
                $query->where('id', '=', $search);
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('item_name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            }
        }

        return $query->orderBy('id', 'asc')->paginate(10);
    }

    // Hapus item berdasarkan ID
    public static function deleteItemById($id)
    {
        $item = self::find($id);

        if (!$item) {
            return false;
        }

        // Cek relasi dengan purchase order
        if ($item->purchaseOrderDetails()->exists()) {
            throw new Exception("Item tidak bisa dihapus karena sudah digunakan di purchase order.");
        }

        $item->delete();
        self::where('id', '>', $id)->decrement('id');

        return true;
    }

    // Hitung total item
    public static function countItem()
    {
        return self::count();
    }

    // Update data item
    public static function updateItem($id, $data)
    {
        $item = self::find($id);

        if (!$item) {
            return null;
        }

        $item->update($data);
        return $item;
    }

    // Tambah item baru
    public function addItem($data)
    {
        return self::create($data);
    }

    // Ambil item berdasarkan ID
    public static function getItemById($id)
    {
        return self::where('id', $id)->first();
    }

    // Hitung item berdasarkan tipe produk (sementara masih count total)
    public static function countItemByProductType()
    {
        return self::count();
    }

    // Ambil item berdasarkan tipe produk
    public static function getItemByType($productType)
    {
        return self::join('products', 'items.product_id', '=', 'products.product_id')
            ->where('products.product_type', $productType)
            ->select('items.*', 'products.product_type', 'products.product_name')
            ->get();
    }

    // Cari item berdasarkan keyword
    public static function searchItem($keyword)
    {
        return self::where('item_name', 'like', '%' . $keyword . '%')->paginate(10);
    }

    // Ambil item berdasarkan kategori produk
    public static function getItemByCategory($categoryId)
    {
        return self::join('products', 'items.product_id', '=', 'products.product_id')
            ->join('category', 'products.product_category', '=', 'category.id')
            ->where('category.id', $categoryId)
            ->select(
                'items.*',
                'products.product_name',
                'products.product_category',
                'category.category as category_name'
            )
            ->get();
    }

    // Hitung jumlah item dalam kategori tertentu
    public static function countItemByCategory($categoryId)
    {
        return self::join('products', 'items.product_id', '=', 'products.product_id')
            ->join('category', 'products.product_category', '=', 'category.id')
            ->where('category.id', $categoryId)
            ->count();
    }
}
