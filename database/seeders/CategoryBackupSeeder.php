<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryBackupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path('backup/categories_restore.sql');
        
        if (\Illuminate\Support\Facades\File::exists($path)) {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            \Illuminate\Support\Facades\DB::unprepared(
                \Illuminate\Support\Facades\File::get($path)
            );
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->command->info('Data categories berhasil di-restore dari categories_restore.sql');
        } else {
            $this->command->error("File backup tidak ditemukan: {$path}");
        }
    }
}
