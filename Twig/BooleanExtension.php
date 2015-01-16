<?php
namespace Lighthart\GridBundle\Twig;

class BooleanExtension extends \Twig_Extension
{
    private $twig;

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('boolean', [
                $this,
                'booleanFilter',
            ]) ,
        ];
    }

    public function booleanFilter($boolean, $html = true)
    {
        if ($html) {
            if ($boolean) {
                return $this->twig->render('LighthartGridBundle:Boolean:true.html.twig');
            } elseif (null === $boolean) {
                return $this->twig->render('LighthartGridBundle:Boolean:null.html.twig');
            } else {
                return $this->twig->render('LighthartGridBundle:Boolean:false.html.twig');
            }
        } else {
            if ($boolean) {
                return 'True';
            } elseif (null === $boolean) {
                return 'Null';
            } else {
                return 'False';
            }
        }
    }

    public function getName()
    {
        return 'boolean_extension';
    }
}
