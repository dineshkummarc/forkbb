<?php

declare(strict_types=1);

namespace ForkBB\Models\Topic;

use ForkBB\Models\Action;
use ForkBB\Models\Forum\Model as Forum;
use ForkBB\Models\Search\Model as Search;
use ForkBB\Models\Topic\Model as Topic;
use PDO;
use InvalidArgumentException;
use RuntimeException;

class View extends Action
{
    /**
     * Возвращает список тем
     */
    public function view(/* mixed */ $arg): array
    {
        if ($arg instanceof Forum) {
            $full = false;
        } elseif ($arg instanceof Search) {
            $full = true;
        } else {
            throw new InvalidArgumentException('Expected Forum or Search');
        }

        if (
            empty($arg->idsList)
            || ! \is_array($arg->idsList)
        ) {
            throw new RuntimeException('Model does not contain of topics list for display');
        }

        $result = $this->c->topics->loadByIds($arg->idsList, $full);

        if (
            ! $this->c->user->isGuest
            && '1' == $this->c->config->o_show_dot
        ) {
            $vars  = [
                ':uid' => $this->c->user->id,
                ':ids' => $arg->idsList,
            ];
            $query = 'SELECT p.topic_id
                FROM ::posts AS p
                WHERE p.poster_id=?i:uid AND p.topic_id IN (?ai:ids)
                GROUP BY p.topic_id';

            $dots = $this->c->DB->query($query, $vars)->fetchAll(PDO::FETCH_COLUMN);

            foreach ($dots as $id) {
                if (
                    isset($result[$id])
                    && $result[$id] instanceof Topic
                ) {
                    $result[$id]->__dot = true;
                }
            }
        }

        return $result;
    }
}
