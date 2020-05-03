# yaf-support

基于yaf框架和laravel框架的融合
-------------

前言
---

yaf (六脉神剑)
六脉神剑，并非真剑，乃是以一阳指的指力化作剑气，有质无形，可称无形气剑。

laravel (降龙十八掌)
降龙十八掌天下第一，本身掌法套路就刚猛无比，在加上使用者雄浑的内力，更是无可抵挡

yaf 脉络清晰；框架简洁，易上手；可随心扩展自有功能；由于框架使用扩展形式，所以执行效率更高。

laravel  框架代码优美，整洁，周边丰富，可扩展性强；但框架可读性较差，每次运行加载文件较重，影响执行效率。

故优势互补，以yaf为底，laravel IOC容器思想、中间件、服务注册等贯穿整个yaf生命周期中，使yaf框架
可以更易扩展，代码也更清晰。

说明
----
    本版本为V1.0版，还未经过实际项目校验，如有使用中出现问题，可以发送邮件到zhaopeng156@126.com中
    共同探讨，如果有更好的想法或者优化也欢迎发送邮件互相切磋讨论，多多益善。

设计思路
------

* 容器(Container)
>>>基于pimple/pimple的容器，贯穿于yaf的全生命周期流程中，index.php入口文件会实例化容器并注册到Yaf_Registry实例中,
以实现全局使用。
``````PHP
        $app = new Application([], realpath(dirname(__FILE__)));
        Yaf_Registry::set('app', $app);
``````
* 服务注册(ServiceProvider)
>>>同样基于pimple/pimple容器，当需要新的中间件，或者新增服务模块时，可以使用服务注册将服务或者中间件注册。
``````PHP
        $app = new Application([], realpath(dirname(__FILE__)));
        Yaf_Registry::set('app', $app);

        app()['request'] = function ($c) {
            return new Request('test/test', 'base_uri/test/test');
        };

        app()[Authenticate::class] = function ($c) {
            return new Authenticate();
        };
``````

* 中间件(Middleware)
>>>在整个请求流程的生命周期中，会有一些鉴权、加解密、路由校验、session等中间件在请求到达核心逻辑之前做一些过滤请求
的处理，当有新增或者修改、删除一些过滤逻辑的时候可以在相应的配置文件中修改，做到随意扩展。
``````PHP
        $app = new Application([], realpath(dirname(__FILE__)));
        \Yaf_Registry::set('app', $app);
        
         app()['request'] = function ($c) {
                $request = new Request('test/test', 'base_uri/test/test');
                $request->setShouldMethod('any');
                $request->setMiddleware(['auth']);
                $request->setParam('api_token','12345678');
        
                return $request;
         };
        
          app()[Kernel::class] = function ($c) {
                return new Kernel($c);
          };
        
          arrayConfig()->set('auth', require __DIR__ . '/../src/Auth/config/auth.php');
        
          $res = app(Kernel::class)->handle(app('request'));
          var_dump(Auth()->id());
          var_dump(Auth()->user());
``````

*核心(Kernel)
>>>当各个组件、Request、Reponse等注册到服务容器中以后，kernel使用管道顺序调用Middleware 作相应的请求处理。
``````PHP
        if ($middlewareList = $request->getMiddleware()) {
              collect($middlewareList)->map(function ($middleware) {
                   $this->middleware[] = $middleware;
              });
        }
        
        return (new Pipeline($this->app))
               ->send($request)
               ->through($this->middleware)
               ->then(function ($request) {
                    return $request;
        });
``````

*输入/输出(Request/Response)
>>>request和response继承yaf自带的request/response 新增一些必要的数据。一并注册到容器中。
``````PHP
         $app = new Application([], realpath(dirname(__FILE__)));
         Yaf_Registry::set('app', $app);
        
         app()['request'] = function ($c) {
              return new Request('cli', 'cli');
         };
``````

*校验器(validation)
>>>参考laravel的校验器来做参数的校验，但校验类型与laravel相比较少，具体读源码
``````PHP
         validator()->validate([
              'age'  => 'required|int|between:1,100',
              'name' => 'required|string'
         ]);
         
         $age  = request()->getParam('age');
         $name = request()->getParam('name', 'test');
         var_dump($age);
         var_dump($name);
``````

*日志(monolog)
>>>完全复用monolog来做为此版本的日志记录工具，以天为单位，每小时记录所有的请求日志，具体代码需要根据实际
项目去配置，本处不做过多讲解。
``````PHP
         date_default_timezone_set("PRC");
         
         $app = new Application([], realpath(dirname(__FILE__)));
         Yaf_Registry::set('app', $app);
         
         app()['request'] = function ($c) {
               return new Request('cli', 'cli');
         };
         
         (new ServiceProvider())->boot();
         
         Log::debug('debug log');
         Log::info('info log');
         Log::notice('notice log');
         Log::error('error log');
         Log::warning('warring log');
``````

未完待续
----