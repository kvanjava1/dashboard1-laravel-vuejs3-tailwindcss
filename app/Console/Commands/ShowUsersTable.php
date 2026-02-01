<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ShowUsersTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:show-users-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the structure of the users table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Users table columns:');
        $this->newLine();

        // For SQLite, use PRAGMA table_info
        $columns = \DB::select("PRAGMA table_info(users)");

        $this->table(
            ['Column', 'Type', 'Not Null', 'Default', 'Primary Key'],
            collect($columns)->map(function ($column) {
                return [
                    $column->name,
                    $column->type,
                    $column->notnull ? 'NO' : 'YES',
                    $column->dflt_value ?? '',
                    $column->pk ? 'YES' : 'NO',
                ];
            })->toArray()
        );
    }
}
