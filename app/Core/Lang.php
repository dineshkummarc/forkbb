<?php

declare(strict_types=1);

namespace ForkBB\Core;

use ForkBB\Core\Container;
use RuntimeException;

class Lang
{
    /**
     * Контейнер
     * @var Container
     */
    protected $c;

    /**
     * @var Psr\SimpleCache\CacheInterface
     */
    protected $cache;

    /**
     * Массив переводов
     * @var array
     */
    protected $tr = [];

    /**
     * Загруженные переводы
     * @var array
     */
    protected $loaded = [];

    /**
     * Порядок перебора языка
     * @var array
     */
    protected $langOrder = [];

    public function __construct(Container $container)
    {
        $this->c     = $container;
        $this->cache = $container->Cache;
    }

    /**
     * Ищет сообщение в загруженных переводах
     */
    public function get(string $message, string $lang = null) /* : string|array */
    {
        if (
            null !== $lang
            && isset($this->tr[$lang][$message])
        ) {
            return $this->tr[$lang][$message];
        }

        foreach ($this->langOrder as $lang) {
            if (isset($this->tr[$lang][$message])) {
                return $this->tr[$lang][$message];
            }
        }

        return $message;
    }

    /**
     * Загружает языковой файл
     */
    public function load(string $name, string $lang = null, string $path = null): void
    {
        if (null !== $lang) {
            // смена порядка перебора языка
            $this->langOrder = [$lang => $lang] + $this->langOrder;

            if (isset($this->loaded[$name][$lang])) {
                return;
            }
        } elseif (isset($this->loaded[$name])) {
            return;
        }

        $lang = $lang ?: $this->c->user->language;
        $path = $path ?: $this->c->DIR_LANG;

        do {
            $flag     = true;
            $fullPath = "{$path}/{$lang}/{$name}.po";

            if (\is_file($fullPath)) {
                $time  = \filemtime($fullPath);
                $key   = 'l_' . \sha1($fullPath);
                $cache = $this->cache->get($key);

                if (
                    isset($cache['time'], $cache['data'])
                    && $cache['time'] === $time
                ) {
                    $data = $cache['data'];
                } else {
                    $data = $this->arrayFromStr(\file_get_contents($fullPath));

                    $this->cache->set(
                        $key,
                        [
                            'time' => $time,
                            'data' => $data,
                        ]
                    );
                }

                if (isset($this->tr[$lang])) {
                    $this->tr[$lang] += $data;
                } else {
                    $this->tr[$lang]  = $data;
                }

                $this->loaded[$name][$lang] = true;
                // порядок перебора языка не изменяется
                $this->langOrder += [$lang => $lang];

                $flag = false;
            } elseif ('en' === $lang) {
                $flag = false;
            }

            $lang = 'en';
        } while ($flag);
    }

    /**
     * Получает массив перевода из строки (.po файла)
     */
    protected function arrayFromStr(string $str): array
    {
        $lines    = \explode("\n", $str);
        $count    = \count($lines);
        $result   = [];
        $cur      = [];
        $curComm  = null;
        $curVal   = '';
        $nplurals = 2;
        $plural   = '($n != 1);';

        for ($i = 0; $i < $count; ++$i) {
            $line = \trim($lines[$i]);

            // пустая строка
            if (! isset($line[0])) {
                // промежуточные данные
                if (isset($curComm)) {
                    $cur[$curComm] = $curVal;
                }

                // ошибка формата
                if (! isset($cur['msgid'])) {
                    throw new RuntimeException('File format error');
                }

                // заголовки
                if (! isset($cur['msgid'][0])) {
                    if (\preg_match('%Plural\-Forms:\s+nplurals=(\d+);\s*plural=([^;\n\r]+;)%i', $cur[0], $v)) {
                        $nplurals = (int) $v[1];
                        $plural   = \str_replace('n', '$n', \trim($v[2]));
                        $plural   = \str_replace(':', ': (', $plural, $curVal);
                        $plural   = \str_replace(';', \str_repeat(')', $curVal). ';', $plural);
                    }

                // перевод
                } else {
                    // множественный
                    if (
                        isset($cur['msgid_plural'][0])
                        || isset($cur[1][0])
                    ) {
                        if (! isset($cur[1][0])) {
                            $cur[1] = $cur['msgid_plural'];
                        }

                        if (! isset($cur[0][0])) {
                            $cur[0] = $cur['msgid'];
                        }

                        $curVal = [];
                        for ($v = 0; $v < $nplurals; ++$v) {
                            if (! isset($cur[$v][0])) {
                                $curVal = null;
                                break;
                            }
                            $curVal[$v] = $cur[$v];
                        }

                        if (isset($curVal)) {
                            $curVal['plural']      = $plural;
                            $result[$cur['msgid']] = $curVal;
                        }

                    // одиночный
                    } elseif (isset($cur[0])) { // [0]
                        $result[$cur['msgid']] = $cur[0];
                    }
                }

                $curComm = null;
                $curVal  = '';
                $cur     = [];
                continue;

            // комментарий
            } elseif ('#' == $line[0]) {
                continue;

            // многострочное содержимое
            } elseif ('"' == $line[0]) {
                if (isset($curComm)) {
                    $curVal .= $this->originalLine($line);
                }
                continue;

            // промежуточные данные
            } elseif (isset($curComm)) {
                $cur[$curComm] = $curVal;
            }

            // выделение команды
            $v       = \explode(' ', $line, 2);
            $command = $v[0];
            $v       = isset($v[1]) ? $this->originalLine(\trim($v[1])) : '';

            switch ($command) {
                case 'msgctxt':
                case 'msgid':
                case 'msgid_plural':
                    $curComm = $command;
                    $curVal  = $v;
                    break;

                case 'msgstr':
                case 'msgstr[0]':
                    $curComm = 0;
                    $curVal  = $v;
                    break;

                case 'msgstr[1]':
                    $curComm = 1;
                    $curVal  = $v;
                    break;

                case 'msgstr[2]':
                    $curComm = 2;
                    $curVal  = $v;
                    break;

                case 'msgstr[3]':
                    $curComm = 3;
                    $curVal  = $v;
                    break;

                case 'msgstr[4]':
                    $curComm = 4;
                    $curVal  = $v;
                    break;

                case 'msgstr[5]':
                    $curComm = 5;
                    $curVal  = $v;
                    break;

                default:
                    throw new RuntimeException('File format error');
            }
        }

        return $result;
    }

    /**
     * Получает оригинальную строку с удалением кавычек
     * и преобразованием спецсимволов
     */
    protected function originalLine(string $line): string
    {
        if (
            isset($line[1])
            && '"' == $line[0]
            && '"' == $line[\strlen($line) - 1]
        ) {
            $line = \substr($line, 1, -1);
        }

        return \str_replace(
            ['\\n', '\\t', '\\"', '\\\\'],
            ["\n",  "\t",  '"',  '\\'],
            $line
        );
    }
}
