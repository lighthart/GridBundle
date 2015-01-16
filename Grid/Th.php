<?php

namespace Lighthart\GridBundle\Grid;


class Th extends Cell
{
    public function __construct($prop = [])
    {
        parent::__construct($prop);
        foreach ($prop as $k => $p) {
            $this->$k = $p;
        }

        if (!$this->value) {
            $this->value = $this->title;
        }
        $this->type = 'th';
    }
}
