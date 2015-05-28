<?php
namespace Lighthart\GridBundle\Twig;

class CSVToArrayExtension extends \Twig_Extension
{
    private $twig;

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('csv2Array', [
                $this,
                'csv2ArrayFilter',
            ]) ,
        ];
    }

    public function csv2ArrayFilter($string, $delimiter = ',', $separator = '"')
    {
        return str_getcsv($string,$delimiter, $separator);
    }

    public function booleanFilter($boolean, $delimiter = ',', $separator = '"')
    {

    }

    public function getName()
    {
        return 'csv2array_extension';
    }
}
