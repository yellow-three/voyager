<?php

namespace YellowThree\Voyager\Events;

use Illuminate\Queue\SerializesModels;
use YellowThree\Voyager\Database\Schema\Table;

class TableAdded
{
    use SerializesModels;

    public $table;

    public function __construct(Table $table)
    {
        $this->table = $table;

        event(new TableChanged($table->name, 'Added'));
    }
}
