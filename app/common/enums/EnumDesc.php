<?php

namespace app\common\enums;

#[\Attribute]
class EnumDesc {
    public function __construct(public string $desc) {}
}