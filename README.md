# moodle-tool_learningplancustomfields

[English](#english) · [Português (Brasil)](#português-brasil)

Documentation / Documentação: <https://moodle-fields-suite.github.io/tool_learningplancustomfields/>

## English

Custom fields for learning plan templates and learning plans, part of the [Moodle Fields Suite](https://moodle-fields-suite.github.io). Moodle has custom fields for courses, cohorts and groups, but not for learning plans: this admin tool adds two areas, with every field type installed on the site. Fields are managed at *Site administration > Competencies*; values are edited from the template and plan pages, shown there according to each field's visibility and read by integrations through the `local_fieldssuite_get_values` web service. Plan values are personal data of the plan owner and are covered by the Privacy API. Requires [`local_fieldssuite`](https://github.com/moodle-fields-suite/local_fieldssuite). Supports Moodle 4.5 LTS and 5.3 LTS.

Install: copy this repository to `admin/tool/learningplancustomfields` (`public/admin/tool/learningplancustomfields` on Moodle 5.1+), with `local_fieldssuite`, and visit *Site administration > Notifications*.

## Português (Brasil)

Campos personalizados para modelos de plano de aprendizagem e planos de aprendizagem, parte da [Moodle Fields Suite](https://moodle-fields-suite.github.io). O Moodle tem campos personalizados para cursos, coortes e grupos, mas não para planos de aprendizagem: esta ferramenta administrativa acrescenta duas áreas, com todos os tipos de campo instalados no site. Os campos são geridos em *Administração do site > Competências*; os valores são editados a partir das páginas do modelo e do plano, aparecem nelas conforme a visibilidade de cada campo e podem ser lidos por integrações pelo web service `local_fieldssuite_get_values`. Os valores dos planos são dados pessoais do dono do plano e são tratados pela Privacy API. Requer o [`local_fieldssuite`](https://github.com/moodle-fields-suite/local_fieldssuite). Suporta Moodle 4.5 LTS e 5.3 LTS.

Instalação: copie este repositório para `admin/tool/learningplancustomfields` (`public/admin/tool/learningplancustomfields` no Moodle 5.1+), junto com o `local_fieldssuite`, e acesse *Administração do site > Notificações*.

## Development / Desenvolvimento

- CI (`.github/workflows/ci.yml`) runs `moodle-plugin-ci` on Moodle 4.5 and 5.3 with PostgreSQL and MariaDB, installing `local_fieldssuite`; run it locally with [`act`](https://github.com/nektos/act) before pushing.

## Support and privacy / Suporte e privacidade

- Issue tracker / Rastreador de issues: <https://github.com/moodle-fields-suite/tool_learningplancustomfields/issues>
- Privacy / Privacidade: plan values belong to the plan owner and are exported and deleted with the Privacy API; template values are not personal data; nothing leaves Moodle / os valores dos planos pertencem ao dono do plano e são exportados e excluídos pela Privacy API; os valores dos modelos não são dados pessoais; nada sai do Moodle.
- Translations / Traduções: only English is shipped; other languages go through AMOS / o pacote traz só inglês; outros idiomas via AMOS.

## License / Licença

GNU GPL v3 or later. See [LICENSE](LICENSE).
