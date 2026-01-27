<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AppSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:app-seed';


    protected $description = 'Command description';


    public function handle() : void
    {
        $databaseName = 'smartduuka_main';
        $query = "SELECT count(*) FROM pg_database WHERE datname = '$databaseName'";
        $exists = DB::connection('pgsql')->select($query)[0]->count;

        if (!$exists) {
            DB::connection('pgsql')->statement("CREATE DATABASE $databaseName");
            $this->info("Database '$databaseName' created successfully.");
        } else {
            $this->info("Database '$databaseName' already exists.");
        }
    }
}
