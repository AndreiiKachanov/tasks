<?php

if (!function_exists('dd')) {
    function dd(...$args): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        displayArguments($trace, $args);
        die;
    }
}

if (!function_exists('dump')) {
    function dump(...$args): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        displayArguments($trace, $args);
    }
}

if (!function_exists('displayArguments')) {
    function displayArguments($trace, $args): void
    {
        $line = $trace[0]['line'];
        $file = $trace[0]['file'];

        echo '<pre>';
        echo "$file on line $line:\n";
        foreach ($args as $var) {
            print_r($var);
        }
        echo '</pre>';
    }
}

if (!function_exists('sanitizeHTML')) {
    function sanitizeHTML(?string $input, array $allowedTags = ['b', 'p', 'u', 'em', 'strong']): string
    {
        $pattern = '/<([^\/>]+)[^>]*>(.*?)<\/\1>|<([^\/>]+)[^>]*\/?>/';

        return preg_replace_callback($pattern, function ($matches) use ($allowedTags) {
            if (isset($matches[3]) && in_array($matches[3], $allowedTags)) {
                // Opening tag is allowed, return it unchanged
                return $matches[0];
            } elseif (isset($matches[1]) && in_array($matches[1], $allowedTags)) {
                // Opening and closing tags are allowed, return them unchanged
                return $matches[0];
            } else {
                // Convert content inside the tag using htmlspecialchars
                return htmlentities($matches[0]);
            }
        }, $input);
    }

}

if (!function_exists('generateSortLink')) {
    function generateSortLink(array $navParams, string $field, string $label, string $currentField, string $currentOrder): string
    {
        $urlSelf = $navParams['url_self'];
        $pageNum = $navParams['page_num'];
        $order = ($currentField === $field && $currentOrder === 'asc') ? 'desc' : 'asc';
        $caret = ($currentField === $field) ? ($currentOrder === 'asc' ? 'up' : 'down') : 'sort';
        $iconClass = "fas fa-caret-$caret";

        $url = $urlSelf . $pageNum . '?sort=' . $field . '&order=' . $order;

        $link = "<a href=\"$url\">$label";
        $link .= ($currentField === $field) ? " <i class=\"$iconClass\"></i>" : " <i class=\"fas fa-sort\"></i>";
        $link .= "</a>";

        return $link;
    }
}

if (!function_exists('randomStr')) {
    function randomStr(): string
    {
        $s = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_";
        return substr(str_shuffle(str_repeat($s, ceil(10 / strlen($s)))), 1, 10);
    }
}

if (!function_exists('getFileExtension')) {
    function getFileExtension(string $file): string
    {
        $tmp = explode('.', $file);
        return strtolower($tmp[count($tmp) - 1]);
    }

}if (!function_exists('deserializeIpInfo')) {
    function deserializeIpInfo(array $arr): array
    {
        return array_map(function($item) {
            if (isset($item['ip_info']) && !empty($item['ip_info'])) {
                $item['ip_info'] = unserialize($item['ip_info']);
            }
            return $item;
        }, $arr);
    }
}

