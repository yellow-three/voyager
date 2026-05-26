<?php

namespace YellowThree\Voyager\Events;

use Illuminate\Queue\SerializesModels;
use YellowThree\Voyager\Models\DataType;

class BreadDataAdded
{
    use SerializesModels;

    public $dataType;

    public $data;

    public function __construct(DataType $dataType, $data)
    {
        $this->dataType = $dataType;

        $this->data = $data;

        event(new BreadDataChanged($dataType, $data, 'Added'));
    }
}
