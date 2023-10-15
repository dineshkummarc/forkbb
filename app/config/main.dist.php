<?php
/**
 * This file is part of the ForkBB <https://github.com/forkbb>.
 *
 * @copyright (c) Visman <mio.visman@yandex.ru, https://github.com/MioVisman>
 * @license   The MIT License (MIT)
 */

declare(strict_types=1);

# development
#\error_reporting(\E_ALL);
#\ini_set('display_errors', '1');
#\ini_set('log_errors', '1');

return [
    'BASE_URL'    => '_BASE_URL_',
    'EOL'         => \PHP_EOL, // Define line breaks in mail headers; possible values can be \PHP_EOL, "\r\n", "\n" or "\r"
    'DB_DSN'      => '_DB_DSN_',
    'DB_USERNAME' => '_DB_USERNAME_',
    'DB_PASSWORD' => '_DB_PASSWORD_',
    'DB_OPTIONS'  => [_DB_OPTIONS_],
    'DB_PREFIX'   => '_DB_PREFIX_',
    'COOKIE' => [
        'prefix'   => '_COOKIE_PREFIX_',
        'domain'   => '_COOKIE_DOMAIN_',
        'path'     => '_COOKIE_PATH_',
        'secure'   => _COOKIE_SECURE_,
        'samesite' => 'Lax', // Strict, Lax or None
        'time'     => 1209600,
        'key1'     => '_COOKIE_KEY1_',
        'key2'     => '_COOKIE_KEY2_',
    ],
    'HMAC' => [
        'algo' => 'sha1',
        'salt' => '_SALT_FOR_HMAC_',
    ],
    'DEBUG'            => 0, // 0&1st: time/memory, 1st: queries, 2nd: message 400+ to log
    'MAINTENANCE_OFF'  => false,
    'BBCODE_INFO'      => [
        'smTpl'    => '<img src="{url}" alt="{alt}">',
        'smTplTag' => 'img',
        'smTplBl'  => ['url'],
    ],
    'MAX_POST_SIZE'    => 65536,
    'MAX_SUBJ_LENGTH'  => 70,     // max 255
    'MAX_IMG_SIZE'     => '2M',
    'MAX_FILE_SIZE'    => '2M',
    'MAX_EMAIL_LENGTH' => 80,
    'FLOOD_INTERVAL'   => 3600,
    'USERNAME'         => [
        'phpPattern' => '%^\p{L}[\p{L}\p{N}\x20\._-]+$%uD',
        'jsPattern'  => '^.{2,}$',
        'min'        => 2,
        'max'        => 25,
    ],
    'HTTP_HEADERS'     => [
        'common' => [
            'X-Content-Type-Options'  => 'nosniff',
            'X-Frame-Options'         => 'DENY',
            'X-XSS-Protection'        => '1; mode=block',
            'Referrer-Policy'         => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => 'default-src \'self\';img-src *;object-src \'none\';frame-ancestors \'none\';base-uri \'self\';form-action \'self\'',
            'Feature-Policy'          => 'accelerometer \'none\';ambient-light-sensor \'none\';autoplay \'none\';battery \'none\';camera \'none\';document-domain \'self\';fullscreen \'self\';geolocation \'none\';gyroscope \'none\';magnetometer \'none\';microphone \'none\';midi \'none\';payment \'none\';picture-in-picture \'none\';sync-xhr \'self\';usb \'none\'',
        ],
        'secure' => [
            'X-Content-Type-Options'  => 'nosniff',
            'X-Frame-Options'         => 'DENY',
            'X-XSS-Protection'        => '1; mode=block',
            'Referrer-Policy'         => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => 'default-src \'self\';object-src \'none\';frame-ancestors \'none\';base-uri \'self\';form-action \'self\'',
            'Feature-Policy'          => 'accelerometer \'none\';ambient-light-sensor \'none\';autoplay \'none\';battery \'none\';camera \'none\';document-domain \'self\';fullscreen \'self\';geolocation \'none\';gyroscope \'none\';magnetometer \'none\';microphone \'none\';midi \'none\';payment \'none\';picture-in-picture \'none\';sync-xhr \'self\';usb \'none\'',
        ],
    ],
    'DATE_FORMATS' => ['Y-m-d', 'd M Y', 'Y-m-d', 'Y-d-m', 'd-m-Y', 'm-d-Y', 'M j Y', 'jS M Y'],
    'TIME_FORMATS' => ['H:i:s', 'H:i', 'H:i:s', 'H:i', 'g:i:s a', 'g:i a'],

    'shared' => [
        '%DIR_ROOT%'   => \realpath(__DIR__ . '/../..'),
        '%DIR_PUBLIC%' => '%DIR_ROOT%/public',
        '%DIR_APP%'    => '%DIR_ROOT%/app',
        '%DIR_CACHE%'  => '%DIR_APP%/cache',
        '%DIR_CONFIG%' => '%DIR_APP%/config',
        '%DIR_LANG%'   => '%DIR_APP%/lang',
        '%DIR_LOG%'    => '%DIR_APP%/log',
        '%DIR_VIEWS%'  => '%DIR_APP%/templates',
        '%DIR_EXT%'    => '%DIR_ROOT%/ext',

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
        'Cache' => [
            'class'      => \ForkBB\Core\Cache\FileCache::class,
            'cache_dir'  => '%DIR_CACHE%',
            'reset_mark' => '%DB_DSN% %DB_PREFIX%',
        ],
        'Validator' => \ForkBB\Core\Validator::class,
        'View' => [
            'class'  => \ForkBB\Core\View::class,
            'config' => [
                'cache'      => '%DIR_CACHE%',
                'defaultDir' => '%DIR_VIEWS%/_default',
                'userDir'    => '%DIR_VIEWS%/_user',
            ],
        ],
        'Router' => [
            'class'    => \ForkBB\Core\Router::class,
            'base_url' => '%BASE_URL%',
            'csrf'     => '@Csrf',
        ],
        'Lang' => \ForkBB\Core\Lang::class,
        'Mail' => [
            'class' => \ForkBB\Core\Mail::class,
            'host'  => '%config.o_smtp_host%',
            'user'  => '%config.o_smtp_user%',
            'pass'  => '%config.o_smtp_pass%',
            'ssl'   => '%config.b_smtp_ssl%',
            'eol'   => '%EOL%',
        ],
        'Func'      => \ForkBB\Core\Func::class,
        'Test'      => [
            'class'  => \ForkBB\Core\Test::class,
            'config' => '%DIR_CONFIG%/test.default.php',
        ],
        'NormEmail' => \MioVisman\NormEmail\NormEmail::class,
        'Log'       => [
            'class'  => \ForkBB\Core\Log::class,
            'config' => [
                'path'       => '%DIR_LOG%/{Y-m-d}.log',
                'lineFormat' => "\\%datetime\\% [\\%level_name\\%] \\%message\\%\t\\%context\\%\n",
                'timeFormat' => 'Y-m-d H:i:s',
            ],
        ],
        'LogViewer' => [
            'class'  => \ForkBB\Core\LogViewer::class,
            'config' => [
                'dir'        => '%DIR_LOG%',
                'pattern'    => '*.log',
                'lineFormat' => "\\%datetime\\% [\\%level_name\\%] \\%message\\%\t\\%context\\%\n",
            ],
            'cache' => '%Cache%',
        ],
        'HTMLCleaner' => [
            'class'  => \ForkBB\Core\HTMLCleaner::class,
            'config' => '%DIR_CONFIG%/jevix.default.php',
        ],

        'config'        => '@ConfigModel:init',
        'bans'          => '@BanListModel:init',
        'censorship'    => '@CensorshipModel:init',
        'stats'         => '@StatsModel:init',
        'admins'        => '@AdminListModel:init',
        'smilies'       => '@SmileyListModel:init',
        'dbMap'         => '@DBMapModel:init',
        'stopwords'     => '@StopwordListModel:init',
        'forums'        => '@ForumManager:init',
        'topics'        => \ForkBB\Models\Topic\Topics::class,
        'posts'         => \ForkBB\Models\Post\Posts::class,
        'polls'         => \ForkBB\Models\Poll\Polls::class,
        'reports'       => \ForkBB\Models\Report\Reports::class,
        'user'          => '@users:current',
        'userRules'     => '@UsersRules:init',
        'users'         => \ForkBB\Models\User\Users::class,
        'groups'        => '@GroupManager:init',
        'categories'    => '@CategoriesManager:init',
        'search'        => \ForkBB\Models\Search\Search::class,
        'subscriptions' => \ForkBB\Models\Subscription\Subscription::class,
        'bbcode'        => '@BBCodeListModel:init',
        'pms'           => \ForkBB\Models\PM\PM::class,
        'providers'     => [
            'class'   => \ForkBB\Models\Provider\Providers::class,
            'drivers' => [
                'github' => \ForkBB\Models\Provider\Driver\GitHub::class,
                'yandex' => \ForkBB\Models\Provider\Driver\Yandex::class,
                'google' => \ForkBB\Models\Provider\Driver\Google::class,
            ],
        ],
        'providerUser'  => \ForkBB\Models\ProviderUser\ProviderUser::class,
        'attachments'   => \ForkBB\Models\Attachment\Attachments::class,
        'extensions'    => '@ExtensionManager:init',

        'Csrf' => [
            'class'   => \ForkBB\Core\Csrf::class,
            'Secury'  => '@Secury',
            'key'     => '%user.password%%user.ip%%user.id%%BASE_URL%',
            'extSalt' => '',
        ],
        'Online' => \ForkBB\Models\Online\Online::class,
        'Cookie' => [
            'class'   => \ForkBB\Models\Cookie\Cookie::class,
            'options' => '%COOKIE%',
        ],

        'Parser' => [
            'class' => \ForkBB\Core\Parser::class,
            'flag'  => \ENT_HTML5,
        ],
        'Files' => [
            'class'   => \ForkBB\Core\Files::class,
            'file'    => '%MAX_FILE_SIZE%',
            'img'     => '%MAX_IMG_SIZE%',
            'drivers' => [
                \ForkBB\Core\Image\ImagickDriver::class,
                \ForkBB\Core\Image\GDDriver::class,
                \ForkBB\Core\Image\DefaultDriver::class,
            ],
        ],

        'BBCodeListModel' => [
            'class' => \ForkBB\Models\BBCodeList\BBCodeList::class,
            'file'  => 'defaultBBCode.php',
        ],
        'PBlockModel'     => \ForkBB\Models\PM\PBlock::class,

        'VLnoURL'    => \ForkBB\Models\Validators\NoURL::class,
        'VLusername' => \ForkBB\Models\Validators\Username::class,
        'VLemail'    => \ForkBB\Models\Validators\Email::class,
        'VLhtml'     => \ForkBB\Models\Validators\Html::class,
        'VLnekot'    => \ForkBB\Models\Validators\Nekot::class,

        'ProfileRules' => \ForkBB\Models\Rules\Profile::class,
        'UsersRules'   => \ForkBB\Models\Rules\Users::class,

        'BanList/check'    => \ForkBB\Models\BanList\Check::class,
        'BanList/delete'   => \ForkBB\Models\BanList\Delete::class,
        'BanList/filter'   => \ForkBB\Models\BanList\Filter::class,
        'BanList/getList'  => \ForkBB\Models\BanList\GetList::class,
        'BanList/insert'   => \ForkBB\Models\BanList\Insert::class,
        'BanList/isBanned' => \ForkBB\Models\BanList\IsBanned::class,
        'BanList/load'     => \ForkBB\Models\BanList\Load::class,
        'BanList/update'   => \ForkBB\Models\BanList\Update::class,

        'BBCodeList/delete'   => \ForkBB\Models\BBCodeList\Delete::class,
        'BBCodeList/generate' => \ForkBB\Models\BBCodeList\Generate::class,
        'BBCodeList/insert'   => \ForkBB\Models\BBCodeList\Insert::class,
        'BBCodeList/load'     => \ForkBB\Models\BBCodeList\Load::class,
        'BBCodeList/update'   => \ForkBB\Models\BBCodeList\Update::class,

        'Censorship/load'    => \ForkBB\Models\Censorship\Load::class,
        'Censorship/refresh' => \ForkBB\Models\Censorship\Refresh::class,
        'Censorship/save'    => \ForkBB\Models\Censorship\Save::class,

        'Config/insensitive' => \ForkBB\Models\Config\Insensitive::class,
        'Config/load'        => \ForkBB\Models\Config\Load::class,
        'Config/save'        => \ForkBB\Models\Config\Save::class,

        'Forum/calcStat' => \ForkBB\Models\Forum\CalcStat::class,

        'Forums/delete'         => \ForkBB\Models\Forum\Delete::class,
        'Forums/loadTree'       => \ForkBB\Models\Forum\LoadTree::class,
        'Forums/markread'       => \ForkBB\Models\Forum\Markread::class,
        'Forums/refresh'        => \ForkBB\Models\Forum\Refresh::class,
        'Forums/save'           => \ForkBB\Models\Forum\Save::class,
        'Forums/updateUsername' => \ForkBB\Models\Forum\UpdateUsername::class,

        'Groups/delete' => \ForkBB\Models\Group\Delete::class,
        'Groups/perm'   => \ForkBB\Models\Group\Perm::class,
        'Groups/save'   => \ForkBB\Models\Group\Save::class,

        'Online/info'           => \ForkBB\Models\Online\Info::class,
        'Online/updateUsername' => \ForkBB\Models\Online\UpdateUsername::class,

        'PMS/delete'         => \ForkBB\Models\PM\Delete::class,
        'PMS/load'           => \ForkBB\Models\PM\Load::class,
        'PMS/save'           => \ForkBB\Models\PM\Save::class,
        'PMS/updateUsername' => \ForkBB\Models\PM\UpdateUsername::class,

        'PTopic/calcStat' => \ForkBB\Models\PM\CalcStat::class,

        'Polls/delete'   => \ForkBB\Models\Poll\Delete::class,
        'Polls/load'     => \ForkBB\Models\Poll\Load::class,
        'Polls/revision' => \ForkBB\Models\Poll\Revision::class,
        'Polls/save'     => \ForkBB\Models\Poll\Save::class,

        'Posts/delete'         => \ForkBB\Models\Post\Delete::class,
        'Posts/feed'           => \ForkBB\Models\Post\Feed::class,
        'Posts/load'           => \ForkBB\Models\Post\Load::class,
        'Posts/move'           => \ForkBB\Models\Post\Move::class,
        'Posts/previousPost'   => \ForkBB\Models\Post\PreviousPost::class,
        'Posts/rebuildIndex'   => \ForkBB\Models\Post\RebuildIndex::class,
        'Posts/save'           => \ForkBB\Models\Post\Save::class,
        'Posts/updateUsername' => \ForkBB\Models\Post\UpdateUsername::class,
        'Posts/userInfoFromIP' => \ForkBB\Models\Post\UserInfoFromIP::class,
        'Posts/userStat'       => \ForkBB\Models\Post\UserStat::class,
        'Posts/view'           => \ForkBB\Models\Post\View::class,

        'Reports/load' => \ForkBB\Models\Report\Load::class,
        'Reports/save' => \ForkBB\Models\Report\Save::class,

        'Search/actionP'       => \ForkBB\Models\Search\ActionP::class,
        'Search/actionT'       => \ForkBB\Models\Search\ActionT::class,
        'Search/actionF'       => \ForkBB\Models\Search\ActionF::class,
        'Search/delete'        => \ForkBB\Models\Search\Delete::class,
        'Search/execute'       => \ForkBB\Models\Search\Execute::class,
        'Search/index'         => \ForkBB\Models\Search\Index::class,
        'Search/prepare'       => \ForkBB\Models\Search\Prepare::class,
        'Search/truncateIndex' => \ForkBB\Models\Search\TruncateIndex::class,

        'SmileyList/delete' => \ForkBB\Models\SmileyList\Delete::class,
        'SmileyList/insert' => \ForkBB\Models\SmileyList\Insert::class,
        'SmileyList/load'   => \ForkBB\Models\SmileyList\Load::class,
        'SmileyList/update' => \ForkBB\Models\SmileyList\Update::class,

        'Subscription/send' => \ForkBB\Models\Subscription\Send::class,

        'Topic/calcStat' => \ForkBB\Models\Topic\CalcStat::class,

        'Topics/access'         => \ForkBB\Models\Topic\Access::class,
        'Topics/delete'         => \ForkBB\Models\Topic\Delete::class,
        'Topics/load'           => \ForkBB\Models\Topic\Load::class,
        'Topics/save'           => \ForkBB\Models\Topic\Save::class,
        'Topics/merge'          => \ForkBB\Models\Topic\Merge::class,
        'Topics/move'           => \ForkBB\Models\Topic\Move::class,
        'Topics/updateUsername' => \ForkBB\Models\Topic\UpdateUsername::class,
        'Topics/view'           => \ForkBB\Models\Topic\View::class,

        'Users/adminsIds'          => \ForkBB\Models\User\AdminsIds::class,
        'Users/changeGroup'        => \ForkBB\Models\User\ChangeGroup::class,
        'Users/current'            => \ForkBB\Models\User\Current::class,
        'Users/delete'             => \ForkBB\Models\User\Delete::class,
        'Users/filter'             => \ForkBB\Models\User\Filter::class,
        'Users/isUniqueName'       => \ForkBB\Models\User\IsUniqueName::class,
        'Users/load'               => \ForkBB\Models\User\Load::class,
        'Users/normUsername'       => \ForkBB\Models\User\NormUsername::class,
        'Users/promote'            => \ForkBB\Models\User\Promote::class,
        'Users/save'               => \ForkBB\Models\User\Save::class,
        'Users/stats'              => \ForkBB\Models\User\Stats::class,
        'Users/updateCountPosts'   => \ForkBB\Models\User\UpdateCountPosts::class,
        'Users/updateCountTopics'  => \ForkBB\Models\User\UpdateCountTopics::class,
        'Users/updateLastVisit'    => \ForkBB\Models\User\UpdateLastVisit::class,
        'Users/updateLoginIpCache' => \ForkBB\Models\User\UpdateLoginIpCache::class,
        'Users/usersNumber'        => \ForkBB\Models\User\UsersNumber::class,
    ],
    'multiple'  => [
        'CtrlPrimary' => \ForkBB\Controllers\Primary::class,
        'Primary'     => '@CtrlPrimary:check',

        'CtrlRouting' => \ForkBB\Controllers\Routing::class,
        'Routing'     => '@CtrlRouting:routing',

        'Message'            => \ForkBB\Models\Pages\Message::class,
        'Index'              => \ForkBB\Models\Pages\Index::class,
        'Forum'              => \ForkBB\Models\Pages\Forum::class,
        'Topic'              => \ForkBB\Models\Pages\Topic::class,
        'Post'               => \ForkBB\Models\Pages\Post::class,
        'Edit'               => \ForkBB\Models\Pages\Edit::class,
        'Delete'             => \ForkBB\Models\Pages\Delete::class,
        'Rules'              => \ForkBB\Models\Pages\Rules::class,
        'Auth'               => \ForkBB\Models\Pages\Auth::class,
        'Userlist'           => \ForkBB\Models\Pages\Userlist::class,
        'Search'             => \ForkBB\Models\Pages\Search::class,
        'Register'           => \ForkBB\Models\Pages\Register::class,
        'RegLog'             => \ForkBB\Models\Pages\RegLog::class,
        'Redirect'           => \ForkBB\Models\Pages\Redirect::class,
        'Maintenance'        => \ForkBB\Models\Pages\Maintenance::class,
        'Ban'                => \ForkBB\Models\Pages\Ban::class,
        'Debug'              => \ForkBB\Models\Pages\Debug::class,
        'Misc'               => \ForkBB\Models\Pages\Misc::class,
        'Moderate'           => \ForkBB\Models\Pages\Moderate::class,
        'Report'             => \ForkBB\Models\Pages\Report::class,
        'Email'              => \ForkBB\Models\Pages\Email::class,
        'Feed'               => \ForkBB\Models\Pages\Feed::class,
        'Poll'               => \ForkBB\Models\Pages\Poll::class,
        'PM'                 => \ForkBB\Models\Pages\PM::class,
        'PMView'             => \ForkBB\Models\Pages\PM\PMView::class,
        'PMPost'             => \ForkBB\Models\Pages\PM\PMPost::class,
        'PMTopic'            => \ForkBB\Models\Pages\PM\PMTopic::class,
        'PMDelete'           => \ForkBB\Models\Pages\PM\PMDelete::class,
        'PMEdit'             => \ForkBB\Models\Pages\PM\PMEdit::class,
        'PMBlock'            => \ForkBB\Models\Pages\PM\PMBlock::class,
        'PMConfig'           => \ForkBB\Models\Pages\PM\PMConfig::class,
        'ProfileView'        => \ForkBB\Models\Pages\Profile\View::class,
        'ProfileEdit'        => \ForkBB\Models\Pages\Profile\Edit::class,
        'ProfileConfig'      => \ForkBB\Models\Pages\Profile\Config::class,
        'ProfilePass'        => \ForkBB\Models\Pages\Profile\Pass::class,
        'ProfileEmail'       => \ForkBB\Models\Pages\Profile\Email::class,
        'ProfileMod'         => \ForkBB\Models\Pages\Profile\Mod::class,
        'ProfileOAuth'       => \ForkBB\Models\Pages\Profile\OAuth::class,
        'ProfileDelete'      => \ForkBB\Models\Pages\Profile\Delete::class,
        'ProfileSearch'      => \ForkBB\Models\Pages\Profile\Search::class,
        'AdminIndex'         => \ForkBB\Models\Pages\Admin\Index::class,
        'AdminStatistics'    => \ForkBB\Models\Pages\Admin\Statistics::class,
        'AdminOptions'       => \ForkBB\Models\Pages\Admin\Options::class,
        'AdminProviders'     => \ForkBB\Models\Pages\Admin\Providers::class,
        'AdminCategories'    => \ForkBB\Models\Pages\Admin\Categories::class,
        'AdminForums'        => \ForkBB\Models\Pages\Admin\Forums::class,
        'AdminGroups'        => \ForkBB\Models\Pages\Admin\Groups::class,
        'AdminCensoring'     => \ForkBB\Models\Pages\Admin\Censoring::class,
        'AdminMaintenance'   => \ForkBB\Models\Pages\Admin\Maintenance::class,
        'AdminUsers'         => \ForkBB\Models\Pages\Admin\Users\View::class,
        'AdminUsersResult'   => \ForkBB\Models\Pages\Admin\Users\Result::class,
        'AdminUsersStat'     => \ForkBB\Models\Pages\Admin\Users\Stat::class,
        'AdminUsersAction'   => \ForkBB\Models\Pages\Admin\Users\Action::class,
        'AdminUsersPromote'  => \ForkBB\Models\Pages\Admin\Users\Promote::class,
        'AdminUsersNew'      => \ForkBB\Models\Pages\Admin\Users\NewUser::class,
        'AdminHost'          => \ForkBB\Models\Pages\Admin\Host::class,
        'AdminBans'          => \ForkBB\Models\Pages\Admin\Bans::class,
        'AdminReports'       => \ForkBB\Models\Pages\Admin\Reports::class,
        'AdminParser'        => \ForkBB\Models\Pages\Admin\Parser\Edit::class,
        'AdminParserSmilies' => \ForkBB\Models\Pages\Admin\Parser\Smilies::class,
        'AdminParserBBCode'  => \ForkBB\Models\Pages\Admin\Parser\BBCode::class,
        'AdminLogs'          => \ForkBB\Models\Pages\Admin\Logs::class,
        'AdminUploads'       => \ForkBB\Models\Pages\Admin\Uploads::class,
        'AdminAntispam'      => \ForkBB\Models\Pages\Admin\Antispam::class,

        'AdminListModel'    => \ForkBB\Models\AdminList\AdminList::class,
        'BanListModel'      => \ForkBB\Models\BanList\BanList::class,
        'BBStructure'       => \ForkBB\Models\BBCodeList\Structure::class,
        'CategoriesManager' => \ForkBB\Models\Category\Categories::class,
        'CensorshipModel'   => \ForkBB\Models\Censorship\Censorship::class,
        'ConfigModel'       => \ForkBB\Models\Config\Config::class,
        'DBMapModel'        => \ForkBB\Models\DBMap\DBMap::class,
        'ExtensionModel'    => \ForkBB\Models\Extension\Extension::class,
        'ExtensionManager'  => \ForkBB\Models\Extension\Extensions::class,
        'ForumModel'        => \ForkBB\Models\Forum\Forum::class,
        'ForumManager'      => \ForkBB\Models\Forum\Forums::class,
        'GroupModel'        => \ForkBB\Models\Group\Group::class,
        'GroupManager'      => \ForkBB\Models\Group\Groups::class,
        'PollModel'         => \ForkBB\Models\Poll\Poll::class,
        'PostModel'         => \ForkBB\Models\Post\Post::class,
        'PPostModel'        => \ForkBB\Models\PM\PPost::class,
        'PTopicModel'       => \ForkBB\Models\PM\PTopic::class,
        'ReportModel'       => \ForkBB\Models\Report\Report::class,
        'SmileyListModel'   => \ForkBB\Models\SmileyList\SmileyList::class,
        'StatsModel'        => \ForkBB\Models\Stats\Stats::class,
        'StopwordListModel' => \ForkBB\Models\StopwordList\StopwordList::class,
        'TopicModel'        => \ForkBB\Models\Topic\Topic::class,
        'UserModel'         => \ForkBB\Models\User\User::class,
    ],
];
