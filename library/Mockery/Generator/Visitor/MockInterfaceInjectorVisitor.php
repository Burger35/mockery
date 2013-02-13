<?php

namespace Mockery\Generator\Visitor;

class MockInterfaceInjectorVisitor extends \PHPParser_NodeVisitorAbstract
{
    protected $target;

    public function __construct(\PHPParser_Builder_Class $target)
    {
        $this->target = $target;
    }

    public function leaveNode(\PHPParser_Node $node) {
        if ($node instanceof \PHPParser_Node_Stmt_Class && $node->name == "Mock") {
            foreach ($node->implements as $interface) {
                $this->target->implement($interface);
            }
        }
    }
}
