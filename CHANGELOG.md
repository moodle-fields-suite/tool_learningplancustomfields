# Changelog

## 1.0.0 - 2026-11-02

First release, built on `local_fieldssuite` 1.1.0.

### Added
- Two custom field areas: learning plan templates (values in the context of the template) and learning plans (values in the user context of the plan owner), with every field type installed on the site.
- Field management at *Site administration > Competencies > Learning plan template custom fields* and *Learning plan custom fields*, with the area settings *Locked* and *Visible to*.
- Values shown, with an *Edit custom fields* link, on the template page, the edit template page, the plan page and the edit plan page; edited on the common page of `local_fieldssuite`.
- Values deleted when their template or plan is deleted, and plan values deleted when their owner is deleted.
- Capabilities `tool/learningplancustomfields:configurecustomfields`, `tool/learningplancustomfields:edittemplate`, `tool/learningplancustomfields:editplan` and `tool/learningplancustomfields:changelockedcustomfields`.
- Full Privacy API for plan values: metadata, contexts and users, export next to the plans exported by `core_competency`, deletion by context, user and list of users.
- PHPUnit tests, CI with moodle-plugin-ci (Moodle 4.5 and 5.3, PostgreSQL and MariaDB), documentation in English and Brazilian Portuguese, release by draft and verification.
