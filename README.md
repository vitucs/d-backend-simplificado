# Projeto "Mais Simplificado" - Laravel Monolito

Este projeto é uma aplicação backend desenvolvida em **Laravel**, estruturada como um monolito.  
O objetivo é fornecer uma base sólida para desenvolvimento, com suporte a banco de dados, cache e mensageria, além de estar totalmente containerizada com **Docker** e **Docker Compose**.

## ✨ Features

* **Monólito Laravel:** Estrutura unificada e centralizada para facilitar o desenvolvimento.
* **ORM Eloquent:** Abstração poderosa para manipulação do banco de dados relacional.
* **Containerização Completa:** Docker + Docker Compose para garantir consistência e facilidade de configuração.

## 🚀 Tecnologias Utilizadas

* **Framework PHP:** [Laravel 10.x](https://laravel.com/)
* **Banco de Dados Relacional:** [MySQL](https://www.mysql.com/)
* **Containerização:** [Docker](https://www.docker.com/) & [Docker Compose](https://docs.docker.com/compose/)

## 📂 Estrutura do Projeto

O repositório está organizado com cada microsserviço em sua própria pasta, facilitando o desenvolvimento e a manutenção independente de cada um.

```
.
├── app/ # Código principal da aplicação (Models, Controllers, Services, etc.)
├── bootstrap/ # Inicialização do framework
├── config/ # Arquivos de configuração
├── database/ # Migrations, Seeders e Factories
├── docker/ # Configurações adicionais para containers
├── public/ # Ponto de entrada público (index.php)
├── resources/ # Views, arquivos de front-end (Blade, JS, CSS)
├── routes/ # Definição de rotas (web.php, api.php)
├── storage/ # Arquivos gerados (logs, cache, uploads)
├── tests/ # Testes automatizados
├── vendor/ # Dependências do Composer
├── docker-compose.yml # Orquestração dos serviços
├── Dockerfile # Configuração da imagem principal da aplicação
├── package.json # Dependências do frontend (Vite, etc.)
├── vite.config.js # Configuração do Vite
└── README.md
```

## 📋 Pré-requisitos

Antes de começar, certifique-se de que você tem as seguintes ferramentas instaladas em seu sistema:

* [Docker Engine](https://docs.docker.com/engine/install/)
* [Docker Compose](https://docs.docker.com/compose/install/)

## 🏁 Como Rodar o Projeto

Siga os passos abaixo para configurar e executar o ambiente de desenvolvimento localmente.

**1. Clone o repositório:**

```bash
git clone https://github.com/vitucs/d-backend-simplificado.git
cd d-backend-simplificado
```

**2. Configure as variáveis de ambiente:**

```bash
cp .env.example .env
```

**3. Inicie os containers:**

Na pasta raiz do projeto (onde o arquivo `docker-compose.yml` está localizado), execute o comando abaixo. Ele irá construir as imagens e iniciar todos os serviços, bancos de dados e ferramentas em background.

```bash
docker-compose up -d --build
```

**4. Execute as migrações do banco de dados:**

Para criar as tabelas necessárias no MySQL, execute os comandos de migração do Laravel nos serviços que interagem com o banco de dados.

```bash
docker-compose exec app php artisan migrate
```

Pronto! A aplicação agora deve estar em execução. A **APP** estará escutando na porta definida no `docker-compose.yml`.

## 🤝 Como Contribuir

Contribuições são o que tornam a comunidade de código aberto um lugar incrível para aprender, inspirar e criar. Qualquer contribuição que você fizer será **muito apreciada**.

1.  Faça um **Fork** do projeto
2.  Crie uma **Branch** para sua Feature (`git checkout -b feature/AmazingFeature`)
3.  Faça o **Commit** de suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4.  Faça o **Push** para a Branch (`git push origin feature/AmazingFeature`)
5.  Abra um **Pull Request**

## 📄 Licença

Distribuído sob a Licença MIT. Veja o arquivo `LICENSE` para mais informações.