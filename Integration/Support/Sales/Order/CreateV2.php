<?php
/**
 * Created by PhpStorm.
 * User: tiagosampaio
 * Date: 28/05/18
 * Time: 17:23
 */

namespace BitTools\SkyHub\Integration\Support\Sales\Order;

use Magento\Sales\Controller\Adminhtml\Order\Create;

class CreateV2 extends Create
{

    public function execute()
    {
        $data = [
            'billing_country_id'       => "",
            'billing_postcode'         => "",
            'billing_regione'          => "",
            'coupon_code'              => "",
            'email'                    => "",
            'entity_id'                => "",
            'in_products'              => "",
            'item'                     => [
                '10' => [
                    'action'       => "",
                    'qty'          => "1",
                    'use_discount' => "1",
                ]
            ],
            'limit'                    => "20",
            'name'                     => "",
            'order'                    => [
                'account'           => [
                    'email'    => "tiago@tiagosampaio.com",
                    'group_id' => "1",
                ],
                'billing_address'   => [
                    'city'                => "Carapicuiba",
                    'company'             => "",
                    'country_id'          => "BR",
                    'customer_address_id' => "3",
                    'fax'                 => "",
                    'firstname'           => "Tiago",
                    'lastname'            => "Sampaio",
                    'middlename'          => "",
                    'postcode'            => "06395010",
                    'prefix'              => "",
                    'region'              => "",
                    'region_id'           => "508",
                    'street'              => [
                        "Av. Marginal, 1455 - Block 5, Apto. 51",
                        "Cidade Ariston"
                    ],
                    'suffix'              => "",
                    'telephone'           => "+5511999210335",
                    'vat_id'              => ""
                ],
                'comment'           => [
                    'customer_note'        => "",
                    'customer_note_notify' => "1"
                ],
                'currency'          => "BRL",
                'send_confirmation' => "1",
                'shipping_method'   => "freeshipping_freeshipping"
            ],
            'page'                     => "1",
            'payment'                  => [
                'method' => "checkmo"
            ],
            'price'                    => [
                'from' => "",
                'to'   => ""
            ],
            'shipping_same_as_billing' => "on",
            'sku'                      => "",
            'store_name'               => "",
            'Telephone'                => ""
        ];

        $this->_getOrderCreateModel()->getQuote()->setCustomerId(2);

        $orderData = $data['order'];

        /**
         * Import post data, in order to make order quote valid
         */
        $this->_getOrderCreateModel()->importPostData($orderData);

        /**
         * Initialize catalog rule data
         */
        $this->_getOrderCreateModel()->initRuleData();

        /**
         * init first billing address, need for virtual products
         */
        $this->_getOrderCreateModel()->getBillingAddress();

        $this->_getOrderCreateModel()->setShippingAsBilling((int) true);
        $this->_getOrderCreateModel()->resetShippingMethod(true);
        $this->_getOrderCreateModel()->collectShippingRates();
    }
}
