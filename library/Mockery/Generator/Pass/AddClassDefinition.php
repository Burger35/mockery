<?php

namespace Mockery\Generator\Pass;

use Mockery\Generator\MockConfiguration;

/**
 * This pass deals with adding a class to extend and any interfaces that need 
 * implementing.
 *
 * It will also declare any classes/interfaces that are missing
 */
class AddClassDefinition 
{
    public function execute(MockConfiguration $config, \PHPParser_Builder_Class $mock)
    {
        if ($config->getTargetClass() && !$config->isTargetClassFinal()) {
            $this->declareClass($config->getTargetClass());
            $mock->extend($config->getTargetClass());
        }

        if ($config->getTargetInterfaces()) {
            foreach ($config->getTargetInterfaces() as $interface) {
                $this->declareInterface($interface);
                $mock->implement($interface);
            }
        }
    }

    /**
     * Takes a class name and declares it
     *
     * @param string $fqcn
     */
    protected function declareClass($fqcn)
    {
        if (class_exists($fqcn)) {
            return;
        }

        if (false !== strpos($fqcn, "\\")) {
            $parts = array_filter(explode("\\", $fqcn), function($part) {
                return $part !== "";
            });
            $cl = array_pop($parts);
            $ns = implode("\\", $parts);
            eval(" namespace $ns { class $cl {} }");
        } else {
            eval(" class $fqcn {} ");
        }
    }

    /**
     * Takes an interface name and declares it
     *
     * @param string $fqcn
     */
    protected function declareInterface($fqcn)
    {
        if (interface_exists($fqcn)) {
            return;
        }

        if (false !== strpos($fqcn, "\\")) {
            $parts = array_filter(explode("\\", $fqcn), function($part) {
                return $part !== "";
            });
            $cl = array_pop($parts);
            $ns = implode("\\", $parts);
            eval(" namespace $ns { interface $cl {} }");
        } else {
            eval(" interface $fqcn {} ");
        }
    }
}
