<?php

declare(strict_types=1);

namespace ForkBB\Core;

use Parserus;
use ForkBB\Core\Container;

class Parser extends Parserus
{
    /**
     * Контейнер
     * @var Container
     */
    protected $c;

    public function __construct(int $flag, Container $container)
    {
        $this->c = $container;
        parent::__construct($flag);
        $this->init();
    }

    /**
     * Инициализация данных
     */
    protected function init(): void
    {
        if (
            '1' == $this->c->config->p_message_bbcode
            || '1' == $this->c->config->p_sig_bbcode
        ) {
            $this->setBBCodes($this->c->bbcode->list);
        }

        if (
            '1' == $this->c->user->show_smilies
            && (
                '1' == $this->c->config->o_smilies_sig
                || '1' == $this->c->config->o_smilies
            )
        ) {
            $smilies = [];

            foreach ($this->c->smilies->list as $cur) {
                $smilies[$cur['sm_code']] = $this->c->PUBLIC_URL . '/img/sm/' . $cur['sm_image'];
            }

            $info = $this->c->BBCODE_INFO;

            $this->setSmilies($smilies)->setSmTpl($info['smTpl'], $info['smTplTag'], $info['smTplBl']);
        }

        $this->setAttr('baseUrl', $this->c->BASE_URL);
        $this->setAttr('showImg', '0' != $this->c->user->show_img);
        $this->setAttr('showImgSign', '0' != $this->c->user->show_img_sig);
    }

    /**
     * Проверяет разметку сообщения с бб-кодами
     * Пытается исправить неточности разметки
     * Генерирует ошибки разметки
     */
    public function prepare(string $text, bool $isSignature = false): string
    {
        if ($isSignature) {
            $whiteList = '1' == $this->c->config->p_sig_bbcode
                ? (empty($this->c->config->a_bb_white_sig) && empty($this->c->config->a_bb_black_sig)
                    ? null
                    : $this->c->config->a_bb_white_sig
                )
                : [];
            $blackList = null;
        } else {
            $whiteList = '1' == $this->c->config->p_message_bbcode
                ? (empty($this->c->config->a_bb_white_mes) && empty($this->c->config->a_bb_black_mes)
                    ? null
                    : $this->c->config->a_bb_white_mes
                )
                : [];
            $blackList = null;
        }

        $this->setAttr('isSign', $isSignature)
             ->setWhiteList($whiteList)
             ->setBlackList($blackList)
             ->parse($text, ['strict' => true])
             ->stripEmptyTags(" \n\t\r\v", true);

        if ('1' == $this->c->config->o_make_links) {
            $this->detectUrls();
        }

        return \preg_replace('%^(\x20*\n)+|(\n\x20*)+$%D', '', $this->getCode());
    }

    /**
     * Преобразует бб-коды в html в сообщениях
     */
    public function parseMessage(string $text = null, bool $hideSmilies = false): string
    {
        // при null предполагается брать данные после prepare()
        if (null !== $text) {
            $whiteList = '1' == $this->c->config->p_message_bbcode ? null : [];
            $blackList = $this->c->config->a_bb_black_mes;

            $this->setAttr('isSign', false)
                 ->setWhiteList($whiteList)
                 ->setBlackList($blackList)
                 ->parse($text);
        }

        if (
            ! $hideSmilies
            && '1' == $this->c->config->o_smilies
        ) {
            $this->detectSmilies();
        }

        return $this->getHtml();
    }

    /**
     * Преобразует бб-коды в html в подписях пользователей
     */
    public function parseSignature(string $text = null): string
    {
        // при null предполагается брать данные после prepare()
        if (null !== $text) {
            $whiteList = '1' == $this->c->config->p_sig_bbcode ? null : [];
            $blackList = $this->c->config->a_bb_black_sig;

            $this->setAttr('isSign', true)
                 ->setWhiteList($whiteList)
                 ->setBlackList($blackList)
                 ->parse($text);
        }

        if ('1' == $this->c->config->o_smilies_sig) {
            $this->detectSmilies();
        }

        return $this->getHtml();
    }
}
