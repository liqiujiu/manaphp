<?php

namespace ManaPHP;

use ManaPHP\Di\Exception as DiException;

/**
 * Class ManaPHP\Di
 *
 * @package  di
 *
 * @property \ManaPHP\AliasInterface                       $alias
 * @property \ManaPHP\Mvc\DispatcherInterface              $dispatcher
 * @property \ManaPHP\Mvc\RouterInterface                  $router
 * @property \ManaPHP\Mvc\UrlInterface                     $url
 * @property \ManaPHP\Http\RequestInterface                $request
 * @property \ManaPHP\Http\FilterInterface                 $filter
 * @property \ManaPHP\Http\ResponseInterface               $response
 * @property \ManaPHP\Http\CookiesInterface                $cookies
 * @property \ManaPHP\Mvc\View\FlashInterface              $flash
 * @property \ManaPHP\Mvc\View\FlashInterface              $flashSession
 * @property \ManaPHP\Http\SessionInterface                $session
 * @property \ManaPHP\Event\ManagerInterface               $eventsManager
 * @property \ManaPHP\DbInterface                          $db
 * @property \ManaPHP\Security\CryptInterface              $crypt
 * @property \ManaPHP\Db\Model\MetadataInterface           $modelsMetadata
 * @property \ManaPHP\Cache\EngineInterface                $modelsCache
 * @property \ManaPHP\Di|\ManaPHP\DiInterface              $di
 * @property \ManaPHP\Mvc\ViewInterface                    $view
 * @property \ManaPHP\Loader                               $loader
 * @property \ManaPHP\LoggerInterface                      $logger
 * @property \ManaPHP\RendererInterface                    $renderer
 * @property \ManaPHP\Configure                            $configure
 * @property \ManaPHP\ApplicationInterface                 $application
 * @property \ManaPHP\DebuggerInterface                    $debugger
 * @property \ManaPHP\Authentication\PasswordInterface     $password
 * @property \ManaPHP\Redis                                $redis
 * @property \ManaPHP\Serializer\AdapterInterface          $serializer
 * @property \ManaPHP\CacheInterface                       $cache
 * @property \ManaPHP\CounterInterface                     $counter
 * @property \ManaPHP\Cache\EngineInterface                $viewsCache
 * @property \ManaPHP\Http\ClientInterface                 $httpClient
 * @property \ManaPHP\AuthorizationInterface               $authorization
 * @property \ManaPHP\Security\CaptchaInterface            $captcha
 * @property \ManaPHP\Security\CsrfTokenInterface          $csrfToken
 * @property \ManaPHP\Authentication\UserIdentityInterface $userIdentity
 * @property \ManaPHP\Paginator                            $paginator
 * @property \ManaPHP\FilesystemInterface                  $filesystem
 * @property \ManaPHP\Security\RandomInterface             $random
 * @property \ManaPHP\Message\QueueInterface               $messageQueue
 * @property \ManaPHP\Text\CrosswordInterface              $crossword
 * @property \ManaPHP\Security\RateLimiterInterface        $rateLimiter
 * @property \ManaPHP\Meter\LinearInterface                $linearMeter
 * @property \ManaPHP\Meter\RoundInterface                 $roundMeter
 * @property \ManaPHP\Security\SecintInterface             $secint
 * @property \ManaPHP\I18n\Translation                     $translation
 * @property \ManaPHP\Renderer\Engine\Sword\Compiler       $swordCompiler
 * @property \ManaPHP\StopwatchInterface                   $stopwatch
 * @property \ManaPHP\Security\HtmlPurifierInterface       $htmlPurifier
 * @property \ManaPHP\Cli\EnvironmentInterface             $environment
 * @property \ManaPHP\Net\ConnectivityInterface            $netConnectivity
 */
class Di implements DiInterface
{

    /**
     * @var array
     */
    protected $_components = [];

    /**
     * @var array
     */
    protected $_aliases = [];

    /**
     * @var array
     */
    protected $_instances = [];

    /**
     * First DI build
     *
     * @var \ManaPHP\Di
     */
    protected static $_default;

    public function __construct()
    {
        if (self::$_default === null) {
            self::$_default = $this;
        }
    }

    /**
     * Return the First DI created
     *
     * @return static
     */
    public static function getDefault()
    {
        return self::$_default;
    }

    /**
     * Registers a component in the components container
     *
     * @param string $name
     * @param mixed  $definition
     *
     * @return static
     */
    public function set($name, $definition)
    {
        if (is_string($definition)) {
            $definition = ['class' => $definition, 'shared' => false];
        } elseif (is_array($definition)) {
            if (!isset($definition['class'])) {
                if (isset($this->_components[$name])) {
                    $component = $this->_components[$name];
                } elseif (isset($this->_aliases[$name])) {
                    $component = $this->_components[$this->_aliases[$name]];
                } elseif (strpos($name, '\\') !== false) {
                    $component = $name;
                } else {
                    /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                    throw new DiException('`:component` component definition is invalid: missing class field', ['component' => $name]);
                }

                $definition['class'] = is_string($component) ? $component : $component['class'];
            }

            $definition['shared'] = false;
        } elseif (is_object($definition)) {
            $definition = ['class' => $definition, 'shared' => !$definition instanceof \Closure];
        } else {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            throw new DiException('`:component` component definition is unknown', ['component' => $name]);
        }

        $this->_components[$name] = $definition;

        return $this;
    }

    /**
     * Registers an "always shared" component in the components container
     *
     * @param string $name
     * @param mixed  $definition
     *
     * @return static
     */
    public function setShared($name, $definition)
    {
        if (is_string($definition)) {
            null;//do nothing
        } elseif (is_array($definition)) {
            if (!isset($definition['class'])) {
                if (isset($this->_components[$name])) {
                    $component = $this->_components[$name];
                } elseif (isset($this->_aliases[$name])) {
                    $component = $this->_components[$this->_aliases[$name]];
                } elseif (strpos($name, '\\') !== false) {
                    $component = $name;
                } else {
                    /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                    throw new DiException('`:component` component definition is invalid: missing class field', ['component' => $name]);
                }

                $definition['class'] = is_string($component) ? $component : $component['class'];
            }
        } elseif (is_object($definition)) {
            $definition = ['class' => $definition];
        } else {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            throw new DiException('`:component` component definition is unknown', ['component' => $name]);
        }

        $this->_components[$name] = $definition;

        return $this;
    }

    /**
     * @param string       $component
     * @param string|array $aliases
     * @param bool         $force
     *
     * @return static
     */
    public function setAliases($component, $aliases, $force = false)
    {
        if (is_string($aliases)) {
            if ($force || !isset($this->_aliases[$aliases])) {
                $this->_aliases[$aliases] = $component;
            }
        } else {
            /** @noinspection ForeachSourceInspection */
            foreach ($aliases as $alias) {
                if ($force || !isset($this->_aliases[$alias])) {
                    $this->_aliases[$alias] = $component;
                }
            }
        }

        return $this;
    }

    /**
     * Removes a component in the components container
     *
     * @param string $name
     *
     * @return static
     */
    public function remove($name)
    {
        if (in_array($name, $this->_aliases, true)) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            throw new DiException('`:name` component is being used by alias, please remove alias first'/**m04c19e730f00d1a9f*/, ['name' => $name]);
        }

        if (isset($this->_aliases[$name])) {
            unset($this->_aliases[$name]);
        } else {
            unset($this->_components[$name], $this->_instances[$name], $this->{$name});
        }

        return $this;
    }

    /**
     * @param mixed  $definition
     * @param array  $parameters
     * @param string $name
     *
     * @return mixed
     */
    public function getInstance($definition, $parameters = null, $name = null)
    {
        if (is_string($definition)) {
            $params = [];
        } else {
            $params = $definition;
            $definition = $definition['class'];
            unset($params['class'], $params['shared']);
        }

        if ($parameters === null) {
            if (isset($params[0]) || count($params) === 0) {
                $parameters = $params;
            } else {
                $parameters = [$params];
            }
        }

        if (is_string($definition)) {
            if (!class_exists($definition)) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                throw new DiException('`:name` component cannot be resolved: `:class` class is not exists'/**m03ae8f20fcb7c5ba6*/, ['name' => $name, 'class' => $definition]);
            }
            $count = count($parameters);

            if ($count === 0) {
                $instance = new $definition();
            } elseif ($count === 1) {
                $instance = new $definition($parameters[0]);
            } elseif ($count === 2) {
                $instance = new $definition($parameters[0], $parameters[1]);
            } elseif ($count === 3) {
                $instance = new $definition($parameters[0], $parameters[1], $parameters[2]);
            } else {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $reflection = new \ReflectionClass($definition);
                $instance = $reflection->newInstanceArgs($parameters);
            }
        } elseif ($definition instanceof \Closure) {
            $instance = call_user_func_array($definition, $parameters);
        } elseif (is_object($definition)) {
            $instance = $definition;
        } else {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            throw new DiException('`:name` component cannot be resolved: component implement type is not supported'/**m072d42756355fb069*/, ['name' => $name]);
        }

        if ($instance instanceof Component) {
            $instance->setDependencyInjector($this);
        }

        return $instance;
    }

    /**
     * Resolves the component based on its configuration
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \ManaPHP\Di\Exception
     */
    public function get($name, $parameters = null)
    {
        if (isset($this->_instances[$name])) {
            return $this->_instances[$name];
        }

        if (isset($this->_aliases[$name], $this->_instances[$this->_aliases[$name]])) {
            return $this->_instances[$this->_aliases[$name]];
        }

        if (isset($this->_components[$name])) {
            $definition = $this->_components[$name];
        } elseif (isset($this->_aliases[$name])) {
            $definition = $this->_components[$this->_aliases[$name]];
        } else {
            return $this->getInstance($name, $parameters, $name);
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $instance = $this->getInstance($definition, $parameters, $name);

        if (is_string($definition) || !isset($definition['shared']) || $definition['shared'] === true) {
            if (isset($this->_components[$name])) {
                $this->_instances[$name] = $instance;
            } else {
                $this->_instances[$this->_aliases[$name]] = $instance;
            }
        }

        return $instance;
    }

    /**
     * Resolves a component, the resolved component is stored in the DI, subsequent requests for this component will return the same instance
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getShared($name)
    {
        if (isset($this->_instances[$name])) {
            return $this->_instances[$name];
        }

        if (isset($this->_aliases[$name], $this->_instances[$this->_aliases[$name]])) {
            return $this->_instances[$this->_aliases[$name]];
        }

        if (isset($this->_components[$name])) {
            return $this->_instances[$name] = $this->getInstance($this->_components[$name], null, $name);
        } elseif (isset($this->_aliases[$name])) {
            return $this->_instances[$this->_aliases[$name]] = $this->getInstance($this->_components[$this->_aliases[$name]], null, $name);
        } else {
            return $this->_instances[$name] = $this->getInstance($name, null, $name);
        }
    }

    /**
     * Magic method __get
     *
     * @param string $propertyName
     *
     * @return mixed
     * @throws \ManaPHP\Di\Exception
     */
    public function __get($propertyName)
    {
        return $this->getShared($propertyName);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @throws \ManaPHP\Di\Exception
     */
    public function __set($name, $value)
    {
        if ($value === null) {
            $this->remove($name);
        } else {
            $this->setShared($name, $value);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * Check whether the DI contains a component by a name
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->_components[$name]) || isset($this->_aliases[$name]);
    }

    /**
     * Magic method to get or set components using setters/getters
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return void
     * @throws \ManaPHP\Di\Exception
     */
    public function __call($method, $arguments = [])
    {
        throw new DiException('Call to undefined method `:method`'/**m06946faf1ec42dea1*/, ['method' => $method]);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return get_object_vars($this);
    }

    public function reConstruct()
    {
        foreach ($this->_instances as $k => $v) {
            if ($v instanceof Component) {
                $v->reConstruct();
            }
        }
    }
}
