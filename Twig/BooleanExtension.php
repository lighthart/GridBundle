<?php
namespace Lighthart\GridBundle\Twig;

class BooleanExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('boolean', array(
                $this,
                'booleanFilter'
            )) ,
        );
    }

    public function booleanFilter($boolean)
    {
        if ($boolean) {
            return 'True';
        } elseif (null === $boolean) {
            return 'Null';
        } else {
            return 'False';
        }
    }

    public function getName()
    {
        return 'boolean_extension';
    }
}
