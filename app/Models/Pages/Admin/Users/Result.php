<?php

declare(strict_types=1);

namespace ForkBB\Models\Pages\Admin\Users;

use ForkBB\Core\Validator;
use ForkBB\Models\Page;
use ForkBB\Models\Pages\Admin\Users;
use ForkBB\Models\User\Model as User;
use function \ForkBB\__;

class Result extends Users
{
    /**
     * Подготавливает данные для шаблона найденных пользователей
     */
    public function view(array $args, string $method): Page
    {
        $data = $this->decodeData($args['data']);
        if (false === $data) {
            return $this->c->Message->message('Bad request');
        }

        if (isset($data['ip'])) {
            if (! $this->c->userRules->viewIP) {
                return $this->c->Message->message('Bad request');
            }

            $idsN   = $this->forIP($data['ip']);
            $crName = $data['ip'];
        } else {
            $idsN   = $this->forFilter($data);
            $crName = __('Results head');
        }

        $number = \count($idsN);
        if (0 == $number) {
            $view = $this->c->AdminUsers;
            $view->fIswev = ['i', __('No users found')];

            return $view->view([], 'GET', $data);
        }

        $page  = isset($args['page']) ? (int) $args['page'] : 1;
        $pages = (int) \ceil(($number ?: 1) / $this->c->config->i_disp_users);

        if ($page > $pages) {
            return $this->c->Message->message('Bad request');
        }

        if ('POST' === $method) {
            $v = $this->c->Validator->reset()
            ->addValidators([
            ])->addRules([
                'token'          => 'token:AdminUsersResult',
                'users'          => 'required|array',
                'users.*'        => 'required|integer|min:2|max:9999999999',
                'ban'            => $this->c->userRules->banUsers ? 'checkbox' : 'absent',
                'delete'         => $this->c->userRules->deleteUsers ? 'checkbox' : 'absent',
                'change_group'   => $this->c->userRules->changeGroup ? 'checkbox' : 'absent',
            ])->addAliases([
                'users'          => 'Select',
                'users.*'        => 'Select',
            ])->addArguments([
                'token'          => $args,
            ])->addMessages([
                'users.required' => 'No users selected',
                'ban'            => 'Action not available',
                'delete'         => 'Action not available',
                'change_group'   => 'Action not available',
            ]);

            if ($v->validation($_POST)) {
                if (
                    ! empty($v->ban)
                    && $this->c->userRules->banUsers
                ) {
                    $action = self::ACTION_BAN;
                } elseif (
                    ! empty($v->delete)
                    && $this->c->userRules->deleteUsers
                ) {
                    $action = self::ACTION_DEL;
                } elseif (
                    ! empty($v->change_group)
                    && $this->c->userRules->changeGroup
                ) {
                    $action = self::ACTION_CHG;
                } else {
                    $this->fIswev = ['v', __('Action not available')];
                }

                if (empty($this->fIswev)) {
                    $selected = $this->checkSelected($v->users, $action);
                    if (\is_array($selected)) {
                        if (self::ACTION_BAN === $action) {
                            return $this->c->Redirect->page('AdminBansNew', ['ids' => \implode('-', $selected)]);
                        } else {
                            return $this->c->Redirect->page('AdminUsersAction', ['action' => $action, 'ids' => \implode('-', $selected)]);
                        }
                    }
                }
            }

            $this->fIswev = $v->getErrors();
        }

        $startNum = ($page - 1) * $this->c->config->i_disp_users;
        $idsN     = \array_slice($idsN, $startNum, $this->c->config->i_disp_users);
        $ids      = [];
        $userList = [];

        foreach ($idsN as $cur) {
            if (\is_int($cur)) {
                $ids[] = $cur;
            }
            $userList[$cur] = $cur;
        }

        if (! empty($ids)) {
            $idsN = $this->c->users->loadByIds($ids);

            foreach ($idsN as $cur)  {
                if ($cur instanceof User) {
                    $userList[$cur->id] = $cur;
                }
            }
        }

        $this->nameTpl    = 'admin/users_result';
        $this->mainSuffix = '-one-column';
        $this->aCrumbs[]  = [
            $this->c->Router->link(
                'AdminUsersResult',
                [
                    'data' => $args['data'],
                ]
            ),
            $crName,
        ];
        $this->formResult = $this->form($userList, $startNum, $args);
        $this->pagination = $this->c->Func->paginate(
            $pages,
            $page,
            'AdminUsersResult',
            [
                'data' => $args['data'],
            ]
        );

        return $this;
    }

    /**
     * Возвращает список id пользователей по ip
     */
    protected function forIP(string $ip): array
    {
        $fromPosts = $this->c->posts->userInfoFromIP($ip);
        $ids       = $this->c->users->filter([
            'registration_ip' => ['=', $ip],
        ]);
        $ids       = \array_flip($ids);

        foreach ($fromPosts as $val) {
            if (isset($ids[$val])) {
                unset($ids[$val]);
            }
        }

        $ids = \array_flip($ids);

        return \array_merge($fromPosts, $ids);
    }

    /**
     * Возвращает список id пользователей по фильтру
     */
    protected function forFilter(array $data): array
    {
        $order = [
            $data['order_by'] => $data['direction'],
        ];
        $filters = [];

        if ($data['user_group'] > -1) {
            $filters['group_id'] = ['=', $data['user_group']];
        }

        foreach ($data as $field => $value) {
            if (
                'order_by' === $field
                || 'direction' === $field
                || 'user_group' === $field
            ) {
                continue;
            }

            $key  = 1;
            $type = '=';

            if (\preg_match('%^(.+?)_(1|2)$%', $field, $matches)) {
                $type  = 'BETWEEN';
                $field = $matches[1];
                $key   = $matches[2];

                if (\is_string($value)) {
                    $value = \strtotime($value . ' UTC');
                }
            } elseif (\is_string($value)) {
                $type  = 'LIKE';
            }

            $filters[$field][0]    = $type;
            $filters[$field][$key] = $value;
        }

        return $this->c->users->filter($filters, $order);
    }

    /**
     * Создает массив данных для формы найденных по фильтру пользователей
     */
    protected function form(array $users, int $number, array $args): array
    {
        $form = [
            'action' => $this->c->Router->link(
                'AdminUsersResult',
                $args
            ),
            'hidden' => [
                'token' => $this->c->Csrf->create(
                    'AdminUsersResult',
                    $args
                ),
            ],
            'sets'   => [],
            'btns'   => [],
        ];

        if ($this->c->userRules->banUsers) {
            $form['btns']['ban'] = [
                'type'      => 'submit',
                'value'     => __('Ban'),
//                'accesskey' => null,
            ];
        }
        if ($this->c->userRules->deleteUsers) {
            $form['btns']['delete'] = [
                'type'      => 'submit',
                'value'     => __('Delete'),
//                'accesskey' => null,
            ];
        }
        if ($this->c->userRules->changeGroup) {
            $form['btns']['change_group'] = [
                'type'      => 'submit',
                'value'     => __('Change group'),
//                'accesskey' => null,
            ];
        }

        \array_unshift($users, $this->c->users->create(['id' => -1]));

        foreach ($users as $user) {
            if (\is_string($user)) {
                $user = $this->c->users->create(['id' => 1, 'username' => $user]);
            }

            $fields = [];
            $fields["l{$number}-wrap1"] = [
                'class' => 'main-result',
                'type'  => 'wrap',
            ];
            $fields["l{$number}-wrap2"] = [
                'class' => 'user-result',
                'type'  => 'wrap',
            ];
            $fields["l{$number}-username"] = [
                'class'   => ['result', 'username'],
                'type'    => $user->isGuest ? 'str' : 'link',
                'caption' => __('Results username head'),
                'value'   => $user->username,
                'href'    => $user->link,
            ];
            $fields["l{$number}-email"] = [
                'class'   => $user->isGuest ? ['result', 'email', 'no-data'] : ['result', 'email'],
                'type'    => $user->isGuest ? 'str' : 'link',
                'caption' => __('Results e-mail head'),
                'value'   => $user->isGuest ? '' : $user->email,
                'href'    => $user->isGuest ? '' : 'mailto:' . $user->email,
            ];
            $fields[] = [
                'type' => 'endwrap',
            ];
            $fields["l{$number}-title"] = [
                'class'   => ['result', 'title'],
                'type'    => 'str',
                'caption' => __('Results title head'),
                'value'   => -1 === $user->id ? null : $user->title(),
            ];
            $fields["l{$number}-posts"] = [
                'class'   => $user->isGuest ? ['result', 'posts', 'no-data'] : ['result', 'posts'],
                'type'    => $user->num_posts ? 'link' : 'str',
                'caption' => __('Results posts head'),
                'value'   => $user->num_posts ? \ForkBB\num($user->num_posts) : null,
                'href'    => $this->c->Router->link(
                    'SearchAction',
                    [
                        'action' => 'posts',
                        'uid'    => $user->id,
                    ]
                ),
                'title'   => __('Results show posts link'),
            ];
            $fields["l{$number}-note"] = [
                'class'   => '' === \trim($user->admin_note ?? '') ? ['result', 'note', 'no-data'] : ['result', 'note'],
                'type'    => 'str',
                'caption' => __('Примечание админа'),
                'value'   => $user->admin_note,
            ];

            if ($this->user->isAdmin) {
                $fields["l{$number}-view-ip"] = [
                    'class'   => $user->isGuest ? ['result', 'view-ip', 'no-data'] : ['result', 'view-ip'],
                    'type'    => $user->isGuest || ! $user->num_posts ? 'str' : 'link',
                    'caption' => __('Results action head'),
                    'value'   => $user->isGuest ? null : __('Results view IP link'),
                    'href'    => $this->c->Router->link(
                        'AdminUserStat',
                        [
                            'id' => $user->id,
                        ]
                    ),
                ];
            }

            $fields[] = [
                'type' => 'endwrap',
            ];
            $key = $user->isGuest ? "guest{$number}" : "users[{$user->id}]";
            $fields[$key] = [
                'class'   => ['check'],
                'caption' => __('Select'),
                'type'    => $user->isGuest ? 'str' : 'checkbox',
                'value'   => $user->isGuest ? null : $user->id,
                'checked' => false,
            ];
            $form['sets']["l{$number}"] = [
                'class'  => 'result',
                'legend' => -1 === $user->id ? null : $number,
                'fields' => $fields,
            ];

            ++$number;
        }

        return $form;
    }
}
