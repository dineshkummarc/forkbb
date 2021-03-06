<?php

declare(strict_types=1);

namespace ForkBB\Models\Topic;

use ForkBB\Core\Container;
use ForkBB\Models\DataModel;
use ForkBB\Models\Forum\Model as Forum;
use PDO;
use RuntimeException;

class Model extends DataModel
{
    /**
     * Получение родительского раздела
     */
    protected function getparent(): ?Forum
    {
        if ($this->forum_id < 1) {
            throw new RuntimeException('Parent is not defined');
        }

        $forum = $this->c->forums->get($this->forum_id);

        if (
            ! $forum instanceof Forum
            || $forum->redirect_url
        ) {
            return null;
        } else {
            return $forum;
        }
    }

    /**
     * Возвращает отцензурированное название темы
     */
    protected function getname(): ?string
    {
        return $this->censorSubject;
    }

    /**
     * Статус возможности ответа в теме
     */
    protected function getcanReply(): bool
    {
        if ($this->c->user->isAdmin) {
            return true;
        } elseif (
            $this->closed
            || $this->c->user->isBot
        ) {
            return false;
        } elseif (
            '1' == $this->parent->post_replies
            || (
                null === $this->parent->post_replies
                && '1' == $this->c->user->g_post_replies
            )
            || $this->c->user->isModerator($this)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Статус возможности использования подписок
     */
    protected function getcanSubscription(): bool
    {
        return '1' == $this->c->config->o_topic_subscriptions
            && $this->id > 0
            && ! $this->c->user->isGuest
            && ! $this->c->user->isUnverified;
    }

    /**
     * Ссылка на тему
     */
    protected function getlink(): string
    {
        return $this->c->Router->link(
            'Topic',
            [
                'id'   => $this->moved_to ?: $this->id,
                'name' => $this->censorSubject,
            ]
        );
    }

    /**
     * Ссылка для ответа в теме
     */
    protected function getlinkReply(): string
    {
        return $this->c->Router->link(
            'NewReply',
            [
                'id' => $this->id,
            ]
        );
    }

    /**
     * Ссылка для перехода на последнее сообщение темы
     */
    protected function getlinkLast(): ?string
    {
        if ($this->moved_to) {
            return null;
        } else {
            return $this->c->Router->link(
                'ViewPost',
                [
                    'id' => $this->last_post_id,
                ]
            );
        }
    }

    /**
     * Ссылка для перехода на первое новое сообщение в теме
     */
    protected function getlinkNew(): string
    {
        return $this->c->Router->link(
            'TopicViewNew',
            [
                'id' => $this->id,
            ]
        );
    }

    /**
     * Ссылка для перехода на первое не прочитанное сообщение в теме
     */
    protected function getlinkUnread(): string
    {
        return $this->c->Router->link(
            'TopicViewUnread',
            [
                'id' => $this->id,
            ]
        );
    }

    /**
     * Ссылка на подписку
     */
    protected function getlinkSubscribe(): ?string
    {
        return $this->c->Router->link(
            'TopicSubscription',
            [
                'tid'   => $this->id,
                'type'  => 'subscribe',
                'token' => null,
            ]
        );
    }

    /**
     * Ссылка на отписку
     */
    protected function getlinkUnsubscribe(): ?string
    {
        return $this->c->Router->link(
            'TopicSubscription',
            [
                'tid'   => $this->id,
                'type'  => 'unsubscribe',
                'token' => null,
            ]
        );
    }

    /**
     * Статус наличия новых сообщений в теме
     */
    protected function gethasNew() /* : int|false */
    {
        if (
            $this->c->user->isGuest
            || $this->moved_to
        ) {
            return false;
        }

        $time = \max(
            (int) $this->c->user->u_mark_all_read,
            (int) $this->parent->mf_mark_all_read,
            (int) $this->c->user->last_visit,
            (int) $this->mt_last_visit
        );

        return $this->last_post > $time ? $time : false;
    }

    /**
     * Статус наличия непрочитанных сообщений в теме
     */
    protected function gethasUnread() /* int|false */
    {
        if (
            $this->c->user->isGuest
            || $this->moved_to
        ) {
            return false;
        }

        $time = \max(
            (int) $this->c->user->u_mark_all_read,
            (int) $this->parent->mf_mark_all_read,
            (int) $this->mt_last_read
        );

        return $this->last_post > $time ? $time : false;
    }

    /**
     * Номер первого нового сообщения в теме
     */
    protected function getfirstNew(): int
    {
        if (false === $this->hasNew) {
            return 0;
        }

        $vars  = [
            ':tid'   => $this->id,
            ':visit' => $this->hasNew,
        ];
        $query = 'SELECT MIN(p.id)
            FROM ::posts AS p
            WHERE p.topic_id=?i:tid AND p.posted>?i:visit';

        $pid = $this->c->DB->query($query, $vars)->fetchColumn();

        return $pid ?: 0;
    }

    /**
     * Номер первого не прочитанного сообщения в теме
     */
    protected function getfirstUnread(): int
    {
        if (false === $this->hasUnread) {
            return 0;
        }

        $vars  = [
            ':tid'   => $this->id,
            ':visit' => $this->hasUnread,
        ];
        $query = 'SELECT MIN(p.id)
            FROM ::posts AS p
            WHERE p.topic_id=?i:tid AND p.posted>?i:visit';

        $pid = $this->c->DB->query($query, $vars)->fetchColumn();

        return $pid ?: 0;
    }

    /**
     * Количество страниц в теме
     */
    protected function getnumPages(): int
    {
        if (null === $this->num_replies) {
            throw new RuntimeException('The model does not have the required data');
        }

        return (int) \ceil(($this->num_replies + 1) / $this->c->user->disp_posts);
    }

    /**
     * Массив страниц темы
     */
    protected function getpagination(): array
    {
        $page = (int) $this->page;

        if (
            $page < 1
            && 1 === $this->numPages
        ) {
            // 1 страницу в списке тем раздела не отображаем
            return [];
        } else { //????
            return $this->c->Func->paginate(
                $this->numPages,
                $page,
                'Topic',
                [
                    'id'   => $this->id,
                    'name' => $this->censorSubject,
                ]
            );
        }
    }

    /**
     * Статус наличия установленной страницы в теме
     */
    public function hasPage(): bool
    {
        return $this->page > 0 && $this->page <= $this->numPages;
    }

    /**
     * Возвращает массив сообщений с установленной страницы
     */
    public function pageData(): array
    {
        if (! $this->hasPage()) {
            throw new InvalidArgumentException('Bad number of displayed page');
        }

        $vars  = [
            ':tid'    => $this->id,
            ':offset' => ($this->page - 1) * $this->c->user->disp_posts,
            ':rows'   => $this->c->user->disp_posts,
        ];
        $query = 'SELECT p.id
            FROM ::posts AS p
            WHERE p.topic_id=?i:tid
            ORDER BY p.id
            LIMIT ?i:offset, ?i:rows';

        $list = $this->c->DB->query($query, $vars)->fetchAll(PDO::FETCH_COLUMN);

        if (
            ! empty($list)
            && (
                $this->stick_fp
                || $this->poll_type
            )
            && ! \in_array($this->first_post_id, $list)
        ) {
            \array_unshift($list, $this->first_post_id);
        }

        $this->idsList = $list;

        return empty($this->idsList) ? [] : $this->c->posts->view($this);
    }

    /**
     * Возвращает массив сообщений обзора темы
     */
    public function review(): array
    {
        if ($this->c->config->i_topic_review < 1) {
            return [];
        }

        $this->page = 1;

        $vars  = [
            ':tid'  => $this->id,
            ':rows' => $this->c->config->i_topic_review,
        ];
        $query = 'SELECT p.id
            FROM ::posts AS p
            WHERE p.topic_id=?i:tid
            ORDER BY p.id DESC
            LIMIT 0, ?i:rows';

        $this->idsList = $this->c->DB->query($query, $vars)->fetchAll(PDO::FETCH_COLUMN);

        return empty($this->idsList) ? [] : $this->c->posts->view($this, true);
    }

    /**
     * Вычисляет страницу темы на которой находится данное сообщение
     */
    public function calcPage(int $pid): void
    {
        $vars  = [
            ':tid' => $this->id,
            ':pid' => $pid,
        ];
        $query = 'SELECT COUNT(p.id) AS num
            FROM ::posts AS p
            INNER JOIN ::posts AS j ON (j.topic_id=?i:tid AND j.id=?i:pid)
            WHERE p.topic_id=?i:tid AND p.id<?i:pid'; //???? может на два запроса разбить?

        $result = $this->c->DB->query($query, $vars)->fetch();

        $this->page = empty($result) ? null : (int) \ceil(($result['num'] + 1) / $this->c->user->disp_posts);
    }

    /**
     * Статус показа/подсчета просмотров темы
     */
    protected function getshowViews(): bool
    {
        return '1' == $this->c->config->o_topic_views;
    }

    /**
     * Увеличивает на 1 количество просмотров темы
     */
    public function incViews(): void
    {
        $vars  = [
            ':tid' => $this->id,
        ];
        $query = 'UPDATE ::topics
            SET num_views=num_views+1
            WHERE id=?i:tid';

        $this->c->DB->exec($query, $vars);
    }

    /**
     * Обновление меток последнего визита и последнего прочитанного сообщения
     */
    public function updateVisits(): void
    {
        if ($this->c->user->isGuest) {
            return;
        }

        $vars = [
            ':uid'   => $this->c->user->id,
            ':tid'   => $this->id,
            ':read'  => (int) $this->mt_last_read,
            ':visit' => (int) $this->mt_last_visit,
        ];
        $flag = false;

        if (false !== $this->hasNew) {
            $flag = true;
            $vars[':visit'] = $this->last_post;
        }
        if (
            false !== $this->hasUnread
            && $this->timeMax > $this->hasUnread
        ) {
            $flag = true;
            $vars[':read'] = $this->timeMax;
            $vars[':visit'] = $this->last_post;
        }

        if ($flag) {
            if (
                empty($this->mt_last_read)
                && empty($this->mt_last_visit)
            ) {
                $query = 'INSERT INTO ::mark_of_topic (uid, tid, mt_last_visit, mt_last_read)
                    SELECT ?i:uid, ?i:tid, ?i:visit, ?i:read
                    FROM ::groups
                    WHERE NOT EXISTS (
                        SELECT 1
                        FROM ::mark_of_topic
                        WHERE uid=?i:uid AND tid=?i:tid
                    )
                    LIMIT 1';
            } else {
                $query = 'UPDATE ::mark_of_topic
                    SET mt_last_visit=?i:visit, mt_last_read=?i:read
                    WHERE uid=?i:uid AND tid=?i:tid';
            }

            $this->c->DB->exec($query, $vars);
        }
    }
}
