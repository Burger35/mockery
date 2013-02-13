<?php

namespace Mockery\Generator;

class Generator 
{
    public function generate(MockConfiguration $config)
    {
        $factory = new \PHPParser_BuilderFactory;


        $parts = explode('\\', $config->getName());
        $class = array_pop($parts);
        
        $mock = $factory->class($class);

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

        $addBaseMockDefinition = new Pass\AddBaseMockDefinition;
        $addBaseMockDefinition->execute($config, $mock);

        if ($config->isInstanceMock()) {
            $addInstanceMockDefinition = new Pass\AddInstanceMockDefinition;
            $addInstanceMockDefinition->execute($config, $mock);
        }

        $addTargetMockMethods = new Pass\AddTargetMockMethods;
        $addTargetMockMethods->execute($config, $mock);

        $prettyPrinter = new \PHPParser_PrettyPrinter_Default;

        $mock = $mock->getNode();

        if (count($parts)) {
            $name = implode("\\", $parts);
            $mock = new \PHPParser_Node_Stmt_Namespace(new \PHPParser_Node_Name($name), array($mock));
        }

        /* echo "\n============================================================\n"; */
        /* echo($prettyPrinter->prettyPrint(array($mock))); */
        /* echo "\n============================================================\n"; */
        file_put_contents("/tmp/last_mock.php", "<?php " . $prettyPrinter->prettyPrint(array($mock)));
        eval($prettyPrinter->prettyPrint(array($mock)));
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
