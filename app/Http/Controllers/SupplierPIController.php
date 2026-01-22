<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupplierPic;
use App\Models\SupplierPICModel; // Jika ada model lama
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SupplierPIController extends Controller
{
    // --- FITUR YANG SUDAH ADA SEBELUMNYA ---

    public function getPICByID($id)
    {
        $pic = SupplierPic::getPICByID($id);
        if (!$pic) {
            return redirect('/supplier')->with('error', 'PIC tidak ditemukan.');
        }
        $supplier = $pic->supplier;
        $pic->supplier_name = $supplier ? $supplier->company_name : null;
        return view('supplier.pic.detail', ['pic' => $pic, 'supplier' => $supplier]);
    }

    public function searchSupplierPic(Request $request)
    {
        $keywords = $request->input('keywords');
        $supplierPics = SupplierPICModel::searchSupplierPic($keywords);
        return view('supplier.pic.list', ['pics' => $supplierPics, 'supplier_id' => $keywords]);
    }

    public function addSupplierPIC(Request $request, $supplierID)
    {
        $validatedData = $request->validate([
            'supplier_id' => 'required|string|max:6',
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:50',
            'phone_number' => 'required|string|max:30',
            'assigned_date' => 'required|date_format:d/m/Y',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if (SupplierPic::isDuplicatePIC(
            $supplierID,
            $request->input('name'),
            $request->input('email'),
            $request->input('phone_number')
        )) {
            return redirect()->back()
                ->withErrors(['duplicate' => 'Data PIC sudah ada.'])
                ->withInput();
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $path = $file->store('public/foto_pic');
            $validatedData['photo'] = basename($path);
        }

        $validatedData['assigned_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['assigned_date'])->format('Y-m-d');
        $validatedData['supplier_id'] = $supplierID;
        $validatedData['supplier_name'] = $request->input('supplier_name');

        SupplierPic::addSupplierPIC($supplierID, $validatedData);

        return redirect()->back()->with('success', 'PIC berhasil ditambahkan!');
    }

    public function getSupplierPICAll()
    {
        $supplierPICs = SupplierPic::getSupplierPICAll();
        return view('supplier.pic.list', ['pics' => $supplierPICs]);
    }

    public function deleteSupplierPIC($id)
    {
        $picDelete = SupplierPic::deleteSupplierPIC($id);
        if ($picDelete) {
            return redirect()->back()->with('success', 'PIC berhasil dihapus!');
        } else {
            return redirect()->back()->with('error', 'PIC gagal dihapus.');
        }
    }

    public function cetakPdf()
    {
        $pics = SupplierPic::with('supplier')->get();
        $pdf = Pdf::loadView('supplier.pic.pdfpic', ['pics' => $pics])->setPaper('a4', 'landscape');
        return $pdf->stream('PIC-Supplier-Semua.pdf');
    }

    // --- FITUR BARU: UPDATE PIC (UNTUK TESTING) ---
    
    public function updateSupplierPICDetail(Request $request, $id)
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'supplier_id'   => 'required|exists:supplier,supplier_id',
            'name'          => 'required|string|max:255',
            'phone_number'  => 'required|string|max:20',
            // Email harus unik kecuali untuk user ini sendiri
            'email'         => 'required|email', 
            'assigned_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 2. Ambil data hasil validasi
        $data = $request->only([
            'supplier_id',
            'name',
            'phone_number',
            'email',
            'assigned_date',
            'position' // Tambahan optional
        ]);

        // 3. Panggil method dari MODEL
        // Pastikan Model SupplierPic punya method updateSupplierPIC
        // Jika error method not found, pakai cara manual: SupplierPic::find($id)->update($data);
        $result = SupplierPic::updateSupplierPIC($id, $data);

        // 4. Return response JSON
        return response()->json([
            'status'  => $result['status'],
            'message' => $result['message'],
            'data'    => $result['data'] ?? null,
        ], $result['code'] ?? 200);
    }
}