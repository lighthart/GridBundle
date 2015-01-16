<?php

namespace Lighthart\GridBundle\Grid;


class Thead extends Section
{
    public function __construct()
    {
        parent::__construct();
        $this->type = 'thead';
    }
}
