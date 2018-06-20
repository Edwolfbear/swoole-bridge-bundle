<?php

namespace Insidestyles\SwooleBridgeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SwooleBridgeServerCommand
 * @package Insidestyles\SwooleBridgeBundle\Command
 */
class SwooleBridgeServerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('swoole:bridge:server')
            ->setDescription('Start swoole server');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $adapter = $container->get('swoole_bridge.adapter.symfony');
        /** @var Application $app */
        $app = $this->getApplication();
        $psr15Kernel = new \Insidestyles\SwooleBridge\Adapter\Kernel\Psr15SymfonyKernel($app->getKernel());
        $adapter->setRequestHandler($psr15Kernel);
        $handler = new \Insidestyles\SwooleBridge\Handler($adapter);

        $http = new \swoole_http_server($container->getParameter('swoole_bridge.server.host'),
            $container->getParameter('swoole_bridge.server.port'));
        $http->on(
            'request',
            function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($handler) {
                $handler->handle($request, $response);
            }
        );

        $http->start();
    }
}