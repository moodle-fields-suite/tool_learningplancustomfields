Privacy
=======

The plugin implements the full Moodle Privacy API for the values of the plan area.

What is personal data
---------------------

Plan values
   They describe the learning plan of a user and are stored, by the custom fields subsystem, in the user context of the plan owner. They are personal data of that user.

Template values
   They describe a template, not a person, and are not personal data.

Fields
   The fields and their categories are site configuration, not personal data.

Data requests
-------------

Export
   The values of each plan are exported under *Competencies > Learning plans > <plan name> (<id>) > Custom fields data*, next to the plan exported by Moodle.

Deletion
   Deleting the data of a user, of a list of users or of a user context deletes the values of the plans of that user. Template values are kept.

Deleting a user
   When a user is deleted, Moodle deletes their plans; the plugin deletes the values of those plans too.

Nothing is sent outside Moodle.
