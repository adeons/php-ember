<?php
namespace PhpEmber;

/**
 * Allows to incrementally write a JSON API response into a stream.
 */
interface ResponseWriterInterface
{

    /**
     * Flags the given type key and identifier combination to be written in
     * the future.
     *
     * This enables serializers to call another which may in turn, either try
     * to serialize the same model (circular reference) or any previously
     * written.
     *
     * @param string $typeKey
     * @param string $id
     * @return bool False if the given type key and identifier was locked or
     * written before, or true otherwise.
     */
    public function lock($typeKey, $id);

    /**
     * Writes the given model payload.
     *
     * @param string $typeKey
     * @param string $id
     * @param mixed $payload
     */
    public function write($typeKey, $id, $payload);

    /**
     * Ensures that all data is written into the output stream.
     *
     * This method should be called after all desired models were written.
     */
    public function flush();

}
