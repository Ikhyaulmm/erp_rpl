<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class PurchaseOrderModel extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number', 
        'supplier_id', 
        'status', 
        'order_date',
        'created_at',
        'updated_at'
    ];

    /**
     * Mengambil data Purchase Order berdasarkan PO Number.
     * Sesuai instruksi tugas: getPurchaseOrderByID($poNumber)
     * * @param string $poNumber
     * @return \App\Models\PurchaseOrderModel|null
     */
    public static function getPurchaseOrderByID($poNumber)
    {
        // Mencari data di mana kolom 'po_number' sama dengan parameter
        return self::where('po_number', $poNumber)->first();
    }
}