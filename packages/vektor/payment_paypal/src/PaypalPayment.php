<?php

namespace Vektor\Paypal;

use Illuminate\Http\Request;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PaypalPayment
{
    public function handle($stage, Request $request, $client_id, $client_secret)
    {
        if ('create' == $stage) {
            return $this->create($request, $client_id, $client_secret);
        }

        if ('execute' == $stage) {
            return $this->execute($request, $client_id, $client_secret);
        }
    }

    public function create(Request $request, $client_id, $client_secret)
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $client_id,
                $client_secret
            )
        );

        $item_name = 'Test';
        $item_price = $request->input('amount');

        $api_env = 'live';
        $apiContext->setConfig(['mode' => $api_env]);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($item_name)
            ->setCurrency('GBP')
            ->setQuantity(1)
            ->setPrice($item_price)
        ;

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $amount = new Amount();
        $amount->setCurrency('GBP')
            ->setTotal($item_price)
        ;

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription($item_name)
            ->setInvoiceNumber(uniqid())
        ;

        $redirectUrls = new RedirectUrls();
        $redirectUrls
            ->setReturnUrl(url('/').'?success=true')
            ->setCancelUrl(url('/').'?success=false')
        ;

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction])
        ;

        try {
            $payment->create($apiContext);
        } catch (Exception $ex) {
            return [
                'error' => true,
                'error_message' => $ex->getMessage(),
                'http_code' => $ex->getCode(),
            ];
        }

        return [
            'success' => true,
            'success_message' => $request->input('success_message', 'Your transaction was created successfully'),
            'data' => [
                'payment_id' => $payment->getId(),
                'state' => $payment->getState(),
            ],
        ];
    }

    public function execute(Request $request, $client_id, $client_secret)
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $client_id,
                $client_secret
            )
        );

        $api_env = 'live';
        $apiContext->setConfig(['mode' => $api_env]);

        $payment = Payment::get($request->input('paymentID'), $apiContext);

        $execution = new PaymentExecution();
        $execution->setPayerId($request->input('payerID'));

        try {
            $payment->execute($execution, $apiContext);
        } catch (Exception $ex) {
            return [
                'error' => true,
                'error_message' => $ex->getMessage(),
                'http_code' => $ex->getCode(),
            ];
        }

        return [
            'success' => true,
            'success_message' => $request->input('success_message', 'Your transaction was processed successfully'),
            'data' => [
                'state' => $payment->getState(),
            ],
        ];
    }
}
