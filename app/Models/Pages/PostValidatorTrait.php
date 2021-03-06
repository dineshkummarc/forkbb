<?php

declare(strict_types=1);

namespace ForkBB\Models\Pages;

use ForkBB\Core\Validator;
use ForkBB\Models\Model;
use function \ForkBB\__;

trait PostValidatorTrait
{
    /**
     * Дополнительная проверка subject
     */
    public function vCheckSubject(Validator $v, $subject, $attr, $executive)
    {
        // после цензуры заголовок темы путой
        if ('' == $this->c->censorship->censor($subject)) {
            $v->addError('No subject after censoring');
        // заголовок темы только заглавными буквами
        } elseif (
            ! $executive
            && '0' == $this->c->config->p_subject_all_caps
            && \preg_match('%\p{Lu}%u', $subject)
            && ! \preg_match('%\p{Ll}%u', $subject)
        ) {
            $v->addError('All caps subject');
        }

        return $subject;
    }

    /**
     * Дополнительная проверка message
     */
    public function vCheckMessage(Validator $v, $message, $attr, $executive)
    {
        $prepare = null;

        // после цензуры текст сообщения пустой
        if ('' == $this->c->censorship->censor($message)) {
            $v->addError('No message after censoring');
        // проверка парсером
        } else {
            $prepare = true;
            $message = $this->c->Parser->prepare($message); //????

            foreach($this->c->Parser->getErrors() as $error) {
                $prepare = false;
                $v->addError($error);
            }
        }

        // текст сообщения только заглавными буквами
        if (
            true === $prepare
            && ! $executive
            && '0' == $this->c->config->p_message_all_caps
        ) {
            $text = $this->c->Parser->getText();

            if (
                \preg_match('%\p{Lu}%u', $text)
                && ! \preg_match('%\p{Ll}%u', $text)
            ) {
                $v->addError('All caps message');
            }
        }

        return $message;
    }

    /**
     * Проверка времени ограничения флуда
     */
    public function vCheckTimeout(Validator $v, $submit)
    {
        if ($v->noValue($submit)) {
            return null;
        }

        $time = \time() - (int) $this->user->last_post;

        if ($time < $this->user->g_post_flood) {
            $v->addError(__('Flood message', $this->user->g_post_flood - $time), 'e');
        }

        return $submit;
    }

    /**
     * Подготовка валидатора к проверке данных из формы создания темы/сообщения
     */
    protected function messageValidator(Model $model, string $marker, array $args, bool $editPost = false, bool $editSubject = false): Validator
    {
        $this->c->Lang->load('validator');

        if ($this->user->isGuest) {
            $ruleEmail    = ('1' == $this->c->config->p_force_guest_email ? 'required|' : '') . 'string:trim|email:noban';
            $ruleUsername = 'required|string:trim|username';
        } else {
            $ruleEmail    = 'absent';
            $ruleUsername = 'absent';
        }

        if (
            $this->user->isAdmin
            || $this->user->isModerator($model)
        ) {
            if ($editSubject) {
                $ruleStickTopic = 'checkbox';
                $ruleStickFP    = 'checkbox';
            } else {
                $ruleStickTopic = 'absent';
                $ruleStickFP    = 'absent';
            }
            if (
                ! $editSubject
                && ! $editPost
            ) {
                $ruleMergePost  = 'checkbox';
            } else {
                $ruleMergePost  = 'absent';
            }
            if (
                $editPost
                && ! $model->user->isGuest
                && ! $model->user->isAdmin
            ) {
                $ruleEditPost   = 'checkbox';
            } else {
                $ruleEditPost   = 'absent';
            }
            $executive          = true;
        } else {
            $ruleStickTopic     = 'absent';
            $ruleStickFP        = 'absent';
            $ruleMergePost      = 'absent:1';
            $ruleEditPost       = 'absent';
            $executive          = false;
        }

        if ($editSubject) {
            $ruleSubject = 'required|string:trim,spaces|min:1|max:70|' . ($executive ? '' : 'noURL|') . 'check_subject';
        } else {
            $ruleSubject = 'absent';
        }

        if (
            ! $editPost
            && '1' == $this->c->config->o_topic_subscriptions
            && $this->user->email_confirmed
        ) {
            $ruleSubscribe = 'checkbox';
        } else {
            $ruleSubscribe = 'absent';
        }

        if ('1' == $this->c->config->o_smilies) {
            $ruleHideSmilies = 'checkbox';
        } else {
            $ruleHideSmilies = 'absent';
        }

        $v = $this->c->Validator->reset()
            ->addValidators([
                'check_subject'  => [$this, 'vCheckSubject'],
                'check_message'  => [$this, 'vCheckMessage'],
                'check_timeout'  => [$this, 'vCheckTimeout'],
            ])->addRules([
                'token'        => 'token:' . $marker,
                'email'        => $ruleEmail,
                'username'     => $ruleUsername,
                'subject'      => $ruleSubject,
                'stick_topic'  => $ruleStickTopic,
                'stick_fp'     => $ruleStickFP,
                'merge_post'   => $ruleMergePost,
                'hide_smilies' => $ruleHideSmilies,
                'edit_post'    => $ruleEditPost,
                'subscribe'    => $ruleSubscribe,
                'preview'      => 'string',
                'submit'       => 'string|check_timeout',
                'message'      => 'required|string:trim|max:' . $this->c->MAX_POST_SIZE . '|check_message',
            ])->addAliases([
                'email'        => 'Email',
                'username'     => 'Username',
                'subject'      => 'Subject',
            ])->addArguments([
                'token'                 => $args,
                'subject.check_subject' => $executive,
                'message.check_message' => $executive,
                'email.email'           => $this->user,
            ])->addMessages([
                'username.login' => 'Login format',
            ]);

        return $v;
    }
}
