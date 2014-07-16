<?php
namespace PhpEmber;

/**
 * Maps type keys to adapter objects.
 * @see AdapterInterface
 */
interface AdapterResolverInterface
{

    /**
     * Checks if the given type key can be resolved to an adapter.
     *
     * @param string $typeKey
     * @return bool
     */
    public function has($typeKey);

    /**
     * Returns an adapter that matches the type key.
     *
     * @param string $typeKey
     * @return AdapterInterface
     */
    public function get($typeKey);
    
}
