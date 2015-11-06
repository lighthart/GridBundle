## GridBundle
This is a a grid bundle for the Symfony framework.


The twigs read the application's globals:

gridTwig:       extended in grid

gridBlock:      block in the gridTwig to put the grid in

### Configure:

#### Step 1: Register Bundle.

In AppKernel.php:

    public function registerBundles()
    {
        $bundles = array(
                ...
            new Lighthart\GridBundle\LighthartGridBundle(),
        );
    ...
    }

#### Step 2:  Wire up assetic.

In config.yml:

        assetic:
        ...
            bundles:
                        ...
                - LighthartGridBundle
            ...

If aggregate fields are needed in postgres, the operator must be added.

        doctrine:
            orm:
            #...
                        dql:
                            string_functions:
                                ARRAYAGG: Lighthart\GridBundle\DQL\Postgres\ArrayAgg
                                ARRAYAGGDISTINCT: Lighthart\GridBundle\DQL\Postgres\ArrayAggDistinct

This is not currently supported for any platform other than postgres.

### Make a Grid:

#### Step 1:  Write a query.

The table alias for the root entity must be 'root'. On all joined tables, you
may use any alias you would like.

```php
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
```

> **Note**: This query will be rewritten significantly, to fetch only partial
> entities based on the columns indicated in step three.

#### Step 2: Initialize your grid.

```php
    $query = $qb->getQuery();
    $gm = $this->get('lg.maker');
    $gm->initialize(array(
        'table' => 'table table-bordered table-condensed table-hover table-striped',
        'html' => true,
        'massAction' => true,
        'request' => $request
    ));
    $gm->setQueryBuilder($qb);
```

> **Note**: Passing the request is optional, but makes flag cookies available in
> controller action as $Request->query parameters for dynamic grid layouts

**Grid Configuration**:

    table:          Classes applied to table

    html:           Indicates if the table is html

    massAction:     If evaluates to true, mass action column is included


#### Step 3: Start adding fields/columns.

```php
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
```

**Field Configuration**:

    search:         Setting to true will add the column to the fields searched via the
                    global search tool. Optionally, you can also specify the kind of search
                    filter to use on this filed. Valid types are: date, number, and string.
                    If a true is used, the field will be highlighted on search without
                    modifying query.  This is useful for compound fields such as made with
                    the otherGroups config (below)

    filter:         Setting to true will add a search filter box to the column header which
                    only searches on this field. Optionally, you can also specify the kind
                    of search filter to use on this filed. Valid types are: date, number,
                    and string.  If filter is not set, it will default to false, and no
                    input box will be displayed.

    sort:           true enables column sort.

    attr:           Sets the html attributes.

    html:           Indicates the title should be interpreted as html, i.e., the twig raw
                    filter is applied.

    entityId:       Evaluating to true stores the entity id on the individual cell. This
                    is mostly useful for javascript related associated entities.

    parentId:       Evaluating to true stores the parent id on the row header. This is
                    mostly useful for javascript related associated entities.

    title:          Sets the title of the column.  Note: the title field sets the on-hover
                    title.

    header:         Sets the display title of the column.  Note: the title field sets the
                    on-hover title.  If not set the header is the same as the title.

    hidden:         Evaluating to true hides the column.

    filterHidden:   Filter on a column which is hidden.  Useful for concatenating fields
                    filtering the end result, such as lastname/firstname combos.  In this
                    case hide the firstname column and add 'filterHidden' =>
                    'alias.firstname' to the lastname column.  Hiddens columns must be
                    set filterable.  Item must be an array or semicolon-separated list

    security:       A primitive boolean, or an anonymous function.  If the value evaluates
                    to true, the button is rendered.  Default is true.  For the anonymous
                    function, the result tuple for the current row is sent as the first
                    parameter, and an alias translation table for the original alias and
                    the new alias in the query is sent as the second parameter. The
                    tildes (see below) function as columns forming indexes, to base the
                    appearance on portions of the tuple.

    value:          Multiple fields may be added using tildes, as with example for title.
                    Passing an array (instead of a string) of values takes the first
                    truthy value, similar to a postgres concat operator.

    boolean:        Field is a boolean, will render with boolean twigs.  If value is a
                    string boolean value will be determined by a == comparison.  If value
                    is an anonymous function, the result tuple for the current row is
                    sent as the first parameter, and an alias translation table for the
                    original alias and the new alias in the query is sent as the second
                    parameter, and the function should return the boolean value.  For
                    example:
    trunc:          get first value of trunc characters
```php
    $gm->addField('consentStatus', 'shortName', [
        'filter'   => 'number',
        'entityId' => true,
        'boolean'  => function($result, $columns){
            return
                ('Agreed' == $result[$columns['consentStatus.shortName']])
                ? true
                : (
                    ('Declined' == $result[$columns['consentStatus.shortName']])
                    ? false
                    : null
                    )
            ;
        },
        // 'hidden'   => true,
        'attr'     => [
            'class' => '',
        ],
        'title' => 'Consent',
        'value' => ['~consentStatus.shortName~', null]
    ]);
```

    dql:            Adds a pseudo column which returns the result of the DQL indicated.
                    The second parameter should be an empty string.  This allows the use
                    of raw dql functions.  For example:
```php
        $gm->addField('BILLERCONSENT', '', [
            'dql'      => 'arrayAgg(
                                concat(
                                    concat(biller.shortName,\' : \'),
                                    consentStatus.shortName
                                      )
                                   ) AS BILLERCONSENT'
                           ,
            'attr'     => [
                'class' => '',
            ],
            'title' => 'Consent',
        ]);
```

    group:          Column is part of a group.  Will automatically put arrayAgg in as
                    function, grouping the field indicated.  Fields which are part of DQL
                    aggregates should be grouped on something.

    count:          Column is part of a group.  If true, will automatically put count in as
                    function on the indicated field.  Id is normally a good choice.

    otherGroup:     Column displays a group of composite fields.  Field must be an array
                    of fields which will me recursively mapped through a DQL concat
                    function.  To display a , use a double semicolon as a display element.
                    If the field is alsoa  filter, the otherGroup display elements will
                    be automatically added.

```php
            $gm->addField('subordinate_othergroup', 'otherGroup', [
            'filter'   => 'string',
            // automatically has:
            // 'filterHidden' => 'subordinate.firstName;subordinate.lastName',
            'header' => 'Subordinate',
            'title' => 'Subordinate',
            'otherGroup' => ['subordinate.lastName','\';; \'','subordinate.firstName'],
        ]);

```

**Use of tildes**:

For 'value', entityId', 'parentId', 'title' and all elements of 'attr', enclosing any
portion of that string with tildes (~) will grab the appropriate column(s) from
the query and insert that text.  This interpolation will ignore html tags.  The
title text in the example above will add:

```html
    Biller:
    <District Name>
    <School Name>
    to the table header cell.
```

#### Step 4:  Add Actions.

```php
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
```

Any number of buttons may be rendered.  By default, if there are more than 4, a fold-up
button will be automatically rendered.  This can be controlled by overwriting the
'buttonNum' value in the twigs

> **Note**: actions are rendered as <a> tags

**Action  Configuration**:

    icon:       A font awesome icon for the button.  Note: Font Awesome is not installed
                as part of this bundle. Without Font Awesome, this feature puts a
                <span class="fa [icon]"></span> tag into the <a> for the action button.

    name:       Text for the button.  Works with icon, with icon being leftmost.  If no
                name is specified, empty space is rendered so the button has some width.

    security:   A primitive boolean, or an anonymous function.  If the value evaluates to
                true, the button is rendered.  Default is true.  For the anonymous
                function, the result tuple for the current row is sent as the first
                parameter, and an alias translation table for the original alias and the
                new alias in the query is sent as the second parameter.  The tildes
                function as columns forming indexes, to base the appearance on portions
                of the tuple.

    severity:   Adds a bootstrap class such as btn-primary to the <a>

    attr:       Sets the html attributes.  Note: the title key of the 'attr' field sets
                the hover-over title.

    route:      Either raw text for the route, or an array of data with the key being a
                symfony alias for a route, and the value being an array of parameters for
                said route.

> **Warning**: Why is my button missing? If the router fails, the exception will be
> caught and the button silently omitted.


#### Step 5:  Hydrate the grid and pass it to a twig.

```php
    $gm->hydrateGrid($request);
    return $this->render('ApplicationBundle:Test:test3.html.twig', array(
        'grid'      => $gm->getGrid() ,
        'flags'     => $flags,
        'newPath'   => $url,
        'export'    => 1000,
        'noResults' => 'No Results'
        'addForm'   => $addForm->createView(),
        'addTitle'  => 'Label for AddForm',
    ));
```

**Render Configuration**:

    newPath:    Path for 'new' to add something to the grid.

    newIcon:    A font awesome icon for the new button.  Note: Font Awesome is not
                installed as part of this bundle. Without Font Awesome, this feature puts
                a <span class="fa [icon]"></span> tag into the <a> for the new button.

    flags:      An array of labels for flags to be rendered as check boxes above grid, to
                be used to modify the grid query.  Flags specified as:

                $flags['Flag name']    = 'Flag Name Title';
                $flags['Another Flag'] = 'Another Flag Title';
                $flags['thirdFlag']    = 'thirdFlag Title';
                $flags['ALLCAPSFLAG']  = 'ALLCAPSFLAG Title';

                'flags' => $flags;

                Would be fetched in a Symfony controller by:

                $flagName    = $request->query->get('flag_name');
                $anotherFlag = $request->query->get('another_flag');
                $thirdFlag = $request->query->get('thirdflag');
                $ALLCAPSFLAG = $request->query->get('allcapsflag');

                That is, all letters will be lower-cased, and all spaces will become
                underscores.  The original presentation of the keys in the flags
                array will be used for displaying labels next to checkboxes.  Titles
                will be added to html title attribute for hoverover captioning.

    export:     Adds export limited to the number of lines specified by the value.  'all'
                returns all results for export.

    noResults:  String to display when there are no results.  Defaults to
                'No Results'

    addForm:    A symfony form view to process adding things to the grid

    addTitle:   Title/label for widget to open and close addForm

> **Note**: A lot of information is rendered with the table, including
> class names and ids for other processing via javascript or other ajax.

#### Step 6:  In your twig.

```html
    {% include 'LighthartGridBundle:Grid:grid.html.twig' %}
```

or

```html
    {% embed 'LighthartGridBundle:Grid:grid.html.twig' %}
    < over-write certain blocks >
    {% endembed %}
```

#### Step 7:  Configure routes.

```yml
    test3:
        pattern:  /test3/
        defaults: { _controller: "ApplicationBundle:Test:test3" }
```

#### Step 8:  Debug helpers

Three functions, setResultsDump(), setQueryDump() and setDebugDump() can be called on
grid maker to dump debug output and die.  setDebugDump() does both of the other two
actions.  Do this in the controller after or during the grid construction: eg:

```php
    $gm = $this->get('lg.maker');
    $gm->setDebugDump();
));
```

#### Step 8:  Customizing twig

There are some defaults controlling the display of buttons.  Num is the number
displayed total, including the expander button if needed.

```html
    {% set buttonNum = 4 %}
    {% set buttonWidth = 25 %}
    {% set buttonPadding = 6 %}
```