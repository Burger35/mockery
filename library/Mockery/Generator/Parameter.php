<?php

namespace Mockery\Generator;

class Parameter 
{
    
    protected $reflectionParameter;

    public function __construct(\ReflectionParameter $reflectionParameter)
    {
        $this->reflectionParameter = $reflectionParameter;  
    }

    public function getTypeHint()
    {
        if ($this->reflectionParameter->isArray()) {
            return 'array';
        }

        if ($this->reflectionParameter->getClass()) {
            return $this->reflectionParameter->getClass()->getName();
        }

        if (preg_match('/^Parameter #[0-9]+ \[ \<(required|optional)\> (?<typehint>\S+ )?.*\$' . $this->reflectionParameter->getName() . ' .*\]$/', $this->reflectionParameter->__toString(), $typehintMatch)) {
            if (!empty($typehintMatch['typehint'])) {
                return $typehintMatch['typehint'];
            }
        }

        return null;
    }

    public function getName()
    {
        return $this->reflectionParameter->name;
    }

    public function __call($name, array $args)
    {
        return call_user_func_array(array($this->reflectionParameter, $name), $args);
    }

    public function build(\PHPParser_BuilderFactory $factory)
    {
        $builder = $factory->param($this->reflectionParameter->name);
        $builder->setTypeHint($this->getTypeHint());

        // todo the rest
        if ($this->reflectionParameter->isDefaultValueAvailable()) {
            $builder->setDefault($this->reflectionParameter->getDefaultValue());
        }
    
        if ($this->reflectionParameter->isPassedByReference()) {
            $builder->makeByRef();
        }
        
        return $builder;
    }
}
