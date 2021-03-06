<?php

declare(strict_types=1);

namespace ForkBB\Controllers;

use ForkBB\Core\Container;
use ForkBB\Models\Page;

class Routing
{
    /**
     * Контейнер
     * @var Container
     */
    protected $c;

    public function __construct(Container $container)
    {
        $this->c = $container;
    }

    /**
     * Маршрутиризация
     */
    public function routing(): Page
    {
        $user   = $this->c->user;
        $config = $this->c->config;
        $r      = $this->c->Router;

        // регистрация/вход/выход
        if ($user->isGuest) {
            // вход
            $r->add(
                $r::DUO,
                '/login',
                'Auth:login',
                'Login'
            );
            // забыли кодовую фразу
            $r->add(
                $r::DUO,
                '/login/forget',
                'Auth:forget',
                'Forget'
            );
            // смена кодовой фразы
            $r->add(
                $r::DUO,
                '/login/{id:\d+}/{key}/{hash}',
                'Auth:changePass',
                'ChangePassword'
            );

            // регистрация
            if ('1' == $config->o_regs_allow) {
                $r->add(
                    $r::GET,
                    '/registration',
                    'Rules:confirmation',
                    'Register'
                );
                $r->add(
                    $r::PST,
                    '/registration/agree',
                    'Register:reg',
                    'RegisterForm'
                );
                $r->add(
                    $r::GET,
                    '/registration/activate/{id:\d+}/{key}/{hash}',
                    'Register:activate',
                    'RegActivate'
                );
            }
        } else {
            // выход
            $r->add(
                $r::GET,
                '/logout/{token}',
                'Auth:logout',
                'Logout'
            );

            // обработка "кривых" перенаправлений с логина и регистрации
            $r->add(
                $r::GET,
                '/login[/{tail:.*}]',
                'Redirect:toIndex'
            );
            $r->add(
                $r::GET,
                '/registration[/{tail:.*}]',
                'Redirect:toIndex'
            );
        }
        // просмотр разрешен
        if ('1' == $user->g_read_board) {
            // главная
            $r->add(
                $r::GET,
                '/',
                'Index:view',
                'Index'
            );
            $r->add(
                $r::GET,
                '/index.php',
                'Redirect:toIndex'
            );
            $r->add(
                $r::GET,
                '/index.html',
                'Redirect:toIndex'
            );
            // правила
            if (
                '1' == $config->o_rules
                && (
                    ! $user->isGuest
                    || '1' == $config->o_regs_allow
                )
            ) {
                $r->add(
                    $r::GET,
                    '/rules',
                    'Rules:view',
                    'Rules'
                );
            }
            // поиск
            if ('1' == $user->g_search) {
                $r->add(
                    $r::GET,
                    '/search[/simple/{keywords}[/{page:[1-9]\d*}]]',
                    'Search:view',
                    'Search'
                );
                $r->add(
                    $r::PST,
                    '/search',
                    'Search:view'
                );

                $r->add(
                    $r::GET,
                    '/search/advanced[/{keywords}/{author}/{forums}/{serch_in:\d}/{sort_by:\d}/{sort_dir:\d}/{show_as:\d}[/{page:[1-9]\d*}]]',
                    'Search:viewAdvanced',
                    'SearchAdvanced'
                );
                $r->add(
                    $r::PST,
                    '/search/advanced',
                    'Search:viewAdvanced'
                );

                $r->add(
                    $r::GET,
                    '/search[/user/{uid:[2-9]|[1-9]\d+}]/{action:(?!search)[a-z_]+}[/in_forum/{forum:[1-9]\d*}][/{page:[1-9]\d*}]',
                    'Search:action',
                    'SearchAction'
                );
            }
            // юзеры
            if ($user->viewUsers) {
                // список пользователей
                $r->add(
                    $r::GET,
                    '/userlist[/{group:all|[1-9]\d*}/{sort:username|registered|num_posts}/{dir:ASC|DESC}/{name}][/{page:[1-9]\d*}]',
                    'Userlist:view',
                    'Userlist'
                );
                $r->add(
                    $r::PST,
                    '/userlist',
                    'Userlist:view'
                );
                // юзеры
                $r->add(
                    $r::GET,
                    '/user/{id:[2-9]|[1-9]\d+}/{name}',
                    'ProfileView:view',
                    'User'
                );
                $r->add(
                    $r::DUO,
                    '/user/{id:[2-9]|[1-9]\d+}/edit/profile',
                    'ProfileEdit:edit',
                    'EditUserProfile'
                );
                $r->add(
                    $r::DUO,
                    '/user/{id:[2-9]|[1-9]\d+}/edit/config',
                    'ProfileConfig:config',
                    'EditUserBoardConfig'
                );
                $r->add(
                    $r::DUO,
                    '/user/{id:[2-9]|[1-9]\d+}/edit/email',
                    'ProfileEmail:email',
                    'EditUserEmail'
                );
                $r->add(
                    $r::DUO,
                    '/user/{id:[2-9]|[1-9]\d+}/edit/passphrase',
                    'ProfilePass:pass',
                    'EditUserPass'
                );
                $r->add(
                    $r::DUO,
                    '/user/{id:[2-9]|[1-9]\d+}/edit/moderation',
                    'ProfileMod:moderation',
                    'EditUserModeration'
                );
            } elseif (! $user->isGuest) {
                // только свой профиль
                $r->add(
                    $r::GET,
                    '/user/{id:' . $user->id . '}/{name}',
                    'ProfileView:view',
                    'User'
                );
                $r->add(
                    $r::DUO,
                    '/user/{id:' . $user->id . '}/edit/profile',
                    'ProfileEdit:edit',
                    'EditUserProfile'
                );
                $r->add(
                    $r::DUO,
                    '/user/{id:' . $user->id . '}/edit/config',
                    'ProfileConfig:config',
                    'EditUserBoardConfig'
                );
                $r->add(
                    $r::DUO,
                    '/user/{id:' . $user->id . '}/edit/email',
                    'ProfileEmail:email',
                    'EditUserEmail'
                );
                $r->add(
                    $r::DUO,
                    '/user/{id:' . $user->id . '}/edit/passphrase',
                    'ProfilePass:pass',
                    'EditUserPass'
                );
            }
            // смена своего email
            if (! $user->isGuest) {
                $r->add(
                    $r::GET,
                    '/user/{id:' . $user->id . '}/{email}/{key}/{hash}',
                    'ProfileEmail:setEmail',
                    'SetNewEmail'
                );
            }
            // пометка разделов прочитанными
            if (! $user->isGuest) {
                $r->add(
                    $r::GET,
                    '/forum/{id:\d+}/markread/{token}',
                    'Misc:markread',
                    'MarkRead'
                );
            }

            // разделы
            $r->add(
                $r::GET,
                '/forum/{id:[1-9]\d*}/{name}[/{page:[1-9]\d*}]',
                'Forum:view',
                'Forum'
            );
            $r->add(
                $r::DUO,
                '/forum/{id:[1-9]\d*}/new/topic',
                'Post:newTopic',
                'NewTopic'
            );
            // темы
            $r->add(
                $r::GET,
                '/topic/{id:[1-9]\d*}/{name}[/{page:[1-9]\d*}]',
                'Topic:viewTopic',
                'Topic'
            );
            $r->add(
                $r::GET,
                '/topic/{id:[1-9]\d*}/view/new',
                'Topic:viewNew',
                'TopicViewNew'
            );
            $r->add(
                $r::GET,
                '/topic/{id:[1-9]\d*}/view/unread',
                'Topic:viewUnread',
                'TopicViewUnread'
            );
            $r->add(
                $r::GET,
                '/topic/{id:[1-9]\d*}/view/last',
                'Topic:viewLast',
                'TopicViewLast'
            );
            $r->add(
                $r::GET,
                '/topic/{id:[1-9]\d*}/new/reply[/{quote:[1-9]\d*}]',
                'Post:newReply',
                'NewReply'
            );
            $r->add(
                $r::PST,
                '/topic/{id:[1-9]\d*}/new/reply',
                'Post:newReply'
            );
            // сообщения
            $r->add(
                $r::GET,
                '/post/{id:[1-9]\d*}#p{id}',
                'Topic:viewPost',
                'ViewPost'
            );
            $r->add(
                $r::DUO,
                '/post/{id:[1-9]\d*}/edit',
                'Edit:edit',
                'EditPost'
            );
            $r->add(
                $r::DUO,
                '/post/{id:[1-9]\d*}/delete',
                'Delete:delete',
                'DeletePost'
            );
            // сигналы (репорты)
            if (
                ! $user->isAdmin
                && ! $user->isGuest
            ) { // ????
                $r->add(
                    $r::DUO,
                    '/post/{id:[1-9]\d*}/report',
                    'Report:report',
                    'ReportPost'
                );
            }
            // отправка email
            if (
                ! $user->isGuest
                && '1' == $user->g_send_email
            ) {
                $r->add(
                    $r::DUO,
                    '/send_email/{id:[2-9]|[1-9]\d+}',
                    'Email:email',
                    'SendEmail'
                );
            }
            // feed
            $r->add(
                $r::GET,
                '/feed/{type:atom|rss}[/forum/{fid:[1-9]\d*}][/topic/{tid:[1-9]\d*}]',
                'Feed:view',
                'Feed'
            );
            // подписки
            if (
                ! $user->isGuest
                && ! $user->isUnverified
            ) {
                $r->add(
                    $r::GET,
                    '/forum/{fid:[1-9]\d*}/{type:subscribe|unsubscribe}/{token}',
                    'Misc:forumSubscription',
                    'ForumSubscription'
                );
                $r->add(
                    $r::GET,
                    '/topic/{tid:[1-9]\d*}/{type:subscribe|unsubscribe}/{token}',
                    'Misc:topicSubscription',
                    'TopicSubscription'
                );
            }

        }
        // админ и модератор
        if ($user->isAdmMod) {
            $r->add(
                $r::GET,
                '/admin/',
                'AdminIndex:index',
                'Admin'
            );
            $r->add(
                $r::GET,
                '/admin/statistics',
                'AdminStatistics:statistics',
                'AdminStatistics'
            );

            if ($this->c->userRules->viewIP) {
                $r->add(
                    $r::GET,
                    '/admin/get/host/{ip:[0-9a-fA-F:.]+}',
                    'AdminHost:view',
                    'AdminHost'
                );
                $r->add(
                    $r::GET,
                    '/admin/users/user/{id:[2-9]|[1-9]\d+}[/{page:[1-9]\d*}]',
                    'AdminUsersStat:view',
                    'AdminUserStat'
                );
            }

            $r->add(
                $r::DUO,
                '/admin/users',
                'AdminUsers:view',
                'AdminUsers'
            );
            $r->add(
                $r::DUO,
                '/admin/users/result/{data}[/{page:[1-9]\d*}]',
                'AdminUsersResult:view',
                'AdminUsersResult'
            );
            $r->add(
                $r::DUO,
                '/admin/users/{action:\w+}/{ids:\d+(?:-\d+)*}[/{token}]',
                'AdminUsersAction:view',
                'AdminUsersAction'
            );

            $r->add(
                $r::GET,
                '/admin/users/promote/{uid:[2-9]|[1-9]\d+}/{pid:[1-9]\d*}/{token}',
                'AdminUsersPromote:promote',
                'AdminUserPromote'
            );

            if ($user->isAdmin) {
                $r->add(
                    $r::DUO,
                    '/admin/users/new',
                    'AdminUsersNew:view',
                    'AdminUsersNew'
                );
                $r->add(
                    $r::PST,
                    '/admin/users/recalculate',
                    'AdminUsers:recalculate',
                    'AdminUsersRecalculate'
                );
            }

            if ($this->c->userRules->banUsers) {
                $r->add(
                    $r::DUO,
                    '/admin/bans',
                    'AdminBans:view',
                    'AdminBans'
                );
                $r->add(
                    $r::DUO,
                    '/admin/bans/new[/{ids:\d+(?:-\d+)*}[/{uid:[2-9]|[1-9]\d+}]]',
                    'AdminBans:add',
                    'AdminBansNew'
                );
                $r->add(
                    $r::DUO,
                    '/admin/bans/edit/{id:[1-9]\d*}',
                    'AdminBans:edit',
                    'AdminBansEdit'
                );
                $r->add(
                    $r::GET,
                    '/admin/bans/result/{data}[/{page:[1-9]\d*}]',
                    'AdminBans:result',
                    'AdminBansResult'
                );
                $r->add(
                    $r::GET,
                    '/admin/bans/delete/{id:[1-9]\d*}/{token}[/{uid:[2-9]|[1-9]\d+}]',
                    'AdminBans:delete',
                    'AdminBansDelete'
                );
            }

            if (
                $user->isAdmin
                || 0 === $config->i_report_method
                || 2 === $config->i_report_method
            ) {
                $r->add(
                    $r::GET,
                    '/admin/reports',
                    'AdminReports:view',
                    'AdminReports'
                );
                $r->add(
                    $r::GET,
                    '/admin/reports/zap/{id:[1-9]\d*}/{token}',
                    'AdminReports:zap',
                    'AdminReportsZap'
                );
            }

            $r->add(
                $r::PST,
                '/moderate',
                'Moderate:action',
                'Moderate'
            );

        }
        // только админ
        if ($user->isAdmin) {
            $r->add(
                $r::GET,
                '/admin/statistics/info',
                'AdminStatistics:info',
                'AdminInfo'
            );
            $r->add(
                $r::DUO,
                '/admin/options',
                'AdminOptions:edit',
                'AdminOptions'
            );
            $r->add(
                $r::DUO,
                '/admin/parser',
                'AdminParser:edit',
                'AdminParser'
            );
            $r->add(
                $r::DUO,
                '/admin/parser/bbcode',
                'AdminParserBBCode:view',
                'AdminBBCode'
            );
            $r->add(
                $r::GET,
                '/admin/parser/bbcode/delete/{id:[1-9]\d*}/{token}',
                'AdminParserBBCode:delete',
                'AdminBBCodeDelete'
            );
            $r->add(
                $r::DUO,
                '/admin/parser/bbcode/edit/{id:[1-9]\d*}',
                'AdminParserBBCode:edit',
                'AdminBBCodeEdit'
            );
            $r->add(
                $r::DUO,
                '/admin/parser/bbcode/new',
                'AdminParserBBCode:edit',
                'AdminBBCodeNew'
            );
            $r->add(
                $r::GET,
                '/admin/parser/bbcode/default/{id:[1-9]\d*}/{token}',
                'AdminParserBBCode:default',
                'AdminBBCodeDefault'
            );
            $r->add(
                $r::DUO,
                '/admin/parser/smilies',
                'AdminParserSmilies:view',
                'AdminSmilies'
            );
            $r->add(
                $r::GET,
                '/admin/parser/smilies/delete/{name}/{token}',
                'AdminParserSmilies:delete',
                'AdminSmiliesDelete'
            );
            $r->add(
                $r::PST,
                '/admin/parser/smilies/upload',
                'AdminParserSmilies:upload',
                'AdminSmiliesUpload'
            );
            $r->add(
                $r::DUO,
                '/admin/categories',
                'AdminCategories:view',
                'AdminCategories'
            );
            $r->add(
                $r::DUO,
                '/admin/categories/{id:[1-9]\d*}/delete',
                'AdminCategories:delete',
                'AdminCategoriesDelete'
            );
            $r->add(
                $r::DUO,
                '/admin/forums',
                'AdminForums:view',
                'AdminForums'
            );
            $r->add(
                $r::DUO,
                '/admin/forums/new',
                'AdminForums:edit',
                'AdminForumsNew'
            );
            $r->add(
                $r::DUO,
                '/admin/forums/{id:[1-9]\d*}/edit',
                'AdminForums:edit',
                'AdminForumsEdit'
            );
            $r->add(
                $r::DUO,
                '/admin/forums/{id:[1-9]\d*}/delete',
                'AdminForums:delete',
                'AdminForumsDelete'
            );
            $r->add(
                $r::GET,
                '/admin/groups',
                'AdminGroups:view',
                'AdminGroups'
            );
            $r->add(
                $r::PST,
                '/admin/groups/default',
                'AdminGroups:defaultSet',
                'AdminGroupsDefault'
            );
            $r->add(
                $r::PST,
                '/admin/groups/new[/{base:[1-9]\d*}]',
                'AdminGroups:edit',
                'AdminGroupsNew'
            );
            $r->add(
                $r::DUO,
                '/admin/groups/{id:[1-9]\d*}/edit',
                'AdminGroups:edit',
                'AdminGroupsEdit'
            );
            $r->add(
                $r::DUO,
                '/admin/groups/{id:[1-9]\d*}/delete',
                'AdminGroups:delete',
                'AdminGroupsDelete'
            );
            $r->add(
                $r::DUO,
                '/admin/censoring',
                'AdminCensoring:edit',
                'AdminCensoring'
            );
            $r->add(
                $r::DUO,
                '/admin/maintenance',
                'AdminMaintenance:view',
                'AdminMaintenance'
            );
            $r->add(
                $r::PST,
                '/admin/maintenance/rebuild',
                'AdminMaintenance:rebuild',
                'AdminMaintenanceRebuild'
            );
            $r->add(
                $r::GET,
                '/admin/maintenance/rebuild/{token}/{clear:[01]}/{limit:[1-9]\d*}/{start:[1-9]\d*}',
                'AdminMaintenance:rebuild',
                'AdminRebuildIndex'
            );

        }

        $uri = $_SERVER['REQUEST_URI'];
        if (false !== ($pos = \strpos($uri, '?'))) {
            $uri = \substr($uri, 0, $pos);
        }
        $uri    = \rawurldecode($uri);
        $method = $_SERVER['REQUEST_METHOD'];

        $route = $r->route($method, $uri);
        $page  = null;
        switch ($route[0]) {
            case $r::OK:
                // ... 200 OK
                list($page, $action) = \explode(':', $route[1], 2);
                $page = $this->c->$page->$action($route[2], $method);
                break;
            case $r::NOT_FOUND:
                // ... 404 Not Found
                if (
                    '1' != $user->g_read_board
                    && $user->isGuest
                ) {
                    $page = $this->c->Redirect->page('Login');
                } else {
                    $page = $this->c->Message->message('Bad request');
                }
                break;
            case $r::METHOD_NOT_ALLOWED:
                // ... 405 Method Not Allowed
                $page = $this->c->Message->message(
                    'Bad request',
                    true,
                    405,
                    [
                        ['Allow', \implode(',', $route[1])],
                    ]
                );
                break;
            case $r::NOT_IMPLEMENTED:
                // ... 501 Not implemented
                $page = $this->c->Message->message('Bad request', true, 501);
                break;
        }

        return $page;
    }
}
