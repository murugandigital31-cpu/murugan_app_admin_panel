<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixTodaysArrivalsTable extends Command
{
    protected $signature = 'fix:todays-arrivals-table';
    protected $description = 'Fix the todays_arrivals table column issues';

    public function handle()
    {
        $this->info('Checking todays_arrivals table structure...');
        
        // Get current columns
        $columns = Schema::getColumnListing('todays_arrivals');
        $this->info('Current columns: ' . implode(', ', $columns));
        
        // Check if poster_images exists (the problematic column)
        if (in_array('poster_images', $columns)) {
            $this->info('✓ poster_images column already exists');
        } else {
            $this->info('✗ poster_images column missing');
        }
        
        // Check other required columns
        $requiredColumns = [
            'arrival_branch_id',
            'main_poster', 
            'whatsapp_message_template',
            'whatsapp_enabled'
        ];
        
        $missingColumns = [];
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $columns)) {
                $missingColumns[] = $column;
                $this->error("✗ Missing column: {$column}");
            } else {
                $this->info("✓ Column exists: {$column}");
            }
        }
        
        if (empty($missingColumns)) {
            $this->info('All required columns exist!');
            
            // Mark the problematic migration as completed
            $migration = '2024_01_15_000003_update_todays_arrivals_table_for_posters';
            $exists = DB::table('migrations')->where('migration', $migration)->exists();
            
            if (!$exists) {
                $batch = DB::table('migrations')->max('batch') + 1;
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => $batch
                ]);
                $this->info("✓ Marked migration as completed: {$migration}");
            } else {
                $this->info("✓ Migration already marked as completed");
            }
        } else {
            $this->info('Some columns are missing. You may need to run a fix migration.');
        }
        
        return 0;
    }
}