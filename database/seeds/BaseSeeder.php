<?php

declare(strict_types=1);

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Seeder;

abstract class BaseSeeder extends Seeder
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
     *
     * @param \Illuminate\Database\DatabaseManager $db
     */
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
        $this->now = now();
    }

    /**
     * @param array $args
     */
    abstract protected function addRecord(...$args);
}
