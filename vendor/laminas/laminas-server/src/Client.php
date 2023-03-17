<?php

/**
 * @see       https://github.com/laminas/laminas-server for the canonical source repository
 */

namespace Laminas\Server;

/**
 * Client Interface
 *
 * @deprecated Since 2.9.0; Client is replaced by ClientInterface and will be removed in 3.0.
 */
interface Client
{
    /**
     * Executes remote call
     *
     * Unified interface for calling custom remote methods.
     *
     * @param  string $method Remote call name.
     * @param  array $params Call parameters.
     * @return mixed Remote call results.
     */
    public function call($method, $params = []);
}
