<?php

namespace Lighthart\GridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Lighthart\GridBundle\FormType\cellType;

class GridController extends Controller
{
    // example
    // have to be able to parse bundle name as well
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

        $backslash = str_replace( '_', '\\', $class );

        try {
            $metadata = $metadataFactory->getMetadataFor( $backslash );
        } catch ( \Exception $ex ) {
            $error .= 'No metadata for class: '.$backslash;
        }

        if ($class) {
            return
                array(
                      'class'    => $class    ,
                      'metadata' => $metadata ,
                      );
        } else {
            $logger = $this->get( 'logger' );
            $logger->error( 'Grid error: '.$error );
            return array();
        }

    }


    public function gridAction( Request $request, $class = 'AcctKey' ) {
        // basic entity grid

        $em        = $this->getDoctrine()->getManager();
        $verity = $this->verifyAction( $class );
        if ($verity) {
            $class = $verity['class'];
            $backslash = str_replace( '_', '\\', $class );
            $metadata = $verity['metadata'];
            $entities        = $em->getRepository( $backslash )->findAll();
            $fields          = $metadata->getFieldNames();

            return $this->render(
                'LighthartGridBundle:Grid:grid.html.twig'
                , array(
                    'entities' => $entities ,
                    'fields'   => $fields   ,
                    'class'    => $class    ,
                )
            );

        }

    }

}
