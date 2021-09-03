<?php

namespace Tiny\Anodoc\Tags;

class NullTag extends Tag
{

    public function __construct($tag_name, $value)
    {
    }

    /**
     *
     */
    public function getValue()
    {
        return '';
    }

    public function getKey(){
        return null;
    }

    public function getTagName()
    {
    }

    public function __toString()
    {
        return __CLASS__;
    }

    public function isNull()
    {
        return true;
    }
}