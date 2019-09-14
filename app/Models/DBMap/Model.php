<?php

namespace ForkBB\Models\DBMap;

use ForkBB\Models\Model as ParentModel;

class Model extends ParentModel
{
    /**
     * Загружает карту БД из кеша/БД
     *
     * @return DBMap\Model
     */
    public function init()
    {
        if ($this->c->Cache->has('db_map')) {
            $this->a = $this->c->Cache->get('db_map');
        } else {
            $map = $this->c->DB->getMap();
            $this->c->Cache->set('db_map', $map);
            $this->a = $map;
        }
        return $this;
    }

    /**
     * Сбрасывает кеш карты БД
     *
     * @return DBMap\Model
     */
    public function reset()
    {
        $this->c->Cache->delete('db_map');
        return $this;
    }
}