<?php
class FileProcessor
{
    public function processFiles(&$data, $srcField, $dstField, $dir)
    {
        $fileCreated = false;
        if (isset($data[$srcField]) && is_array($data[$srcField]) && !empty($data[$srcField]['tmp_name']) && is_file($data[$srcField]['tmp_name']))
        {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            if (!empty($data[$dstField]) && is_file(WWW_ROOT.$data[$dstField]))	{
                unlink(WWW_ROOT.$data[$dstField]);
                $source = str_replace('_resized', '', WWW_ROOT.$data[$dstField]);

                if (is_file($source)) {
                    unlink($source);
                }
            }

            $file = new File(strtolower($data[$srcField]['name']));
            $urlPart = $dir.'/'.time().'_'.rand(0, 999).'.'.$file->ext();
            copy($data[$srcField]['tmp_name'], WWW_ROOT.$urlPart);
            $data[$dstField] = $urlPart;
            $fileCreated = true;
        }
        else
        {
            if (is_array($data[$dstField]))
            {
                $data[$dstField] = '';
            }
        }
        return $fileCreated;
    }

    public function deleteTMP(&$data, $srcField)
    {
        @unlink($data[$srcField]['tmp_name']);
    }
}
