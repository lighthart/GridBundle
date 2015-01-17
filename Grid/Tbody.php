<?php

namespace Lighthart\GridBundle\Grid;

class Tbody extends Section
{
    public function __construct()
    {
        parent::__construct();
        $this->type = 'tbody';
    }
}
