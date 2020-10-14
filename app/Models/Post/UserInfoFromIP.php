<?php

declare(strict_types=1);

namespace ForkBB\Models\Post;

use ForkBB\Models\Action;
use ForkBB\Models\Post\Model as Post;

class UserInfoFromIP extends Action
{
    /**
     * Возвращает массив данных с id пользователей (именами гостей)
     */
    public function userInfoFromIP(string $ip): array
    {
        $vars  = [
            ':ip' => $ip,
        ];
        $query = 'SELECT p.poster_id, p.poster
            FROM ::posts AS p
            WHERE p.poster_ip=?s:ip
            GROUP BY p.poster_id, p.poster
            ORDER BY p.poster';

        $stmt   = $this->c->DB->query($query, $vars);
        $result = [];
        $ids    = [];

        while ($row = $stmt->fetch()) {
            if (1 === $row['poster_id']) {
                $result[] = $row['poster'];
            } elseif (empty($ids[$row['poster_id']])) {
                $result[]               = $row['poster_id'];
                $ids[$row['poster_id']] = true;
            }
        }

        return $result;
    }
}
