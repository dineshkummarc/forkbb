<?php

namespace ForkBB\Models\BBCodeList;

use ForkBB\Models\Method;
use ForkBB\Models\BBCodeList\Model as BBCodeList;
use RuntimeException;

class Generate extends Method
{
    /**
     * Содержимое генерируемого файла
     * @var string
     */
    protected $file;

    /**
     * Создает файл с массивом сгенерированных bbcode
     */
    public function generate(): BBCodeList
    {
        $query = 'SELECT bb_structure
            FROM ::bbcode';

        $this->file = "<?php\n\nuse function \\ForkBB\\__;\n\nreturn [\n";

        $stmt = $this->c->DB->query($query);
        while ($row = $stmt->fetch()) {
            $this->file .= "    [\n"
                . $this->addArray(\json_decode($row['bb_structure'], true, 512, \JSON_THROW_ON_ERROR))
                . "    ],\n";
        }

        $this->file .= "];\n";

        if (false === \file_put_contents($this->model->fileCache, $this->file, \LOCK_EX)) {
            throw new RuntimeException('The generated bbcode file cannot be created');
        } else {
            return $this->model->invalidate();
        }
    }

    /**
     * Преобразует массив по аналогии с var_export()
     */
    protected function addArray(array $array, int $level = 0): string
    {
        $space  = \str_repeat('    ', $level + 2);
        $result = '';

        foreach ($array as $key => $value) {
            $type = \gettype($value);

            switch ($type) {
                case 'NULL':
                    $value = 'null';
                    break;
                case 'boolean':
                    $value = $value ? 'true' : 'false';
                    break;
                case 'array':
                    $value = "[\n" . $this->addArray($value, $level + 1) . "{$space}]";
                    break;
                case 'double':
                case 'integer':
                    break;
                case 'string':
                    if (
                        0 === $level
                        && (
                             'handler' === $key
                             || 'text handler' === $key
                        )
                    ) {
                        $value = "function(\$body, \$attrs, \$parser) {\n{$value}\n{$space}}";
                    } else {
                        $value = '\'' . \addslashes($value) . '\'';
                    }
                    break;
                default:
                    throw new RuntimeException("Invalid data type ({$type})");
                    break;
            }

            if (\is_string($key)) {
                $key = "'{$key}'";
            }

            $result .= "{$space}{$key} => {$value},\n";
        }

        return $result;
    }
}
