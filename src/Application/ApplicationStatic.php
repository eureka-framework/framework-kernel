<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eureka\Kernel\Http\Application;

use Eureka\Component\Config\Config;
use Eureka\Component\Http\Message as HttpMessage;
use Eureka\Component\Http\Server as HttpServer;
use Eureka\Kernel\Http\Middleware;
use Psr\Container;

/**
 * Application class
 *
 * @author Romain Cottard
 */
class ApplicationStatic implements ApplicationInterface
{
    /** @var \Eureka\Psr\Http\Server\MiddlewareInterface[] $middleware */
    protected $middleware = [];

    /** @var \Psr\Container\ContainerInterface $container */
    protected $container = null;

    /** @var \Eureka\Component\Config\Config $container */
    protected $config = null;

    /** @var string $type Static content type. */
    protected $type = '';


    /**
     * ApplicationStatic constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \Eureka\Component\Config\Config $config
     */
    public function __construct(Container\ContainerInterface $container, Config $config)
    {
        $this->container = $container;
        $this->config    = $config;
    }

    /**
     * Run application based on the route.
     *
     * @return void
     */
    public function run()
    {
        $this->loadMiddleware();

        //~ Default response
        $response = new HttpMessage\Response();

        //~ Get response
        $handler  = new HttpServer\RequestHandler($response, $this->middleware);
        $response = $handler->handle(HttpMessage\ServerRequest::createFromGlobal());

        //~ Send response
        (new HttpMessage\ResponseSender($response))->send();
    }

    /**
     * Load middleware
     *
     * @return void
     */
    private function loadMiddleware()
    {
        $this->middleware[] = new Middleware\ErrorMiddleware($this->container, $this->config);

        //~ Request
        $request = HttpMessage\ServerRequest::createFromGlobal();
        $query   = $request->getQueryParams();

        $this->type = $query['type'];

        switch ($this->type) {
            case 'css':
                $this->middleware[] = new Middleware\StaticMiddleware\CssMiddleware($this->container, $this->config);
                break;
            case 'js':
                $this->middleware[] = new Middleware\StaticMiddleware\JsMiddleware($this->container, $this->config);
                break;
            case 'image':
                $this->middleware[] = new Middleware\StaticMiddleware\ImageMiddleware($this->container, $this->config);
                break;
            case 'font':
                $this->middleware[] = new Middleware\StaticMiddleware\FontMiddleware($this->container, $this->config);
                break;
        }
    }
}
