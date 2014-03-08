<?php

namespace Lighthart\GridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Lighthart\GridBundle\FormType\cellType;

class GridController extends Controller
{
    example
    public function indexAction( Request $request, $class = 'AcctKey' ) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository( 'LighthartSnapshotBundle:'.$class )->findAll();

        return $this->render(
            'LighthartGridBundle:Grid:grid.html.twig'
            , array(
                'entities' => $entities,
            )
        );
    }
}
