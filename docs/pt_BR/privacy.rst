Privacidade
===========

O plugin implementa a Privacy API completa do Moodle para os valores da área de planos.

O que é dado pessoal
--------------------

Valores dos planos
   Descrevem o plano de aprendizagem de um usuário e são guardados, pelo subsistema de campos personalizados, no contexto de usuário do dono do plano. São dados pessoais desse usuário.

Valores dos modelos
   Descrevem um modelo, não uma pessoa, e não são dados pessoais.

Campos
   Os campos e suas categorias são configuração do site, não dados pessoais.

Pedidos de dados
----------------

Exportação
   Os valores de cada plano são exportados em *Competências > Planos de aprendizagem > <nome do plano> (<id>) > Dados dos campos personalizados*, junto com o plano exportado pelo Moodle.

Exclusão
   Excluir os dados de um usuário, de uma lista de usuários ou de um contexto de usuário exclui os valores dos planos desse usuário. Os valores dos modelos continuam.

Excluindo um usuário
   Quando um usuário é excluído, o Moodle exclui os planos dele; o plugin exclui também os valores desses planos.

Nada é enviado para fora do Moodle.
