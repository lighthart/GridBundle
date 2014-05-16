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
        $entities = $em->getRepository( 'LighthartAwesomeBundle:'.$class )->findAll();

        return $this->render(
            'LighthartGridBundle:Grid:grid.html.twig'
            , array(
                'entities' => $entities,
            )
        );
    }

    public function verifyAction( $class = null ) {
        // This is a helper action to make sure the
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
            $metadata=null;
            $error .= 'No metadata for class: '.$backslash;
        }

        if ( $metadata ) {
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


    public function gridAction( Request $request, $class = null ) {
        // basic entity grid

        $em     = $this->getDoctrine()->getManager();
        $verity = $this->verifyAction( $class );
        if ( $verity != array() ) {
            $class      = $verity['class'];
            $backslash  = str_replace( '_', '\\', $class );
            $metadata   = $verity['metadata'];
            $entities   = $em->getRepository( $backslash )
            ->findBy(
                    array() , // $where
                    array() , // $orderBy
                    10    // $limit
                    // 0    , // $offset
            );

            $fields     = array_filter( $metadata->getFieldNames(),
                function( $mapping ) use ( $metadata ) {
                    return 'datetime' != $metadata->getTypeOfColumn( $mapping );
                }
            );

            $dates      = array_filter( $metadata->getFieldNames(),
                function( $mapping ) use ( $metadata ) {
                    return 'datetime' == $metadata->getTypeOfColumn( $mapping );
                }
            );


            $oneToOne = array_filter(
                $metadata->getAssociationMappings() ,
                function( $mapping ) use ( $metadata ) {
                    return
                    $metadata::ONE_TO_ONE ==
                    $metadata->getAssociationMapping( $mapping['fieldName'] )['type'];
                }
            );

            $oneToMany  = array_filter(
                $metadata->getAssociationMappings() ,
                function( $mapping ) use ( $metadata ) {
                    return
                    $metadata::ONE_TO_MANY ==
                    $metadata->getAssociationMapping( $mapping['fieldName'] )['type'];
                }
            );

            $manyToOne  = array_filter(
                $metadata->getAssociationMappings() ,
                function( $mapping ) use ( $metadata ) {
                    return
                    $metadata::MANY_TO_ONE ==
                    $metadata->getAssociationMapping( $mapping['fieldName'] )['type'];
                }
            );

            $manyToMany  = array_filter(
                $metadata->getAssociationMappings() ,
                function( $mapping ) use ( $metadata ) {
                    return
                    $metadata::MANY_TO_MANY ==
                    $metadata->getAssociationMapping( $mapping['fieldName'] )['type'];
                }
            );

            // print_r( "<pre>" );

            // // var_dump($fields);

            // // array_map(
            // //     function( $mapping ) use ( $metadata ) {
            // //         // var_dump( $metadata->getTypeOfColumn( $mapping ) );
            // //         var_dump( $metadata->getFieldMapping( $mapping ) );
            // //     }
            // //     , $fields
            // // );
            // var_dump( '12M' );
            // var_dump( $oneToMany );
            // var_dump( 'M21' );
            // var_dump( $manyToOne );
            // die;

            // array_map(
            //     function( $mapping ) use ( $metadata ) {
            //         try {

            //             var_dump( $mapping );
            //             var_dump( $metadata->getAssociationMapping( $mapping['fieldName'] ) );
            //             print_r( "<br><br>" );
            //         } catch ( \Exception $ex ) {
            //             var_dump( 'Failed for: '.$mapping );
            //         }
            //     }
            //     , $assoc
            // );

            // die;

            return $this->render(
                'LighthartGridBundle:Grid:grid.html.twig' ,
                array(
                    'class'      => $class      ,
                    'fields'     => $fields     ,
                    'dates'      => $dates      ,
                    'oneToOne'   => $oneToOne   ,
                    'oneToMany'  => $oneToMany  ,
                    'manyToOne'  => $manyToOne  ,
                    'manyToMany' => $manyToMany ,
                    'entities'   => $entities   ,
                )
            );
        } else {
            return $this->render(
                'LighthartGridBundle:Grid:nogrid.html.twig' ,
                array(
                    'class'      => $class      ,
                )
            );
        }
    }
}
