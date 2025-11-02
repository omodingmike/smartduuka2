<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateTable extends Command
{
    protected $signature = 'table:truncate {table}';

    protected $description = 'Command description';

    public function handle() : int
    {
        $table = $this->argument('table');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table($table)->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return 1;
    }
}
