Grids need to be made clickable by placing a 'editable' class on the corresponding header.

The th should also contain the class name in data-role-class, specified as: '{Vendor}_{Project}_{ModuleBundle}_Entity_{entity}'.  The undersGrids get interpolated in the controller code.  This is necessary because html barfs on /, \, and :, which are the normal ways

and field name specified in data-role-field.

Example:

<th class="editable" data-role-class="MESD_ORMed_ORMedBundle_Entity_Log" data-role-field="shortName">Name</th>


The td should indicate the entity id in data-role-entity-id (this accomodates situations where more than one entity type is in the grid).

Example:
<td data-role-entity-id="{{entity.id}}">{{entity.description}}</td>

Since this is all ajax, failures are invisible, but logged using monolog logger.