<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenjualanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['penjualan_id'=> 1,'user_id'=> 3,'pembeli'=> 'Iyazuz','penjualan_kode'=> 'PJ001','penjualan_tanggal'=> '2024-09-11',], 
            ['penjualan_id'=> 2,'user_id'=> 3,'pembeli'=> 'Zidan','penjualan_kode'=> 'PJ002','penjualan_tanggal'=> '2024-09-11',], 
            ['penjualan_id'=> 3,'user_id'=> 3,'pembeli'=> 'Fakhar','penjualan_kode'=> 'PJ003','penjualan_tanggal'=> '2024-09-11',], 
            ['penjualan_id'=> 4,'user_id'=> 3,'pembeli'=> 'Reza','penjualan_kode'=> 'PJ004','penjualan_tanggal'=> '2024-09-12',], 
            ['penjualan_id'=> 5,'user_id'=> 3,'pembeli'=> 'Alfito','penjualan_kode'=> 'PJ005','penjualan_tanggal'=> '2024-09-12',], 
            ['penjualan_id'=> 6,'user_id'=> 3,'pembeli'=> 'Dianova','penjualan_kode'=> 'PJ006','penjualan_tanggal'=> '2024-09-12',], 
            ['penjualan_id'=> 7,'user_id'=> 3,'pembeli'=> 'Naufal','penjualan_kode'=> 'PJ007','penjualan_tanggal'=> '2024-09-13',], 
            ['penjualan_id'=> 8,'user_id'=> 3,'pembeli'=> 'Putra','penjualan_kode'=> 'PJ008','penjualan_tanggal'=> '2024-09-13',], 
            ['penjualan_id'=> 9,'user_id'=> 3,'pembeli'=> 'Mita','penjualan_kode'=> 'PJ009','penjualan_tanggal'=> '2024-09-12',], 
            ['penjualan_id'=> 10,'user_id'=> 3,'pembeli'=> 'Oktavia','penjualan_kode'=> 'PJ010','penjualan_tanggal'=> '2024-09-12',], 
        ];
        DB::table('t_penjualan')->insert($data);
    }
}
