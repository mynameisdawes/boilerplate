<?php

namespace Vektor\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class RestApiController extends Controller
{
    protected $model_name;

    protected $model_class;

    protected $resource_class;

    public function __construct()
    {
        $this->model_name = str_replace(['App\Http\Controllers\Api\\', 'Controller'], '', get_called_class());
        $this->model_class = 'App\\'.$this->model_name;
        $this->resource_class = 'App\Http\Resources\\'.$this->model_name.'Resource';
    }

    /**
     * Display a listing of the resource by search terms.
     *
     * @param  Request
     *
     * @return Response
     */
    public function search(Request $request): JsonResponse
    {
        $fields = !empty($request->input('f')) ? explode(',', str_replace(', ', ',', $request->input('f'))) : null;

        return response()->json(transformResponse([
            'success' => true,
            'data' => $this->resource_class::collection($this->model_class::search($request->input('s'), $fields)->get()),
        ]));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): JsonResponse
    {
        return response()->json(transformResponse([
            'success' => true,
            'data' => $this->resource_class::collection($this->model_class::all()),
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request): JsonResponse
    {
        $model = new $this->model_class();

        $fields = $model->getFillable();
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $model->{$field} = $request->input($field);
            }
        }

        $model->save();

        return response()->json(transformResponse([
            'success' => true,
            'success_message' => 'The '.strtolower($this->model_name).' was created successfully',
            'data' => [
                'id' => $model->id,
            ],
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id): JsonResponse
    {
        $model = $this->model_class::find($id);

        if ($model) {
            return response()->json(transformResponse([
                'success' => true,
                'data' => new $this->resource_class($model),
            ]));
        }

        return response()->json(transformResponse([
            'error' => true,
            'error_message' => 'The '.strtolower($this->model_name).' could not be found',
            'http_code' => 404,
            'http_message' => 'Not Found',
        ]), 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update(Request $request, $id): JsonResponse
    {
        $model = $this->model_class::find($id);

        if ($model) {
            $fields = $model->getFillable();
            foreach ($fields as $field) {
                if ($request->filled($field)) {
                    $model->{$field} = $request->input($field);
                }
            }

            $model->save();

            return response()->json(transformResponse([
                'success' => true,
                'success_message' => 'The '.strtolower($this->model_name).' was updated successfully',
                'data' => [
                    'id' => $model->id,
                ],
            ]));
        }

        return response()->json(transformResponse([
            'error' => true,
            'error_message' => 'The '.strtolower($this->model_name).' could not be found',
            'http_code' => 404,
            'http_message' => 'Not Found',
        ]), 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id): JsonResponse
    {
        $model = $this->model_class::find($id);

        if ($model) {
            $model->delete();

            return response()->json(transformResponse([
                'success' => true,
                'success_message' => 'The '.strtolower($this->model_name).' was deleted successfully',
            ]));
        }

        return response()->json(transformResponse([
            'error' => true,
            'error_message' => 'The '.strtolower($this->model_name).' could not be found',
            'http_code' => 404,
            'http_message' => 'Not Found',
        ]), 404);
    }
}
