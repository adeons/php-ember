<?php
namespace PhpEmber;

/**
 * Represents a JSON API response.
 */
class JsonApiResponse extends \Symfony\Component\HttpFoundation\Response
{

    /**
     *
     * @var array
     */
    private $meta = array();

    /**
     *
     * @var array
     */
    private $options = array();

    /**
     *
     * @var AdapterInterface
     */
    private $adapter;

    /**
     *
     * @var array|object|\Traversable
     */
    private $data;

    /**
     *
     * @var bool
     */
    private $dataIsTraversable;

    /**
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Constructor.
     *
     * @param SerializerInterface $serializer
     * @param int $status
     * @param array $headers
     */
    public function __construct(SerializerInterface $serializer = null, $status = 200, $headers = array())
    {
        parent::__construct(null, $status, $headers);

        $this->serializer = $serializer;

        $this->headers->set('Content-Type', 'application/json');
    }

    public static function create($serializer = null, $status = 200, $headers = array())
    {
        return new static($serializer, $status, $headers);
    }

    /**
     *
     * @return SerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     *
     * @param SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }

    /**
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     *
     * @return array|object|\Traversable
     */
    public function getData()
    {
        return $this->data;
    }

    public function getContent()
    {
        return false;
    }

    /**
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     *
     * @param array $options
     * @return JsonApiResponse
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     *
     * @param array $meta
     * @return JsonApiResponse
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * Sets a model to be sent.
     *
     * @param AdapterInterface $adapter
     * @param object $model
     * @return JsonApiResponse
     */
    public function bindOne(AdapterInterface $adapter, $model)
    {
        $this->adapter = $adapter;
        $this->data = $model;
        $this->dataIsTraversable = false;

        return $this;
    }

    /**
     * Sets the models to be sent.
     *
     * @param AdapterInterface $adapter
     * @param array|\Traversable $models
     * @return JsonApiResponse
     */
    public function bindMany(AdapterInterface $adapter, $models)
    {
        $this->adapter = $adapter;
        $this->data = $models;
        $this->dataIsTraversable = true;

        return $this;
    }

    /**
     * Calls the serializer to write the model or models (if any) to the PHP
     * output.
     */
    public function sendContent()
    {
        $output = fopen('php://output', 'a');

        try {

            $writer = $this->createWriter($output);
            $this->serialize($writer);
            $writer->flush();

        } catch(Exception $e) {

            fclose($output);
            throw $e;
        }

        fclose($output);

        return $this;
    }

    /**
     * Creates the response writer.
     *
     * @param resource $output
     * @return ResponseWritterInterface
     */
    protected function createWriter($output)
    {
        return new MemoryResponseWriter(
            $this->adapter->getTypeKey(), $output, $this->meta);
    }

    /**
     * Serializes the model or models (if any).
     *
     * @param ResponseWritterInterface $buffer
     */
    protected function serialize($buffer)
    {
        if (!$this->data)
        {
            return;
        }

        if ($this->dataIsTraversable) {

            foreach ($this->data as $model) {

                $this->serializer->serialize(
                    $this->adapter, $model, $buffer, $this->options);
            }

        } else {

            $this->serializer->serialize(
                $this->adapter, $this->data, $buffer, $this->options);
        }
    }
}
