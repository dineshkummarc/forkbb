<?php

namespace ForkBB\Models\Forum;

use ForkBB\Models\ManagerModel;
use ForkBB\Models\Forum\Model as Forum;
use RuntimeException;

class Manager extends ManagerModel
{
    /**
     * Закешированные данные по разделам
     * @var array
     */
    protected $forumList = [];

    /**
     * Создает новую модель раздела
     * 
     * @param array $attrs
     * 
     * @return Forum
     */
    public function create(array $attrs = [])
    {
        return $this->c->ForumModel->setAttrs($attrs);
    }

    /**
     * Инициализация списка разделов
     * 
     * @param int $gid
     *
     * @return Manager
     */
    public function init($gid = 0)
    {
        if (empty($gid)) {
            $gid = $this->c->user->group_id;
        }

        $mark = $this->c->Cache->get('forums_mark');
        if (empty($mark)) {
            $this->c->Cache->set('forums_mark', time());
            $list = $this->refresh($gid);
        } else {
            $result = $this->c->Cache->get('forums_' . $gid);
            if (empty($result['time']) || $result['time'] < $mark) {
                $list = $this->refresh($gid);
            } else {
                $list = $result['list'];
            }
        }

        $this->forumList = $list;
        return $this;
    }

    /**
     * Получение модели раздела
     * 
     * @param int $id
     * 
     * @return null|Forum
     */
    public function get($id)
    {
        $forum = parent::get($id);

        if (! $forum instanceof Forum) {
            if (empty($this->forumList[$id])) {
                return null;
            }
            $forum = $this->create($this->forumList[$id]);
            $this->set($id, $forum);
        }

        return $forum;
    }

    /**
     * Обновляет раздел в БД
     *
     * @param Forum $forum
     * 
     * @return Forum
     */
    public function update(Forum $forum)
    {
        return $this->Save->update($forum);
    }

    /**
     * Добавляет новый раздел в БД
     *
     * @param Forum $forum
     * 
     * @return int
     */
    public function insert(Topic $forum)
    {
        $id = $this->Save->insert($forum);
        $this->set($id, $forum);
        return $id;
    }
}
