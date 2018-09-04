<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/9/3
 */

namespace Sdk\Signaller\Contracts;

/**
 * Interface SentinelInterface
 * @package Sdk\Signaller\Contracts
 */
interface SentinelInterface
{

    /**
     * 获取所有服务节点
     *
     * @return array
     */
    public function list();

    /**
     * 获取服务节点
     *
     * @param string $serviceName
     * @return array
     */
    public function node(string $serviceName);

    /**
     * 获取服务节点所有路由
     *
     * @param string $serviceName
     * @return array
     */
    public function routes(string $serviceName);

    /**
     * 获取服务节点路由信息
     *
     * @param string $path
     * @return array
     */
    public function route(string $serviceName, string $path);

    /**
     * 获取节点状态
     *
     * @param string $serviceName
     * @param string $path
     * @return array
     */
    public function status(string $serviceName, string $path);
}
