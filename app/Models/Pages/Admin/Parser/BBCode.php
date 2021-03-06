<?php

declare(strict_types=1);

namespace ForkBB\Models\Pages\Admin\Parser;

use ForkBB\Core\Validator;
use ForkBB\Models\Page;
use ForkBB\Models\BBCodeList\Structure;
use ForkBB\Models\Pages\Admin\Parser;
use function \ForkBB\__;

class BBCode extends Parser
{
    /**
     * Редактирование натроек bbcode
     */
    public function view(array $args, string $method): Page
    {
        $this->c->bbcode->load();

        if ('POST' === $method) {
            $v = $this->c->Validator->reset()
                ->addValidators([
                ])->addRules([
                    'token'           => 'token:AdminBBCode',
                    'bbcode.*.in_mes' => 'required|integer|min:0|max:2',
                    'bbcode.*.in_sig' => 'required|integer|min:0|max:2',
                ])->addAliases([
                ])->addArguments([
                ])->addMessages([
                ]);

            if ($v->validation($_POST)) {
                $mesClear  = true;
                $sigClear  = true;
                $white_mes = [];
                $black_mes = [];
                $white_sig = [];
                $black_sig = [];

                foreach ($this->c->bbcode->bbcodeTable as $id => $tagData) {
                    $tag    = $tagData['bb_tag'];
                    $bbcode = $v->bbcode;

                    if ('ROOT' === $tag) {
                        continue;
                    }

                    if (! isset($bbcode[$tag]['in_mes'], $bbcode[$tag]['in_sig'])) {
                        $mesClear  = false;
                        $sigClear  = false;
                        continue;
                    }

                    switch ($bbcode[$tag]['in_mes']) {
                        case 2:
                            $white_mes[] = $tag;
                            break;
                        case 0:
                            $black_mes[] = $tag;
                        default:
                            $mesClear  = false;
                            break;
                    }

                    switch ($bbcode[$tag]['in_sig']) {
                        case 2:
                            $white_sig[] = $tag;
                            break;
                        case 0:
                            $black_sig[] = $tag;
                        default:
                            $sigClear  = false;
                            break;
                    }
                }

                $this->c->config->a_bb_white_mes = $mesClear ? [] : $white_mes;
                $this->c->config->a_bb_black_mes = $mesClear ? [] : $black_mes;
                $this->c->config->a_bb_white_sig = $sigClear ? [] : $white_sig;
                $this->c->config->a_bb_black_sig = $sigClear ? [] : $black_sig;

                $this->c->config->save();

                return $this->c->Redirect->page('AdminBBCode')->message('Parser settings updated redirect');
            }

            $this->fIswev  = $v->getErrors();
        }

        $this->nameTpl   = 'admin/form';
        $this->aCrumbs[] = [
            $this->c->Router->link('AdminBBCode'),
            __('BBCode management'),
        ];
        $this->form      = $this->formView();
        $this->titleForm = __('BBCode head');
        $this->classForm = 'bbcode';

        return $this;
    }

    /**
     * Формирует данные для формы
     */
    protected function formView(): array
    {
        $form = [
            'action' => $this->c->Router->link('AdminBBCode'),
            'hidden' => [
                'token' => $this->c->Csrf->create('AdminBBCode'),
            ],
            'sets' => [
                'bbcode-legend' => [
                    'class'  => 'bbcode-legend',
                    'legend' => __('BBCode list subhead'),
                    'fields' => [],
                ],
            ],
            'btns'   => [
                'new' => [
                    'type'      => 'btn',
                    'value'     => __('New BBCode'),
                    'link'      => $this->c->Router->link('AdminBBCodeNew'),
//                    'accesskey' => 'n',
                ],
                'save' => [
                    'type'      => 'submit',
                    'value'     => __('Save changes'),
//                    'accesskey' => 's',
                ],
            ],
        ];

        $selectList = [
            2 => __('BBCode allowed'),
            1 => __('BBCode display only'),
            0 => __('BBCode not allowed'),
        ];

        foreach ($this->c->bbcode->bbcodeTable as $id => $tagData) {
            $fields = [];
            $tag    = $tagData['bb_tag'];

            $fields["bbcode{$id}-tag"] = [
                'class'     => ['bbcode', 'tag'],
                'type'      => $tagData['bb_edit'] > 0 ? 'link' : 'str',
                'value'     => $tag,
                'caption'   => __('BBCode tag label'),
                'title'     => __('BBCode tag title'),
                'href'      => 1 === $tagData['bb_edit']
                    ? $this->c->Router->link('AdminBBCodeEdit', ['id' => $id])
                    : null,
            ];
            $fields["bbcode[{$tag}][in_mes]"] = [
                'class'     => ['bbcode', 'in_mes'],
                'type'      => 'select',
                'options'   => $selectList,
                'value'     => $this->getValue($tag, $this->c->config->a_bb_white_mes, $this->c->config->a_bb_black_mes),
                'caption'   => __('BBCode mes label'),
                'disabled'  => 'ROOT' === $tag,
            ];
            $fields["bbcode[{$tag}][in_sig]"] = [
                'class'     => ['bbcode', 'in_sig'],
                'type'      => 'select',
                'options'   => $selectList,
                'value'     => $this->getValue($tag, $this->c->config->a_bb_white_sig, $this->c->config->a_bb_black_sig),
                'caption'   => __('BBCode sig label'),
                'disabled'  => 'ROOT' === $tag,
            ];
            $fields["bbcode{$id}-del"] = [
                'class'     => ['bbcode', 'delete'],
                'type'      => 'btn',
                'value'     => '❌',
                'caption'   => __('Delete'),
                'title'     => __('Delete'),
                'link'      => $this->c->Router->link(
                    'AdminBBCodeDelete',
                    [
                        'id'    => $id,
                        'token' => null,
                    ]
                ),
                'disabled'  => 1 !== $tagData['bb_delete'],
            ];

            $form['sets']["bbcode{$id}"] = [
                'class'  => 'bbcode',
                'legend' => __('BBCode %s', $tag),
                'fields' => $fields,
            ];
        }

        return $form;
    }

    /**
     * Вычисляет значение для select на основе белого и черного списков bbcode
     */
    protected function getValue(string $tag, array $white, array $black): int
    {
        if ('ROOT' === $tag) {
            return 1;
        } elseif (empty($white) && empty($black)) {
            return 2;
        } elseif (\in_array($tag, $black)) {
            return 0;
        } elseif (\in_array($tag, $white)) {
            return 2;
        } else {
            return 1;
        }
    }

    /**
     * Удаляет bbcode
     */
    public function delete(array $args, string $method): Page
    {
        if (! $this->c->Csrf->verify($args['token'], 'AdminBBCodeDelete', $args)) {
            return $this->c->Message->message($this->c->Csrf->getError());
        }

        $this->c->bbcode->delete((int) $args['id']);

        return $this->c->Redirect->page('AdminBBCode')->message('BBCode deleted redirect');
    }

    /**
     * Редактирование/добавление нового bbcode
     */
    public function edit(array $args, string $method): Page
    {
        $this->c->bbcode->load();

        $structure = $this->c->BBStructure;
        $id        = isset($args['id']) ? (int) $args['id'] : 0;
        if ($id > 0) {
            if (
                empty($this->c->bbcode->bbcodeTable[$id])
                || 1 !== $this->c->bbcode->bbcodeTable[$id]['bb_edit']
            ) {
                return $this->c->Message->message('Bad request');
            }

            $structure = $structure->fromString($this->c->bbcode->bbcodeTable[$id]['bb_structure']);
        }

        $bbTypes = [];
        $bbNames = [];
        foreach ($this->c->bbcode->bbcodeTable as $cur) {
            $type = $this->c->BBStructure->fromString($cur['bb_structure'])->type;
            $bbTypes[$type] = $type;
            $bbNames[$cur['bb_tag']] = $cur['bb_tag'];
        }
        $this->bbTypes = $bbTypes;

        if ($id > 0) {
            $title            = __('Edit bbcode head');
            $page             = 'AdminBBCodeEdit';
            $pageArgs         = ['id' => $id];
        } else {
            $title            = __('Add bbcode head');
            $page             = 'AdminBBCodeNew';
            $pageArgs         = [];
        }
        $this->formAction = $this->c->Router->link($page, $pageArgs);
        $this->formToken  = $this->c->Csrf->create($page, $pageArgs);

        if ('POST' === $method) {
            $v = $this->c->Validator->reset()
                ->addValidators([
                    'check_all'                 => [$this, 'vCheckAll'],
                ])->addRules([
                    'token'                     => 'token:' . $page,
                    'tag'                       => $id > 0 ? 'absent' : 'required|string:trim|regex:%^[a-z\*][a-z\d-]{0,10}$%|not_in:' . \implode(',', $bbNames),
                    'type'                      => 'required|string|in:' . \implode(',', $bbTypes),
                    'type_new'                  => 'string:trim|regex:%^[a-z][a-z\d-]{0,19}$%',
                    'parents.*'                 => 'required|string|in:' . \implode(',', $bbTypes),
                    'handler'                   => 'string:trim|max:65535',
                    'text_handler'              => 'string:trim|max:65535',
                    'recursive'                 => 'required|integer|in:0,1',
                    'text_only'                 => 'required|integer|in:0,1',
                    'tags_only'                 => 'required|integer|in:0,1',
                    'pre'                       => 'required|integer|in:0,1',
                    'single'                    => 'required|integer|in:0,1',
                    'auto'                      => 'required|integer|in:0,1',
                    'self_nesting'              => 'required|integer|min:0|max:10',
                    'no_attr.allowed'           => 'required|integer|in:0,1',
                    'no_attr.body_format'       => 'string:trim|max:1024',
                    'no_attr.text_only'         => 'required|integer|in:0,1',
                    'def_attr.allowed'          => 'required|integer|in:0,1',
                    'def_attr.required'         => 'required|integer|in:0,1',
                    'def_attr.format'           => 'string:trim|max:1024',
                    'def_attr.body_format'      => 'string:trim|max:1024',
                    'def_attr.text_only'        => 'required|integer|in:0,1',
                    'other_attrs.*.allowed'     => 'required|integer|in:0,1',
                    'other_attrs.*.required'    => 'required|integer|in:0,1',
                    'other_attrs.*.format'      => 'string:trim|max:1024',
                    'other_attrs.*.body_format' => 'string:trim|max:1024',
                    'other_attrs.*.text_only'   => 'required|integer|in:0,1',
                    'new_attr.name'             => ['string:trim', 'regex:%^(?:|[a-z-]{2,15})$%'],
                    'new_attr.allowed'          => 'required|integer|in:0,1',
                    'new_attr.required'         => 'required|integer|in:0,1',
                    'new_attr.format'           => 'string:trim|max:1024',
                    'new_attr.body_format'      => 'string:trim|max:1024',
                    'new_attr.text_only'        => 'required|integer|in:0,1',
                    'save'                      => 'check_all',
                ])->addAliases([
                ])->addArguments([
                    'token'                    => $pageArgs,
                    'save'                     => $structure,
                ])->addMessages([
                ]);

                if ($v->validation($_POST)) {
                    if ($id > 0) {
                        $this->c->bbcode->update($id, $structure);
                        $message = 'BBCode updated redirect';
                    } else {
                        $id = $this->c->bbcode->insert($structure);
                        $message = 'BBCode added redirect';
                    }

                    return $this->c->Redirect->page('AdminBBCodeEdit', ['id' => $id])->message($message);
                }

                $this->fIswev = $v->getErrors();
        }

        $this->aCrumbs[] = [
            $this->formAction,
            $title,
        ];
        if ($id > 0) {
            $this->aCrumbs[] = __('"%s"', $this->c->bbcode->bbcodeTable[$id]['bb_tag']);
        }
        $this->aCrumbs[] = [
            $this->c->Router->link('AdminBBCode'),
            __('BBCode management'),
        ];
        $this->form      = $this->formEdit($id, $structure);
        $this->titleForm = $title;
        $this->classForm = 'editbbcode';
        $this->nameTpl   = 'admin/form';

        return $this;
    }

    /**
     * Проверяет данные bb-кода
     */
    public function vCheckAll(Validator $v, string $txt, $attrs, Structure $structure): string
    {
        if (! empty($v->getErrors())) {
            return $txt;
        }

        $data = $v->getData();
        unset($data['token'], $data['save']);

        foreach ($data as $key => $value) {
            if ('type_new' === $key) {
                if (isset($value[0])) {
                    $structure->type = $value;
                }
            } else {
                $structure->{$key} = $value;
            }
        }

        $error = $structure->getError();

        if (\is_array($error)) {
            $v->addError(__(...$error));
        }

        return $txt;
    }


    /**
     * Формирует данные для формы
     */
    protected function formEdit(int $id, Structure $structure): array
    {
        $form = [
            'action' => $this->formAction,
            'hidden' => [
                'token' => $this->formToken,
            ],
            'sets' => [],
            'btns'   => [
                'reset' => [
                    'type'      => 'btn',
                    'value'     => __('Default structure'),
                    'link'      => $this->c->Router->link(
                        'AdminBBCodeDefault',
                        [
                            'id'    => $id,
                            'token' => null,
                        ]
                    ),
//                    'accesskey' => 'r',
                ],
                'save' => [
                    'type'      => 'submit',
                    'value'     => __('Save'),
//                    'accesskey' => 's',
                ],
            ],
        ];

        if (! $structure->isInDefault()) {
            unset($form['btns']['reset']);
        }

        $yn = [1 => __('Yes'), 0 => __('No')];

        $form['sets']['structure'] = [
            'class'  => 'structure',
//            'legend' => ,
            'fields' => [
                'tag' => [
                    'type'      => $id > 0 ? 'str' : 'text',
                    'value'     => $structure->tag,
                    'caption'   => __('Tag label'),
                    'info'      => __('Tag info'),
                    'maxlength' => '11',
                    'pattern'   => '^[a-z\*][a-z\d-]{0,10}$',
                    'required'  => true,
                ],
                'type' => [
                    'type'      => 'select',
                    'options'   => $this->bbTypes,
                    'value'     => $structure->type,
                    'caption'   => __('Type label'),
                    'info'      => __('Type info'),
                ],
                'type_new' => [
                    'type'      => 'text',
                    'value'     => isset($this->bbTypes[$structure->type]) ? '' : $structure->type,
                    'caption'   => __('Type label'),
                    'info'      => __('New type info'),
                    'maxlength' => '20',
                    'pattern'   => '^[a-z][a-z\d-]{0,19}$',
                ],
                'parents' => [
                    'type'      => 'multiselect',
                    'options'   => $this->bbTypes,
                    'value'     => $structure->parents,
                    'caption'   => __('Parents label'),
                    'info'      => __('Parents info'),
                    'size'      => \min(15, \count($this->bbTypes)),
                    'required'  => true,
                ],
                'handler' => [
                    'class'     => 'handler',
                    'type'      => 'textarea',
                    'value'     => $structure->handler,
                    'caption'   => __('Handler label'),
                    'info'      => __('Handler info'),
                ],
                'text_handler' => [
                    'class'     => 'handler',
                    'type'      => 'textarea',
                    'value'     => $structure->text_handler,
                    'caption'   => __('Text handler label'),
                    'info'      => __('Text handler info'),
                ],
                'recursive' => [
                    'type'    => 'radio',
                    'value'   => true === $structure->recursive ? 1 : 0,
                    'values'  => $yn,
                    'caption' => __('Recursive label'),
                    'info'    => __('Recursive info'),
                ],
                'text_only' => [
                    'type'    => 'radio',
                    'value'   => true === $structure->text_only ? 1 : 0,
                    'values'  => $yn,
                    'caption' => __('Text only label'),
                    'info'    => __('Text only info'),
                ],
                'tags_only' => [
                    'type'    => 'radio',
                    'value'   => true === $structure->tags_only ? 1 : 0,
                    'values'  => $yn,
                    'caption' => __('Tags only label'),
                    'info'    => __('Tags only info'),
                ],
                'pre' => [
                    'type'    => 'radio',
                    'value'   => true === $structure->pre ? 1 : 0,
                    'values'  => $yn,
                    'caption' => __('Pre label'),
                    'info'    => __('Pre info'),
                ],
                'single' => [
                    'type'    => 'radio',
                    'value'   => true === $structure->single ? 1 : 0,
                    'values'  => $yn,
                    'caption' => __('Single label'),
                    'info'    => __('Single info'),
                ],
                'auto' => [
                    'type'    => 'radio',
                    'value'   => true === $structure->auto ? 1 : 0,
                    'values'  => $yn,
                    'caption' => __('Auto label'),
                    'info'    => __('Auto info'),
                ],
                'self_nesting' => [
                    'type'    => 'number',
                    'value'   => $structure->self_nesting > 0 ? $structure->self_nesting : 0,
                    'min'     => '0',
                    'max'     => '10',
                    'caption' => __('Self nesting label'),
                    'info'    => __('Self nesting info'),
                ],
            ],
        ];

        $tagStr = $id > 0 ? $structure->tag : 'TAG';

        $form['sets']['no_attr'] = $this->formEditSub(
            $structure->no_attr,
            'no_attr',
            'no_attr',
            __('No attr subhead', $tagStr),
            __('Allowed no_attr info')
        );

        $form['sets']['def_attr'] = $this->formEditSub(
            $structure->def_attr,
            'def_attr',
            'def_attr',
            __('Def attr subhead', $tagStr),
            __('Allowed def_attr info')
        );

        foreach ($structure->other_attrs as $name => $attr) {
            $form['sets']["{$name}_attr"] = $this->formEditSub(
                $attr,
                $name,
                "{$name}_attr",
                __('Other attr subhead', $tagStr, $name),
                __('Allowed %s attr info', $name)
            );
        }

        $form['sets']['new_attr'] = $this->formEditSub(
            $structure->new_attr,
            'new_attr',
            'new_attr',
            __('New attr subhead'),
            __('Allowed new_attr info')
        );

        return $form;
    }

    /**
     * Формирует данные для формы
     */
    protected function formEditSub(/* mixed */ $data, string $name, string $class, string $legend, string $info): array
    {
        $yn     = [1 => __('Yes'), 0 => __('No')];
        $fields = [];
        $other  = '_attr' !== \substr($name, -5);
        $key    = $other ? "other_attrs[{$name}]" : $name;

        if ('new_attr' === $name) {
            $fields["{$key}[name]"] = [
                'type'      => 'text',
                'value'     => $data['name'] ?? '',
                'caption'   => __('Attribute name label'),
                'info'      => __('Attribute name info'),
                'maxlength' => '15',
                'pattern'   => '^[a-z-]{2,15}$',
            ];
        }

        $fields["{$key}[allowed]"] = [
            'type'    => 'radio',
            'value'   => null === $data ? 0 : 1,
            'values'  => $yn,
            'caption' => __('Allowed label'),
            'info'    => $info,
        ];
        if ('no_attr' !== $name) {
            $fields["{$key}[required]"] = [
                'type'    => 'radio',
                'value'   => empty($data['required']) ? 0 : 1,
                'values'  => $yn,
                'caption' => __('Required label'),
                'info'    => __('Required info'),
            ];
            $fields["{$key}[format]"] = [
                'class'     => 'format',
                'type'      => 'text',
                'value'     => $data['format'] ?? '',
                'caption'   => __('Format label'),
                'info'      => __('Format info'),
            ];
        }
        $fields["{$key}[body_format]"] = [
            'class'     => 'format',
            'type'      => 'text',
            'value'     => $data['body_format'] ?? '',
            'caption'   => __('Body format label'),
            'info'      => __('Body format info'),
        ];
        $fields["{$key}[text_only]"] = [
            'type'    => 'radio',
            'value'   => empty($data['text_only']) ? 0 : 1,
            'values'  => $yn,
            'caption' => __('Text only label'),
            'info'    => __('Text only info'),
        ];

        return [
            'class'  => ['attr', $class],
            'legend' => $legend,
            'fields' => $fields,
        ];
    }

    /**
     * Устанавливает структуру bb-кода по умолчанию
     */
    public function default(array $args, string $method): Page
    {
        if (! $this->c->Csrf->verify($args['token'], 'AdminBBCodeDefault', $args)) {
            return $this->c->Message->message($this->c->Csrf->getError());
        }

        $id = (int) $args['id'];

        $structure = $this->c->BBStructure
            ->fromString($this->c->bbcode->load()->bbcodeTable[$id]['bb_structure'])
            ->setDefault();

        $this->c->bbcode->update($id, $structure);

        return $this->c->Redirect->page('AdminBBCodeEdit', ['id' => $id])->message('BBCode updated redirect');
    }
}
