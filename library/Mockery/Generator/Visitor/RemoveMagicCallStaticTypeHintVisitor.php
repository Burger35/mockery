<?php

namespace Mockery\Generator\Visitor;

class RemoveMagicCallStaticTypeHintVisitor extends \PHPParser_NodeVisitorAbstract
{
    public function leaveNode(\PHPParser_Node $node) {
        if ($node instanceof PHPParser_Node_Stmt_ClassMethod && $node->name == "__callStatic") {
            $params = $node->params;
            $params[1]->type = null;
        }

    }
}
