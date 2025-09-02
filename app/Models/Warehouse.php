<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Constants\WarehouseColumns;

class Warehouse extends Model
{
    use HasFactory;
    
    protected $table;
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Tetapkan nama tabel dan kolom
        $this->table = config('db_tables.warehouse');
        $this->fillable = WarehouseColumns::getFillable();
    }

    public static function getWarehouseAll($search = null)
    {
        $query = self::query();

        if ($search) {
            $query->where(WarehouseColumns::NAME, 'LIKE', "%{$search}%")
                  ->orWhere(WarehouseColumns::ADDRESS, 'LIKE', "%{$search}%")
                  ->orWhere(WarehouseColumns::PHONE, 'LIKE', "%{$search}%");
        }

        return $query->orderBy(WarehouseColumns::CREATED_AT, 'asc')->paginate(config('pagination.branch_per_page'));
    }

    public static function addWarehouse($data)
    {
        return self::create($data);
    }

    public static function getWarehouseById($id)
    {
        return self::find($id);
    }

    public static function countWarehouse()
    {
        return self::count();
    }

    public function updateWarehouse($id, $data)
    {
        $warehouse = $this->getWarehouseById($id);

        if (!$warehouse) {
            return false;
        }

        return $warehouse->update($data);
    }

    public function searchWarehouse($keyword)
    {
        return self::where(function ($query) use ($keyword) {
            $query->where(WarehouseColumns::NAME, 'like', "%{$keyword}%")
                ->orWhere(WarehouseColumns::ADDRESS, 'like', "%{$keyword}%")
                ->orWhere(WarehouseColumns::PHONE, 'like', "%{$keyword}%");
        })->get();
    }

    /**
     * Static method for deleting warehouse (consistent with Branch model)
     */
    public static function deleteWarehouse($id)
    {
        return self::where(WarehouseColumns::ID, $id)->delete();
    }
}
