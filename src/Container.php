<?php

/**
 * Class Container
 */
class Container {
	protected $instances = [];
  protected $singletons = [];

  public function singleton($abstract, $concrete) {
    $this->singletons[$abstract] = $concrete;
  }

	public function set($abstract, $concrete = NULL) {
		if ($concrete === NULL) {
			$concrete = $abstract;
		}
    $this->instances[$abstract] = $concrete;
  }

	public function get($abstract, $parameters = []) {
    if (isset($this->singletons[$abstract])) {
      return $this->singletons[$abstract];
    }
		if (!isset($this->instances[$abstract])) {
			$this->set($abstract);
		}
		return $this->resolve($this->instances[$abstract], $parameters);
	}

  public function resolve($concrete, $parameters)	{
		if ($concrete instanceof Closure) {
			return $concrete($this, $parameters);
		}

		$reflector = new ReflectionClass($concrete);
		if (!$reflector->isInstantiable()) {
			throw new Exception("Class {$concrete} is not instantiable");
		}

		$constructor = $reflector->getConstructor();
		if (is_null($constructor)) {
			return $reflector->newInstance();
		}

		$parameters   = $constructor->getParameters();
		$dependencies = $this->getDependencies($parameters);

		return $reflector->newInstanceArgs($dependencies);
	}

	public function getDependencies($parameters) {
		$dependencies = [];
		foreach ($parameters as $parameter) {
			$dependency = $parameter->getClass();
			if ($dependency === NULL) {
				if ($parameter->isDefaultValueAvailable()) {
					$dependencies[] = $parameter->getDefaultValue();
				} else {
					throw new Exception("Can not resolve class dependency {$parameter->name}");
				}
			} else {
				$dependencies[] = $this->get($dependency->name);
			}
		}
		return $dependencies;
	}
}