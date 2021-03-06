<?php

declare(strict_types=1);

namespace ForkBB\Models;

use ForkBB\Core\Container;
use ForkBB\Models\Model;

class Method
{
    /**
     * Контейнер
     * @var Container
     */
    protected $c;

    /**
     * Модель
     * @var Model
     */
    protected $model;

    public function __construct(Container $container)
    {
        $this->c = $container;
    }

    /**
     * Объявление модели
     */
    public function setModel(Model $model): Method
    {
        $this->model = $model;

        return $this;
    }
}
