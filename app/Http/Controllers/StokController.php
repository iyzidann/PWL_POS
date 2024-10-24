<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\StokModel;
use App\Models\SupplierModel;
use App\Models\UserModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;

class StokController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Stok',
            'list' => ['Home', 'Stok']
        ];
        $page = (object) [
            'title' => 'Daftar stok yang terdaftar dalam sistem'
        ];
        $activeMenu = 'stok';
        $stok = StokModel::all();
        return view('stok.index', ['breadcrumb' => $breadcrumb, 'page' => $page, 'stok' => $stok, 'activeMenu' => $activeMenu]);
    }

    public function list(Request $request)
    {
        $stoks = StokModel::select('stok_id', 'supplier_id', 'barang_id', 'user_id', 'stok_tanggal', 'stok_jumlah')->with('barang')->with('user')->with('supplier')->get();
        return DataTables::of($stoks)
            // menambahkan kolom index / no urut (default nama kolom: DT_RowIndex) 
            ->addIndexColumn()
            ->addColumn('aksi', function ($stok) {  // menambahkan kolom aksi 
                $btn  = '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id .
                    '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id .
                    '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id .
                    '/delete_ajax') . '\')"  class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi']) // memberitahu bahwa kolom aksi adalah html 
            ->make(true);
    }

    public function create_ajax()
    {
        $supplier = SupplierModel::select('supplier_id', 'supplier_nama')->get();
        $barang = BarangModel::select('barang_id', 'barang_nama')->get();
        $user = UserModel::select('user_id', 'nama')->get();
        return view('stok.create_ajax')->with('supplier', $supplier)->with('barang', $barang)->with('user', $user);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'supplier_id' => 'required|integer',
                'barang_id' => 'required|integer',
                'user_id' => 'required|integer',
                'stok_tanggal' => 'required|date',
                'stok_jumlah' => 'required|integer',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors(),
                ]);
            }
            StokModel::create($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Data user berhasil disimpan'
            ]);
        }
        redirect('/');
    }

    public function edit_ajax(string $id)
    {
        $stok = StokModel::find($id);
        $supplier = SupplierModel::select('supplier_id', 'supplier_nama')->get();
        $barang = BarangModel::select('barang_id', 'barang_nama')->get();
        $user = UserModel::select('user_id', 'nama')->get();
        return view('stok.edit_ajax', ['stok' => $stok, 'barang' => $barang, 'supplier' => $supplier, 'user' => $user]);
    }

    public function update_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax 
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'supplier_id' => 'required|integer',
                'barang_id' => 'required|integer',
                'user_id' => 'required|integer',
                'stok_tanggal' => 'required|date',
                'stok_jumlah' => 'required|integer',
            ];
            // use Illuminate\Support\Facades\Validator; 
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status'   => false,    // respon json, true: berhasil, false: gagal 
                    'message'  => 'Validasi gagal.',
                    'msgField' => $validator->errors()  // menunjukkan field mana yang error 
                ]);
            }
            $check = StokModel::find($id);
            if ($check) {
                $check->update($request->all());
                return response()->json([
                    'status'  => true,
                    'message' => 'Data berhasil diupdate'
                ]);
            } else {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
        }
        return redirect('/');
    }

    public function show_ajax(string $id)
    {
        $stok = StokModel::find($id);
        $supplier = SupplierModel::find($stok->supplier_id);
        $barang = BarangModel::find($stok->barang_id);
        $user = UserModel::find($stok->user_id);
        return view('stok.show_ajax', ['stok' => $stok, 'supplier' => $supplier, 'barang' => $barang, 'user' => $user]);
    }

    public function confirm_ajax(string $id)
    {
        $stok = StokModel::with(['barang', 'supplier', 'user'])->find($id);
        return view('stok.confirm_ajax', ['stok' => $stok]);
    }

    public function delete_ajax(Request $request, string $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $stok = StokModel::find($id);
            if ($stok) {
                $stok->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
            return redirect('/');
        }
    }
    
    public function import() 
    { 
        return view('stok.import');    
    } 

    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                // validasi file harus xls atau xlsx, max 1MB
                'file_stok' => ['required', 'mimes:xlsx', 'max:1024']
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }
            $file = $request->file('file_stok'); // ambil file dari request
            $reader = IOFactory::createReader('Xlsx'); // load reader file excel
            $reader->setReadDataOnly(true); // hanya membaca data
            $spreadsheet = $reader->load($file->getRealPath()); // load file excel
            $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif
            $data = $sheet->toArray(null, false, true, true); // ambil data excel
            $insert = [];
            if (count($data) > 1) { // jika data lebih dari 1 baris
                foreach ($data as $baris => $value) {
                    if ($baris > 1) { // baris ke 1 adalah header, maka lewati
                        $insert[] = [
                            'supplier_id' => $value['A'],
                            'barang_id' => $value['B'],
                            'user_id' => $value['C'],
                            'stok_tanggal' => now(),
                            'stok_jumlah' => $value['D'],
                            'created_at' => now(),
                        ];
                    }
                }
                if (count($insert) > 0) {
                    // insert data ke database, jika data sudah ada, maka diabaikan
                    StokModel::insertOrIgnore($insert);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil diimport'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada data yang diimport'
                ]);
            }
        }
        return redirect('/');
    }

    public function export_excel()
    {
        // ambil data barang yang akan di export
        $stok = StokModel::select('supplier_id', 'barang_id', 'user_id', 'stok_tanggal', 'stok_jumlah')
            ->orderBy('barang_id')
            ->with('supplier')
            ->with('barang')
            ->with('user')
            ->get();
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Supplier_id');
        $sheet->setCellValue('C1', 'Barang_id');
        $sheet->setCellValue('D1', 'User_id');
        $sheet->setCellValue('E1', 'Stok_tanggal');
        $sheet->setCellValue('F1', 'Stok_Jumlah');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true); // bold header
        $no = 1; // nomor data dimulai dari 1
        $baris = 2; // baris data dimulai dari baris ke 2
        foreach ($stok as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->supplier->supplier_nama);
            $sheet->setCellValue('C' . $baris, $value->barang->barang_nama);
            $sheet->setCellValue('D' . $baris, $value->user->nama);
            $sheet->setCellValue('E' . $baris, $value->stok_tanggal);
            $sheet->setCellValue('F' . $baris, $value->stok_jumlah); 
            $baris++;
            $no++;
        }
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true); // set auto size untuk kolom
        }
        $sheet->setTitle('Data Stok'); // set title sheet
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Stok ' . date('Y-m-d H:i:s') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $writer->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        // Mengambil data stok dengan relasi yang diperlukan
        $stok = StokModel::select('supplier_id', 'barang_id', 'user_id', 'stok_tanggal', 'stok_jumlah')
            ->with('supplier')
            ->with('barang')
            ->with('user')
            ->get();

        // Menghasilkan PDF menggunakan data yang diambil
        $pdf = Pdf::loadView('stok.export_pdf', ['stok' => $stok])
            ->setPaper('a4', 'portrait') // Mengatur ukuran kertas dan orientasi
            ->setOption('isHtml5ParserEnabled', true) // Mengaktifkan parser HTML5
            ->setOption('isRemoteEnabled', true); // Mengizinkan akses asset remote seperti gambar

        // Menghasilkan PDF dan menampilkannya di browser
        return $pdf->stream('Data stok' . date('Y-m-d_His') . '.pdf');
    }
}
