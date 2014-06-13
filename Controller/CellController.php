<?php

namespace Lighthart\GridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Lighthart\GridBundle\FormType\cellType;

class CellController extends Controller
{
    public function verifyAction( $class = null, $field = null, $id = null, $action = 'unspecified' ) {
        // This is a helper acton to make sure the
        // grid cell is configured properly

        $em = $this->getDoctrine()->getManager();
        $metadataFactory = $em->getMetadataFactory();

        $error = '';

        if ( !$class ) {
            $error .= 'Data class for grid cell not specified';
        }
        if ( !$field ) {
            $error .= 'Data field for grid cell not specified';
        }
        if ( !$id ) {
            $error .= 'Id for grid cell not specified' ;
        }

        $class = str_replace( '_', '\\', $class );

        try {
            $metadata = $metadataFactory->getMetadataFor( $class );
            if ( !in_array( $field, $metadata->getFieldNames() ) ) {
                $error .= 'No metadata for field: '.$field.' in class: '.$class;
            }
        } catch ( \Exception $ex ) {
            $error .= 'No metadata for class: '.$class;
        }

        if ( $metadata ) {
            return
            array(
                'class'    => $class    ,
                'metadata' => $metadata ,
            );
        } else {
            $logger = $this->get( 'logger' );
            $logger->error( 'Grid Cell error: '.$error.' in '.$action );
            return array();
        }

    }

    public function editAction( Request $request, $class = null, $field = null, $id = null ) {
        // This returns the input control for the cell
        // it is responsible for setting the data roles
        // that update reads

        $verity = $this->verifyAction( $class );
        if ( $verity ) {
            $class    = str_replace( '_', '\\', $class );
            $em       = $this->getDoctrine()->getManager();
            $metadata = $verity['metadata'];
            $assoc    = array_filter(
                $metadata->getAssociationMappings() ,
                function( $mapping ) use ( $metadata ) {
                    $mapping['fieldName'] ;
                }
            );

            if ( in_array($field, $assoc) ) {
                var_dump('Entity Selector');die;

                // for selectors here

            } else {

                $em = $this->getDoctrine()->getManager();
                $entity = $em->getRepository( $class )->findOneById( $id );
                $method = 'get'.ucfirst( $field );
                $form = $this->createForm(
                    'cell',
                    $entity->{$method}(),
                    array(
                        'attr'  =>
                        array(
                            'data-role-class'     => $class ,
                            'data-role-field'     => $field ,
                            'data-role-entity-id' => $id    ,
                        )
                    )
                )->createView();

                return $this->render(
                    'LighthartGridBundle:Cell:edit.html.twig'
                    , array(
                        'form' => $form
                    )
                );
            }
        } else {
            return $this->render( 'LighthartGridBundle:Cell:configerror.html.twig' );
        }
    }

    public function updateAction( Request $request, $class = null, $field = null, $id = null ) {
        // This function, post only, reads data roles
        // and persists the data

        // $logger = $this->get( 'logger' );
        // $logger->error( 'Gridcell Update: '.$class.' / '.$field.' / '.$id );

        $verity = $this->verifyAction( $class );
        if ( $verity ) {
            $class = str_replace( '_', '\\', $class );
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository( $class )->findOneById( $id );
            if ( !$entity ) {
                $logger = $this->get( 'logger' );
                $logger->error( 'Bad Grid Cell Update: Entity '.$class.', id '.$id.' does not exist' );
            }
            $method = 'set'.ucfirst( $field );
            $data = $request->request->get( 'data' );
            $entity->{$method}( $data );
            $em->flush( $entity );
            // Switch to get for rendering
            $method = 'get'.ucfirst( $field );
            $value  = $entity->{$method}();

            return $this->render(
                'LighthartGridBundle:Cell:value.html.twig'
                , array(
                    'value'  => $value
                )
            );
        } else {
            return $this->render( 'LighthartGridBundle:Cell:configerror.html.twig' );
        }
    }

    public function valueAction( Request $request, $class = null, $field = null, $id = null ) {
        // This function just returns the cell value

        $verity = $this->verifyAction( $class );
        if ( $verity ) {
            $class =str_replace( '_', '\\', $class );
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository( $class )->findOneById( $id );

            if ( !$entity ) {
                $logger = $this->get( 'logger' );
                $logger->error( 'Bad Grid Cell value Request: Entity '.$class.', id '.$id.' does not exist' );
            }

            $method = 'get'.ucfirst( $field );
            $value  = $entity->{$method}();

            return $this->render(
                'LighthartGridBundle:Cell:value.html.twig'
                , array(
                    'value'  => $value
                )
            );
        } else {
            return $this->render( 'LighthartGridBundle:Cell:configerror.html.twig' );
        }
    }
}
