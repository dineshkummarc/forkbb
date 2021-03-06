<?php

declare(strict_types=1);

namespace ForkBB\Models\Pages;

use ForkBB\Models\Page;
use function \ForkBB\__;

class Message extends Page
{
    /**
     * Подготавливает данные для шаблона
     */
    public function message(string $message, bool $back = true, int $status = 404, array $headers = []): Page
    {
        $this->nameTpl    = 'message';
        $this->httpStatus = \max(200, $status);
        $this->titles     = __('Info');
        $this->back       = $back;

        if (! empty($headers)) {
            foreach ($headers as $header) {
                $this->header($header[0], $header[1], $header[2] ?? true);
            }
        }

        if ($status < 200) {
            $type = 'i';
        } elseif ($status < 300) {
            $type = 's';
        } elseif ($status < 400) {
            $type = 'w';
        } else {
            $type = 'e';
        }
        $this->fIswev = [$type, __($message)];

        return $this;
    }
}
