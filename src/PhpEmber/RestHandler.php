<?php
namespace PhpEmber;

use ICanBoogie\Inflector;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestHandler {
	
	private $adapters;
	private $serializer;
	
	function __construct(AdapterContainer $adapters, Serializer $serializer) {
		$this->adapters = $adapters;
		$this->serializer = $serializer;
	}
	
	function getAdapters() {
		return $this->adapters;
	}
	
	function getSerializer() {
		return $this->serializer;
	}
	
	function run(Request $request) {
		
		$query = $request->query;
		
		$id = $query->get('id');
		$typeKey = Inflector::get()->singularize($query->get('scope'));
		
		$adapter = $this->adapters->getAdapter($typeKey);
		
		if(!$adapter) {
			
			return $this->makeErrorResponse(Response::HTTP_NOT_FOUND, "Adapter $typeKey not found");
		}
		
		switch($request->getMethod()) {
		case 'GET':
		case 'HEAD':
			
			if($id) {
				return $this->doGet($adapter, $id);
			}
			
			if($query->has('ids')) {
				return $this->doMap($adapter, (array) $query->get('ids'));
			}
			
			return $this->doQuery($adapter, $request);
			
		case 'POST':
			return $this->doPut($adapter, null, $request);
			
		case 'PUT':
			return $this->doPut($adapter, $id, $request);
			
		case 'DELETE':
			return $this->doRemove($adapter, $id);
			
		default:
			
			return $this->makeErrorResponse(Response::HTTP_METHOD_NOT_ALLOWED);
		}
	}
	
	protected function doGet(Adapter $adapter, $id) {
		
		$model = $adapter->find($id);
		
		if(!$model) {
			return $this->makeErrorResponse(Response::HTTP_NOT_FOUND, "Model $id not found");
		}
		
		return $this->makeModelResponse($model);
	}
	
	protected function doMap(Adapter $adapter, $ids) {
		
		return $this->makeModelsResponse($adapter->findMany($ids));
	}
	
	protected function doQuery(Adapter $adapter, Request $request) {
		
		$options = $this->parseOptions($adapter, $request);
		
		list($models, $total) = $adapter->findAll(null, $options);
		
		return $this->makeModelsResponse($models, $total);
	}
	
	protected function doPut(Adapter $adapter, $id, Request $request) {
		
		$typeKey = $adapter->getTypeKey();
		
		$payload = json_decode($request->getContent(), true);
		
		if(!isset($payload[$typeKey])) {
			
			return $this->makeErrorResponse(Response::HTTP_BAD_REQUEST, "Missing $typeKey payload");
		}
		
		if($id) {
			
			$model = $adapter->find($id);
			
			if(!$model) {
				return $this->makeErrorResponse(Response::HTTP_NOT_FOUND, "Model $typeKey with ID $id not found");
			}
			
		} else {
			
			$model = $adapter->create();
		}
		
		$context = new SerializerContext($model);
		$context->payload = $payload[$typeKey];
		
		$this->serializer->decode($context);
		
		if($context->hasErrors()) {
			
			return new JsonResponse(array(
				'error' => array(
					'message' => 'Decode failed',
					'attributes' => $context->errors->getErrors()
				)
				
			), Response::HTTP_BAD_REQUEST);
		}
		
		if($model->save($context) == false) {
			
			return $this->makeErrorResponse(Response::HTTP_BAD_REQUEST, 'Validation failed');
		}
		
		return $this->makeModelResponse($model, false);
	}
	
	protected function doRemove(Adapter $adapter, $id) {
		
		if($adapter->remove($id) == false) {
			
			return $this->makeErrorResponse(Response::HTTP_NOT_FOUND, "Model $id not found");
		}
		
		return new Response(null, Response::HTTP_FOUND);
	}
	
	protected function parseOptions(Adapter $adapter, Request $request) {
		$query = $request->query;
		
		$options = array();
		
		if($query->has('sort')) {
			$options['sort'] = $this->parseSortOptions($adapter, (array) $query->get('sort'));
		}
		
		if($query->has('start')) {
			$options['start'] = max($query->getInt('start'), 0);
		}
		
		if($query->has('count')) {
			$options['count'] = max($query->getInt('count'), 0);
		}
		
		return $options;
	}
	
	protected function parseSortOptions(Adapter $adapter, $params) {
		
		$options = array();
		
		foreach($params as $token) {
			
			if(strpos($token, '-') === 0) {
				
				$name = substr($token, 1);
				$descending = true;
				
			} elseif(strpos($token, ' ') === 0) {
				
				$name = substr($token, 1);
				$descending = false;
				
			} else {
				
				$name = $token;
				$descending = false;
			}
			
			$info = $adapter->getAttributeInfo($name);
			
			if(!$info || !$info->sortable) {
				continue;
			}
			
			$options[] = array(
				'attribute' => $name,
				'descending' => $descending
			);
		}
		
		return $options;
	}
	
	protected function makeModelsResponse(ModelIterator $models, $total = null) {
		
		$pool = $this->createPool();
		
		if($total !== null) {
			$pool->setMeta('total', $total);
		}
		
		$pool->poolModels($models, true);
		
		return new JsonResponse($pool->toArray());
	}
	
	protected function makeModelResponse(ModelProxy $model, $relations = true) {
		
		$pool = $this->createPool();
		$pool->setSingularized($model->getAdapter()->getTypeKey());
		$pool->poolModel($model, $relations);
		
		return new JsonResponse($pool->toArray());
	}
	
	protected function makeErrorResponse($code, $message = null) {
		
		return new JsonResponse(array(
			'error' => array(
				'message' => $message
			)
		), $code);
	}
	
	protected function createPool() {
		return new ModelPool($this->serializer);
	}
	
}
