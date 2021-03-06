<?php

declare(strict_types=1);

namespace ForkBB\Models\Post;

use ForkBB\Models\Action;
use ForkBB\Models\DataModel;
use ForkBB\Models\Topic\Model as Topic;
use ForkBB\Models\Forum\Model as Forum;

use InvalidArgumentException;
use RuntimeException;

class Feed extends Action
{
    /**
     * Загружает данные для feed
     */
    public function Feed(DataModel $model): array
    {
        if ($model instanceof Topic) {
            if (0 !== $model->moved_to) {
                return [];
            }

            $vars  = [
                ':id' => $model->id,
            ];
            $query = 'SELECT p.id as pid, p.poster as username, p.poster_id as uid, p.message as content,
                p.hide_smilies, p.posted, p.edited
                FROM ::posts AS p
                WHERE p.topic_id=?i:id
                ORDER BY p.id DESC
                LIMIT 50';

        } else if ($model instanceof Forum) {
            $ids = \array_keys($model->descendants);
            if ($model->id) {
                $ids[] = $model->id;
            }

            if (empty($ids)) {
                return [];
            }

            $vars  = [
                ':forums' => $ids,
            ];
            $query = 'SELECT p.id as pid, p.poster as username, p.poster_id as uid, p.message as content,
                p.hide_smilies, p.posted, p.edited, t.id as tid, t.subject as topic_name, t.forum_id as fid
                FROM ::posts AS p
                INNER JOIN ::topics AS t ON t.id=p.topic_id
                WHERE t.forum_id IN(?ai:forums)
                ORDER BY p.id DESC
                LIMIT 50';

        } else {
            throw new InvalidArgumentException('Expected Topic or Forum');
        }

        return $this->c->DB->query($query, $vars)->fetchAll();
    }
}
