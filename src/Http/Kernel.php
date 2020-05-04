<?php

namespace Yaf\Support\Http;

use Yaf\Support\Foundation\Application;
use Yaf\Support\Http\Middleware\RouterMiddleware;
use Yaf\Support\Pipeline\Pipeline;

class Kernel
{
    /**
     * The application implementation.
     *
     * @var Application
     */
    protected $app;

    /**
     * The application's middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        'router'
    ];

    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param Request $request
     * @return mixed
     */
    public function handle(Request $request)
    {
        if ($middlewareList = $request->getMiddleware()) {
            collect($middlewareList)->filter()->unique()->values()->map(function ($middleware) {
                $this->middleware[] = $middleware;
            });
        }

        return (new Pipeline($this->app))
            ->send($request)
            ->through($this->middleware)
            ->then(function ($request) {
                return $request;
            });
    }

    /**
     * Add a new middleware to end of the stack if it does not already exist.
     *
     * @param string $middleware
     * @return $this
     */
    public function pushMiddleware($middleware)
    {
        if (array_search($middleware, $this->middleware) === false) {
            $this->middleware[] = $middleware;
        }

        return $this;
    }
}