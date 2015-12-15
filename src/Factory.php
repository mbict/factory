<?php

namespace MBIct\Factory;

use Closure;
use Faker\Generator as Faker;
use MBIct\Factory\Exceptions\DefinitionAlreadyDefinedException;
use MBIct\Factory\Exceptions\DefinitionNotCallableException;
use MBIct\Factory\Exceptions\DefinitionNotFoundException;
use MBIct\Factory\Exceptions\DirectoryNotFoundException;
use MBIct\Factory\Exceptions\ModelNotFoundException;
use MBIct\Factory\Exceptions\SetterNotCallableException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class Factory
{
    /**
     * @var Closure[]
     */
    protected $definitions = [];

    /**
     * @var Faker
     */
    protected $faker;

    /**
     * @return Faker
     */
    public function getFaker()
    {
        return $this->faker;
    }

    /**
     * @param Faker $faker
     * @return Factory
     */
    public function setFaker(Faker $faker)
    {
        $this->faker = $faker;
        return $this;
    }

    /**
     * @param string $class
     * @param Closure $generatorDefinition
     * @return $this
     * @throws DefinitionAlreadyDefinedException
     */
    public function define($class, $generatorDefinition)
    {
        if (isset($this->definitions[$class])) {
            throw new DefinitionAlreadyDefinedException($class);
        }

        if (!is_callable($generatorDefinition)) {
            throw new DefinitionNotCallableException($class);
        }

        $this->definitions[$class] = $generatorDefinition;
        return $this;
    }

    /**
     * @param string $class
     * @param array $data
     * @return mixed
     * @throws DefinitionNotFoundException
     * @throws ModelNotFoundException
     */
    public function create($class, $data = [])
    {
        $definition = $this->getDefinition($class);

        if (!class_exists($class)) {
            throw new ModelNotFoundException($class);
        }

        $generatedData = call_user_func($definition, $this->faker, $data);
        $mergedData = array_merge($generatedData, $data);

        $model = new $class();
        foreach ($mergedData as $key => $value) {
            $callable = [$model, 'set' . ucfirst($key)];
            if ( !is_callable($callable) ) {
                throw new SetterNotCallableException($key);
            }
            call_user_func($callable, $value);
        }

        return $model;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function has($class)
    {
        return isset($this->definitions[$class]);
    }

    /**
     * @param string $paths
     * @return $this
     * @throws DirectoryNotFoundException
     */
    public function loadFactories($paths)
    {
        foreach ((array)$paths as $path) {
            if (!is_dir($path)) {
                throw new DirectoryNotFoundException($path);
            }
            $this->loadDirectory($path);
        }
        return $this;
    }

    /**
     * @param string $path
     */
    private function loadDirectory($path)
    {
        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($iterator, '/^.+\.php$/i');

        $factory = $this;
        foreach ($files as $file) {
            require $file->getPathName();
        }
    }

    /**
     * @param string $class
     * @return Closure
     * @throws DefinitionNotFoundException
     */
    private function getDefinition($class)
    {
        if (!$this->has($class)) {
            throw new DefinitionNotFoundException($class);
        }

        return $this->definitions[$class];
    }



}



