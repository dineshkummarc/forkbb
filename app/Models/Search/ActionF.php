<?php

declare(strict_types=1);

namespace ForkBB\Models\Search;

use ForkBB\Models\Method;
use ForkBB\Models\Forum\Model as Forum;
use PDO;
use InvalidArgumentException;

class ActionF extends Method
{
    /**
     * Поисковые действия по разделам (подписка на разделы)
     */
    public function actionF(string $action, Forum $root, int $uid = null) /* : array|false */
    {
        $forums = \array_keys($root->descendants);
        if ($root->id) {
            $forums[] = $root->id;
        }
        if (empty($forums)) {
            return [];
        }

        $list = [];
        switch ($action) {
            case 'forums_subscriptions':
                if (0 !== $root->id) {
                    return false;
                }

                $user = $this->c->users->load($uid);

                if (! $this->c->ProfileRules->setUser($user)->viewSubscription) {
                    return false;
                }

                $subscr     = $this->c->subscriptions;
                $subscrInfo = $subscr->info($user, $subscr::FORUMS_DATA);
                $ids        = $subscrInfo[$subscr::FORUMS_DATA] ?? [];

                if (empty($ids)) {
                    break;
                }

                $all = $this->c->forums->loadTree(0)->descendants;

                foreach ($ids as $id) {
                    if (
                        isset($all[$id])
                        && $all[$id] instanceof Forum
                    ) {
                        $forum                  = clone $all[$id];
                        $forum->parent_forum_id = 0;

                        unset($forum->subforums, $forum->descendants);

                        $list[$id] = $forum;
                    }
                }

                break;
            default:
                throw new InvalidArgumentException('Unknown action: ' . $action);
        }

        $this->model->numPages = 1;

        // нет такой страницы в результате поиска
        if (! $this->model->hasPage()) {
            return false;
        }

        return $list;
    }
}
