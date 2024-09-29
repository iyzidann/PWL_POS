<?php

namespace App\Http\Controllers;

use App\Models\KategoriModel;
use App\Models\BarangModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BarangController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Barang',
            'list'  => ['Home', 'Barang']
        ];

        $page = (object) [
            'title' => 'Daftar barang yang terdaftar dalam sistem'
        ];

        $activeMenu = 'barang';

        $kategori = KategoriModel::all();

        return view('barang.index', ['breadcrumb' => $breadcrumb, 'page' => $page, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    }

    public function list(Request $request) 
    { 
        $barang = barangModel::select('barang_id', 'barang_kode', 'barang_nama', 'harga_beli', 'harga_jual', 'kategori_id') ->with('kategori'); 
        
        if ($request->kategori_id){
            $barang->where('kategori_id', $request->kategori_id);
        }
    
        return DataTables::of($barang) 
            // menambahkan kolom index / no urut (default nama kolom: DT_RowIndex) 
            ->addIndexColumn()  
            ->addColumn('aksi', function ($barang) {  // menambahkan kolom aksi 
                $btn = '<a href="'.url('/barang/' . $barang->barang_id).'" class="btn btn-info btn-sm">Detail</a> ';
                $btn .= '<a href="'.url('/barang/' . $barang->barang_id . '/edit').'" class="btn btn-warning btn-sm">Edit</a> ';
                $btn .= '<form class="d-inline-block" method="POST" action="'.url('/barang/'.$barang->barang_id).'">'
                    . csrf_field() . method_field('DELETE') .
                    '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin menghapus data ini?\');">Hapus</button></form>';
                
                return $btn; 
        }) 
        ->rawColumns(['aksi']) // memberitahu bahwa kolom aksi adalah html 
        ->make(true); 
    } 

    public function create()
    {
        $breadcrumb = (object) [
            'title' => 'Tambah barang',
            'list'  => ['Home', 'barang', 'Tambah']
        ];

        $page = (object) [
            'title' => 'Tambah barang Baru'
        ];

        $kategori = kategoriModel::all();

        $activeMenu = 'barang';
        
        return view('barang.create', ['breadcrumb' => $breadcrumb, 'page' => $page, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'barang_kode' => 'required|string|min:3', 
            'barang_nama' => 'required|string|max:100',                    
            'harga_beli'  => 'required|integer',      
            'harga_jual'  => 'required|integer',                         
            'kategori_id' => 'required|integer',                       
        ]);
    
        barangModel::create([
            'barang_kode'  => $request->barang_kode,
            'barang_nama'  => $request->barang_nama,
            'harga_beli'   => $request->harga_beli,
            'harga_jual'   => $request->harga_jual,
            'kategori_id'  => $request->kategori_id,
        ]);
        
        return redirect('/barang')->with('success', 'Data barang berhasil disimpan');
    }
    
    public function show(string $id)
    {
        $barang = BarangModel::with('kategori')->find($id);
        
        $breadcrumb = (object) [
            'title' => 'Detail barang',
            'list'  => ['Home', 'barang', 'Detail']
        ];
        
        $page = (object) [
            'title' => 'Detail barang'
        ];
        
        $activeMenu = 'barang';
        
        return view('barang.show', ['breadcrumb' => $breadcrumb, 'page' => $page, 'barang' => $barang, 'activeMenu' => $activeMenu]);
    }
    
    public function edit(string $id)
    {
        $barang = barangModel::find($id);

        $kategori = kategoriModel::all();
        
        $breadcrumb = (object) [
            'title' => 'Edit barang',
            'list' => ['Home', 'barang', 'Edit']
        ];
        
        $page = (object) [
            'title' => 'Edit barang'
        ];
        
        $activeMenu = 'barang';
        
        return view('barang.edit', ['breadcrumb' => $breadcrumb, 'page' => $page, 'barang' => $barang, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'barang_kode' => 'required|string|min:3', 
            'barang_nama' => 'required|string|max:100',                    
            'harga_beli'  => 'required|integer',      
            'harga_jual'  => 'required|integer',                         
            'kategori_id' => 'required|integer',  
        ]);

        barangModel::find($id)->update([
            'barang_kode'  => $request->barang_kode,
            'barang_nama'  => $request->barang_nama,
            'harga_beli'   => $request->harga_beli,
            'harga_jual'   => $request->harga_jual,
            'kategori_id'  => $request->kategori_id,
        ]);

        return redirect('/barang')->with('success', 'Data barang berhasil diubah');
    }

    public function destroy (string  $id)
    {
        $check = barangModel::find($id);
        if (!$check) {
            return redirect('/barang')->with('error', 'Data barang tidak Ditemukan');
        } 
        
        try{
            barangModel::destroy($id);
            return redirect('/barang')->with('success', 'Data barang Berhasil dihapus');
        } catch (\Illuminate\Database\QueryException)
        {
            return redirect('/barang')->with('error', 'Data barang Gagal dihapus karena terdapat Tabel lain yang terkait dengan data ini');
        }
    }
}
