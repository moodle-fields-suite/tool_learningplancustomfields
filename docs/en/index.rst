moodle-tool_learningplancustomfields
====================================

**Custom fields for learning plan templates and learning plans: manage them like course custom fields, edit the values from the plan pages and read them in integrations.**

.. figure:: img/display.png
   :alt: A learning plan page with the custom field values and the Edit custom fields button above the plan.
   :width: 100%

   The values of a learning plan, with the link to edit them.

* **Fields where Moodle has none.** Moodle has custom fields for courses, cohorts and groups, but not for learning plans. This tool adds two areas: learning plan templates and learning plans.
* **Every field type.** Text, text area, menu, checkbox, date and any other custom field type installed on the site, with the usual settings.
* **Control who sees each value.** Every field is visible to everyone, only to those who can edit the values, or to nobody.
* **Privacy by design.** Plan values belong to the plan owner: they are exported and deleted through the Privacy API, and deleted with the user.
* **Ready for integrations.** The ``local_fieldssuite_get_values`` web service returns the values the user can see.

Part of the `Moodle Fields Suite <https://moodle-fields-suite.github.io>`_; requires `local_fieldssuite <https://moodle-fields-suite.github.io/local_fieldssuite/>`_.

Versão em português: `Português (Brasil) <../pt-br/index.html>`_.

.. toctree::
   :maxdepth: 2
   :caption: Contents

   installation
   configuration
   usage
   privacy

Links
-----

* Source code and issues: https://github.com/moodle-fields-suite/tool_learningplancustomfields
