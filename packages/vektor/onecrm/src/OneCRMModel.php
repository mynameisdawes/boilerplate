<?php

namespace Vektor\OneCRM;

class OneCRMModel
{
    protected $models;

    protected $model_name;

    public function __construct()
    {
        $crm = new OneCRM();
        $this->models = $crm->models;
    }

    public function index($model_name, $payload = [])
    {
        $crm = new OneCRMClient();
        $model_name = isset($this->models[$model_name]) ? $this->models[$model_name] : $model_name;
        $_model = $crm->client->model($model_name);

        try {
            $params = $payload;
            if (isset($params['page'])) {
                unset($params['page']);
            }
            if (isset($params['per_page'])) {
                unset($params['per_page']);
            }

            $page = intval((isset($payload['page']) && !empty($payload['page'])) ? $payload['page'] : 0);
            $page = ((0 == $page) ? 0 : ($page - 1));
            $per_page = intval((isset($payload['per_page']) && !empty($payload['per_page'])) ? $payload['per_page'] : 20);
            $offset = $page * $per_page;

            $collection = $_model->getList($params, $offset, $per_page);
            $records = $collection->getRecords();
            $total = $collection->totalResults();

            $response = [
                'success' => true,
                'data' => [
                    'records' => $records,
                    'pagination' => [
                        'page' => $page + 1,
                        'per_page' => $per_page,
                        'pages' => ceil($total / $per_page),
                        'visible_records' => count($records),
                        'total_records' => $total,
                    ],
                ],
            ];
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'error_message' => trim($e->getMessage()),
                'http_code' => $e->getCode(),
            ];
        }

        return $response;
    }

    public function index_related($model_name, $model_id, $related_model_name, $payload = [])
    {
        $crm = new OneCRMClient();
        $model_name = isset($this->models[$model_name]) ? $this->models[$model_name] : $model_name;
        $_model = $crm->client->model($model_name);

        try {
            $params = $payload;
            if (isset($params['page'])) {
                unset($params['page']);
            }
            if (isset($params['per_page'])) {
                unset($params['per_page']);
            }

            $page = intval((isset($payload['page']) && !empty($payload['page'])) ? $payload['page'] : 0);
            $page = ((0 == $page) ? 0 : ($page - 1));
            $per_page = intval((isset($payload['per_page']) && !empty($payload['per_page'])) ? $payload['per_page'] : 20);
            $offset = $page * $per_page;

            $collection = $_model->getRelated($model_id, $related_model_name, $params, $offset, $per_page);
            $records = $collection->getRecords();
            $total = $collection->totalResults();

            $response = [
                'success' => true,
                'data' => [
                    'records' => $records,
                    'pagination' => [
                        'page' => $page + 1,
                        'per_page' => $per_page,
                        'pages' => ceil($total / $per_page),
                        'visible_records' => count($records),
                        'total_records' => $total,
                    ],
                ],
            ];
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'error_message' => trim($e->getMessage()),
                'http_code' => $e->getCode(),
            ];
        }

        return $response;
    }

    public function create($model_name, $payload = [])
    {
        $crm = new OneCRMClient();
        $model_name = isset($this->models[$model_name]) ? $this->models[$model_name] : $model_name;
        $_model = $crm->client->model($model_name);

        try {
            if (empty($payload)) {
                throw new \Exception('No data provided', 400);
            }
            $id = $_model->create($payload);

            try {
                $model = $_model->get($id, array_keys($payload));
            } catch (\Exception $e) {
                $model = [
                    'id' => $id,
                ];
            }

            $response = [
                'success' => true,
                'http_code' => 201,
                'data' => [
                    'record' => $model,
                ],
            ];
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'error_message' => trim($e->getMessage()),
                'http_code' => $e->getCode(),
            ];
        }

        return $response;
    }

    public function create_related($model_name, $model_id, $related_model_name, $payload = [])
    {
        $crm = new OneCRMClient();
        $model_name = isset($this->models[$model_name]) ? $this->models[$model_name] : $model_name;
        $_model = $crm->client->model($model_name);

        try {
            if (empty($payload)) {
                throw new \Exception('No data provided', 400);
            }
            $success = $_model->addRelated($model_id, $related_model_name, $payload);

            $response = [
                'success' => $success,
                'http_code' => 201,
            ];
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'error_message' => trim($e->getMessage()),
                'http_code' => $e->getCode(),
            ];
        }

        return $response;
    }

    public function tally($model_name, $model_id, $payload = [])
    {
        $crm = new OneCRMClient();
        $model_name = isset($this->models[$model_name]) ? $this->models[$model_name] : $model_name;
        $_model = $crm->client->model($model_name);

        try {
            $model = $_model->tally($model_id, $payload);

            $response = [
                'success' => true,
                'data' => $model,
            ];
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'error_message' => trim($e->getMessage()),
                'http_code' => $e->getCode(),
            ];
        }

        return $response;
    }

    public function show($model_name, $model_id, $payload = [])
    {
        $crm = new OneCRMClient();
        $model_name = isset($this->models[$model_name]) ? $this->models[$model_name] : $model_name;
        $_model = $crm->client->model($model_name);

        try {
            $model = $_model->get($model_id, $payload);

            $response = [
                'success' => true,
                'data' => [
                    'record' => $model,
                ],
            ];
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'error_message' => trim($e->getMessage()),
                'http_code' => $e->getCode(),
            ];
        }

        return $response;
    }

    public function update($model_name, $model_id, $payload = [])
    {
        $crm = new OneCRMClient();
        $model_name = isset($this->models[$model_name]) ? $this->models[$model_name] : $model_name;
        $_model = $crm->client->model($model_name);

        try {
            if (empty($payload)) {
                throw new \Exception('No data provided', 400);
            }
            $success = $_model->update($model_id, $payload);

            $response = [
                'success' => $success,
            ];
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'error_message' => trim($e->getMessage()),
                'http_code' => $e->getCode(),
            ];
        }

        return $response;
    }

    public function destroy($model_name, $model_id)
    {
        $crm = new OneCRMClient();
        $model_name = isset($this->models[$model_name]) ? $this->models[$model_name] : $model_name;
        $_model = $crm->client->model($model_name);

        try {
            $success = $_model->delete($model_id);

            $response = [
                'success' => $success,
            ];
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'error_message' => trim($e->getMessage()),
                'http_code' => $e->getCode(),
            ];
        }

        return $response;
    }

    public function metadata($model_name)
    {
        $crm = new OneCRMClient();
        $model_name = isset($this->models[$model_name]) ? $this->models[$model_name] : $model_name;
        $_model = $crm->client->model($model_name);

        try {
            $success = $_model->metadata($model_name);

            $response = [
                'success' => $success,
            ];
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'error_message' => trim($e->getMessage()),
                'http_code' => $e->getCode(),
            ];
        }

        return $response;
    }
}
