<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        $id = $this->route('id');
        return [
            'warehouse_name' => 'required|min:3|unique:warehouse,warehouse_name,' . $id,
            'warehouse_address' => 'required',
            'warehouse_telephone' => 'required',
            'is_rm_whouse' => 'required|boolean',
            'is_fg_whouse' => 'required|boolean',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages()
    {
        return [
            'warehouse_name.required' => 'Nama gudang wajib diisi',
            'warehouse_name.min' => 'Nama gudang minimal 3 karakter',
            'warehouse_name.unique' => 'Nama gudang sudah ada, silakan gunakan nama lain',
            'warehouse_address.required' => 'Alamat gudang wajib diisi',
            'warehouse_telephone.required' => 'Telepon gudang wajib diisi',
            'is_rm_whouse.required' => 'Status RM wajib diisi',
            'is_rm_whouse.boolean' => 'Status RM harus berupa boolean',
            'is_fg_whouse.required' => 'Status FG wajib diisi',
            'is_fg_whouse.boolean' => 'Status FG harus berupa boolean',
            'is_active.required' => 'Status aktif wajib diisi',
            'is_active.boolean' => 'Status aktif harus berupa boolean',
        ];
    }
}
