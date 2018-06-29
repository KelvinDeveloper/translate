Tradução Altomática para Laravel
====================

A biblioteca utiliza [Google Translate API](https://cloud.google.com/translate/) e [AWS Translate API](https://aws.amazon.com/translate/).

## Instalação

O pacote pode ser instalado usando o compositor adicionando ao objeto "require"

```
"require": {
    "kelvinsouza/translate": "dev-master"
}
```

ou pelo console:

```
composer require kelvinsouza/translate
```

## Configuração

Adicione o provider em seu arquivo config/app.php
````
...
Translate\Providers\TranslateProvider::class,
````

Publique os arquivos de configuração:
````
php artisan vendor:publish --tag=config
````

Publique as views:
````
php artisan vendor:publish --tag=views
````

Publique as migrations:
````
php artisan vendor:publish --tag=migrations
````

## Uso

Para traduzir os termos, chame o helper "_t(...)"
````
_t('Termo para tradução');
````

### License

This repository code is open-sourced software licensed under the MIT license