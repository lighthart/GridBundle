<?php
namespace Lighthart\GridBundle\Twig;

class MoneyExtension extends \Twig_Extension
{
    private $twig;

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('money', [
                $this,
                'moneyFilter',
            ]) ,
        ];
    }

    public function moneyFilter($value, $html = true)
    {
        if ($html) {
            $positive = ($value >= 0);
            // Below should be in a config setting
            setlocale(LC_MONETARY, 'en_US');
            $value = money_format('%!(10.0n', $value);
            if ($positive) {
                return $this->twig->render('LighthartGridBundle:Money:positive.html.twig', ['value' => $value]);
            } else {
                return $this->twig->render('LighthartGridBundle:Money:negative.html.twig', ['value' => $value]);
            }
        } else {
        }
    }

    public function getName()
    {
        return 'money_extension';
    }
}
