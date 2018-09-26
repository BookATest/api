<?php

namespace App\Database\Migrations;

use Illuminate\Database\Migrations\Migration;

abstract class MigrationSeeder extends Migration
{
    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $db;

    /**
     * @var \Illuminate\Support\Carbon
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
        $this->now = now();
    }

    /**
     * @param array $args
     *
     * @return void
     */
    abstract protected function addRecord(...$args);
}
