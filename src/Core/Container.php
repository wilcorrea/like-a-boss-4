<?php

namespace Hero\Core;

/**
 * Class Container
 * @package Hero\App
 */
class Container
{
    /**
     * @var Container Instance of Container container
     */
    protected static $instance;

    /**
     * @var array List of IoC Bindings, empty array for default
     */
    protected $bindings = [];

    /**
     * Container constructor.
     *
     * Constructor is protected so people can never
     * do "new Container()"
     */
    protected function __construct()
    {
        //
    }

    /**
     * @return Container Current Container container instance
     */
    public static function getInstance()
    {
        // if there is not a instance yet, create a new one
        if (null === self::$instance) {
            self::$instance = new self();
        }

        // return the new or already existing instance
        return self::$instance;
    }

    /**
     * Register a class or alias into the Container.
     *
     * @param $alias string Interface/class/alias register
     * @param $implementation mixed current implementation
     */
    public function register($alias, $implementation)
    {
        $this->bindings[$alias] = $implementation;
    }

    /**
     * UnRegister a Interface/Class/Alias.
     *
     * @param $aliasOrClassName
     */
    public function unRegister($aliasOrClassName)
    {
        if (array_key_exists($aliasOrClassName, $this->bindings)) {
            unset($this->bindings[$aliasOrClassName]);
        }
    }

    /**
     * Resolves and created a new instance of a desired class.
     *
     * @param $alias
     * @return mixed
     */
    public function make($alias)
    {
        if (class_exists($alias)) {
            return $this->makeInstance($alias);
        }

        if (array_key_exists($alias, $this->bindings)) {
            $classOrObject = $this->bindings[$alias];

            if (is_object($classOrObject)) {
                return $classOrObject;
            }

            return $this->makeInstance($classOrObject);
        }

        return null;
    }

    /**
     * Created a instance of a desired class.
     *
     * @param $className
     * @return mixed
     */
    protected function makeInstance($className)
    {
        // class reflection
        $reflection = new \ReflectionClass($className);
        // get the class constructor
        $constructor = $reflection->getConstructor();

        // if there is no constructor, just create and
        // return a new instance
        if (!$constructor) {
            return $reflection->newInstance();
        }

        // if there is parameters, get them!
        $constructorParameters = $constructor->getParameters();

        // resolved array of parameters
        $parametersToPass = [];

        // for each expected parameter,
        // go through the container and resolve it
        foreach ($constructorParameters as $parameter) {
            // get the expected class
            $parameterClassName = $parameter->getClass()->name;

            // if there is a class
            if ($parameterClassName) {
                // ask the container to resolve it
                $parametersToPass[] = self::make($parameterClassName);
            }
        }

        // created and returns the new instance passing the
        // resolved parameters
        return $reflection->newInstanceArgs($parametersToPass ? $parametersToPass : []);
    }
}