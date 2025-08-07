<?php

namespace Vektor\OneCRM;

class OneCRM
{
    public $models = [];

    public $data_models = [];

    public function __construct()
    {
        $this->models = [
            'accounts' => 'Account',
            // 'activities' => 'Activity',
            'cases' => 'aCase',
            'contacts' => 'Contact',
            'emails' => 'Email',
            'invoice_adjustments' => 'InvoiceAdjustment',
            'invoice_comments' => 'InvoiceComment',
            'invoice_line_groups' => 'InvoiceLineGroup',
            'invoice_lines' => 'InvoiceLine',
            'invoices' => 'Invoice',
            'leads' => 'Lead',
            'meetings' => 'Meeting',
            'notes' => 'Note',
            'payments' => 'Payment',
            'product_attributes' => 'ProductAttribute',
            'product_categories' => 'ProductCategory',
            'products' => 'Product',
            // 'properties' => 'Properties',
            'quote_adjustments' => 'QuoteAdjustment',
            'quote_comments' => 'QuoteComment',
            'quote_line_groups' => 'QuoteLineGroup',
            'quote_lines' => 'QuoteLine',
            'quotes' => 'Quote',
            'sales_order_adjustments' => 'SalesOrderAdjustment',
            'sales_order_comments' => 'SalesOrderComment',
            'sales_order_line_groups' => 'SalesOrderLineGroup',
            'sales_order_lines' => 'SalesOrderLine',
            'sales_orders' => 'SalesOrder',
            'shipping' => 'Shipping',
            'shipping_adjustments' => 'ShippingAdjustment',
            'shipping_comments' => 'ShippingComment',
            'shipping_line_groups' => 'ShippingLineGroup',
            'shipping_lines' => 'ShippingLine',
            'tasks' => 'Task',
            'tax_codes' => 'TaxCode',
            'tax_rates' => 'TaxRate',
        ];

        foreach ($this->models as $model_key => $model_value) {
            $this->data_models[$model_key] = 'data/'.$model_value;
        }
    }

    public function get($endpoint, $payload = [])
    {
        $crm = new OneCRMClient();

        try {
            $endpoint = isset($this->data_models[$endpoint]) ? $this->data_models[$endpoint] : $endpoint;
            $result = $crm->client->request('GET', $endpoint, $payload);

            $response = [
                'success' => true,
                'data' => $result,
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

    public function post($endpoint, $payload = [])
    {
        $crm = new OneCRMClient();

        try {
            $endpoint = isset($this->data_models[$endpoint]) ? $this->data_models[$endpoint] : $endpoint;
            $result = $crm->client->request('POST', $endpoint, $payload);

            $response = [
                'success' => true,
                'data' => $result,
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

    public function put($endpoint, $payload = [])
    {
        $crm = new OneCRMClient();

        try {
            $endpoint = isset($this->data_models[$endpoint]) ? $this->data_models[$endpoint] : $endpoint;
            $result = $crm->client->request('PATCH', $endpoint, $payload);

            $response = [
                'success' => true,
                'data' => $result,
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

    public function delete($endpoint, $payload = [])
    {
        $crm = new OneCRMClient();

        try {
            $endpoint = isset($this->data_models[$endpoint]) ? $this->data_models[$endpoint] : $endpoint;
            $result = $crm->client->request('DELETE', $endpoint, $payload);

            $response = [
                'success' => true,
                'data' => $result,
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
