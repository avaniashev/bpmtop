<?php
/**
 * Created by PhpStorm.
 * User: k1785
 * Date: 20.07.2017
 * Time: 17:51
 */

class SearchViewTplHelper extends AppHelper{

    protected $_tpl = [
        'range' => '<div class=":div-class:">
                    :label:
                    <div class="range-wrapper">
                        :label-from:
                        :input-from:
                        :label-to:
                        :input-to:
                    </div>
                    </div>'
    ];

    public function setTpls($tpls = []){
        $this->_tpl = $tpls;
    }

    public function setTpl($alias, $html){
        $this->_tpl[$alias] = $html;
    }

    public function getTpl($alias){
        return ! empty($this->_tpl[$alias]) ? $this->_tpl[$alias] : null;
    }

    public function renderTpl($alias = null, array $data = []){
        $tpl = $this->getTpl($alias);
        if(! $tpl){
            return false;
        }
        $replacer = $replacerKeys = [];
        foreach ($data as $key => $value){
            $replacerKeys[] = ':'.$key.':';
            $replacer[] = $value;
        }
        $tpl = str_replace($replacerKeys, $replacer, $tpl);
        //clear empty
        return $tpl;
    }
}