<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    // Nama tabel sesuai struktur database kamu
    protected $table = 'purchase_order_detail';

    // Kolom yang bisa diisi secara mass assignment
    protected $fillable = [
        'po_number',
        'product_id',
        'quantity',
        'amount',
        'created_at',
        'updated_at',
    ];
}
