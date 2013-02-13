<?php

namespace Mockery\Generator\Pass;

use Mockery\Generator\MockConfiguration;

class AddBaseMockDefinition 
{
    public function __construct(\PHPParser_NodeTraverser $traverser = null, \PHPParser_Parser $parser = null, $path = null)
    {
        $this->traverser = $traverser ?: new \PHPParser_NodeTraverser;
        $this->path = $path ?: __DIR__.'/../../Mock.php';
        $this->parser = $parser ?: new \PHPParser_Parser(new \PHPParser_Lexer);
    }
    
    public function execute(MockConfiguration $config, \PHPParser_Builder_Class $mock)
    {
        /**
         * Should resolve all names for use, so we don't have to worry about 
         * namespaces etc
         */
        $this->traverser->addVisitor(new \PHPParser_NodeVisitor_NameResolver());

        if ($config->requiresCallTypeHintRemoval()) {
            $visitor = new \Mockery\Generator\Visitor\RemoveMagicCallTypeHintVisitor;
            $this->traverser->addVisitor($visitor);
        }

        if ($config->requiresCallStaticTypeHintRemoval()) {
            $visitor = new \Mockery\Generator\Visitor\RemoveMagicCallStaticTypeHintVisitor;
            $this->traverser->addVisitor($visitor);
        }

        $stmtInjector = new \Mockery\Generator\Visitor\MockStmtInjectorVisitor($mock);
        $this->traverser->addVisitor($stmtInjector);
        $interfaceInjector = new \Mockery\Generator\Visitor\MockInterfaceInjectorVisitor($mock);
        $this->traverser->addVisitor($interfaceInjector);
        $base = $this->parser->parse(file_get_contents($this->path));
        $this->traverser->traverse($base);
    }
}
