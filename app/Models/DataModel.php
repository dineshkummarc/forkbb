<?php

namespace ForkBB\Models;

use ForkBB\Models\Model;
use InvalidArgumentException;
use RuntimeException;

class DataModel extends Model
{
    /**
     * Массив флагов измененных свойств модели
     * @var array
     */
    protected $modified = [];

    /**
     * Устанавливает значения для свойств
     * Флаги модификации свойст сброшены
     *
     * @param array $attrs
     *
     * @return DataModel
     */
    public function setAttrs(array $attrs)
    {
        $this->a        = $attrs; //????
        $this->aCalc    = [];
        $this->modified = [];
        return $this;
    }

    /**
     * Перезаписывает свойства модели
     * Флаги модификации свойств сбрасываются/устанавливаются в зависимости от второго параметра
     *
     * @param array $attrs
     * @param bool $setFlags
     *
     * @return DataModel
     */
    public function replAttrs(array $attrs, $setFlags = false)
    {
        foreach ($attrs as $name => $value) {
            $this->__set($name, $value);

            if (! $setFlags) {
//                $this->modified[$name] = true;
//            } else {
                unset($this->modified[$name]);
            }
        }

        return $this;
    }

    /**
     * Возвращает значения свойств в массиве
     *
     * @return array
     */
    public function getAttrs()
    {
        return $this->a; //????
    }

    /**
     * Возвращает массив имен измененных свойств модели
     *
     * @return array
     */
    public function getModified()
    {
        return \array_keys($this->modified);
    }

    /**
     * Обнуляет массив флагов измененных свойств модели
     */
    public function resModified()
    {
        $this->modified = [];
    }

    /**
     * Устанавливает значение для свойства
     *
     * @param string $name
     * @param mixed $val
     */
    public function __set($name, $val)
    {
        // без отслеживания
        if (\strpos($name, '__') === 0) {
            $track = null;
            $name  = substr($name, 2);
        // с отслеживанием
        } else {
            $track = false;
            if (\array_key_exists($name, $this->a)) {
                $track = true;
                $old   = $this->a[$name];
                // fix
                if (\is_int($val) && \is_numeric($old) && \is_int(0 + $old)) {
                    $old = (int) $old;
                }
            }
        }

        parent::__set($name, $val);

        if (null === $track) {
            return;
        }

        if ((! $track && \array_key_exists($name, $this->a))
            || ($track && $old !== $this->a[$name])
        ) {
            $this->modified[$name] = true;
        }
    }
}
