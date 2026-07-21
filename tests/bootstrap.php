<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

$basePath = dirname(__DIR__);
$testingEnvPath = $basePath.'/.env.testing';

if (! is_file($testingEnvPath)) {
    fwrite(
        STDERR,
        <<<'MSG'
Arquivo .env.testing não encontrado.

Copie o exemplo e preencha o APP_KEY (o mesmo do .env da aplicação):

  cp .env.testing.example .env.testing

MSG
    );

    exit(1);
}

/*
| O Compose injeta o .env da app no processo (env_file). O Dotenv imutável
| do Laravel não sobrescreve $_SERVER já preenchido — por isso usamos
| createMutable() para o perfil de teste vencer Redis/session/DB da app.
*/
Dotenv::createMutable($basePath, '.env.testing')->load();

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';
