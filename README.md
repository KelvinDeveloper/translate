Tradução Altomática para Laravel
====================

A biblioteca utiliza [Google Translate API](https://cloud.google.com/translate/) e/ou [AWS Translate API](https://aws.amazon.com/translate/), para traduzir automaticamente os termos requisitados. A biblioteca contempla também um painel administrativo onde você pode alterar os termos traduzidos automaticamente.

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

Adicione o provider e aliases em seu arquivo config/app.php
````
...
Translate\Providers\TranslateProvider::class,


...
'Translate' => \Translate\Translate::class,

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

Para gerenciar as traduções, basta adicionar a rota em seu arquivo.<br>
Você pode adicionar a rota publica (quando não haverá autenticação ou validações) adicionando a linha abaixo em seu arquivo de rotas:
````
Route::get('/translate/manager/{translate_lang?}', '\Translate\Http\Controllers\TranslateManager@index');
````

Caso queira validar o acesso antes, basta chamar o controller como exemplo abaixo:
````
Route::get('/translate/manager/{translate_lang?}', function ($translate_lang) {
   if (! Auth::check()) return redirect('/auth/login');
   return app('\Translate\Http\Controllers\TranslateManager')->index($translate_lang);
});
````

## Uso

Para traduzir os termos, chame o helper "_t(...)"
````
_t('Termo para tradução');
````

Você pode utilizar variáveis nas traduções
````
_t('Olá {nome_usuario}', [$nome_usuario]);
````

### License

This repository code is open-sourced software licensed under the MIT license