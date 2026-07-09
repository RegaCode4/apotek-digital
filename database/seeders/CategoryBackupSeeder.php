<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CategoryBackupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path('backup/categories_restore.sql');

        if (File::exists($path)) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::unprepared(
                File::get($path)
            );
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->command->info('Data categories berhasil di-restore dari categories_restore.sql');
        } else {
            $this->command->error("File backup tidak ditemukan: {$path}");
        }
    }
}
