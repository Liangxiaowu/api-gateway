<?php
declare(strict_types=1);

namespace Jcsp\SpiGateway\Listenter;
use Jcsp\Core\Annotation\Mapping\Parameter;
use Jcsp\Core\Http\Register\ApiRegister;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Log\Helper\CLog;
use Swoft\Log\Helper\Log;
use Swoft\Stdlib\Helper\Arr;
use Swoft\Validator\ValidateRegister;

/**
 * Class GatewayListenter
 * @Listener(event=SwooleEvent::START)
 */
class GatewayListenter implements EventHandlerInterface
{
    public function handle(EventInterface $event): void
    {
        //路由处理
        $this->route();
        CLog::info('route handle success!');
        //配置处理
        $this->setting();
        CLog::info('setting handle success!');
        //api同步
        $this->sync();
        CLog::info('sync handle success!');
        //异步删除无用内存数据
        $this->destory();
        CLog::info('destory handle success!');
        Log::info('StartListener handle success!');

    }

    /**
     * 补充路由信息
     * @return void
     */
    protected function route(): void
    {
        $controllerData = [];
        /* @var Router $router */
        $router = bean('httpRouter');
        foreach ($router->getRoutes() as $route) {
            [$className, $methodName] = explode('@', $route['handler']);
            $apiInfo = ApiRegister::getApiInfo($className, $methodName);
            //是否需要auth验证 默认需要
            $isAuth = ApiRegister::getApiSetting('auth', $className, $methodName);
            $isAuth = $isAuth ?: true;
            $info = [
                'title' => empty($apiInfo['title']) ? $methodName : $apiInfo['title'],
                'path' => $route['path'],
                'method' => [$route['method']],
                'params' => $route['params']
            ];
            //路由信息注册
            ApiRegister::registerInfo($info, $className, $methodName);

            //防止重复添加
            $flag = $controllerData["$className@$methodName"] ?? false;
            if ($flag === false) {
                //通过验证器获取swagger参数并注册
                $parameters = $this->handleSwaggerParameter($className, $methodName);

                $parameterConfig = config('swagger.parameters');
                $parameters[] = $parameterConfig['appKey'];
                if ($isAuth) {
                    $parameters[] = $parameterConfig['auth'];
                }
                $controllerData["$className@$methodName"] = true;
                ApiRegister::registerParameter($parameters, $className, $methodName);
            }
        }

        unset($controllerData);
    }

    /**
     * 配置设置
     * @return void
     */
    protected function setting(): void
    {
        //补充限流设置
        $limiters = RateLimiterRegister::getAllRateLimiter();
        foreach ($limiters as $className => $methods) {
            foreach ($methods as $methodName => $limiter) {
                ApiRegister::registerSetting(['limiter' => $limiter], $className, $methodName);
            }
        }
    }

    /**
     * api同步
     * @return void
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    protected function sync(): void
    {
        //同步接口数据
        if (!config('syncApi')) {
            Log::info('StartListener skip sync!');
            return;
        }
        $imageTag = config('imageTag');
        $gatewayApiName = config('name');
        $apiData = ApiRegister::getApiData();
        $res = $this->apiService->syncData($gatewayApiName, $imageTag, $apiData);

        foreach ($res['controllers'] as $className => $controller) {
            ApiRegister::registerSetting(['active' => $controller['active']], $className);
        }
        foreach ($res['methods'] as $id => $method) {
            ApiRegister::registerInfo(['id' => $id], $method['class'], $method['method']);
            $setting = $method['setting'];
            if (!empty($setting['limiter']['rate'])) {
                RateLimiterRegister::registerLimiter($method['class'], $method['method'], $setting['limiter']);
            }
            if (isset($setting['active']) && !$setting['active']) {
                ApiRegister::registerSetting(['active' => false], $method['class'], $method['method']);
            }
        }
    }

    /**
     * 删除无用内存数据
     * @return void
     */
    protected function destory()
    {
        $d = 'd';
    }

    /**
     * swagger处理 获取方法下注册器方式的引用参数
     * @param $className
     * @param $methodName
     * @return array
     */
    protected function handleSwaggerParameter($className, $methodName): array
    {
        $parameters = [];
        //方法下所有验证器
        $validates = ValidateRegister::getValidates($className, $methodName);
        foreach ($validates as $key => $validate) {
            //验证器下所有字段
            $swaggerData = ValidatorRegister::getSwagger($key);
            if (!empty($validate['fields'])) {
                $swaggerData = Arr::only($swaggerData, $validate['fields']);
            }
            if (!empty($validate['unfields'])) {
                $swaggerData = Arr::except($swaggerData, $validate['unfields']);
            }
//            $in = (($validate['type'] ?? 'body') === ValidateType::BODY)
//                ? Parameter::FIELD_STYLE_QUERY : Parameter::FIELD_STYLE_PATH;
            $in = Parameter::FIELD_STYLE_QUERY;
            foreach ($swaggerData as $key => $value) {
                $value = Arr::merge($value, ['in' => $in]);
                $parameters[$key] = $value;
            }
        }
        $parameters = array_values($parameters);
        //parameters config
        return $parameters;
    }


}
