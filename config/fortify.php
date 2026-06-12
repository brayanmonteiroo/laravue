<?php

use Laravel\Fortify\Features;

return [

    /*
    |--------------------------------------------------------------------------
    | Fortify Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify which authentication guard Fortify will use while
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    | PT-BR: Aqui você pode especificar qual guardia de autenticação o Fortify usará enquanto autentica usuários. Este valor deve corresponder a uma das guardias que já estão presentes em seu arquivo de configuração "auth".
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Fortify Password Broker
    |--------------------------------------------------------------------------
    |
    | Here you may specify which password broker Fortify can use when a user
    | is resetting their password. This configured value should match one
    | of your password brokers setup in your "auth" configuration file.
    |
    | PT-BR: Aqui você pode especificar qual broker de senha o Fortify usará enquanto um usuário está redefinindo sua senha. Este valor deve corresponder a um dos brokers de senha que já estão configurados em seu arquivo de configuração "auth".
    */

    'passwords' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Username / Email
    |--------------------------------------------------------------------------
    |
    | This value defines which model attribute should be considered as your
    | application's "username" field. Typically, this might be the email
    | address of the users but you are free to change this value here.
    |
    | Out of the box, Fortify expects forgot password and reset password
    | requests to have a field named 'email'. If the application uses
    | another name for the field you may define it below as needed.
    |
    | PT-BR: Este valor define qual atributo do modelo deve ser considerado como o campo "username" da aplicação. Normalmente, isso pode ser o endereço de e-mail dos usuários, mas você pode alterar este valor aqui conforme necessário.
    |
    | Fora da caixa, o Fortify espera que as solicitações de redefinição de senha e senha tenham um campo chamado 'email'. Se a aplicação usa outro nome para o campo, você pode defini-lo abaixo conforme necessário.
    |
    */

    'username' => 'email',

    'email' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Lowercase Usernames
    |--------------------------------------------------------------------------
    |
    | This value defines whether usernames should be lowercased before saving
    | them in the database, as some database system string fields are case
    | sensitive. You may disable this for your application if necessary.
    |
    | PT-BR: Este valor define se os nomes de usuário devem ser convertidos para minúsculas antes de serem salvos no banco de dados, pois alguns campos de sistema de string do banco de dados são sensíveis a maiúsculas e minúsculas. Você pode desabilitar isso para sua aplicação se necessário.
    |
    */

    'lowercase_usernames' => true,

    /*
    |--------------------------------------------------------------------------
    | Home Path
    |--------------------------------------------------------------------------
    |
    | Here you may configure the path where users will get redirected during
    | authentication or password reset when the operations are successful
    | and the user is authenticated. You are free to change this value.
    |
    | PT-BR: Aqui você pode especificar o caminho onde os usuários serão redirecionados durante a autenticação ou redefinição de senha quando as operações forem bem-sucedidas e o usuário estiver autenticado. Você é livre para alterar este valor.
    |
    */

    'home' => '/admin/dashboard',

    /*
    |--------------------------------------------------------------------------
    | Fortify Routes Prefix / Subdomain
    |--------------------------------------------------------------------------
    |
    | Here you may specify which prefix Fortify will assign to all the routes
    | that it registers with the application. If necessary, you may change
    | subdomain under which all of the Fortify routes will be available.
    |
    | PT-BR: Aqui você pode especificar qual prefixo Fortify atribuirá a todas as rotas que ele registra com a aplicação. Se necessário, você pode alterar o subdomínio em que todas as rotas do Fortify estarão disponíveis.
    |
    */

    'prefix' => '',

    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Fortify Routes Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify which middleware Fortify will assign to the routes
    | that it registers with the application. If necessary, you may change
    | these middleware but typically this provided default is preferred.
    |
    | PT-BR: Aqui você pode especificar qual middleware Fortify atribuirá às rotas que ele registra com a aplicação. Se necessário, você pode alterar esses middleware, mas normalmente este padrão fornecido é preferido.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | By default, Fortify will throttle logins to five requests per minute for
    | every email and IP address combination. However, if you would like to
    | specify a custom rate limiter to call then you may specify it here.
    |
    | PT-BR: Por padrão, o Fortify limita o login a 5 requisições por minuto para cada e-mail e endereço IP. No entanto, se você deseja especificar um limite personalizado para chamar, você pode especificar aqui.
    |
    */

    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Register View Routes
    |--------------------------------------------------------------------------
    |
    | Here you may specify if the routes returning views should be disabled as
    | you may not need them when building your own application. This may be
    | especially true if you're writing a custom single-page application.
    |
    | PT-BR: Aqui você pode especificar se as rotas retornando visualizações devem ser desabilitadas, pois você pode não precisar delas ao construir sua própria aplicação. Isso pode ser especialmente verdadeiro se você estiver escrevendo uma aplicação de página única personalizada.
    |
    */

    'views' => true,

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Some of the Fortify features are optional. You may disable the features
    | by removing them from this array. You're free to only remove some of
    | these features, or you can even remove all of these if you need to.
    |
    | PT-BR: Algumas das funcionalidades do Fortify são opcionais. Você pode desabilitá-las removendo-as deste array. Você é livre para remover apenas algumas dessas funcionalidades, ou até mesmo remover todas elas se necessário.
    |
    */

    'features' => [
        Features::resetPasswords(),
        Features::emailVerification(),
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
            // 'window' => 0
        ]),
    ],

];
