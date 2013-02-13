<?php

namespace Mockery\Generator;

/**
 * Not to sure where this belongs, but I wanted to hide away some of the 
 * reflection rubbish
 */
class Method
{
    protected $reflectionMethod;

    public function __construct(\ReflectionMethod $reflectionMethod)
    {
        $this->reflectionMethod = $reflectionMethod;  
    }

    public function getParameters()
    {
        return array_map(function ($param) {
            return new Parameter($param);
        }, $this->reflectionMethod->getParameters());
    }

    public function getName()
    {
        return $this->reflectionMethod->name;
    }

    public function __call($name, array $args)
    {
        return call_user_func_array(array($this->reflectionMethod, $name), $args);
    }


    public function build(\PHPParser_BuilderFactory $factory)
    {
        $builder = $factory->method($this->reflectionMethod->name);

        foreach ($this->getParameters() as $param) {
            $builder->addParam($param->build($factory));
        }

        // todo visiblity etc
        //
        if ($this->reflectionMethod->isStatic()) {
            $builder->makeStatic();
        }

        return $builder;
    }
}
