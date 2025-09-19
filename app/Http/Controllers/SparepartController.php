<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WarehouseApiService;

class SparepartController extends Controller
{
    protected $warehouseApi;

    public function __construct(WarehouseApiService $warehouseApi)
    {
        $this->warehouseApi = $warehouseApi;
    }

    /**
     * Display sparepart index page
     */
    public function index()
    {
        // Ambil data barang dari warehouse
        $response = $this->warehouseApi->getBarangBoxOnly(25); // 25 items per page
        
        $barangs = [];
        if ($response['success']) {
            $barangs = $response['data']['data'];
        }

        return view('sparepart.index', compact('barangs', 'response'));
    }

    /**
     * API endpoint untuk DataTables atau AJAX
     */
    public function getData(Request $request)
    {
        $response = $this->warehouseApi->getBarangBoxOnly($request->per_page);
        
        // Format untuk AJAX response
        if ($response['success']) {
            return response()->json([
                'success' => true,
                'data' => $response['data']['data'] ?? [], // Nested data dari pagination
                'message' => 'Data berhasil dimuat'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'data' => [],
            'message' => $response['message']
        ]);
    }

    /**
     * Search barang via AJAX
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $response = $this->warehouseApi->searchBarang($query);
        
        return response()->json($response);
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        $this->warehouseApi->clearCache();
        
        return redirect()->back()->with('success', 'Cache berhasil dibersihkan');
    }
}