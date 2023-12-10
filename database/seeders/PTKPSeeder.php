<?php

namespace Database\Seeders;

use App\Models\PTKP;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PTKPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            $data = [
                [
                    'criteria'    => 'Tidak Kawin Tanpa Tanggungan',
                    'status'      => 'TK/0',
                    'ptkp_amount' => 54000000,
                ],
                [
                    'criteria'    => 'Tidak Kawin 1 Orang Tanggungan',
                    'status'      => 'TK/1',
                    'ptkp_amount' => 58500000,
                ],
                [
                    'criteria'    => 'Tidak Kawin 2 Orang Tanggungan',
                    'status'      => 'TK/2',
                    'ptkp_amount' => 63000000,
                ],
                [
                    'criteria'    => 'Tidak Kawin 3 Orang Tanggungan',
                    'status'      => 'TK/3',
                    'ptkp_amount' => 67500000,
                ],
                [
                    'criteria'    => 'Kawin Tanpa Tanggungan',
                    'status'      => 'K/0',
                    'ptkp_amount' => 58500000,
                ],
                [
                    'criteria'    => 'Kawin 1 Orang Tanggungan',
                    'status'      => 'K/1',
                    'ptkp_amount' => 63000000,
                ],
                [
                    'criteria'    => 'Kawin 2 Orang Tanggungan',
                    'status'      => 'K/2',
                    'ptkp_amount' => 67500000,
                ],
                [
                    'criteria'    => 'Kawin 3 Orang Tanggungan',
                    'status'      => 'K/3',
                    'ptkp_amount' => 72000000,
                ],
                [
                    'criteria'    => 'Kawin Penghasilan Istri Digabung Dengan Suami Tanpa Tanggungan',
                    'status'      => 'K/I/0',
                    'ptkp_amount' => 112500000,
                ],
                [
                    'criteria'    => 'Kawin Penghasilan Istri Digabung Dengan Suami 1 Orang Tanggungan',
                    'status'      => 'K/I/1',
                    'ptkp_amount' => 117000000,
                ],
                [
                    'criteria'    => 'Kawin Penghasilan Istri Digabung Dengan Suami 2 Orang Tanggungan',
                    'status'      => 'K/I/2',
                    'ptkp_amount' => 121500000,
                ],
                [
                    'criteria'    => 'Kawin Penghasilan Istri Digabung Dengan Suami 3 Orang Tanggungan',
                    'status'      => 'K/I/3',
                    'ptkp_amount' => 126000000,
                ],
            ];
            foreach ($data as $row) {
                PTKP::create($row);
            }
            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            dd($err->getMessage());
        }
    }
}
