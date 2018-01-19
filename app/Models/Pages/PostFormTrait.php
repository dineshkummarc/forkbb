<?php

namespace ForkBB\Models\Pages;

use ForkBB\Models\Model;

trait PostFormTrait
{
    /**
     * Возвращает данные для построения формы создания темы/сообщения
     *
     * @param Model $model
     * @param string $marker
     * @param array $args
     * @param bool $editPost
     * @param bool $editSubject
     * @param bool $quickReply
     *
     * @return array
     */
    protected function messageForm(Model $model, $marker, array $args, $editPost = false, $editSubject = false, $quickReply = false)
    {
        $vars = isset($args['_vars']) ? $args['_vars'] : null;
        unset($args['_vars']);

        $autofocus = $quickReply ? null : true;
        $form = [
            'action' => $this->c->Router->link($marker, $args),
            'hidden' => [
                'token' => $this->c->Csrf->create($marker, $args),
            ],
            'sets'   => [],
            'btns'   => [
                'submit'  => [
                    'type'      => 'submit',
                    'value'     => \ForkBB\__('Submit'),
                    'accesskey' => 's',
                ],
                'preview' => [
                    'type'      => 'submit',
                    'value'     => \ForkBB\__('Preview'),
                    'accesskey' => 'p',
                    'class'     => 'f-minor',
                ],
            ],
        ];

        $fieldset = [];
        if ($this->c->user->isGuest) {
            $fieldset['username'] = [
                'dl'        => 'w1',
                'type'      => 'text',
                'maxlength' => 25,
                'title'     => \ForkBB\__('Username'),
                'required'  => true,
                'pattern'   => '^.{2,25}$',
                'value'     => isset($vars['username']) ? $vars['username'] : null,
                'autofocus' => $autofocus,
            ];
            $fieldset['email'] = [
                'dl'        => 'w2',
                'type'      => 'text',
                'maxlength' => 80,
                'title'     => \ForkBB\__('Email'),
                'required'  => '1' == $this->c->config->p_force_guest_email,
                'pattern'   => '.+@.+',
                'value'     => isset($vars['email']) ? $vars['email'] : null,
            ];
            $autofocus = null;
        }

        if ($editSubject) {
            $fieldset['subject'] = [
                'type'      => 'text',
                'maxlength' => 70,
                'title'     => \ForkBB\__('Subject'),
                'required'  => true,
                'value'     => isset($vars['subject']) ? $vars['subject'] : null,
                'autofocus' => $autofocus,
            ];
            $autofocus = null;
        }

        $fieldset['message'] = [
            'type'     => 'textarea',
            'title'    => \ForkBB\__('Message'),
            'required' => true,
            'value'    => isset($vars['message']) ? $vars['message'] : null,
            'bb'       => [
                ['link', \ForkBB\__('BBCode'), \ForkBB\__($this->c->config->p_message_bbcode == '1' ? 'on' : 'off')],
                ['link', \ForkBB\__('url tag'), \ForkBB\__($this->c->config->p_message_bbcode == '1' && $this->c->user->g_post_links == '1' ? 'on' : 'off')],
                ['link', \ForkBB\__('img tag'), \ForkBB\__($this->c->config->p_message_bbcode == '1' && $this->c->config->p_message_img_tag == '1' ? 'on' : 'off')],
                ['link', \ForkBB\__('Smilies'), \ForkBB\__($this->c->config->o_smilies == '1' ? 'on' : 'off')],
            ],
            'autofocus' => $autofocus,
        ];
        $form['sets'][] = [
            'fields' => $fieldset,
        ];
        $autofocus = null;

        $fieldset = [];
        if ($this->c->user->isAdmin || $this->c->user->isModerator($model)) {
            if ($editSubject) {
                $fieldset['stick_topic'] = [
                    'type'    => 'checkbox',
                    'label'   => \ForkBB\__('Stick topic'),
                    'value'   => '1',
                    'checked' => isset($vars['stick_topic']) ? (bool) $vars['stick_topic'] : false,
                ];
                $fieldset['stick_fp'] = [
                    'type'    => 'checkbox',
                    'label'   => \ForkBB\__('Stick first post'),
                    'value'   => '1',
                    'checked' => isset($vars['stick_fp']) ? (bool) $vars['stick_fp'] : false,
                ];
            } elseif (! $editPost) {
                $fieldset['merge_post'] = [
                    'type'    => 'checkbox',
                    'label'   => \ForkBB\__('Merge posts'),
                    'value'   => '1',
                    'checked' => isset($vars['merge_post']) ? (bool) $vars['merge_post'] : true,
                ];
            }
            if ($editPost) {
                $fieldset['edit_post'] = [
                    'type'    => 'checkbox',
                    'label'   => \ForkBB\__('EditPost edit'),
                    'value'   => '1',
                    'checked' => isset($vars['edit_post']) ? (bool) $vars['edit_post'] : false,
                ];
            }
        }
        if (! $quickReply && $this->c->config->o_smilies == '1') {
            $fieldset['hide_smilies'] = [
                'type'    => 'checkbox',
                'label'   => \ForkBB\__('Hide smilies'),
                'value'   => '1',
                'checked' => isset($vars['hide_smilies']) ? (bool) $vars['hide_smilies'] : false,
            ];
        }
        if ($fieldset) {
            $form['sets'][] = [
                'legend' => \ForkBB\__('Options'),
                'fields' => $fieldset,
            ];
        }

        return $form;
    }
}
