## Descrição do projeto

Endpoint para receber um arquivo CSV contendo informações para senrem enviadas por email.

## Solução de Performance.

1. O Endpoint chama o controller `FileProcessingController` que envia o arquivo csv para o job `ProcessChunksJob`;
2. O `ProcessChunksJob` separa o arquivo .CSV em lotes de 100 registros e chama o Gerador de Boletos `ProcessPaymentSlipJob`;
3. O `ProcessPaymentSlipJob` Verifica se já existe o registro no banco de dados, caso não ele irá gerar o Boleto;
4. Após todos os boletos do lote serem gerados, o `ProcessPaymentSlipJob` chama o job `ProcessEmailsJob` que envia os emails;
5. `ProcessEmailsJob` Envia os emails e também atualixza o status no banco de dados.

## Requisitos

-   PHP 8.2 ou superior;
-   Composer
-   MySQL

## Como rodar no Docker

Duplicar o arquivo .env.example e renomear para .env<br>
Alterar no arquivo .env as credenciais do banco de dados<br>

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=secret
```


Construa e inicie os containers:
```
docker-compose up --build -d
```

Instale as dependências do Laravel:
```
docker-compose exec app composer install
```

Gere a chave do aplicativo:
```
docker-compose exec app php artisan key:generate
```

Execute as migrações:
```
docker-compose exec app php artisan migrate
```


## Como rodar em localhost

Duplicar o arquivo .env.example e renomear para .env<br>
Alterar no arquivo .env as credenciais do banco de dados<br>

Instalar as dependências do PHP
```
composer install
```

Gerar a chave do APP
```
php artisan key:generate
```

Executar o migration.
```
php artisan migrate
```

Iniciar o proejto
```
php artisan serve
```

## Testes

Temos 3 testes Unitários:

* tests/Feature/FileProcessingControllerTest.php
* tests/Feature/ProcessChunksJobTest.php
* tests/Feature/ProcessEmailsJobTest.php

E 2 teste de Integração:

* tests/Feature/FileProcessingIntegrationTest.php

Para rodar utilize o comando abaixo:
```
php artisan test
```