# Teste Técnico Full Stack — Gestão de Pedidos

Projeto desenvolvido para o teste técnico de Desenvolvedor Full Stack Pleno.

A aplicação implementa um módulo de gestão de pedidos para uma operação de e-commerce com múltiplos afiliados. Os dados são importados a partir da Fake Store API, onde cada `cart` é tratado como um pedido e cada `user` como um afiliado.

O sistema foi pensado considerando uma base crescente, com possibilidade de chegar a centenas de milhares de pedidos, por isso foram aplicadas decisões como processamento assíncrono com filas, cache em Redis, filtros paginados, índices no banco e separação entre Controller, Service e Repository.

---

## Tecnologias utilizadas

### Backend

* PHP 8.3+
* Laravel
* MySQL 8
* Redis
* Laravel Queue
* Laravel Cache
* PHPUnit

### Frontend

* Vue.js
* Inertia.js
* Tailwind CSS
* Vite

### Infraestrutura

* Docker
* Docker Compose
* Nginx
* MySQL 8
* Redis
* Worker separado para filas
* N8N

---

## Funcionalidades principais

### Backend

* Importação de pedidos a partir da Fake Store API.
* Comando Artisan `orders:sync`.
* Processamento assíncrono dos pedidos com Jobs.
* Upsert de afiliados, produtos, pedidos e itens.
* Prevenção de duplicidade durante múltiplas execuções do comando.
* Rate limiting simples nas chamadas HTTP externas.
* Controle de status com máquina de estados.
* Histórico de alterações de status em `order_status_logs`.
* Métricas cacheadas em Redis por 5 minutos.
* Invalidação de cache ao alterar status de pedido.
* Filtros, ordenação e paginação de pedidos.
* Endpoint de resumo por afiliado.
* Endpoint de health check para MySQL, Redis e worker.
* Respostas padronizadas no formato:

```json
{
  "data": {},
  "meta": {},
  "errors": []
}
```

### Frontend

* Dashboard com cards de métricas.
* Skeleton loading nos cards.
* Auto-refresh das métricas a cada 60 segundos.
* Indicador de atualização.
* Tabela de pedidos com:

  * ID
  * Afiliado
  * Valor
  * Status
  * Data
  * Ações
* Filtros reativos com debounce.
* Sincronização dos filtros com query params da URL.
* Ordenação por colunas.
* Seleção múltipla de pedidos.
* Ação em lote para cancelamento de pedidos selecionados.
* Estado vazio.
* Drawer lateral com:

  * detalhes do pedido
  * itens do pedido
  * histórico de status
  * resumo do afiliado
  * dropdown com transições válidas
* Layout responsivo para desktop e mobile.
* Cards mobile para telas pequenas.
* Acessibilidade básica com `aria-label` e foco visível.

---

## Regras de status

A aplicação possui uma máquina de estados para evitar transições inválidas.

Transições permitidas:

```txt
pending -> approved
pending -> cancelled
approved -> refunded
```

Transições inválidas retornam erro `422` com mensagem clara.

Exemplos de transições inválidas:

```txt
cancelled -> approved
refunded -> pending
approved -> cancelled
```

---

## Estrutura principal do projeto

```txt
app/
├── Console/
│   └── Commands/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Jobs/
├── Models/
├── Repositories/
└── Services/

database/
├── migrations/
└── factories/

resources/js/
├── components/
├── composables/
└── pages/

docker/
└── nginx/

queries.sql
docker-compose.yml
Dockerfile
```

---

## Pré-requisitos

Para rodar o projeto, é necessário ter instalado:

* Docker
* Docker Compose
* Node.js
* NPM

O Composer é executado dentro do container da aplicação.

---

## Configuração do ambiente

Copie o arquivo de exemplo:

```bash
cp .env.example .env
```

Configure as variáveis de ambiente principais:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=teste_tecnico
DB_USERNAME=laravel
DB_PASSWORD=CHANGE_ME_IN_PRODUCTION
DB_ROOT_PASSWORD=CHANGE_ME_IN_PRODUCTION

CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

N8N_USER=admin
N8N_PASSWORD=CHANGE_ME_IN_PRODUCTION
N8N_WEBHOOK_URL=http://n8n:5678/webhook
```

As senhas do `.env.example` não devem ser usadas em produção. Elas estão documentadas como valores de exemplo e devem ser substituídas em ambiente real.

---

## Como rodar com Docker

Suba os containers:

```bash
docker compose up -d --build
```

Instale as dependências PHP dentro do container:

```bash
docker compose exec app composer install
```

Gere a chave da aplicação:

```bash
docker compose exec app php artisan key:generate
```

Execute as migrations:

```bash
docker compose exec app php artisan migrate
```

Instale as dependências do frontend:

```bash
npm install
```

Gere os assets do frontend:

```bash
npm run build
```

Importe os dados da Fake Store API:

```bash
docker compose exec app php artisan orders:sync
```

A aplicação ficará disponível em:

```txt
http://localhost:8000
```

O N8N ficará disponível em:

```txt
http://localhost:5678
```

---

## Serviços Docker

O `docker-compose.yml` possui os seguintes serviços:

### app

Container PHP/Laravel responsável por executar a aplicação.

### nginx

Servidor web responsável por expor a aplicação em:

```txt
http://localhost:8000
```

### mysql

Banco de dados MySQL 8.

### redis

Redis utilizado para cache e filas.

### worker

Container separado responsável por executar a fila:

```bash
php artisan queue:work redis --queue=default --tries=3 --sleep=3 --timeout=90
```

Esse serviço garante que os Jobs sejam processados fora do container principal da aplicação.

### n8n

Serviço N8N disponível em:

```txt
http://localhost:5678
```

As credenciais são definidas por:

```env
N8N_USER=admin
N8N_PASSWORD=CHANGE_ME_IN_PRODUCTION
```

---

## Comandos úteis

Ver containers ativos:

```bash
docker compose ps
```

Ver logs da aplicação:

```bash
docker compose logs -f app
```

Ver logs do worker:

```bash
docker compose logs -f worker
```

Rodar migrations:

```bash
docker compose exec app php artisan migrate
```

Rodar importação de pedidos:

```bash
docker compose exec app php artisan orders:sync
```

Limpar cache de configuração:

```bash
docker compose exec app php artisan config:clear
```

Limpar cache da aplicação:

```bash
docker compose exec app php artisan cache:clear
```

Rodar testes:

```bash
docker compose exec app php artisan test
```

---

## Importação de pedidos

A importação é iniciada pelo comando:

```bash
php artisan orders:sync
```

Esse comando busca dados da Fake Store API e prepara o processamento dos pedidos.

Fluxo simplificado:

```txt
orders:sync
   ↓
Busca users, products e carts
   ↓
Sincroniza afiliados e produtos
   ↓
Divide os pedidos em páginas/chunks
   ↓
Dispara Jobs para processamento assíncrono
   ↓
Persiste orders e order_items
```

Os pedidos são gravados com `external_id`, evitando duplicidade ao executar o comando mais de uma vez.

Quando um pedido já existe, a importação atualiza dados como valor, afiliado e itens, mas preserva o status atual para não sobrescrever alterações feitas pelo usuário no dashboard.

---

## Filas e retry

Os pedidos são processados por Jobs assíncronos.

O worker roda em container separado e utiliza Redis como backend de fila.

Configuração principal:

```env
QUEUE_CONNECTION=redis
```

O worker executa os Jobs com até 3 tentativas:

```bash
php artisan queue:work redis --queue=default --tries=3 --sleep=3 --timeout=90
```

Caso um Job falhe definitivamente, ele pode ser reprocessado com:

```bash
docker compose exec app php artisan queue:retry all
```

---

## Health check

A aplicação possui endpoint de health check:

```http
GET /api/health
```

Esse endpoint verifica os serviços dependentes:

* MySQL
* Redis
* Worker

Exemplo de resposta esperada:

```json
{
  "data": {
    "status": "ok",
    "services": {
      "mysql": true,
      "redis": true,
      "worker": true
    }
  },
  "meta": {},
  "errors": []
}
```

---

## Endpoints da API

### Listar pedidos

```http
GET /api/orders
```

Parâmetros suportados:

```txt
affiliate_id
status
date_from
date_to
min_value
max_value
sort_by
sort_dir
page
```

Exemplo:

```http
GET /api/orders?status=pending&sort_by=created_at&sort_dir=desc&page=1
```

A listagem é paginada com 20 registros por página.

---

### Detalhar pedido

```http
GET /api/orders/{id}
```

Retorna o pedido com:

* afiliado
* itens
* produto dos itens
* histórico de status

---

### Métricas

```http
GET /api/orders/metrics
```

Retorna métricas gerais do dashboard.

As métricas são cacheadas em Redis por 5 minutos.

O cache é invalidado quando o status de um pedido é atualizado.

---

### Atualizar status do pedido

```http
POST /api/orders/{id}/status
```

Body:

```json
{
  "status": "approved"
}
```

A atualização respeita a máquina de estados da aplicação.

---

### Resumo do afiliado

```http
GET /api/affiliates/{id}/summary
```

Retorna:

* total de pedidos
* receita total
* ticket médio
* taxa de cancelamento

---

## Banco de dados

Principais tabelas:

### affiliates

Armazena os afiliados importados da Fake Store API.

Campos principais:

```txt
id
external_id
username
email
password
status
created_at
updated_at
```

### products

Armazena os produtos importados.

Campos principais:

```txt
id
external_id
title
price
category
description
image
created_at
updated_at
```

### orders

Armazena os pedidos.

Campos principais:

```txt
id
external_id
affiliate_id
status
total
ordered_at
created_at
updated_at
deleted_at
```

A tabela possui Soft Delete.

Também possui índice composto para melhorar consultas por afiliado, status e data:

```txt
affiliate_id
status
created_at
```

### order_items

Armazena os itens dos pedidos.

Campos principais:

```txt
id
order_id
product_id
quantity
unit_price
created_at
updated_at
```

### order_status_logs

Armazena o histórico de alterações de status.

Campos principais:

```txt
id
order_id
user_id
from_status
to_status
changed_at
created_at
updated_at
```

---

## SQL

O arquivo `queries.sql` contém as consultas exigidas no teste:

### Query A

Ranking de afiliados por receita líquida, considerando pedidos `approved` e `refunded`.

Utiliza CTE e window function com `RANK()`.

### Query B

Cohort simplificada dos últimos 6 meses.

Garante meses sem pedidos com valor zero usando CTE recursiva.

### Query C

Identificação de pedidos duplicados por:

* mesmo afiliado
* mesmo dia
* mesmo valor total

### Query D

Produto mais vendido por afiliado.

Critério principal:

```txt
maior quantidade vendida
```

Critério de desempate:

```txt
maior valor vendido
```

### Query E

Otimização de query lenta.

Principais melhorias aplicadas:

* evitar função `DATE()` diretamente em coluna indexável
* substituir subquery por `JOIN`
* substituir subquery correlacionada por agregação prévia em CTE
* evitar `SELECT *`
* reduzir I/O selecionando apenas colunas necessárias

---

## Frontend

A interface principal é um dashboard de pedidos.

Funcionalidades implementadas:

* cards de métricas
* skeleton loading
* auto-refresh
* tabela desktop
* cards mobile
* filtros reativos
* debounce nos filtros
* query params na URL
* ordenação
* paginação
* seleção múltipla
* ação em lote para cancelamento
* drawer lateral de detalhes
* timeline de status
* transições válidas no dropdown
* mensagens de erro
* layout responsivo

---

## Ação em lote

A tabela permite selecionar múltiplos pedidos e executar a ação:

```txt
Cancelar selecionados
```

A ação reutiliza o endpoint individual de atualização de status:

```http
POST /api/orders/{id}/status
```

Para cada pedido selecionado, o frontend envia uma requisição tentando alterar o status para:

```txt
cancelled
```

Pedidos que não puderem ser cancelados por causa da máquina de estados retornam erro e são informados ao usuário.

Exemplo:

```txt
Pedido 1: pending   -> cancelled
Pedido 2: approved  -> erro
Pedido 3: pending   -> cancelled
```

Essa abordagem mantém a regra de negócio protegida no backend.

---

## Testes

Rodar os testes:

```bash
docker compose exec app php artisan test
```

Ou localmente:

```bash
php artisan test
```

Foram priorizados testes para:

* máquina de estados dos pedidos
* métricas com cache
* comando de importação `orders:sync`

---

## N8N

O projeto inclui um container do N8N no `docker-compose.yml`, acessível em:

```txt
http://localhost:5678
```

As credenciais são configuradas pelas variáveis de ambiente:

```env
N8N_USER=admin
N8N_PASSWORD=CHANGE_ME_IN_PRODUCTION
N8N_WEBHOOK_URL=http://n8n:5678/webhook
```

Neste teste, o N8N foi incluído como serviço de infraestrutura para demonstrar como a aplicação poderia se integrar com workflows externos.

A integração completa por evento ficou fora do escopo desta entrega por limitação de tempo. Em uma evolução natural do projeto, eu implementaria o fluxo da seguinte forma:

1. Criar o evento Laravel `OrderStatusChanged`.
2. Disparar esse evento ao atualizar o status de um pedido.
3. Criar um listener responsável por despachar um job assíncrono.
4. Criar um job para enviar um `POST` para `N8N_WEBHOOK_URL`.
5. Configurar retry e backoff no job para evitar impacto no fluxo principal.
6. Exportar os workflows JSON do N8N para a pasta `n8n/workflows`.

Essa decisão mantém a aplicação principal funcional e evita que falhas em uma integração externa afetem a atualização de status dos pedidos.

---

## Segurança e variáveis de ambiente

Nenhuma credencial real deve ser versionada no código-fonte.

O `.env.example` contém apenas valores de exemplo.

Exemplo:

```env
DB_PASSWORD=CHANGE_ME_IN_PRODUCTION
DB_ROOT_PASSWORD=CHANGE_ME_IN_PRODUCTION
N8N_PASSWORD=CHANGE_ME_IN_PRODUCTION
```

Em produção, esses valores devem ser substituídos por secrets seguros.

---

## Decisões técnicas

### Service e Repository

A aplicação separa responsabilidades entre Controller, Service e Repository.

Controllers ficam responsáveis por receber a requisição e retornar resposta.

Services concentram regras de negócio.

Repositories concentram consultas ao banco.

Essa separação facilita manutenção, testes e evolução do projeto.

---

### Cache em Redis

As métricas do dashboard são cacheadas por 5 minutos para reduzir consultas agregadas repetidas.

Quando o status de um pedido é atualizado, o cache é invalidado para evitar dados desatualizados.

---

### Filas

A importação de pedidos usa filas para evitar processamento pesado durante a execução direta do comando.

Isso permite evoluir o sistema para volumes maiores de dados sem travar a aplicação principal.

---

### Upsert e idempotência

A importação utiliza identificadores externos da Fake Store API para evitar duplicidade.

O comando pode ser executado mais de uma vez sem criar pedidos repetidos.

Além disso, pedidos já existentes não têm o status sobrescrito para `pending`, evitando inconsistência após alterações feitas pelo usuário.

---

### Índices

A tabela `orders` possui índice composto para melhorar consultas frequentes do dashboard:

```txt
affiliate_id
status
created_at
```

Essa decisão considera os filtros mais utilizados na listagem:

* afiliado
* status
* período
* ordenação por data

---

### Soft Deletes

A tabela `orders` utiliza Soft Delete.

Isso permite remover pedidos da visualização padrão sem excluir fisicamente os dados, preservando histórico e integridade para auditoria.

---

## Trade-offs

### Setup Docker

O Docker Compose provisiona os serviços necessários para o projeto:

* aplicação PHP/Laravel
* Nginx
* MySQL
* Redis
* worker
* N8N

Para manter a configuração simples e explícita, optei por documentar os comandos de primeira execução no README em vez de automatizar todo o setup em um entrypoint.

Em um ambiente real de produção, passos como instalação de dependências, geração de chave, migrations e build de assets seriam executados por um pipeline de deploy ou script de bootstrap controlado.

---

### N8N

O container do N8N foi incluído, mas a integração completa por webhook ficou documentada como evolução.

Priorizei o fluxo principal da aplicação:

* importação
* persistência
* dashboard
* filtros
* métricas
* cache
* filas
* status
* histórico
* health check

Essa escolha evita entregar uma integração externa incompleta ou instável.

---

### Fake Store API

A Fake Store API possui volume pequeno e não oferece paginação real para todos os recursos utilizados.

Para simular processamento em páginas, os dados importados são divididos em chunks e processados por Jobs separados.

Em uma API real paginada, o comando poderia despachar um Job por página retornada diretamente pela API externa.

---

## Possíveis melhorias futuras

* Implementar integração completa com N8N via evento, listener e job.
* Adicionar endpoint específico para ação em lote no backend.
* Adicionar autenticação e autorização nas rotas administrativas.
* Melhorar observabilidade dos Jobs.
* Adicionar logs estruturados.
* Criar workflow CI com lint, testes e build.
* Adicionar mais testes de integração para filtros e paginação.
* Adicionar seeders para ambiente local.
* Criar paginação real no processo de importação caso a API externa suporte.
* Adicionar políticas de permissão para atualização de status.

---

## Checklist do desafio

### Backend

* [x] Comando `orders:sync`
* [x] Processamento assíncrono com Jobs
* [x] Upsert sem duplicidade
* [x] Retry de Jobs
* [x] Rate limiting nas chamadas HTTP
* [x] Migrations principais
* [x] Índice composto em pedidos
* [x] Histórico de status
* [x] Service + Repository
* [x] API paginada com filtros
* [x] Detalhe do pedido
* [x] Métricas cacheadas
* [x] Máquina de estados
* [x] Resumo do afiliado
* [x] Resposta padronizada

### Frontend

* [x] Cards de métricas
* [x] Skeleton loading
* [x] Auto-refresh
* [x] Indicador de atualização
* [x] Tabela de pedidos
* [x] Filtros reativos
* [x] Debounce
* [x] Query params
* [x] Ordenação
* [x] Seleção múltipla
* [x] Ação em lote
* [x] Estado vazio
* [x] Drawer de detalhes
* [x] Timeline de status
* [x] Dropdown com transições válidas
* [x] Responsividade
* [x] Acessibilidade básica

### SQL

* [x] Query A
* [x] Query B
* [x] Query C
* [x] Query D
* [x] Query E

### DevOps

* [x] Docker Compose
* [x] PHP/Laravel
* [x] MySQL 8
* [x] Redis
* [x] Worker separado
* [x] N8N
* [x] `.env.example`
* [x] Health check
* [x] Retry de Jobs
* [x] README com instalação, decisões e trade-offs

---

## Autor

Desenvolvido por Lucas Martins.
