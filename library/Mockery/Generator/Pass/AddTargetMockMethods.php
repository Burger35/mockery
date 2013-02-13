<?php

namespace Mockery\Generator\Pass;

use Mockery\Generator\MockConfiguration;

class AddTargetMockMethods 
{
    public function __construct(\PHPParser_Template $template = null, \PHPParser_BuilderFactory $factory = null)
    {
        $this->template = $template;
        $this->factory = $factory ?: new \PHPParser_BuilderFactory;
    }

    public function execute(MockConfiguration $config, \PHPParser_Builder_Class $mock)
    {
        foreach ($config->getMethodsToMock() as $method)
        {
            $builder = $method->build($this->factory);

            if (!$method->isConstructor()) {
                $builder->addStmts($this->getTemplate()->getStmts(array('name' => $method->getName())));
            }

            $mock->addStmt($builder);
        }
    }

    protected function getTemplate()
    {
        if ($this->template) {
            return $this->template;
        }

        $parser = new \PHPParser_Parser(new \PHPParser_Lexer);

        return $this->template = new \PHPParser_Template($parser, file_get_contents(__DIR__.'/templates/method.php'));
    }
}
