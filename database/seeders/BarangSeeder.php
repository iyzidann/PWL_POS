<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Kategori 1: Elektronik
            [
                'barang_id' => 1,
                'kategori_id' => 1,
                'barang_kode' => 'BRG001',
                'barang_nama' => 'Laptop',
                'harga_beli' => 6000000,
                'harga_jual' => 7000000,
            ],
            [
                'barang_id' => 2,
                'kategori_id' => 1,
                'barang_kode' => 'BRG002',
                'barang_nama' => 'Headphone',
                'harga_beli' => 400000,
                'harga_jual' => 500000,
            ],
            [
                'barang_id' => 3,
                'kategori_id' => 1,
                'barang_kode' => 'BRG003',
                'barang_nama' => 'Monitor',
                'harga_beli' => 1500000,
                'harga_jual' => 2000000,
            ],

            // Kategori 2: Peralatan Rumah Tangga
            [
                'barang_id' => 4,
                'kategori_id' => 2,
                'barang_kode' => 'BRG004',
                'barang_nama' => 'Sofa',
                'harga_beli' => 3500000,
                'harga_jual' => 4000000,
            ],
            [
                'barang_id' => 5,
                'kategori_id' => 2,
                'barang_kode' => 'BRG005',
                'barang_nama' => 'Meja',
                'harga_beli' => 1200000,
                'harga_jual' => 1500000,
            ],
            [
                'barang_id' => 6,
                'kategori_id' => 2,
                'barang_kode' => 'BRG006',
                'barang_nama' => 'Kursi',
                'harga_beli' => 800000,
                'harga_jual' => 1000000,
            ],

            // Kategori 3: Pakaian
            [
                'barang_id' => 7,
                'kategori_id' => 3,
                'barang_kode' => 'BRG007',
                'barang_nama' => 'Kaos',
                'harga_beli' => 40000,
                'harga_jual' => 50000,
            ],
            [
                'barang_id' => 8,
                'kategori_id' => 3,
                'barang_kode' => 'BRG008',
                'barang_nama' => 'Jeans',
                'harga_beli' => 120000,
                'harga_jual' => 150000,
            ],
            [
                'barang_id' => 9,
                'kategori_id' => 3,
                'barang_kode' => 'BRG009',
                'barang_nama' => 'Jaket',
                'harga_beli' => 170000,
                'harga_jual' => 200000,
            ],

            // Kategori 4: Makanan & Minuman
            [
                'barang_id' => 10,
                'kategori_id' => 4,
                'barang_kode' => 'BRG010',
                'barang_nama' => 'Soda',
                'harga_beli' => 5000,
                'harga_jual' => 8000,
            ],
            [
                'barang_id' => 11,
                'kategori_id' => 4,
                'barang_kode' => 'BRG011',
                'barang_nama' => 'Mie Instan',
                'harga_beli' => 2000,
                'harga_jual' => 3000,
            ],
            [
                'barang_id' => 12,
                'kategori_id' => 4,
                'barang_kode' => 'BRG012',
                'barang_nama' => 'Biskuit',
                'harga_beli' => 10000,
                'harga_jual' => 15000,
            ],

            // Kategori 5: Alat Tulis
            [
                'barang_id' => 13,
                'kategori_id' => 5,
                'barang_kode' => 'BRG013',
                'barang_nama' => 'Buku',
                'harga_beli' => 3000,
                'harga_jual' => 5000,
            ],
            [
                'barang_id' => 14,
                'kategori_id' => 5,
                'barang_kode' => 'BRG014',
                'barang_nama' => 'Pensil',
                'harga_beli' => 1000,
                'harga_jual' => 2000,
            ],
            [
                'barang_id' => 15,
                'kategori_id' => 5,
                'barang_kode' => 'BRG015',
                'barang_nama' => 'Pulpen',
                'harga_beli' => 2000,
                'harga_jual' => 4000,
            ],
        ];
        DB::table('m_barang')->insert($data);
    }
}
