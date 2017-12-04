<?php
namespace GetCandy\Http\Controllers\Api\Baskets;

use GetCandy\Http\Controllers\Api\BaseController;
use GetCandy\Http\Transformers\Fractal\Baskets\BasketTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Http\Requests\Api\Baskets\CreateRequest;
use GetCandy\Http\Requests\Api\Baskets\UpdateRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BasketController extends BaseController
{

    /**
     * Returns a listing of channels
     * @return Json
     */
    public function index(Request $request)
    {
        $attributes = app('api')->baskets()->getPaginatedData($request->per_page);
        return $this->respondWithCollection($attributes, new BasketTransformer);
    }

    /**
     * Handles the request to show a channel based on it's hashed ID
     * @param  String $id
     * @return Json
     */
    public function show($id)
    {
        try {
            $basket = app('api')->baskets()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }
        return $this->respondWithItem($basket, new BasketTransformer);
    }

    public function store(CreateRequest $request)
    {
        $basket = app('api')->baskets()->create($request->all());
        return $this->respondWithItem($basket, new BasketTransformer);
    }

    public function update($id, UpdateRequest $request)
    {
        try {
            $result = app('api')->baskets()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }
        return $this->respondWithItem($result, new BasketTransformer);
    }
}
