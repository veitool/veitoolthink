<?php

use Symfony\Component\VarDumper\VarDumper;
use think\dumper\Dumper;

VarDumper::setHandler(function ($var, ?string $label = null) {
    Dumper::dump($var, $label);
});
