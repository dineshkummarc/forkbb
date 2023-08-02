<?php
/**
 * This file is part of the ForkBB <https://github.com/forkbb>.
 *
 * @copyright (c) Visman <mio.visman@yandex.ru, https://github.com/MioVisman>
 * @license   The MIT License (MIT)
 */

declare(strict_types=1);

namespace ForkBB\Core;

use ForkBB\Core\Container;
use ForkBB\Core\Validator;

class Test
{
    public function __construct(protected Container $c)
    {
    }

    public function beforeValidation(Validator $v): Validator
    {
        $v->addValidators([
            'check_field_validation' => [$this, 'vTestCheck'],
        ])->addRules([
            'verificationField' => 'check_field_validation',
        ])->addAliases([
        ]);

        return $v;
    }

    public function vTestCheck(Validator $v, mixed $value): mixed
    {
        if (null !== $value) {
            $v->addError('The :alias contains an invalid value');

            $this->log('Invalid value for field');

            return $value;
        }

        $index = 0;

        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            $index += 1;
        } elseif (\preg_match('%\bmsie\b%i', $_SERVER['HTTP_USER_AGENT'])) {
            $v->addError('Old browser', FORK_MESS_WARN);

            $this->log('Old browser');

            return $value;
        }

        if (empty($_SERVER['HTTP_ACCEPT'])) {
            $index += 5;
        } elseif (false === \strpos($_SERVER['HTTP_ACCEPT'], 'text/html')) {
            $index += 1;
        }

        if (empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            $index += 1;
        }

        if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $index += 1;
        }

        if (
            ! empty($_SERVER['HTTP_PRAGMA'])
            && ! \preg_match('%^no-cache$%iD', $_SERVER['HTTP_PRAGMA'])
        ) {
            $index += 1;
        }

        if (
            ! empty($_SERVER['HTTP_CONNECTION'])
            && ! \preg_match('%^(?:keep-alive|close)$%iD', $_SERVER['HTTP_CONNECTION'])
        ) {
            $index += 3;
        }

        if (! empty($_SERVER['HTTP_REFERER'])) {
            $ref = $this->c->Router->validate($_SERVER['HTTP_REFERER'], 'Index');
            $ref = \strstr($ref, '#', true) ?: $ref;

            if ($ref !== $_SERVER['HTTP_REFERER']) {
                $index += 3;
            }
        }

        if ($index > 3)  {
            $v->addError('Bad browser', FORK_MESS_ERR);

            $this->log('Bad browser');
        }

        return $value;
    }

    protected function log(string $message): void
    {
        $this->c->Log->debug("TEST: {$message}", [
            'user'    => $this->c->user->fLog(),
            'headers' => true,
        ]);
    }
}
