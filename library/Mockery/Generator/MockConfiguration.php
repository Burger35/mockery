<?php

namespace Mockery\Generator;

/**
 * This class describes the configuration of mocks and hides away some of the 
 * reflection implementation
 */
class MockConfiguration 
{
    /**
     * A class that we'd like to mock
     */
    protected $targetClass;

    /**
     * A number of interfaces we'd like to mock
     */
    protected $targetInterfaces = array();

    /**
     * An object we'd like our mock to proxy to
     */
    protected $targetObject;

    /**
     * The class name we'd like to use for a generated mock
     */
    protected $name; 

    /**
     * Methods that should specifically not be mocked
     *
     * This is currently populated with stuff we don't know how to deal with, 
     * should really be somewhere else
     */
    protected $blackListedMethods = array(
        '__call',
        '__clone',
        '__wakeup',
        '__set',
        '__get',
        '__toString',
        '__isset',
        '__destruct',

        // below are reserved words in PHP
        "__halt_compiler", "abstract", "and", "array", "as",
        "break", "callable", "case", "catch", "class",
        "clone", "const", "continue", "declare", "default",
        "die", "do", "echo", "else", "elseif",
        "empty", "enddeclare", "endfor", "endforeach", "endif",
        "endswitch", "endwhile", "eval", "exit", "extends",
        "final", "for", "foreach", "function", "global",
        "goto", "if", "implements", "include", "include_once",
        "instanceof", "insteadof", "interface", "isset", "list",
        "namespace", "new", "or", "print", "private",
        "protected", "public", "require", "require_once", "return",
        "static", "switch", "throw", "trait", "try",
        "unset", "use", "var", "while", "xor"
    );

    /**
     * If not empty, only these methods will be mocked
     */
    protected $whiteListedMethods = array();

    /**
     * An instance mock is where we override the original class before it's 
     * autoloaded
     */
    protected $instanceMock = false;

    /**
     * Gets a list of methods from the classes, interfaces and objects and 
     * filters them appropriately. Lot's of filtering going on, perhaps we could 
     * have filter classes to iterate through
     */
    public function getMethodsToMock()
    {
        $methods = $this->getAllMethods();

        foreach ($methods as $key => $method) {
            if ($method->isFinal()) {
                unset($methods[$key]);
            }
        }

        /**
         * Whitelist trumps blacklist
         */
        if (count($this->getWhiteListedMethods())) {
            $whitelist = $this->getWhiteListedMethods();
            $methods = array_filter($methods, function($method) use ($whitelist) {
                return in_array($method->getName(), $whitelist);
            });

            return $methods;
        }

        /**
         * Remove blacklisted methods
         */
        if (count($this->getBlackListedMethods())) {
            $blacklist = $this->getBlackListedMethods();
            $methods = array_filter($methods, function ($method) use ($blacklist) {
                return !in_array($method->getName(), $blacklist);
            });
        }

        return $methods;
    }

    public function isTargetClassFinal()
    {
        if (!$this->getTargetClass()) {
            return false;
        }

        if (!class_exists($this->getTargetClass())) {
            return false;
        }

        $rfc = new \ReflectionClass($this->getTargetClass());

        return $rfc->isFinal();
    }

    /**
     * We declare the __call method to handle undefined stuff, if the class 
     * we're mocking has also defined it, we need to comply with their interface
     */
    public function requiresCallTypeHintRemoval()
    {
        foreach ($this->getAllMethods() as $method) {
            if ("__call" === $method->getName()) {
                $params = $method->getParameters();
                return !$params[1]->isArray();
            }
        }

        return false;
    }

    /**
     * We declare the __callStatic method to handle undefined stuff, if the class 
     * we're mocking has also defined it, we need to comply with their interface
     */
    public function requiresCallStaticTypeHintRemoval()
    {
        foreach ($this->getAllMethods() as $method) {
            if ("__callStatic" === $method->getName()) {
                $params = $method->getParameters();
                return !$params[1]->isArray();
            }
        }

        return false;
    }


    public function addTarget($target)
    {
        if (is_object($target)) {
            $this->setTargetObject($target);
            $this->setTargetClass(get_class($target));
            return $this;
        }

        if ($target[0] !== "\\") {
            $target = "\\" . $target;
        }

        if (class_exists($target)) {
            $this->setTargetClass($target);
            return $this;
        }

        if (interface_exists($target)) {
            $this->addTargetInterface($target);
            return $this;
        }

        /**
         * Default is to set as class, or interface if class already set
         */
        if ($this->getTargetClass()) {
            $this->addTargetInterface($target);
            return $this;
        } 

        $this->setTargetClass($target);
    }

    public function addTargets($interfaces)
    {
        foreach ($interfaces as $interface) {
            $this->addTarget($interface);
        }
    }

    public function getTargetClass()
    {
        return $this->targetClass;
    }

    public function getTargetInterfaces()
    {
        $this->targetInterfaces = array_unique($this->targetInterfaces); // just in case
        return $this->targetInterfaces;
    }

    public function getTargetObject()
    {
        return $this->targetObject;
    }

    public function getName()
    {
        if ($this->name) {
            return $this->name;
        }

        return $this->name = uniqid('Mockery_');
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getBlackListedMethods()
    {
        return $this->blackListedMethods;
    }

    public function addBlackListedMethod($blackListedMethod)
    {
        $this->blackListedMethods[] = $blackListedMethod;
        return $this;
    }

    public function addBlackListedMethods(array $blackListedMethods)
    {
        foreach ($blackListedMethods as $method) {
            $this->addBlackListedMethod($method);
        }
        return $this;
    }

    public function setBlackListedMethods(array $blackListedMethods)
    {
        $this->blackListedMethods = $blackListedMethods;
        return $this;
    }

    public function getWhiteListedMethods()
    {
        return $this->whiteListedMethods;
    }

    public function addWhiteListedMethod($whiteListedMethod)
    {
        $this->whiteListedMethods[] = $whiteListedMethod;
        return $this;
    }

    public function addWhiteListedMethods(array $whiteListedMethods)
    {
        foreach ($whiteListedMethods as $method) {
            $this->addWhiteListedMethod($method);
        }
        return $this;
    }

    public function setWhiteListedMethods(array $whiteListedMethods)
    {
        $this->whiteListedMethods = $whiteListedMethods;
        return $this;
    }

    public function setInstanceMock($instanceMock)
    {
        $this->instanceMock = (bool) $instanceMock;
    }

    public function isInstanceMock()
    {
        return $this->instanceMock;
    }

    public function getConstructorArgs()
    {
        return $this->constructorArgs;
    }

    public function setConstructorArgs(array $args = null)
    {
        $this->constructorArgs = $args;
        return $this;
    }
    protected function setTargetClass($targetClass)
    {
        $this->targetClass = $targetClass;
        return $this;
    } 

    protected function getAllMethods()
    {
        $toReflect = $this->getTargetInterfaces();

        if ($this->getTargetClass()) {
            $toReflect[] = $this->getTargetClass();
        }

        $methods = array();
        foreach ($toReflect as $thing)
        {
            $class = new \ReflectionClass($thing);
            $methods = array_merge($methods, $class->getMethods());
        }

        $methods = array_map(function ($method) {
            return new Method($method);
        }, $methods);

        return $methods;
    }

    /**
     * If we attempt to implement Traversable, we must ensure we are also 
     * implementing either Iterator or IteratorAggregate, and that whichever one 
     * it is comes before Traversable in the list of implements.
     */
    protected function addTargetInterface($targetInterface)
    {
        $rfc = new \ReflectionClass($targetInterface);
        $extendedInterfaces = array_keys($rfc->getInterfaces());
        $extendedInterfaces[] = $targetInterface;

        $traversableFound = false;
        $iteratorShiftedToFront = false;
        foreach ($extendedInterfaces as $interface) {

            if (!$traversableFound && preg_match("/^\\?Iterator(|Aggregate)$/i", $interface)) {
                break;
            }

            if (preg_match("/^\\\\?IteratorAggregate$/i", $interface)) {
                $this->targetInterfaces[] = "\\IteratorAggregate";
                $iteratorShiftedToFront = true;
            } else if (preg_match("/^\\\\?Iterator$/i", $interface)) {
                $this->targetInterfaces[] = "\\Iterator";
                $iteratorShiftedToFront = true;
            } else if (preg_match("/^\\\\?Traversable$/i", $interface)) {
                $traversableFound = true;
            }
        }

        if ($traversableFound && !$iteratorShiftedToFront) {
            $this->targetInterfaces[] = "\\IteratorAggregate";
        }

        /**
         * We never straight up implement Traversable
         */
        if (!preg_match("/^\\\\?Traversable$/i", $targetInterface)) {
            $this->targetInterfaces[] = $targetInterface;
        }

        return $this;
    }

    protected function setTargetObject($object)
    {
        $this->targetObject = $object;
    }

}
