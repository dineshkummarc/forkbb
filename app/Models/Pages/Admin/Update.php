<?php
/**
 * This file is part of the ForkBB <https://github.com/forkbb>.
 *
 * @copyright (c) Visman <mio.visman@yandex.ru, https://github.com/MioVisman>
 * @license   The MIT License (MIT)
 */

declare(strict_types=1);

namespace ForkBB\Models\Pages\Admin;

use ForkBB\Core\Config as CoreConfig;
use ForkBB\Core\Container;
use ForkBB\Core\Validator;
use ForkBB\Models\Page;
use ForkBB\Models\Pages\Admin;
use PDO;
use PDOException;
use RuntimeException;
use ForkBB\Core\Exceptions\ForkException;
use function \ForkBB\__;

class Update extends Admin
{
    const PHP_MIN                    = '7.3.0';
    const REV_MIN_FOR_UPDATE         = 42;
    const LATEST_REV_WITH_DB_CHANGES = 43;
    const LOCK_NAME                  = 'lock_update';
    const LOCk_TTL                   = 1800;
    const JSON_OPTIONS               = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR;
    const CONFIG_FILE                = 'main.php';

    protected $configFile;

    public function __construct(Container $container)
    {
        parent::__construct($container);

        $container->Lang->load('validator');
        $container->Lang->load('admin_update');

        $this->aIndex     = 'update';
        $this->httpStatus = 503;
        $this->onlinePos  = null;
        $this->nameTpl    = 'admin/form';
        $this->titleForm  = 'Update ForkBB';
        $this->classForm  = ['updateforkbb'];
        $this->configFile = $container->DIR_APP . '/config/' . self::CONFIG_FILE;

        $this->header('Retry-After', '3600');
    }

    /**
     * Подготовка страницы к отображению
     */
    public function prepare(): void
    {
        $this->aNavigation = $this->aNavigation();
        $this->crumbs      = $this->crumbs(...$this->aCrumbs);
    }

    /**
     * Возвращает массив ссылок с описанием для построения навигации админки
     */
    protected function aNavigation(): array
    {
        return [
            'update' => [
                $this->c->Router->link('AdminUpdate'),
                __('Update ForkBB'),
            ],
        ];
    }

    /**
     * Возвращает страницу обслуживания с доп.сообщением
     */
    protected function returnMaintenance(bool $isStage = true): Page
    {
        $maintenance = $this->c->Maintenance;
        $maintenance->fIswev = ['w', 'Update script is running'];

        if ($isStage) {
            $maintenance->fIswev = ['e', 'Script runs error'];
        }

        return $maintenance;
    }

    /**
     * Проверяет наличие блокировки скрипта обновления
     */
    protected function hasLock(string $uid = null): bool
    {
        $lock = $this->c->Cache->get(self::LOCK_NAME);

        if (null === $uid) {
            return ! empty($lock);
        } else {
            return empty($lock) || ! \hash_equals($uid, (string) $lock);
        }
    }

    protected function setLock(string $uid = null): ?string
    {
        if (true === $this->hasLock($uid)) {
            return null;
        }

        if (null === $uid) {
            $uid = $this->c->Secury->randomHash(33);
        }

        $this->c->Cache->set(self::LOCK_NAME, $uid, self::LOCk_TTL);

        if (true === $this->hasLock($uid)) {
            return null;
        }

        return $uid;
    }

    /**
     * Подготавливает данные для страницы обновления форума
     */
    public function view(array $args, string $method): Page
    {
        if (true === $this->hasLock()) {
            return $this->returnMaintenance(false);
        }

        if (
            'POST' === $method
            && empty($this->fIswev)
        ) {
            $v = $this->c->Validator->reset()
                ->addValidators([
                    'check_pass' => [$this, 'vCheckPass'],
                ])->addRules([
                    'token'                 => 'token:AdminUpdate',
                    'dbpass'                => 'required|string:trim|check_pass',
                    'o_maintenance_message' => 'required|string:trim|max:65000 bytes|html',
                ])->addAliases([
                    'dbpass'                => 'Database password',
                    'o_maintenance_message' => 'Maintenance message',
                ])->addMessages([
                ]);

                if ($v->validation($_POST)) {
                    $e = null;

                    // версия PHP
                    if (
                        null === $e
                        && \version_compare(\PHP_VERSION, self::PHP_MIN, '<')
                    ) {
                        $e = __(['You are running error', 'PHP', \PHP_VERSION, $this->c->FORK_REVISION, self::PHP_MIN]);
                    }

                    // база не от ForkBB или старая ревизия
                    if (
                        null === $e
                        && $this->c->config->i_fork_revision < self::REV_MIN_FOR_UPDATE
                    ) {
                        $e = 'Version mismatch error';
                    }

                    // загрузка и проверка конфига
                    if (null === $e) {
                        try {
                            $coreConfig = new CoreConfig($this->configFile);
                        } catch (ForkException $excp) {
                            $e = $excp->getMessage();
                        }
                    }

                    // проверка доступности базы данных на изменения
                    if (
                        null === $e
                        && $this->c->config->i_fork_revision < self::LATEST_REV_WITH_DB_CHANGES
                    ) {
                        $test_table = 'test_tb_for_update';

                        if (
                            null === $e
                            && true === $this->c->DB->tableExists($test_table)
                        ) {
                            $e = ['The %s table already exists. Delete it.', $test_table];
                        }

                        $schema = [
                            'FIELDS' => [
                                'id' => ['SERIAL', false],
                            ],
                            'PRIMARY KEY' => ['id'],
                        ];
                        if (
                            null === $e
                            && false === $this->c->DB->createTable($test_table, $schema)
                        ) {
                            $e = ['Unable to create %s table', $test_table];
                        }

                        if (
                            null === $e
                            && false === $this->c->DB->addField($test_table, 'test_field', 'VARCHAR(80)', false, '')
                        ) {
                            $e = ['Unable to add test_field field to %s table', $test_table];
                        }

                        $sql = "INSERT INTO ::{$test_table} (test_field) VALUES ('TEST_VALUE')";
                        if (
                            null === $e
                            && false === $this->c->DB->exec($sql)
                        ) {
                            $e = ['Unable to insert line to %s table', $test_table];
                        }

                        if (
                            null === $e
                            && false === $this->c->DB->dropField($test_table, 'test_field')
                        ) {
                            $e = ['Unable to drop test_field field from %s table', $test_table];
                        }

                        if (
                            null === $e
                            && false === $this->c->DB->dropTable($test_table)
                        ) {
                            $e = ['Unable to drop %s table', $test_table];
                        }
                    }

                    if (null !== $e) {
                        return $this->c->Message->message($e, true, 503);
                    }

                    $uid = $this->setLock();

                    if (null === $uid) {
                        $this->fIswev = ['e', 'Unable to write update lock'];
                    } else {
                        $this->c->config->o_maintenance_message = $v->o_maintenance_message;

                        $this->c->config->save();

                        return $this->c->Redirect->page('AdminUpdateStage', ['uid' => $uid, 'stage' => 1]);
                    }
                } else {
                    $this->fIswev = $v->getErrors();
                }
        } else {
            $v = null;
        }

        $this->form = $this->form($v);

        return $this;
    }

    /**
     * Проверяет пароль базы
     */
    public function vCheckPass(Validator $v, $dbpass)
    {
        if (\substr($this->c->DB_DSN, 0, 6) === 'sqlite') {
            if (! \hash_equals($this->c->DB_DSN, "sqlite:{$dbpass}")) {  // ????
                $v->addError(['Invalid file error', self::CONFIG_FILE]);
            }
        } else {
            if (! \hash_equals($this->c->DB_PASSWORD, $dbpass)) {
                $v->addError(['Invalid password error', self::CONFIG_FILE]);
            }
        }

        return $dbpass;
    }

    /**
     * Формирует массив для формы
     */
    protected function form(?Validator $v): array
    {
        return [
            'action' => $this->c->Router->link('AdminUpdate'),
            'hidden' => [
                'token' => $this->c->Csrf->create('AdminUpdate'),
            ],
            'sets'   => [
                'update-info' => [
                    'info' => [
                        [
                            'value' => __('Update message'),
                        ],
                    ],
                ],
                'update' => [
                    'legend' => 'Update ForkBB',
                    'fields' => [
                        'dbpass' => [
                            'type'     => 'password',
                            'value'    => '',
                            'caption'  => 'Database password',
                            'help'     => 'Database password note',
                            'required' => true,
                        ],
                        'o_maintenance_message' => [
                            'type'     => 'textarea',
                            'value'    => $v ? $v->o_maintenance_message : $this->c->config->o_maintenance_message,
                            'caption'  => 'Maintenance message',
                            'help'     => 'Maintenance message info',
                            'required' => true,
                        ],
                    ],
                ],
                'member-info' => [
                    'info' => [
                        [
                            'value' => __('Members message'),
                        ],
                    ],
                ],
            ],
            'btns'   => [
                'start' => [
                    'type'  => 'submit',
                    'value' => __('Start update'),
                ],
            ],
        ];
    }

    /**
     * Обновляет форум
     */
    public function stage(array $args, string $method): Page
    {
        try {
            $uid = $this->setLock($args['uid']);

            if (null === $uid) {
                return $this->returnMaintenance();
            }

            $stage = \max($args['stage'], $this->c->config->i_fork_revision);

            do {
                if (\method_exists($this, 'stageNumber' . $stage)) {
                    $start = $this->{'stageNumber' . $stage}($args);

                    if (null === $start) {
                        ++$stage;
                    }

                    return $this->c->Redirect->page(
                        'AdminUpdateStage',
                        ['uid' => $uid, 'stage' => $stage, 'start' => $start]
                    )->message(['Stage %1$s (%2$s)', $stage, (int) $start]);
                }

                ++$stage;
            } while ($stage < $this->c->FORK_REVISION);

            $this->c->config->i_fork_revision = $this->c->FORK_REVISION;

            $this->c->config->save();

            if (true !== $this->c->Cache->clear()) {
                throw new RuntimeException('Unable to clear cache');
            }

            return $this->c->Redirect->page('Index')->message('Successfully updated');
        } catch (ForkException $excp) {
            return $this->c->Message->message($excp->getMessage(), true, 503);
        }
    }

#    /**
#     * Выполняет определенный шаг обновления
#     *
#     * Возвращает null, если шаг выпонен
#     * Возвращает положительный int, если требуется продолжить выполнение шага
#     */
#    protected function stageNumber1(array $args): ?int
#    {
#        $coreConfig = new CoreConfig($this->configFile);
#
#        $coreConfig->add(
#            'multiple=>AdminUsersRecalculate',
#            '\\ForkBB\\Models\\Pages\\Admin\\Users\\Recalculate::class',
#            'AdminUsersNew'
#        );
#
#        $coreConfig->save();
#
#        return null;
#    }

    /**
     * rev.42 to rev.43
     */
    protected function stageNumber42(array $args): ?int
    {
        $query = 'DELETE FROM ::users WHERE id=1';

        $this->c->DB->exec($query);

        $query = 'UPDATE ::forums SET last_poster_id=0 WHERE last_poster_id=1';

        $this->c->DB->exec($query);

        $query = 'UPDATE ::online SET user_id=0 WHERE user_id=1';

        $this->c->DB->exec($query);

        $query = 'UPDATE ::pm_posts SET poster_id=0 WHERE poster_id=1';

        $this->c->DB->exec($query);

        $query = 'UPDATE ::pm_topics SET poster_id=0 WHERE poster_id=1';

        $this->c->DB->exec($query);

        $query = 'UPDATE ::pm_topics SET target_id=0 WHERE target_id=1';

        $this->c->DB->exec($query);

        $query = 'UPDATE ::posts SET poster_id=0 WHERE poster_id=1';

        $this->c->DB->exec($query);

        $query = 'UPDATE ::posts SET editor_id=0 WHERE editor_id=1';

        $this->c->DB->exec($query);

        $query = 'UPDATE ::reports SET reported_by=0 WHERE reported_by=1';

        $this->c->DB->exec($query);

        $query = 'UPDATE ::reports SET zapped_by=0 WHERE zapped_by=1';

        $this->c->DB->exec($query);

        $query = 'UPDATE ::topics SET poster_id=0 WHERE poster_id=1';

        $this->c->DB->exec($query);

        $query = 'UPDATE ::topics SET last_poster_id=0 WHERE last_poster_id=1';

        $this->c->DB->exec($query);

        $query = 'UPDATE ::warnings SET poster_id=0 WHERE poster_id=1';

        $this->c->DB->exec($query);

        $coreConfig = new CoreConfig($this->configFile);

        $coreConfig->add(
            'shared=>Groups/save',
            '\\ForkBB\\Models\\Group\\Save::class',
            'Group/save'
        );

        $coreConfig->add(
            'shared=>Groups/perm',
            '\\ForkBB\\Models\\Group\\Perm::class',
            'Group/save'
        );

        $coreConfig->add(
            'shared=>Groups/delete',
            '\\ForkBB\\Models\\Group\\Delete::class',
            'Group/save'
        );

        $result = $coreConfig->delete('shared=>Group/delete');
        $result = $coreConfig->delete('shared=>Group/perm');
        $result = $coreConfig->delete('shared=>Group/save');

        $coreConfig->save();

        $this->c->config->a_guest_set = [
            'show_smilies' => 1,
            'show_sig'     => 1,
            'show_avatars' => 1,
            'show_img'     => 1,
            'show_img_sig' => 1,
        ];

        $this->c->config->save();

        return null;
    }
}
