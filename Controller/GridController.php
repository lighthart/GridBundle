<?php

namespace Lighthart\GridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Lighthart\GridBundle\FormType\GridCellType;

class GridController extends Controller
{
    // example
    // public function indexAction( Request $request, $class = 'AcctKey' ) {
    //     $em = $this->getDoctrine()->getManager();
    //     $entities = $em->getRepository( 'LighthartSnapshotBundle:'.$class )->findAll();

    //     return $this->render(
    //         'LighthartGridBundle:Grid:grid.html.twig'
    //         , array(
    //             'entities' => $entities,
    //         )
    //     );
    // }

    public function verifyCellAction( $class = null, $field = null, $id = null, $action = '' ) {
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

        $logger = $this->get( 'logger' );
        $logger->error( 'Gridcell error: '.$error.' in '.$action );

        return $error;
    }

    public function cellEditAction( Request $request, $class = null, $field = null, $id = null ) {

        // $logger = $this->get( 'logger' );
        // $logger->error( 'Gridcell Edit: '.$class.' / '.$field.' / '.$id );

        $error = $this->verifyCellAction( $class, $field, $id, 'editAction' );
        $class = str_replace( '_', '\\', $class );
        if ( $error ) {
            return $this->render(
                'LighthartGridBundle:GridCell:configerror.html.twig'
                , array(
                )
            );
        } else {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository( $class )->findOneById( $id );
            $method = 'get'.ucfirst( $field );
            $form = $this->createForm(
                'gridcell',
                $entity->{$method}(),
                array(
                    'attr'  =>
                    array(
                        'data-role-class'     => $class,
                        'data-role-field'     => $field,
                        'data-role-entity-id' => $id,
                    )
                )
            )->createView();

            return $this->render(
                'LighthartGridBundle:GridCell:cellEdit.html.twig'
                , array(
                    'form' => $form
                )
            );
        }
    }

    public function cellUpdateAction( Request $request, $class = null, $field = null, $id = null ) {

        // $logger = $this->get( 'logger' );
        // $logger->error( 'Gridcell Update: '.$class.' / '.$field.' / '.$id );

        $error = $this->verifyCellAction( $class, $field, $id , 'updateAction' );
        $class = str_replace( '_', '\\', $class );
        if ( $error ) {
            return $this->render(
                'LighthartGridBundle:GridCell:configerror.html.twig'
                , array(
                )
            );
        } else {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository( $class )->findOneById( $id );
            if ( !$entity ) {
                $logger = $this->get( 'logger' );
                $logger->error( 'Bad Grid Cell Update: Entity '.$class.', id '.$id.' does not exist' );
            }
            $method = 'set'.ucfirst( $field );
            $data = $request->request->get( 'data' );
            $entity->{$method}( $data );
            $em->flush($entity);
            // Switch to get for rendering
            $method = 'get'.ucfirst( $field );
            $value  = $entity->{$method}();
        }

        return $this->render(
            'LighthartGridBundle:GridCell:cellValue.html.twig'
            , array(
                    'value'  => $value
            )
        );
    }

    public function cellValueAction( Request $request, $class = null, $field = null, $id = null ) {

        $error = $this->verifyCellAction( $class, $field, $id, 'valueAction' );
        $class =str_replace( '_', '\\', $class );
        if ( $error ) {
            return $this->render(
                'LighthartGridBundle:GridCell:configerror.html.twig'
                , array(
                )
            );
        } else {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository( $class )->findOneById( $id );

            if ( !$entity ) {
                $logger = $this->get( 'logger' );
                $logger->error( 'Bad Grid Cell value Request: Entity '.$class.', id '.$id.' does not exist' );
            }

            $method = 'get'.ucfirst( $field );
            $value  = $entity->{$method}();

            return $this->render(
                'LighthartGridBundle:GridCell:cellValue.html.twig'
                , array(
                    'value'  => $value
                )
            );
        }
    }

}
