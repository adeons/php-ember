<?php
namespace PhpEmber;

/**
 * Holds response data in a PHP array.
 */
class MemoryResponseWriter implements ResponseWriterInterface
{

    /**
     *
     * @var resource
     */
    private $output;

    /**
     *
     * @var string
     */
    private $primaryType;

    /**
     *
     * @var array
     */
    private $meta = array();

    /**
     *
     * @var array
     */
    private $data = array();

    /**
     * Constructor.
     *
     * @param string $primaryType
     * @param resource $output
     * @param array $meta
     */
    public function __construct($primaryType, $output = null, $meta = array())
    {
        $this->output = $output;
        $this->primaryType = $primaryType;
        $this->meta = $meta;
    }

    /**
     *
     * @return resource
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     *
     * @return string
     */
    public function getPrimaryType()
    {
        return $this->primaryType;
    }

    /**
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    public function lock($typeKey, $id)
    {
        if (isset($this->data[$typeKey][$id])) {
            return false;
        }

        $this->data[$typeKey][$id] = true;
        return true;
    }

    public function write($typeKey, $id, $payload)
    {
        $this->data[$typeKey][$id] = $payload;
    }

    public function flush()
    {
        fwrite($this->output, $this->toJson());
    }

    /**
     * Builds a JSON API formatted array.
     * @return array
     */
    public function toArray()
    {
        $result = array();

        if ($this->meta) {
            $result['meta'] = $this->meta;
        }

        foreach ($this->data as $typeKey => $payload) {

            if ($payload === true) {
                // true is internally used as a flag
                continue;
            }

            $result[$typeKey] = array_values($payload);
        }

        if (!isset($this->data[$this->primaryType])) {
            // main type result should be an array, but no payloads for it were found
            // so, add an empty array (see #1)
            $result[$this->primaryType] = array();
        }

        return $result;
    }

    /**
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = null)
    {
        return json_encode($this->toArray(), $options);
    }

}
