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

        $addClassDefinition = new Pass\AddClassDefinition;
        $addClassDefinition->execute($config, $mock);

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

}
