<?php

namespace ForkBB\Models;

use ForkBB\Models\DataModel;
use ForkBB\Models\Model;
use ForkBB\Models\Forum;
use RuntimeException;

class User extends DataModel
{
    /**
     * Статус неподтвержденного
     *
     * @return bool
     */
    protected function getisUnverified()
    {
        return $this->group_id == $this->c->GROUP_UNVERIFIED;
    }

    /**
     * Статус гостя
     *
     * @return bool
     */
    protected function getisGuest()
    {
        return $this->group_id == $this->c->GROUP_GUEST
            || $this->id < 2
            || $this->group_id == $this->c->GROUP_UNVERIFIED;
    }

    /**
     * Статус админа
     *
     * @return bool
     */
    protected function getisAdmin()
    {
        return $this->group_id == $this->c->GROUP_ADMIN;
    }

    /**
     * Статус админа/модератора
     *
     * @return bool
     */
    protected function getisAdmMod()
    {
        return $this->group_id == $this->c->GROUP_ADMIN
            || $this->g_moderator == '1';
    }

    /**
     * Статус модератора для указанной модели
     * 
     * @param Model $model
     * 
     * @throws RuntimeException
     * 
     * @return bool
     */
    public function isModerator(Model $model)
    {
        while (! $model instanceof Forum) {
            $model = $model->parent;
            if (! $model instanceof Model) {
                throw new RuntimeException('Moderator\'s rights can not be found');
            }
        }
        return isset($model->moderators[$this->id]);
    }

    /**
     * Время последнего действия пользователя
     *
     * @return int
     */
    protected function getlogged()
    {
        return empty($this->a['logged']) ? time() : $this->a['logged'];
    }

    /**
     * Статус наличия данных пользователя в таблице online //????
     *
     * @return bool
     */
    protected function getisLogged()
    {
        return ! empty($this->a['logged']);
    }

    /**
     * Текущий язык пользователя
     *
     * @return string
     */
    protected function getlanguage()
    {
        $langs = $this->c->Func->getLangs();

        $lang = $this->isGuest || empty($this->a['language']) || ! in_array($this->a['language'], $langs)
            ? $this->c->config->o_default_lang
            : $this->a['language'];

        if (in_array($lang, $langs)) {
            return $lang;
        } else {
            return isset($langs[0]) ? $langs[0] : 'English';
        }
    }

    /**
     * Текущий стиль отображения
     *
     * @return string
     */
    protected function getstyle()
    {
        $styles = $this->c->Func->getStyles();

        $style = $this->isGuest || empty($this->a['style']) || ! in_array($this->a['style'], $styles)
            ? $this->c->config->o_default_style
            : $this->a['style'];

        if (in_array($style, $styles)) {
            return $style;
        } else {
            return isset($styles[0]) ? $styles[0] : 'ForkBB';
        }
    }

    /**
     * Ссылка на профиль пользователя
     *
     * @return string
     */
    protected function getlink()
    {
        return $this->c->Router->link('User', ['id' => $this->id, 'name' => $this->username]);
    }

    /**
     * Ссылка на аватару пользователя
     *
     * @return null|string
     */
    protected function getavatar()
    {
        $filetypes = array('jpg', 'gif', 'png');

        foreach ($filetypes as $type) {
            $path = $this->c->DIR_PUBLIC . "/{$this->c->config->o_avatars_dir}/{$this->id}.{$type}";

            if (file_exists($path) && getimagesize($path)) {
                return $this->c->PUBLIC_URL . "/{$this->c->config->o_avatars_dir}/{$this->id}.{$type}";
            }
        }

        return null;
    }

    /**
     * Титул пользователя
     *
     * @return string
     */
    public function title()
    {
        if (isset($this->c->bans->userList[mb_strtolower($this->username)])) { //????
            return __('Banned');
        } elseif ($this->title != '') {
            return $this->cens()->title;
        } elseif ($this->g_user_title != '') {
            return $this->cens()->g_user_title;
        } elseif ($this->isGuest) {
            return __('Guest');
        } else {
            return __('Member');
        }
    }

    /**
     * Статус online
     *
     * @return bool
     */
    protected function getonline()
    {
        return isset($this->c->Online->online[$this->id]);
    }

    /**
     * HTML код подписи
     * 
     * @return string
     */
    protected function gethtmlSign()
    {
        return $this->c->Parser->parseSignature($this->cens()->signature); //????
    }
}
