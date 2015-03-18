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
));
$gm->setQueryBuilder($qb);
```

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

    search:     Setting to true will add the column to the fields searched via the global
                search tool. Optionally, you can also specify the kind of search filter
                to use on this filed. Valid types are: date, number, and string.

    filter:     Setting to true will add a search filter box to the column header which
                only searches on this field. Optionally, you can also specify the kind of
                search filter to use on this filed. Valid types are: date, number, and
                string.

    attr:       Sets the html attributes.

    html:       Indicates the title should be interpreted as html, i.e., the twig raw
                filter is applied.

    entityId:   Evaluating to true stores the entity id on the individual cell. This is
                mostly useful for javascript related associated entities.

    parentId:   Evaluating to true stores the parent id on the row header. This is mostly
                useful for javascript related associated entities.

    title:      Sets the title of the column.  Note: the title key of the 'attr' field
                sets the on-hover title.

    hidden:     Evaluating to true hides the column.

    security:   A primitive boolean, or an anonymous function.  If the value evaluates to
                true, the button is rendered.  Default is true.  For the anonymous
                function, the result tuple for the current row is sent as the first
                parameter, and an alias translation table for the original alias and the
                new alias in the query is sent as the second parameter. The tildes (see
                below) function as columns forming indexes, to base the appearance on
                portions of the tuple.

    value:      Multiple fields may be added using tildes, as with example for title.
                Passing an array (instead of a string )of values takes the first truthy
                value, similar to a postgres concat operator.

    boolean:    Field is a boolean, will render with boolean twigs.  If value is a string
                boolean value will be determined by a == comparison.  If value is an anonymous
                function, the result tuple for the current row is sent as the first
                parameter, and an alias translation table for the original alias and the
                new alias in the query is sent as the second parameter, and the function
                should return the boolean value.  for example:
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
    'grid'    => $gm->getGrid() ,
    'flags'   => $flags,
    'newPath' => $url,
    'export'  => 1000,
));
```

**Render Configuration**:

    newPath:    Path for 'new' to add something to the grid.

    newIcon:    A font awesome icon for the new button.  Note: Font Awesome is not
                installed as part of this bundle. Without Font Awesome, this feature puts
                a <span class="fa [icon]"></span> tag into the <a> for the new button.

    flags:      An array of labels for flags to be rendered as check boxes above grid, to
                be used to modify the grid query.  Flags specified as:

                'flags' => ['flagName', 'anotherFlag']

                Would be fetched in a Symfony controller by:

                $flagname    = $request->query->get('flagName');
                $anotherFlag = $request->query->get('anotherFlag');

    export:     Adds export limited to the number of lines specified by the value.  'all'
                returns all results for export.

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