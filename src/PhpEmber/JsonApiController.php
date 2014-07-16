<?php
namespace PhpEmber;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Represents a JSON API resource for a data type using an adapter.
 *
 * Contains methods to handle HTTP requests, parse URL query parameters (such as
 * query sorting, etc), and generating responses.
 */
class JsonApiController
{

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     *
     * @var int
     */
    private $pageSize = 30;

    /**
     * Constructor.
     *
     * @param AdapterInterface $adapter
     * @param SerializerInterface $serializer
     */
    public function __construct(AdapterInterface $adapter, SerializerInterface $serializer)
    {
        $this->adapter = $adapter;
        $this->serializer = $serializer;
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
     * @return SerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     *
     * @param int $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     * Creates a JSON API response whose content is a serialized model.
     *
     * @param object $model
     * @return JsonApiResponse
     */
    public function one($model)
    {
        $response = new JsonApiResponse($this->serializer);
        $response->bindOne($this->adapter, $model);

        return $response;
    }

    /**
     * Creates a JSON API response whose content is the serialized
     * representation of the given models.
     *
     * @param array|\Traversable $models
     * @return JsonApiResponse
     */
    public function many($models)
    {
        $response = new JsonApiResponse($this->serializer);
        $response->bindMany($this->adapter, $models);

        return $response;
    }

    /**
     * Creates an error response.
     *
     * @param int $code HTTP status code.
     * @param string $message Error description.
     * @return Response
     */
    public function error($code, $message = null)
    {
        return new JsonResponse(array(
            'errors' => array(
                array('detail' => $message)
            )
        ), $code);
    }

    /**
     *
     * @param Request $request
     * @return JsonApiResponse
     */
    public function index(Request $request)
    {
        $query = $request->query;

        $serializeOptions = $this->parseSerializeOptions($query->all());

        if ($serializeOptions instanceof Response) {
            // bad request
            return $serializeOptions;
        }

        if ($query->has('ids')) {

            // load many models by identifier;
            // requests tipically looks like /fruits?ids[]=orange&ids[]=banana

            $models = $this->adapter->findMany((array) $query->get('ids'));

            return $this->many($models)
                ->setOptions($serializeOptions);
        }

        // filter models

        $options = $this->parseQueryOptions($query->all());

        if ($options instanceof Response) {
            // bad request
            return $options;
        }

        list($models, $total) = $this->adapter->findAll($options);

        return $this->many($models)
            ->setMeta(array(
                'maxPage' => ceil($total / $options['count'])
            ))
            ->setOptions($serializeOptions);
    }

    /**
     *
     * @param Request $request
     * @param string $id
     * @return JsonApiResponse
     */
    public function show(Request $request, $id)
    {
        $model = $this->adapter->find($id);

        if (!$model) {

            return $this->error(Response::HTTP_NOT_FOUND,
                sprintf('"%s" not found.', $id));
        }

        $serializeOptions = $this->parseSerializeOptions(
            $request->query->all());

        if ($serializeOptions instanceof Response) {
            // bad request
            return $serializeOptions;
        }

        return $this->one($model)
            ->setOptions($serializeOptions);
    }

    /**
     * Handles a HTTP request for the type as a whole, exposing it as a
     * HTTP resource.
     *
     * Usually this method should be called for routes ending like
     * <code>/resource</code>.
     *
     * The behavior changes depending of the request method:
     *
     * <ul>
     * <li>GET: Returns the models.</li>
     * </ul>
     *
     * @param Request $request
     * @return Response
     */
    public function runCollection(Request $request)
    {
        switch ($request->getMethod()) {
            case 'HEAD':
            case 'GET':
                return $this->index($request);

            default:
                return $this->error(Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    /**
     * Handles a request for an individual model, exposing it as a
     * HTTP resource.
     *
     * Usually this method should be called for routes ending like
     * <code>/resource/id</code>.
     *
     * The behavior changes depending of the request method:
     *
     * <ul>
     * <li>GET: Returns the serialized model.</li>
     * <li>DELETE: Deletes the model. If found, returns status code 204 without
     * content.</li>
     * </ul>
     *
     * In any case, if the model is not found, 404 status is returned.
     *
     * @param Request $request
     * @return Response
     */
    public function runModel(Request $request, $id)
    {
        switch ($request->getMethod()) {
            case 'HEAD':
            case 'GET':
                return $this->show($request, $id);

            case 'DELETE':

                if (!$this->adapter->remove($id)) {

                    return $this->error(Response::HTTP_NOT_FOUND,
                        sprintf('"%s" not found.', $id));
                }

                return new Response(null, Response::HTTP_NO_CONTENT);

            default:
                return $this->error(Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    /**
     *
     * @param array $query
     * @return array|Response
     */
    public function parseQueryOptions($query)
    {
        $options = $this->parsePaginationOptions($query);

        if ($options instanceof Response) {
            return $options;
        }

        if (isset($query['sort'])) {
            $option = $this->parseSortOptions($query['sort']);

            if ($option instanceof Response) {
                return $option;
            }

            $options['sort'] = $option;
        }

        return $options;
    }

    /**
     *
     * @param array $query
     * @return array|Response
     */
    protected function parsePaginationOptions($query)
    {
        $page = 1;
        $pageSize = $this->pageSize;

        if (isset($query['page'])) {
            $option = $query['page'];

            if ($option) {
                $page = intval($option);

                if ($page <= 0) {

                    return $this->error(Response::HTTP_BAD_REQUEST,
                        'Invalid page number.');
                }
            }
        }

        return array(
            'start' => ($page - 1) * $pageSize,
            'count' => $pageSize
        );
    }

    /**
     *
     * @param string $sort
     * @return array|Response
     */
    protected function parseSortOptions($sort)
    {
        if (!$sort) {
            return array();
        }

        $attributes = $this->adapter->getAttributes();
        $sorting = array();

        foreach (explode(',', $sort) as $token) {

            if (strpos($token, '-') === 0) {
                $name = substr($token, 1);
                $descending = true;

            } elseif (strpos($token, ' ') === 0) {
                $name = substr($token, 1);
                $descending = false;

            } else {
                $name = $token;
                $descending = false;
            }

            if (!isset($attributes[$name])) {

                // attribute not found
                return $this->error(Response::HTTP_BAD_REQUEST,
                    sprintf('Can not sort by undefined attribute "%s".'));
            }

            $sorting[] = array(
                'attribute' => $name,
                'descending' => $descending
            );
        }

        return $sorting;
    }

    /**
     * Parses serialization options (such as what attributes to include) from
     * URL query parameters.
     *
     * Note that the resulting options may need to be sanitized since the
     * HTTP request may contain options that try to include attributes or related
     * models that are not intended to be sent to the user.
     *
     * @param array $query URL query parameters.
     * @return array|Response
     */
    public function parseSerializeOptions($query)
    {
        $options = array();

        if (isset($query['fields'])) {
            $fields = $query['fields'];

            if ($fields) {

                if (is_array($fields)) {

                    // fields per type key
                    $options = $this->parseFieldsArray($this->adapter, $fields);

                } else {

                    // only for the primary type
                    $options = $this->parseFieldsString($this->adapter, $fields);
                }
            }
        }

        return $options;
    }

    /**
     * Parses a comma separated string with attribute names to send.
     *
     * Example:
     * <code>fields=id,name,age</code>
     *
     * @param AdapterInterface $adapter
     * @param string $fields
     * @return array|Response
     */
    protected function parseFieldsString($adapter, $fields)
    {
        $options = array();

        $fields = explode(',', $fields);

        foreach ($adapter->getAttributes() as $name => $attribute) {

            if (!in_array($name, $fields, true)) {
                // attribute not found in field list; exclude it
                $options[$name] = false;
            }
        }

        return $options;
    }

    /**
     * Parses an array where each key should be a type key and the value the
     * attributes to send.
     *
     * Example:
     * <code>fields[posts]=id,title&fields[people]=id,name</code>
     *
     * @param AdapterInterface $adapter
     * @param array $fields
     * @return array|Response
     */
    protected function parseFieldsArray($adapter, $fields)
    {
        $optionsByType = array();

        $primaryType = \ICanBoogie\pluralize($adapter->getTypeKey());

        if (isset($fields[$primaryType])) {

            $options = $this->parseFieldsString(
                $adapter, $fields[$primaryType]);

            if ($options instanceof Response) {
                return $options;
            }

            $optionsByType[$primaryType] = $options;

        } else {
            $options = array();
        }

        foreach ($adapter->getAttributes() as $name => $attribute) {

            $relatedType = $attribute->getRelatedType();

            if (!$relatedType) {
                // not a relation
                continue;
            }

            $relatedType = \ICanBoogie\pluralize($relatedType);

            if (!isset($fields[$relatedType])) {
                // this attribute relation type not matches any option
                continue;
            }

            if (isset($optionsByType[$relatedType])) {

                // the field list was parsed for this type key before
                $relationOptions = $optionsByType[$relatedType];

            } else {

                // parse options for related type
                // if another relation is of the same type, this result
                // will be reused

                $relationOptions = $this->parseFieldsString(
                    $attribute->getRelatedAdapter(), $fields[$relatedType]);

                if ($relationOptions instanceof Response) {
                    return $relationOptions;
                }

                // cache result
                $optionsByType[$relatedType] = $relationOptions;
            }

            $options[$name] = $relationOptions;
        }

        return $options;
    }

}
