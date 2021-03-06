<?php

declare(strict_types=1);

namespace ForkBB\Models\Online;

use ForkBB\Models\Method;
use ForkBB\Models\Online\Model as Online;

class Info extends Method
{
    /**
     * Получение информации об онлайн посетителях
     */
    public function info(): ?Online
    {
        if (! $this->model->detail) {
            return null;
        }

        $this->model->maxNum  = $this->c->config->a_max_users['number'];
        $this->model->maxTime = $this->c->config->a_max_users['time'];

        $info = [];
        if ('1' == $this->c->user->g_view_users) {
            foreach ($this->model->users as $id => $name) {
                $info[] = [
                    $this->c->Router->link(
                        'User',
                        [
                            'id'   => $id,
                            'name' => $name,
                        ]
                    ),
                    $name,
                ];
            }
        } else {
            foreach ($this->model->users as $name) {
                $info[] = $name;
            }
        }
        $this->model->numUsers = \count($info);

        $s = 0;
        foreach ($this->model->bots as $bot => $arr) {
            $count = \count($arr);
            $s    += $count;
            if ($count > 1) {
                $info[] = '[Bot] ' . $bot . ' (' . $count . ')';
            } else {
                $info[] = '[Bot] ' . $bot;
            }
        }
        $this->model->numGuests = $s + \count($this->model->guests);
        $this->model->info      = $info;

        return $this->model;
    }
}
