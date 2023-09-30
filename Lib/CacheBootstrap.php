<?php
/**
 * Created by PhpStorm.
 * User: k1785
 * Date: 10.01.2018
 * Time: 9:53
 */

class CacheBootstrap
{

    public function bootstrap($plugins = null){
        if(! $plugins){
            $pluginBootstraps = Configure::read('Hook.bootstraps');
            $plugins = array_filter(explode(',', $pluginBootstraps));
        }
        $file = $this->file($plugins);
        $out = '';
        foreach ($plugins as $plugin){
            $pluginPath = CakePlugin::path($plugin);
            $bootstrapFile = $pluginPath.'Config'.DS.'bootstrap.php';
            if(file_exists($bootstrapFile)){
                $content = file_get_contents($bootstrapFile);
                $content = str_replace(['<?php', '?>'], '', $content);
                $content = str_replace("require_once('", "require_once('".$pluginPath.'Config'.DS, $content);
                $content = str_replace("require_once ('", "require_once('".$pluginPath.'Config'.DS, $content);
                $out .= $content;
            }
        }
        $out = '<?php '.$out;
        $this->writeBootstrap($file, $out);
        return $file;
    }

    public function file(array $plugins = []){
        if(! $plugins){
            $pluginBootstraps = Configure::read('Hook.bootstraps');
            $plugins = array_filter(explode(',', $pluginBootstraps));
        }
        $name = 'bootstrap_'.md5(implode('_', $plugins));
        $cachePath = $this->getCachePath();
        return $cachePath. $name.'.php';
    }

    public function writeBootstrap($fileBootstrap = null,$content = null){
        App::uses('Folder', 'Utility');
        $dir = new Folder();
        $cachePath = $this->getCachePath();
        $dir->create($cachePath, 0775);
        /*$files = $dir->find('*');
        foreach ($files as $file){
            unlink($cachePath.$file);
        }*/

        file_put_contents($fileBootstrap,$content);
    }

    public function getCachePath(){
        return APP.'tmp'.DS.'bootstrap'.DS;
    }
}