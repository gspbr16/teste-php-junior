# BUGFIX.md

**Data da correção:** 06/05/2026
**Corrigido por:** Gabriel Soares

---

## O que era o bug

**Arquivo:** `src/Controllers/EntregaController.php`
**Método:** `store()`
**Linha:** 92

A query que valida a transportadora no momento de criação de uma entrega
verificava apenas se a transportadora existia no banco, sem verificar se
ela estava ativa:

```sql
SELECT id FROM transportadoras WHERE id = ?
```

O sistema usa soft delete para desativar transportadoras — elas não são
apagadas do banco, apenas recebem uma data no campo `deleted_at`. Como a
query ignorava esse campo, transportadoras inativas passavam na validação
normalmente e era possível criar entregas vinculadas a elas.

A correção foi adicionar `AND deleted_at IS NULL` na query, garantindo que
apenas transportadoras ativas sejam aceitas. O status de erro também foi
ajustado de 404 para 422, pois a transportadora existe no banco — ela
apenas está inativa, o que é uma violação de regra de negócio.

```sql
SELECT id FROM transportadoras WHERE id = ? AND deleted_at IS NULL
```
---

## Resposta para a Camila (Operações)

Bom dia Camila, tudo bem?

Obrigado por reportar! Conseguimos identificar e corrigir o problema.

O que estava acontecendo: quando uma transportadora é desativada, ela não
some do sistema — ela fica salva para manter o histórico. Só que tinha uma
verificação faltando no cadastro de entregas, que não checava se a
transportadora ainda estava ativa. Por isso o sistema deixava passar.

Já corrigimos. A partir de agora, se alguém tentar cadastrar uma entrega
com uma transportadora desativada, vai aparecer uma mensagem de erro
bloqueando o cadastro.

Sobre as entregas que já foram criadas com a Logística Norte Ltda depois
da desativação: elas ainda estão no sistema. Vale o time de operações dar
uma olhada nelas e decidir se precisam ser transferidas para outra
transportadora ou canceladas.

Qualquer coisa é só me chamar!

---

## Como reproduzir (antes da correção)

1. Buscar transportadoras inativas: `GET /transportadoras?incluir_inativas=true` e anotar o id de uma com `"ativa": false`

2. Tentar criar uma entrega com esse id: `POST /entregas` com `"id_transportadora": 3`

3. O sistema criava a entrega normalmente, retornando status `201` sem nenhum erro

## Como verificar que está corrigido

1. Fazer `POST /entregas` com `"id_transportadora": 3` (Logística Norte Ltda — inativa)

2. O sistema deve bloquear e retornar status `422` com a mensagem `"Transportadora não encontrada ou inativa"`

3. Fazer `POST /entregas` com `"id_transportadora": 1` (Transportes Rápido Ltda — ativa)

4. O sistema deve criar a entrega normalmente, retornando status `201`
