<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf; // Import PDF Facade

class ItemPdfController extends Controller
{
    public function generatePDF()
    {
        // 1. Ambil data item
        $items = Item::all();

        // 2. Load View khusus PDF dan kirim data items
        $pdf = Pdf::loadView('item.pdf_report', compact('items'));

        // 3. Download file dengan nama yang SAMA PERSIS dengan di file Test
        return $pdf->download('laporan_item.pdf');
    }
}