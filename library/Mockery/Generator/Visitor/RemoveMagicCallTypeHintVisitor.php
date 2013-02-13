<?php

namespace Mockery\Generator\Visitor;

class RemoveMagicCallTypeHintVisitor extends \PHPParser_NodeVisitorAbstract
{
    public function leaveNode(\PHPParser_Node $node) {
        if ($node instanceof PHPParser_Node_Stmt_ClassMethod && $node->name == "__call") {
            $params = $node->params;
            $params[1]->type = null;
        }

    }
}
