<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicineBackupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path('backup/medicines_percat.sql');
        
        if (\Illuminate\Support\Facades\File::exists($path)) {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            \Illuminate\Support\Facades\DB::unprepared(
                \Illuminate\Support\Facades\File::get($path)
            );
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->command->info('Data medicines berhasil di-restore dari medicines_percat.sql');
        } else {
            $this->command->error("File backup tidak ditemukan: {$path}");
        }
    }
}
