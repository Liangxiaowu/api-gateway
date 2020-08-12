<?php
declare(strict_types=1);

namespace Wudner\Gateway\Listenter;
//use Jcsp\Core\Http\Register\ApiRegister;
//use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
//use Swoft\Event\EventInterface;
//use Swoft\Log\Helper\CLog;
//use Swoft\Log\Helper\Log;
//use Swoft\Stdlib\Helper\Arr;
//use Swoft\Validator\ValidateRegister;
use Swoft\Server\SwooleEvent;

/**
 * Class GatewayListenter
 *
 * @since 2.0
 *
 * @Listener(event=SwooleEvent::START)
 */
class GatewayListenter implements EventHandlerInterface
{
    public function handle(EventInterface $event): void
    {

        var_dump(111);
        // TODO: Implement handle() method.
    }


}
