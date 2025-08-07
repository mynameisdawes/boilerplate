<?php

namespace Vektor\OneCRM\Models;

use Illuminate\Database\Eloquent\Model;
use Vektor\Api\Api;
use Vektor\OneCRM\OneCRM;
use Vektor\OneCRM\OneCRMModel;
use Vektor\OneCRM\Utilities;

class Email extends Model
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
        'new_record',
        'message_id',
        'thread_id',
        'name',
        'to_addrs',
        'from_addrs',
        'from_name',
        'folder',
        'isread',
        'parent_type',
        'parent_id',
        'date_start',
        'account_id',
        'contact_id',
        'description_html',
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
        'message_id',
        'thread_id',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'new_record' => false,
        'isread' => '1',
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
     * Get the model's message_id.
     */
    public function getMessageIdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : Utilities::createMessageId(1, config('mail.from.address'));
    }

    /**
     * Get the model's thread_id.
     */
    public function getThreadIdAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : Utilities::createGUId();
    }

    /**
     * Get the model's date_start.
     */
    public function getDateStartAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : date('Y-m-d H:i:s');
    }

    /**
     * Get the model's from_addrs.
     */
    public function getFromAddrsAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : config('mail.from.address');
    }

    /**
     * Get the model's from_name.
     */
    public function getFromNameAttribute(?string $value): ?string
    {
        return !empty($value) ? $value : config('mail.from.name');
    }

    public function show($id, $data = [])
    {
        $_response = $this->crm_model->show('emails', $id, $data);
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

        $_response = $this->crm_model->create('emails', $data);
        $response = Api::transformResponse($_response);

        if ($response['success']) {
            $this->new_record = true;
            $this->id = $response['data']['record']['id'];

            return $response['data']['record'];
        }

        return null;
    }
}
