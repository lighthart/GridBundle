<?php

namespace Lighthart\GridBundle\Grid;


class Td extends Cell
{
    public function __construct($prop = [])
    {
        parent::__construct($prop);
        foreach ($prop as $k => $p) {
            $this->$k = $p;
        }
        $this->type = 'td';
    }
}
