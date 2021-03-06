<?php

declare(strict_types=1);

\error_reporting(\E_ALL);
\ini_set('display_errors', '1');
\ini_set('log_errors', '1');

function forkGetBaseURL()
{
    $baseURL = 'http://'
        . \preg_replace('%:(80|443)$%', '', $_SERVER['HTTP_HOST'])
        . \str_replace('\\', '/', \dirname($_SERVER['SCRIPT_NAME']));

    return \rtrim($baseURL, '/');
}

return [
    'BASE_URL'         => forkGetBaseURL(),
    'DEBUG'            => 0,
    'GROUP_ADMIN'      => 1,
    'GROUP_MOD'        => 2,
    'GROUP_GUEST'      => 3,
    'GROUP_MEMBER'     => 4,
    'GROUP_NEW_MEMBER' => 5,
    'EOL'              => PHP_EOL,
    'MAX_EMAIL_LENGTH' => 80,
    'FLOOD_INTERVAL'   => 3600,


    'HMAC' => [
        'algo' => 'sha1',
        'salt' => '_SALT_FOR_HMAC_',
    ],

    'forConfig' => [
        'o_default_lang'   => 'en',
        'o_default_style'  => 'ForkBB',
        'o_redirect_delay' => 0,
        'o_maintenance'    => 0,
        'o_smtp_host'      => '',
        'o_smtp_user'      => '',
        'o_smtp_pass'      => '',
        'o_smtp_ssl'       => '',
    ],

    'shared' => [
        'DB' => [
            'class' => \ForkBB\Core\DB::class,
            'dsn'      => '%DB_DSN%',
            'username' => '%DB_USERNAME%',
            'password' => '%DB_PASSWORD%',
            'options'  => '%DB_OPTIONS%',
            'prefix'   => '%DB_PREFIX%',
        ],
        'Secury' => [
            'class' => \ForkBB\Core\Secury::class,
            'hmac'  => '%HMAC%',
        ],
        'FileCache' => [
            'class'     => \ForkBB\Core\Cache\FileCache::class,
            'cache_dir' => '%DIR_CACHE%',
        ],
        'Cache' => [
            'class'    => \ForkBB\Core\Cache::class,
            'provider' => '@FileCache',
        ],
        'Validator' => \ForkBB\Core\Validator::class,
        'View' => [
            'class'     => \ForkBB\Core\View::class,
            'cache_dir' => '%DIR_CACHE%',
            'views_dir' => '%DIR_VIEWS%',
        ],
        'Router' => [
            'class'    => \ForkBB\Core\Router::class,
            'base_url' => '%BASE_URL%',
            'csrf'     => '@Csrf'
        ],
        'Lang' => \ForkBB\Core\Lang::class,
        'Mail' => [
            'class' => \ForkBB\Core\Mail::class,
            'host'  => '%config.o_smtp_host%',
            'user'  => '%config.o_smtp_user%',
            'pass'  => '%config.o_smtp_pass%',
            'ssl'   => '%config.o_smtp_ssl%',
            'eol'   => '%EOL%',
        ],
        'Func' => \ForkBB\Core\Func::class,
        'NormEmail' => \MioVisman\NormEmail\NormEmail::class,
        'Csrf' => [
            'class'  => \ForkBB\Core\Csrf::class,
            'Secury' => '@Secury',
            'key'    => '%user.password%%user.ip%%user.id%%BASE_URL%',
        ],

        'config'     => '@ConfigModel:install',
        'users'      => \ForkBB\Models\User\Manager::class,

        'VLemail'    => \ForkBB\Models\Validators\Email::class,
    ],
    'multiple'  => [
        'PrimaryController' => \ForkBB\Controllers\Install::class,
        'Primary' => '@PrimaryController:routing',

        'Debug'    => \ForkBB\Models\Pages\Debug::class,
        'Install'  => \ForkBB\Models\Pages\Admin\Install::class,
        'Redirect' => \ForkBB\Models\Pages\Redirect::class,

        'UserModel' => \ForkBB\Models\User\Model::class,

        'ConfigModel'        => \ForkBB\Models\Config\Model::class,
        'ConfigModelInstall' => \ForkBB\Models\Config\Install::class,

    ],
];
