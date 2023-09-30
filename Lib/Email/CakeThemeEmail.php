<?php

App::uses('CakeEmail', 'Network/Email');

class CakeThemeEmail extends CakeEmail{

    public function send($content = null)
    {
        if($this->_theme == null){
            $theme = Configure::read('Site.theme');
            if($theme != 'Default'){
                $this->_theme = $theme;
            }
        }
        return parent::send($content);
    }

    public function getDefaultFrom(){
        $from = Configure::read('Site.emailFrom');
        $from = $from ? trim($from) : Configure::read('Site.email');
        return $from;
    }
}
