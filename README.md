# TMS — Transportation Management System

API para gerenciamento de entregas e transportadoras, desenvolvida como teste técnico.

**Autor:** Gabriel Soares Prates

---

## Como rodar

**Requisitos:** PHP 8.1+, MySQL 8+, Composer

```bash
# Clone o repositório
git clone https://github.com/gspbr16/teste-php-junior.git
cd teste-php-junior

# Instale as dependências
composer install

# Configure o banco
cp .env.example .env
# abra o .env e preencha com suas credenciais MySQL

# Crie as tabelas e popule os dados
vendor/bin/phinx migrate
vendor/bin/phinx seed:run

# Suba o servidor
php -S localhost:8000 public/index.php
```

---

## Endpoints

### Transportadoras

```bash
GET  /transportadoras                        # lista ativas
GET  /transportadoras?incluir_inativas=true  # lista todas
GET  /transportadoras/1                      # busca por ID
POST /transportadoras                        # cria nova
PATCH /transportadoras/1/desativar
PATCH /transportadoras/1/reativar
```

Exemplo de criação:
```json
{ "cnpj": "12345678000195", "nome_fantasia": "Transportadora Exemplo" }
```

### Entregas

```bash
GET   /entregas          # lista entregas
GET   /entregas/1        # busca por ID
POST  /entregas          # cria nova entrega
PATCH /entregas/1/status # atualiza status
```

Exemplo de criação:
```json
{
  "id_transportadora": 1,
  "id_remetente": 1,
  "id_destinatario": 1,
  "data_prazo": "2026-12-31",
  "peso_kg": 10.5,
  "volumes": 2
}
```

Exemplo de atualização de status:
```json
{ "status": "COLETADA", "descricao": "Coletado na origem", "cidade": "São Paulo", "uf": "SP" }
```

### Não Conformidades

```bash
GET  /motivos-nao-conformidade          # lista motivos ativos
POST /entregas/1/nao-conformidades      # registra uma NC
```

Exemplo:
```json
{ "id_motivo": 1, "descricao": "Produto chegou com embalagem danificada" }
```

---

## Fluxo de status das entregas

CRIADA → COLETADA → EM_TRANSITO → SAIU_ENTREGA → ENTREGUE
                                               ↘ DEVOLVIDA

Tentativas de transição fora desse fluxo retornam `422`.

---

## Decisões que tomei

**422 em vez de 404 para transportadora inativa**
Quando a transportadora não está ativa, o sistema agora retorna 422.
Faz mais sentido que 404 porque a transportadora existe — ela só está inativa.

**Índice em `id_entrega` na tabela de não conformidades**
As buscas mais comuns vão ser por entrega, então adicionei um índice
nesse campo para não ter problema de performance no futuro.

**`descricao` opcional nas não conformidades**
O motivo já diz o que aconteceu. A descrição é para quem quiser
detalhar mais, mas não é obrigatória.

**Com mais tempo faria:**
testes automatizados e Docker para facilitar rodar o projeto em
qualquer máquina.                                               