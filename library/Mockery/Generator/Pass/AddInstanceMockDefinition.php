<?php

namespace Mockery\Generator\Pass;

use Mockery\Generator\MockConfiguration;

class AddInstanceMockDefinition
{
    public function __construct(\PHPParser_Template $template = null)
    {
        $this->template = $template;
    }

    public function execute(MockConfiguration $config, \PHPParser_Builder_Class $mock)
    {
        foreach ($config->getMethodsToMock() as $method) {
            $stmts = $this->getTemplate()->getStmts();
            $mock->addStmts($stmts);
        }
    }

    protected function getTemplate()
    {
        if ($this->template) {
            return $this->template;
        }

        $parser = new \PHPParser_Parser(new \PHPParser_Lexer);

        return $this->template = new \PHPParser_Template($parser, file_get_contents(__DIR__.'/templates/instance_mock.php'));
    }
}
