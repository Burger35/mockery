<?php
$stack = debug_backtrace();
$args = array();
if (isset($stack[0]['args'])) {
    for($i=0; $i<count($stack[0]['args']); $i++) {
        $args[$i] =& $stack[0]['args'][$i];
    }
}
return $this->__call('__name__', $args);
