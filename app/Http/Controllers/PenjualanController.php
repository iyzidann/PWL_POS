<?php

namespace App\Http\Controllers;
use App\Models\BarangModel;
use App\Models\DetailPenjualanModel;
use App\Models\PenjualanModel;
use App\Models\TransaksiDetailModel;
use App\Models\TransaksiModel;
use App\Models\UserModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;

class PenjualanController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Penjualan',
            'list' => ['Home', 'Penjualan']
        ];
        $page = (object) [
            'title' => 'Daftar penjualan yang terdaftar dalam sistem'
        ];
        $activeMenu = 'penjualan'; // set menu yang sedang aktif

        return view('Penjualan.index', ['breadcrumb' => $breadcrumb, 'page' => $page, 'activeMenu' => $activeMenu]);
    }

    public function list(Request $request)
    {
        $penjualan = PenjualanModel::select('penjualan_id', 'user_id', 'pembeli', 'penjualan_kode', 'penjualan_tanggal')
            ->with('user');
        // Filter data user berdasarkan level_id
        return DataTables::of($penjualan)
            ->addIndexColumn() // menambahkan kolom index / no urut (default nama kolom:DT_RowIndex)
            ->addColumn('aksi', function ($penjualan) { // menambahkan kolom aksi
                $btn = '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                    '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                    '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/penjualan/' . $penjualan->penjualan_id .
                    '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi']) // memberitahu bahwa kolom aksi adalah html
            ->make(true);
    }

    public function create_ajax()
    {
        $user = UserModel::select('user_id', 'nama')->get();
        $barang = BarangModel::select('barang_id', 'barang_nama')->get();
        return view('penjualan.create_ajax')->with('user', $user)->with('barang', $barang);
    }

    public function store_ajax(Request $request)
    {
        //cek apakah request berupa ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'user_id' => 'required|integer',
                'pembeli' => 'required|string|max:100',
                'penjualan_kode' => 'required|string|max:20|min:3|unique:t_penjualan,penjualan_kode',
                'penjualan_tanggal' => 'required|date',
                'penjualan_details.*.barang_id' => 'required|integer',
                'penjualan_details.*.jumlah' => 'required|integer',
                'penjualan_details.*.harga' => 'required|numeric',
            ];
            //use Illuminate\Support\Facades\Validator;
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false, //response status, false: error/gagal, true: berhasil
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors(), //pesan error validasi
                ]);
            }
            // Create the transaction
            $penjualan = penjualanModel::create($request->only(['user_id', 'pembeli', 'penjualan_kode', 'penjualan_tanggal']));
            // Add transaction details
            foreach ($request->penjualan_details as $detail) {
                DetailPenjualanModel::create([
                    'penjualan_id' => $penjualan->penjualan_id,
                    'barang_id' => $detail['barang_id'],
                    'harga' => $detail['harga'],
                    'jumlah' => $detail['jumlah'],
                ]);
            }
            return response()->json([
                'status' => true,
                'message' => 'penjualan berhasil disimpan'
            ]);
        }
    }

    public function edit_ajax(string $id)
    {
        $penjualan = penjualanModel::with(['penjualanDetail.barang'])->find($id);
        $user = UserModel::select('user_id', 'nama')->get();
        $barang = BarangModel::select('barang_id', 'barang_nama')->get();
        // dd($penjualan->user);
        return view('penjualan.edit_ajax', ['penjualan' => $penjualan, 'user' => $user, 'barang' => $barang]);
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'user_id' => 'required|integer',
                'pembeli' => 'required|string|max:100',
                'penjualan_kode' => 'required|string|max:20|min:3|unique:t_penjualan,penjualan_kode,' . $id . ',penjualan_id',
                'penjualan_tanggal' => 'required|date',
                'penjualan_details.*.barang_id' => 'required|integer',
                'penjualan_details.*.jumlah' => 'required|integer',
                'penjualan_details.*.harga' => 'required|numeric',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors(),
                ]);
            }
            $penjualan = penjualanModel::find($id);
            if (!$penjualan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan',
                ]);
            }
            // Update main penjualan data
            $penjualan->update($request->only(['user_id', 'pembeli', 'penjualan_kode', 'penjualan_tanggal']));
            // Process transaction details (updating, deleting, and adding new)
            $existingDetailIds = $penjualan->penjualanDetail->pluck('detail_id')->toArray();
            $incomingDetailIds = array_keys($request->penjualan_details);
            // Delete removed details
            $toDelete = array_diff($existingDetailIds, $incomingDetailIds);
            DetailPenjualanModel::destroy($toDelete);
            // Update or create details
            foreach ($request->penjualan_details as $detailId => $detailData) {
                if (in_array($detailId, $existingDetailIds)) {
                    // Update existing detail
                    DetailPenjualanModel::where('detail_id', $detailId)->update($detailData);
                } else {
                    // Add new detail
                    DetailPenjualanModel::create([
                        'penjualan_id' => $penjualan->penjualan_id,
                        'barang_id' => $detailData['barang_id'],
                        'harga' => $detailData['harga'],
                        'jumlah' => $detailData['jumlah'],
                    ]);
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'Data penjualan berhasil diubah.',
            ]);
        }
    }

    public function confirm_ajax(string $id)
    {
        $penjualan = penjualanModel::with(['user', 'penjualanDetail.barang'])->find($id);
        return view('penjualan.confirm_ajax', ['penjualan' => $penjualan]);
    }

    public function delete_ajax(Request $request, string $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $penjualan = penjualanModel::find($id);
            if ($penjualan) {
                $penjualan->delete();
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
        }
        return redirect('/');
    }

    public function show_ajax(string $id)
    {
        $penjualan = penjualanModel::with(['user', 'penjualanDetail.barang'])->find($id);
        return view('penjualan.show_ajax', ['penjualan' => $penjualan]);
    }

    public function import()
    {
        return view('penjualan.import');
    }

    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                // validasi file harus xls atau xlsx, max 1MB
                'file_penjualan' => ['required', 'mimes:xlsx', 'max:1024']
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }
            $file = $request->file('file_penjualan'); // ambil file dari request
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
                            'user_id' => $value['A'],
                            'pembeli' => $value['B'],
                            'penjualan_kode' => $value['C'],
                            'penjualan_tanggal' => now(),
                            'created_at' => now(),
                        ];
                    }
                }
                if (count($insert) > 0) {
                    // insert data ke database, jika data sudah ada, maka diabaikan
                    PenjualanModel::insertOrIgnore($insert);
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
        $penjualan = penjualanModel::select('user_id', 'penjualan_kode', 'penjualan_tanggal', 'pembeli')
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
        foreach ($penjualan as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->user->nama);
            $sheet->setCellValue('C' . $baris, $value->pembeli);
            $sheet->setCellValue('D' . $baris, $value->penjualan_kode);
            $sheet->setCellValue('E' . $baris, $value->penjualan_tanggal);
            $baris++;
            $no++;
        }
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true); // set auto size untuk kolom
        }
        $sheet->setTitle('Data Penjualan'); // set title sheet
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
    } // end function export excel

    public function export_pdf()
    {
        $penjualan = PenjualanModel::select('user_id', 'penjualan_kode', 'penjualan_tanggal', 'pembeli')
            ->with('user')
            ->get();
        $pdf = Pdf::loadView('penjualan.export_pdf', ['penjualan' => $penjualan]);
        $pdf->setPaper('a4', 'potrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();
        return $pdf->stream('Data penjualan' . date('Y-m-d H:i:s') . '.pdf');
    }
    public function export_detail_pdf($id)
    {
        $penjualan = PenjualanModel::with(['user', 'penjualanDetail.barang'])->find($id);
        // dd($penjualan->penjualanDetail);
        $pdf = Pdf::loadView('penjualan.export_detail_pdf', ['penjualan' => $penjualan]);
        $pdf->setPaper('a4', 'potrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();
        return $pdf->stream('Data penjualan' . date('Y-m-d H:i:s') . '.pdf');
    }
}
