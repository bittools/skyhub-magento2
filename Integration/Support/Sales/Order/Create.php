<?php

namespace BitTools\SkyHub\Integration\Support\Sales\Order;

use BitTools\SkyHub\Functions;
use BitTools\SkyHub\Integration\Context as IntegrationContext;
use BitTools\SkyHub\Model\Backend\Session\Quote as AdminSessionQuote;
use Magento\Customer\Model\Customer;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Magento\Store\Api\Data\StoreInterface;
use BitTools\SkyHub\Model\Sales\AdminOrder\Create as AdminOrderCreate;

class Create
{

    use Functions, \BitTools\SkyHub\Traits\Customer;

    const CARRIER_PREFIX = 'bseller_skyhub_';

    /** @var StoreInterface */
    private $store;

    /** @var array */
    private $data = [];

    /** @var AdminOrderCreate */
    protected $creator;

    /** @var IntegrationContext */
    protected $context;


    /**
     * Create constructor.
     *
     * @param IntegrationContext $context
     * @param null               $store
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(IntegrationContext $context, $store = null)
    {
        $this->context = $context;

        $data = [
            'session' => [
                'store_id' => $this->getStore($store)->getId(),
            ],
            'order'   => [
                'currency' => $this->getStore($store)->getCurrentCurrencyCode(),
            ],
        ];

        $this->merge($data);
    }


    /**
     * @param DataObject $order
     *
     * @return $this
     */
    public function setOrderInfo(DataObject $order)
    {
        $data = [
            'order' => [
                'increment_id'      => $order->getData('increment_id'),
                'send_confirmation' => $order->getData('send_confirmation')
            ],
        ];

        $this->merge($data);

        return $this;
    }


    /**
     * @param null|string $comment
     *
     * @return $this
     */
    public function setComment($comment = null)
    {
        $data = [
            'order' => [
                'comment' => [
                    'customer_note' => $comment,
                ]
            ],
        ];

        $this->merge($data);

        return $this;
    }


    /**
     * @param array $data
     *
     * @return $this
     */
    public function addProduct(array $data)
    {
        $productId = (int)  $this->arrayExtract($data, 'product_id');
        $qty       = (float) $this->arrayExtract($data, 'qty');

        if (!$productId) {
            return $this;
        }

        $data = [
            'products' => [
                [
                    'product' => $data,
                    'config'  => [
                        'qty' => $qty ? $qty : 1,
                    ],
                ]
            ]
        ];

        $this->merge($data);

        return $this;
    }


    /**
     * @param string $method
     *
     * @return $this
     */
    public function setPaymentMethod($method = 'checkmo')
    {
        $data = [
            'payment' => [
                'method' => $method,
            ]
        ];

        $this->merge($data);

        return $this;
    }


    /**
     * @param float $discount
     *
     * @return $this
     */
    public function setDiscountAmount($discount)
    {
        $data = [
            'order' => [
                'discount_amount' => (float) $discount,
            ]
        ];

        $this->merge($data);

        return $this;
    }


    /**
     * @param float $discount
     *
     * @return $this
     */
    public function setInterestAmount($discount)
    {
        $data = [
            'order' => [
                'interest' => (float) $discount,
            ]
        ];

        $this->merge($data);

        return $this;
    }


    /**
     * @param null  $title
     * @param null  $carrier
     * @param float $cost
     *
     * @return $this
     */
    public function setShippingMethod($title = null, $carrier = null, $cost = 0.0000)
    {
        if (!$title) {
            $title = 'Standard';
        }

        /** @var string $methodCode */
        $methodCode = $this->normalizeString($title);

        $data = [
            'order' => [
                'shipping_method'        => self::CARRIER_PREFIX.$methodCode,
                'shipping_method_code'   => $methodCode,
                'shipping_title'         => $title,
                'shipping_carrier'       => $carrier,
                'shipping_cost'          => (float) $cost,
            ]
        ];

        $this->merge($data);

        return $this;
    }


    /**
     * @param Customer $customer
     *
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $data = [
            'order' => [
                'account' => [
                    'group_id' => $customer->getGroupId(),
                    'email'    => $customer->getEmail()
                ]
            ],
            'session' => [
                'customer_id' => $customer->getId()
            ]
        ];

        $this->merge($data);

        return $this;
    }


    /**
     * @param string     $type
     * @param DataObject $address
     *
     * @return $this
     */
    public function addOrderAddress($type, DataObject $address)
    {
        $fullname = trim($address->getData('full_name'));

        /** @var DataObject $nameObject */
        $nameObject = $this->breakName($fullname);

        $addressSize = $this->getAddressSizeConfig();

        $simpleAddressData = $this->formatAddress($address, $addressSize);

        $data = [
            'order' => [
                "{$type}_address" => [
                    'customer_address_id' => $address->getData('customer_address_id'),
                    'prefix'              => '',
                    'firstname'           => $nameObject->getData('firstname'),
                    'middlename'          => $nameObject->getData('middlename'),
                    'lastname'            => $nameObject->getData('lastname'),
                    'suffix'              => '',
                    'company'             => '',
                    'street'              => $simpleAddressData,
                    'city'                => $address->getData('city'),
                    'country_id'          => $address->getData('country'),
                    'region'              => $address->getData('region'),
                    'region_id'           => '',
                    'postcode'            => $address->getData('postcode'),
                    'telephone'           => $this->formatPhone($address->getData('phone')),
                    'fax'                 => $address->getData('secondary_phone'),
                ]
            ]
        ];

        $this->merge($data);

        return $this;
    }


    /**
     * @return $this
     */
    public function reset()
    {
        $this->creator = null;
        $this->data    = [];
        $this->store   = null;

        return $this;
    }


    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->getOrderCreator()->getQuote();
    }


    /**
     * @return $this
     */
    protected function resetQuote()
    {
        $this->getQuote()
            ->setTotalsCollectedFlag(false);

        return $this;
    }


    /**
     * @return AdminSessionQuote
     */
    protected function getSession()
    {
        /** @var AdminSessionQuote $session */
        $session = $this->context->objectManager()->get(AdminSessionQuote::class);
        return $session;
    }


    /**
     * Retrieve order create model
     *
     * @return \BitTools\SkyHub\Model\Sales\AdminOrder\Create
     */
    protected function getOrderCreator()
    {
        if (!$this->creator) {
            $this->creator = $this->context
                ->objectManager()
                ->create(\BitTools\SkyHub\Model\Sales\AdminOrder\Create::class);
        }

        return $this->creator;
    }


    /**
     * Initialize order creation session data
     *
     * @param array $data
     *
     * @return $this
     */
    protected function initSession($data)
    {
        /* Get/identify customer */
        if (!empty($data['customer_id'])) {
            $this->getSession()->setCustomerId((int) $this->arrayExtract($data, 'customer_id'));
        }

        /* Get/identify store */
        if (!empty($data['store_id'])) {
            $this->getSession()->setStoreId((int) $this->arrayExtract($data, 'store_id'));
        }

        return $this;
    }


    /**
     * @return Order|null
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create()
    {
        $registry = $this->context->helperContext()->registryManager();

        $orderData = $this->data;
        $order     = null;

        if (!empty($orderData)) {
            $this->initSession($this->arrayExtract($orderData, 'session'));

            $this->processQuote($orderData);
            $payment = $this->arrayExtract($orderData, 'payment');

            if (!empty($payment)) {
                $this->getOrderCreator()
                    ->setPaymentData($payment);

                $this->getQuote()
                    ->getPayment()
                    ->addData($payment);
            }

            /** @todo Find another way to avoid customer e-mail sending */
            // $this->context->helperContext()->scopeConfig()->setConfig(Order::XML_PATH_EMAIL_ENABLED, "0");

            $this->context->eventManager()->dispatch('bseller_skyhub_order_import_before', [
                'order'      => $order,
                'order_data' => $orderData,
                'creator'    => $this,
            ]);

            /** @var Order $order */
            $order = $this->getOrderCreator()
                ->importPostData($this->arrayExtract($orderData, 'order'))
                ->createOrder();

            $this->context->eventManager()->dispatch('bseller_skyhub_order_import_success', [
                'order'      => $order,
                'order_data' => $orderData,
            ]);

            $this->getSession()->clear();
            $registry->unregister('rule_data');
        }

        return $order;
    }


    /**
     * @param array $data
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processQuote($data = array())
    {
        $order = (array) $this->arrayExtract($data, 'order', []);

        /* Saving order data */
        if (!empty($order)) {
            $this->getOrderCreator()->importPostData($order);
            $this->getQuote()
                ->setReservedOrderId($this->arrayExtract($order, 'increment_id'));
        }

        /* Just like adding products from Magento admin grid */
        $products = (array) $this->arrayExtract($data, 'products', []);

        /** @var array $product */
        foreach ($products as $item) {
            $this->getOrderCreator()->addProductByData($item);
        }

        $this->registerDiscount($order);
        $this->registerInterest($order);

        $shippingMethod       = (string) $this->arrayExtract($data, 'order/shipping_method');
        $shippingMethodCode   = (string) $this->arrayExtract($data, 'order/shipping_method_code');
        $shippingCarrier      = (string) $this->arrayExtract($data, 'order/shipping_carrier');
        $shippingTitle        = (string) $this->arrayExtract($data, 'order/shipping_title');
        $shippingAmount       = (float) $this->arrayExtract($data, 'order/shipping_cost');

        $this->getQuote()
            ->setFixedShippingAmount($shippingAmount)
            ->setFixedShippingMethod($shippingMethod)
            ->setFixedShippingMethodCode($shippingMethodCode)
            ->setFixedShippingCarrier($shippingCarrier)
            ->setFixedShippingTitle($shippingTitle);

        /* Collect shipping rates */
        $this->resetQuote()
            ->getOrderCreator()
            ->collectShippingRates();

        /* Add payment data */
        $payment = $this->arrayExtract($data, 'payment', []);
        if (!empty($payment)) {
            $this->getOrderCreator()
                ->getQuote()
                ->getPayment()
                ->addData($payment);
        }

        $this->getOrderCreator()
            ->initRuleData()
            ->saveQuote();

        if (!empty($payment)) {
            $this->getOrderCreator()
                ->getQuote()
                ->getPayment()
                ->addData($payment);
        }

        return $this;
    }


    /**
     * @param array $data
     *
     * @return $this
     */
    protected function registerDiscount(array $data)
    {
        $registry = $this->context->helperContext()->registryManager();

        $key = 'bseller_skyhub_discount_amount';
        if ($registry->registry($key)) {
            $registry->unregister($key);
        }

        $discount = (float) $this->arrayExtract($data, 'discount_amount');

        if (!$discount) {
            return $this;
        }

        $registry->register($key, $discount, true);

        return $this;
    }


    /**
     * @param array $data
     *
     * @return $this
     */
    protected function registerInterest(array $data)
    {
        $registry = $this->context->helperContext()->registryManager();

        $key = 'bseller_skyhub_interest';
        if ($registry->registry($key)) {
            $registry->unregister($key);
        }

        $interest = (float) $this->arrayExtract($data, 'interest');

        if (!$interest) {
            return $this;
        }

        $registry->register($key, $interest, true);

        return $this;
    }


    /**
     * @param array $data
     *
     * @return $this
     */
    protected function merge(array $data = [])
    {
        $this->data = array_merge_recursive($this->data, $data);

        return $this;
    }


    /**
     * @param null|StoreInterface|int $store
     *
     * @return StoreInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStore($store = null)
    {
        if (empty($store)) {
            $store = null;
        }

        if (!$this->store) {
            $this->store = $this->context->helperContext()->storeManager()->getStore($store);
        }

        return $this->store;
    }
}
