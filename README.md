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

Note: This query will be rewritten significantly, to fetch only partial entities based on the columns indicated in step 3.

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
            for 'entityId', 'parentId', 'title' and all elements of 'attr', enclosing any portion of that string
            with ~~ will grab the appropriate column(s) from the query and insert that text.  This interpolation
            will ignore html tags.  So the title text in the example above will add
            Biller:
            <District Name>
            <School Name>
            to the table header cell.

Step 4.  Add Actions.

        $gm->addAction(array(
            'icon' => 'fa-rocket',
            'route' => array(
                'student_show' => array(
                    'id' => '~t.id~'
                )
            ) ,
            'security' => function($result, $columns){
                return 'F' == $result[$columns['g.shortName']];
            },
            'attr' => array(
                'title' => 'Star3'
            )
        ));

Note: actions are rendered as <a> tags

    Features:
        'icon'          A font awesome icon for the button.  Note: Font Awesome not installed  Without
                        font-awesome, this feature puts a <span class="fa [icon]"></span> tag into the
                        <a> for the button
        'name'          Text for the button.  Works with icon, with icon being leftmost.  If no name is
                        specified, empty space is rendered so the button has some width
        'security'      A primitive, or an anonymous function.  If the value evaluates to true, the
                        button is rendered.  Default is true.  For the anonymous function, the result
                        tuple for the current row is sent as the first parameter, and an alias translation
                        table for the original alias and the new alias in the query is sent as the second
                        parameter.  The tildes function as columns forming indexes, to base the appearance
                        on portions of the tuple.
        'severity'      adds a bootstrap class such as btn-primary to the <a>
        'attr'          sets the html attributes
        'title'         sets the title of the <a>
        'route'         Either raw text for the route, or an array of data with the key being a symfony
                        alias for a route, and the value being an array of parameters for said route


Step 5.  Hydrate the grid and pass it to a twig

        $gm->hydrateGrid($request);
        return $this->render('MesdOrmedBundle:Test:test3.html.twig', array(
            'grid' => $gm->getGrid() ,
        ));

Note: A lot of information is rendered with the table, including classnames and ids for other processing via javascript or other ajax.

Step 5.  In your twig:
        {% include 'LighthartGridBundle:Grid:grid.html.twig' %}

        or

        {% embed 'LighthartGridBundle:Grid:grid.html.twig' %}
        < over-write certain blocks >
        {% endembed %}
