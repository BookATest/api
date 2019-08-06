<?php

namespace App\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Date;

abstract class MigrationSeeder extends Migration
{
    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $db;

    /**
     * @var \Carbon\CarbonInterface
     */
    protected $now;

    /**
     * @var array
     */
    protected $records;

    /**
     * BaseSeeder constructor.
     */
    public function __construct()
    {
        $this->now = Date::now();
    }

    /**
     * @param array $args
     */
    abstract protected function addRecord(...$args);
}
