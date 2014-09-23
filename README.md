This is a a grid bundle for the Symfony framework.


The twigs read the application's globals:
gridTwig:       extended in grid
gridBlock:      block in the gridTwig to put the grid in


Step 1.  Write a query.

The alias for the root entity must be 'root', but otherwise, you may use any other entities you want.

Eg:
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('ApplicationBundle:Student');
        $root = 'root';
        $qb = $rep->createQueryBuilder($root);
        $qb->join($root . '.snowflake', 'f');
        $qb->join('f.school', 's');
        $qb->join('s.district', 'd');
        $qb->join('root.biller', 'b');
        $qb->addOrderBy('f.lastName');
        $qb->addOrderBy('f.firstName');

Step 2.  Initialize your grid.

        $query = $qb->getQuery();
        $gm = $this->get('grid.maker');
        $gm->initialize(array(
            'table' => 'table table-bordered table-condensed table-hover table-striped',
            'html' => true,
            'massAction' => true,
        ));
        $gm->setQueryBuilder($qb);

    Features:
        'table'         classes applied to table
        'html'          indicates the table is html
        'massAction'    if evaluates to true, mass action column is included


Step 3.  Start adding fields/columns.

        $gm->addField('b', 'shortName', array(
            // 'search' => false,
            // 'filter' => false,
            'attr' => array(
                'class' => '',
                'entity_id' => true,
                'html' => true
            ) ,
            'title' => 'Biller:<br/> ~d.shortName~<br/>~s.shortName~',
        ));

    Features:
        'search'    evaluating to true will add the column to the fields searched via the global search
                    It also specifies the kind of filter, eg date, number or string
        'filter'    evaluating to true will add a filter box to the column which restricts only on the current column.
                    It also specifies the kind of filter, eg date, number or string
        'attr'      sets the html attributes
        'html'      indicates the title should be interpreted as html, ie, the raw filter is applied
        'entityId'  evaluating to true stores the entity id on the individual cell.
                    This is mostly useful for javascript related associated entities
        'parentId'  evaluating to true stores the parent id on the row header.
                    This is mostly useful for javascript related associated entities
        'title'     sets the title of the cell
        'hidden'    evaluating to true hides the column


        tildes:
            for 'entityId', 'parentId' and 'title', enclosing any portion of that string with ~~ will
            grab the appropriate column(s) from the query and insert that text.  This interpolation
            will ignore html tags.  So the title text in the example above will add
            Biller:
            <District Name>
            <School Name>
            to the table header cell.

Step 4. Hydrate the grid and pass it to a twig

        $gm->hydrateGrid($request);
        return $this->render('MesdOrmedBundle:Test:test3.html.twig', array(
            'grid' => $gm->getGrid() ,
        ));

Step 5. In your twig:
        {% include 'LighthartGridBundle:Grid:grid.html.twig' %}

        or

        {% embed 'LighthartGridBundle:Grid:grid.html.twig' %}
        < over-write certain blocks >
        {% endembed %}
