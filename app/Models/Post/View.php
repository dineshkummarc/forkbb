<?php

declare(strict_types=1);

namespace ForkBB\Models\Post;

use ForkBB\Models\Action;
use ForkBB\Models\Post\Model as Post;
use ForkBB\Models\Search\Model as Search;
use ForkBB\Models\Topic\Model as Topic;
use PDO;
use InvalidArgumentException;
use RuntimeException;

class View extends Action
{
    /**
     * Возвращает список сообщений
     */
    public function view(/* mixed */ $arg, bool $review = false): array
    {
        if (
            ! $arg instanceof Topic
            && ! $arg instanceof Search
        ) {
            throw new InvalidArgumentException('Expected Topic or Search');
        }

        if (
            empty($arg->idsList)
            || ! \is_array($arg->idsList)
        ) {
            throw new RuntimeException('Model does not contain of posts list for display');
        }

        if (! $review) {
            $vars  = [
                ':ids' => $arg->idsList,
            ];
            $query = 'SELECT w.id, w.message, w.poster, w.posted
                FROM ::warnings AS w
                WHERE w.id IN (?ai:ids)';

            $warnings = $this->c->DB->query($query, $vars)->fetchAll(PDO::FETCH_GROUP);
        }

        $userIds = [];
        $result  = $this->manager->loadByIds($arg->idsList, ! $arg instanceof Topic);

        foreach ($result as $post) {
            if ($post instanceof Post) {
                if (isset($warnings[$post->id])) {
                    $post->__warnings = $warnings[$post->id];
                }
                $userIds[$post->poster_id] = $post->poster_id;
            }
        }

        $this->c->users->loadByIds($userIds);

        $offset    = ($arg->page - 1) * $this->c->user->disp_posts;
        $timeMax   = 0;
        if ($review) {
            $postCount = $arg->num_replies + 2;
            $sign      = -1;
        } else {
            $postCount = 0;
            $sign      = 1;
        }

        if ($arg instanceof Topic) {
            foreach ($result as $post) {
                if ($post->id === $arg->last_post_id) { // время последнего сообщения в теме может равняться
                    $timeMax = $arg->last_post;         // времени его редактирования, а не создания
                } elseif ($post->posted > $timeMax) {
                    $timeMax = $post->posted;
                }
                if (
                    $post->id === $arg->first_post_id
                    && $offset > 0
                ) {
                    if (empty($post->id)) {
                        continue;
                    }
                    $post->__postNumber = 1;
                } else {
                    $postCount += $sign;
                    if (empty($post->id)) {
                        continue;
                    }
                    $post->__postNumber = $offset + $postCount;
                }
            }
            $arg->timeMax = $timeMax;
        } else {
            foreach ($result as $post) {
                ++$postCount;
                if (empty($post->id)) {
                    continue;
                }
                $post->__postNumber = $offset + $postCount; //????
            }
        }

        return $result;
    }
}
