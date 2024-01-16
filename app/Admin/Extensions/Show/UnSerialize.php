<?php

namespace App\Admin\Extensions\Show;

use Dcat\Admin\Show\AbstractField;

class UnSerialize extends AbstractField
{
    // 这个属性设置为false则不会转义HTML代码
    public $escape = true;

    public function render($arg = '')
    {
        // 返回任意可被渲染的内容
        return unserialize($this->value);
    }
}