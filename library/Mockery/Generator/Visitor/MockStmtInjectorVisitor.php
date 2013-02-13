<?php

namespace Mockery\Generator\Visitor;

class MockStmtInjectorVisitor extends \PHPParser_NodeVisitorAbstract
{
    protected $target;

    public function __construct(\PHPParser_Builder_Class $target)
    {
        $this->target = $target;
    }

    public function leaveNode(\PHPParser_Node $node) {
        if ($node instanceof \PHPParser_Node_Stmt_Class && $node->name == "Mock") {
            $this->target->addStmts($node->stmts);
        }
    }
}
