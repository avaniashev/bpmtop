<?php

class PlaceholderFiller
{
    public function fill($template, array $current)
    {
        $matches = [];
        if (preg_match_all('!\\{([^\\}]+)\\}!', $template, $matches)) {
//            debug($matches);
//            die();
            foreach ($matches[1] as $i => $match) {
                $value = '';
                if ($match[0] == '$') {
                    $path = substr($match, 1);
                    $data = Hash::get($current, $path);
                    $value = !empty($data) ? '$' . number_format($data) : '';
                } elseif ($match[0] == '!') {
                    $data = Hash::get($current, substr($match, 1));
                    $value = !empty($value) ? date('m/d/Y', strtotime($data)) : '';
                } else {
                    $value = Hash::get($current, $match);
                }
//                    debug($value);
                $template = str_replace($matches[0][$i], htmlspecialchars($value), $template);
            }
        }
        return $template;
    }
}