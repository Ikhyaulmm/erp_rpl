<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use App\Constants\Messages;
use App\Constants\WarehouseColumns;

class WarehouseController extends Controller
{

    /** Display a listing of the resource. */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        //Use enhanced query for API requests
        if ($request->wantsJson()) {
            // Best Practice: Use Model method instead of Controller query
            $filters = [
                'search' => $search,
                'status' => $request->get('status'),
                'sort_by' => $request->get('sort_by', WarehouseColumns::CREATED_AT),
                'sort_order' => $request->get('sort_order', 'desc'),
            ];

            $query = Warehouse::searchWithFilters($filters);
            $warehouses = $query->paginate($request->get('per_page', 15));
            return new WarehouseCollection($warehouses);
        }

        // Web functionality - use existing logic
        $warehouses = Warehouse::getWarehouseAll($search);

        // Handle PDF Export (existing functionality)
        if ($request->has('export') && $request->input('export') === 'pdf'){
            $pdf = Pdf::loadView('warehouse.report', ['warehouses' => $warehouses]);
            return $pdf->stream('report-warehouse.pdf');
        }

        return view('warehouse.index', ['warehouses' => $warehouses]);
    }

    /** Show the form for creating a new resource. */
    public function create()
    {
        return view('warehouse.create');
    }

    public function store(StoreWarehouseRequest $request)
    {
        try {
            // Single business logic - no duplication!
            $warehouse = Warehouse::addWarehouse([
                WarehouseColumns::NAME => $request->input('warehouse_name') ?? $request->input(WarehouseColumns::NAME),
                WarehouseColumns::ADDRESS => $request->input('warehouse_address') ?? $request->input(WarehouseColumns::ADDRESS),
                WarehouseColumns::PHONE => $request->input('warehouse_telephone') ?? $request->input(WarehouseColumns::PHONE),
                // WarehouseColumns::IS_ACTIVE => $request->input(WarehouseColumns::IS_ACTIVE, 0),
            ]);

            // Handle API Response
            if ($this->wantsJson($request)) {
                return response()->json([
                    'success' => true,
                    'message' => Messages::WAREHOUSE_CREATED,
                    'data' => new WarehouseResource($warehouse)
                ], 201);
            }

            // Handle Web Response (existing)
            return redirect()->route('warehouses.index')->with('success', Messages::WAREHOUSE_CREATED);
            
        } catch (\Exception $e) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    #TODO membuat method edit dan update
    /**
     * Show the form for editing the specified warehouse.
     */
    public function edit($id)
    {
        $warehouse = Warehouse::getWarehouseById($id);
        if (!$warehouse) {
            return abort(404, Messages::WAREHOUSE_NOT_FOUND);
        }
        return view('warehouse.edit', compact('warehouse'));
    }


    public function getWarehouseById($id)
    {
        $warehouse = (new Warehouse())->getWarehouseByID($id);

        if (!$warehouse) {
            return abort(404, Messages::WAREHOUSE_NOT_FOUND);
        }

        return view('warehouse.detail', compact('warehouse'));
    }

    public function countWarehouse()
    {
        $total = Warehouse::countWarehouse();

        return response()->json([
            'total_warehouse' => $total
        ]);
    }

    public function searchWarehouse(Request $request)
    {
        $keyword = $request->input('keyword');
        $warehouses = (new Warehouse())->searchWarehouse($keyword);

        if ($warehouses->isEmpty()) {
            return response()->json(['message' => 'Tidak ada warehouse yang ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $warehouses
        ]);
    }

    public function deleteWarehouse($id)
    {
        return (new Warehouse)->deleteWarehouse($id);
    }

    public function exportPdf()
    {
        $warehouse = [
            [
                'id' => 1,
                'warehouse_name' => 'Warehouse A',
                'warehouse_address' => 'Location A',
                'warehouse_telephone' => '1234567890',
                'is_active' => true,
                'created_at' => '2023-01-01',
                'updated_at' => '2023-01-02',
            ],
            [
                'id' => 2,
                'warehouse_name' => 'Warehouse B',
                'warehouse_address' => 'Location B',
                'warehouse_telephone' => '1234567890',
                'is_active' => false,
                'created_at' => '2023-02-01',
                'updated_at' => '2023-04-01',
            ],
            [
                'id' => 3,
                'warehouse_name' => 'Warehouse C',
                'warehouse_address' => 'Location C',
                'warehouse_telephone' => '1234567890',
                'is_active' => true,
                'created_at' => '2023-01-06',
                'updated_at' => '2023-01-04',
            ],
        ];

        $pdf = Pdf::loadView('warehouse.report', compact('warehouse'));
        return $pdf->stream('warehouse_report.pdf');
    }

    public function getWarehouseAll()
    {
        $warehouses = Warehouse::getWarehouseAll();

        if ($warehouses->isEmpty()) {
            return response()->json(['message' => 'Tidak ada warehouse yang ditemukan'], 404);
        }

        return view('warehouse.index', compact('warehouses'));
    }

        /**
         * Update the specified warehouse in storage.
         */
        public function update(UpdateWarehouseRequest $request, $id)
        {

            try {
                $updated = (new Warehouse())->updateWarehouse($id, [
                    'warehouse_name' => $request->input('warehouse_name'),
                    'warehouse_address' => $request->input('warehouse_address'),
                    'warehouse_telephone' => $request->input('warehouse_telephone'),
                    'is_rm_whouse' => $request->input('is_rm_whouse'),
                    'is_fg_whouse' => $request->input('is_fg_whouse'),
                    'is_active' => $request->input('is_active'),
                ]);

                if ($updated) {
                    $warehouse = (new Warehouse())->getWarehouseById($id);
                    // API response
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => Messages::WAREHOUSE_UPDATED,
                            'data' => $warehouse,
                        ]);
                    }
                    // Web response
                    return redirect()->route('warehouse.index')->with('success', Messages::WAREHOUSE_UPDATED);
                }

                // Not found
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => Messages::WAREHOUSE_NOT_FOUND,
                    ], 404);
                }
                return redirect()->back()->withInput()->with('error', Messages::WAREHOUSE_NOT_FOUND);
            } catch (\Exception $e) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                    ], 500);
                }
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }
}
