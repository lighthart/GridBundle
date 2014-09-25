<?php
namespace Lighthart\GridBundle\Twig;

class SubcookieExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('subcookie', array(
                $this,
                'subcookieFilter'
            )) ,
        );
    }

    public function subcookieFilter($cookie, $tag, $delimiter = ';', $separator = ':')
    {
        $return = strrev(strstr(strrev(strstr(strrev(strstr(strrev($cookie) , strrev($tag) , true)) , $delimiter, true)) , $separator, true));
        return $return;
    }

    public function getName()
    {
        return 'subcookie_extension';
    }
}
