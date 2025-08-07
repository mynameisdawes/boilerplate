<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;

class Task extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public $crm;

    public $crm_model;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'assigned_user_id',
        'name',
        'status',
        'date_due',
        'parent_type',
        'parent_id',
        'account_id',
        'priority',
        'description',
        'production_stream',
        'stock_status',
        'fixed_deadline',
        'needs_artwork',
        'new_record',
        'artwork_status',
    ];

    protected $casts = [
        'new_record' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'date_due',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'assigned_user_id' => '55a86af3-9af2-12b2-090d-4cce83574f07',
        'new_record' => false,
        'status' => 'Not Started',
        'parent_type' => 'SalesOrders',
        'priority' => 'P1',
        'production_stream' => 'dtg',
        'stock_status' => 'not_ordered',
        'fixed_deadline' => '0',
        'needs_artwork' => '0',
    ];

    /**
     * The attributes that are not to be used to update.
     *
     * @var array
     */
    protected $excluded_update_attributes = [
    ];

    public function __construct()
    {
        $this->crm = new OneCRM();
        $this->crm_model = new OneCRMModel();

        return $this;
    }

    /**
     * Get the model's date_due.
     */
    public function getDateDueAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : $this->generateTaskDueDate();
    }

    public function show($id, $data = [])
    {
        $_response = $this->crm_model->show('tasks', $id, $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }

    public function persist()
    {
        $data = $this->toArray();
        unset($data['new_record']);

        $_response = $this->crm_model->create('tasks', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }

    private function generateTaskDueDate()
    {
        date_default_timezone_set('Europe/London');
        $day = date('N');
        $hour = date('H');
        $conditions_for_tues = [
            'is_after_weds' => $day > 3,
            'is_after_2pm_weds' => 3 === $day && $hour >= 14,
            'is_before_9am_mon' => 1 === $day && $hour < 9,
        ];
        $str_time = in_array(true, $conditions_for_tues) ? 'next monday 2pm' : 'next thursday 2pm';

        return date('Y-m-d H:i:s', strtotime($str_time));
    }
}
