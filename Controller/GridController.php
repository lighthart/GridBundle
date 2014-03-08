<?php

namespace Lighthart\GridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Lighthart\GridBundle\FormType\cellType;

class GridController extends Controller
{
    // example
    // have to be able toparse bundle name as well
    public function indexAction( Request $request, $class = 'AcctKey' ) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository( 'BlackfishBudgetSnapshotBundle:'.$class )->findAll();

        return $this->render(
            'LighthartGridBundle:Grid:grid.html.twig'
            , array(
                'entities' => $entities,
            )
        );
    }

    public function verifyAction( $class = null ) {
        // This is a helper acton to make sure the
        // grid cell is configured properly

        $em = $this->getDoctrine()->getManager();
        $metadataFactory = $em->getMetadataFactory();

        $error = '';

        if ( !$class ) {
            $error .= 'Data class for grid cell not specified';
        }

        $class = str_replace( '_', '\\', $class );

        try {
            $metadata = $metadataFactory->getMetadataFor( $class );
        } catch ( \Exception $ex ) {
            $error .= 'No metadata for class: '.$class;
        }

        $logger = $this->get( 'logger' );
        $logger->error( 'Grid error: '.$error );

        return $class;
    }


    public function gridAction( Request $request, $class = 'AcctKey' ) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository( $this->verifyAction( $class ) )->findAll();

        return $this->render(
            'LighthartGridBundle:Grid:grid.html.twig'
            , array(
                'entities' => $entities,
            )
        );
    }

}
