# Sistema de Gestão Financeira Casal 💰

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

Uma aplicação robusta de controle financeiro pessoal e familiar desenvolvida em **Laravel 11**. Projetada para garantir precisão nos relatórios financeiros diários, controle rigoroso de cartões de crédito e planejamento em supermercados.  

Este projeto adere fortemente a conceitos maduros de desenvolvimento de software, contando com rastreabilidade total de logs, proteções de segurança em ambiente de produção (rate limiting, secure headers, force HTTPS) e camadas de autenticação nativa.

## 🚀 Funcionalidades Principais

*   **Dashboard Executivo:**
    *   Painel consolidado com a saúde financeira (saldo unificado vs despesas provisionadas e contas a pagar).
    *   Gráficos dinâmicos e relatórios filtráveis via Chart.js.
*   **Gestão de Contas Bancárias:**
    *   Controle de saldos manuais de Contas Correntes, Poupanças e Investimentos.
    *   💡 **Inteligência de Benefícios:** Contas marcadas como benefício (ex: Vale-Alimentação/Refeição) não integram artificialmente a soma do "Saldo Líquido" do casal.
*   **Gestão de Transações e Pagamentos:**
    *   Registro de despesas corporativas ou eventuais e receitas de salário/dividendos.
    *   Gerenciamento de despesas recorrentes mensais, com capacidade de prever contas do mês vindouro automaticamente.
*   **Controle de Cartões de Crédito:**
    *   Integração total do planejamento de faturas vinculadas aos cartões.
    *   Ao pagar a fatura, a movimentação é descontada do saldo e quitada do período vigente.
*   **Módulo de Compras (Supermercado Inteligente):**
    *   Listas dinâmicas: adicione itens com quantidades e projeções ("Valor Estimado").
    *   Acompanhe o gap (Diferença) ao comparar com o modelo atual ("Valor Real").
    *   A tela de checkout de compras permite finalização híbrida (pagamento distribuído entre Mão, Cartão de Crédito ou Saldo de Múltiplas Contas).
*   **Segurança e Privacidade (WORM Tracking):**
    *   Rotinas enclausuradas (Auth) para proteção de ponta-a-ponta contra invasões anônimas.
    *   Acompanhado pelo pacote de _Audit Trail_ (`spatie/laravel-activitylog`). Todas as operações de criação, modificação ou destruição de registros são versionadas sem capacidade preestabelecida de edição dos logs. Tudo é rastreável (Quem mudou, Quando e O que alterou).

## 🛠️ Tecnologias e Dependências

*   **Framework:** Laravel 11 (PHP 8.2+)
*   **Frontend:** Bootstrap 5 nativo (Blade Templates), sem complexidade adicional de Node/npm build pipes, priorizando a estabilidade visual e alta reusabilidade de UI.
*   **Auditoria:** `spatie/laravel-activitylog` (Monitoramento nativo sobre o Eloquent)
*   **SGBD Compatível:** MySQL 8+ / PostgreSQL (Totalmente refatorado utilizando Migrations escaláveis do Laravel).
*   **Estabilidade Temporal:** `Carbon`

## ⚙️ Configuração Local e Instalação

Siga os passos abaixo para estabelecer a arquitetura do projeto no seu ambiente.

1. **Clone o repósitório:**
    ```sh
    git clone https://github.com/AndreFilippe/financas.git
    cd financas
    ```

2. **Instale as dependências via Composer:**
    ```sh
    composer install --no-dev --optimize-autoloader
    ```

3. **Configure as variáveis de ambiente:**
    ```sh
    cp .env.example .env
    php artisan key:generate
    ```
    Edite o arquivo `.env` para apontar p/ seu banco de dados correto.

4. **Prepare as Migrations e Seeds (Banco de Dados):**
    ```sh
    php artisan migrate
    ```
    ⚠️ **Importante:** Inicialize também o banco populando o SuperAdmin inicial (Obrigatório, toda a rota é bloqueada).
    ```sh
    php artisan db:seed
    ```

5. **Inicie o servidor e aproveite (Em ambiente Dev):**
    ```sh
    php artisan serve
    ```
    *Para Login utilize as credenciais populadas pelo arquivo DatabaseSeeder.php.*

## 🛡️ Notas de Deploy em Nuvem / Produção

Esta aplicação está atrelada à salvaguardas voltadas para instâncias web. Em seu arquivo `AppServiceProvider.php`, o Force HTTPS é habilitado diretamente no ambiente de `production` garantindo tunelamento, aliado ao bloqueador de excessos massivos (`Global Route Throttle 60/1`) presente diretamente no kernel principal (`bootstrap/app.php`), que corta agressões contínuas e força expiração a bots/scrappers de tráfego. Adicionalmente, mitigadores Header (ex: `X-XSS-Protection`) estão vigentes pelo handler de `SecurityHeadersMiddleware`.

## 📜 Licença

Distribuído sob licença do Autor. Este repositório foca em gestão financeira de escopo privado.
