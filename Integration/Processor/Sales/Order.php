<?php

namespace BitTools\SkyHub\Integration\Processor\Sales;

use BitTools\SkyHub\Integration\Context as IntegrationContext;
use BitTools\SkyHub\Integration\Processor\AbstractProcessor;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Address as CustomerAddress;
use Magento\Framework\DataObject;
use Magento\Catalog\Model as CatalogModelDir;
use Magento\Store\Model\Store;

class Order extends AbstractProcessor
{

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var CustomerRepositoryInterface */
    protected $customerRepository;

    /** @var AddressRepositoryInterface */
    protected $addressRepository;

    /** @var AddressFactory */
    protected $addressFactory;


    public function __construct(
        IntegrationContext $integrationContext,
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        AddressFactory $addressFactory
    )
    {
        parent::__construct($integrationContext);

        $this->orderRepository    = $orderRepository;
        $this->productRepository  = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->addressRepository  = $addressRepository;
        $this->addressFactory     = $addressFactory;
    }


    /**
     * @param array $data
     *
     * @return bool|SalesOrder
     */
    public function createOrder(array $data)
    {
        try {
            /** @var SalesOrder $order */
            $order = $this->processOrderCreation($data);
        } catch (\Exception $e) {
            $this->eventManager()->dispatch(
                'bseller_skyhub_order_import_exception', [
                    'exception' => $e,
                    'order_data' => $data,
                ]
            );

            $this->logger()->critical($e);

            return false;
        }

        if ($order && $order->getId()) {
            $this->updateOrderStatus($data, $order);
        }

        return $order;
    }


    /**
     * @param array $data
     *
     * @return bool|SalesOrder
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     * @throws \Exception
     */
    protected function processOrderCreation(array $data)
    {
        $code        = $this->arrayExtract($data, 'code');
        $channel     = $this->arrayExtract($data, 'channel');
        $orderId = $this->getOrderId($code);

        if ($orderId) {
            /**
             * @var SalesOrder $order
             *
             * Order already exists.
             */
            $order = $this->orderRepository->get($orderId);
            return $order;
        }

//        $this->simulateStore($this->getStore());

        $billingAddress  = new DataObject($this->arrayExtract($data, 'billing_address'));
        $shippingAddress = new DataObject($this->arrayExtract($data, 'shipping_address'));

        $customerData = (array) $this->arrayExtract($data, 'customer', []);
        $customerData = array_merge_recursive(
            $customerData,
            [
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress
            ]
        );

        /** @var Customer $customer */
        $customer = $this->getCustomer($customerData);

        $shippingCarrier = (string) $this->arrayExtract($data, 'shipping_carrier');
        $shippingMethod  = (string) $this->arrayExtract($data, 'shipping_method');
        $shippingCost    = (float)  $this->arrayExtract($data, 'shipping_cost', 0.0000);
        $discountAmount  = (float)  $this->arrayExtract($data, 'discount', 0.0000);
        $interestAmount  = (float)  $this->arrayExtract($data, 'interest', 0.0000);

        /** @var \BitTools\SkyHub\Integration\Support\Sales\Order\Create $creator */
        $creator = $this->objectManager()
            ->create(\BitTools\SkyHub\Integration\Support\Sales\Order\Create::class);

        /** @var \BitTools\SkyHub\Helper\Sales\Order $helper */
        $helper = $this->objectManager()->create(\BitTools\SkyHub\Helper\Sales\Order::class);

        $incrementId = $helper->getNewOrderIncrementId($code);
        $info = new DataObject(
            [
                'increment_id' => $incrementId,
                'send_confirmation' => 0
            ]
        );

        $creator->setOrderInfo($info)
            ->setCustomer($customer)
            ->setShippingMethod($shippingMethod, $shippingCarrier, (float) $shippingCost)
            ->setPaymentMethod('bseller_skyhub_standard')
            ->setDiscountAmount($discountAmount)
            ->setInterestAmount($interestAmount)
            ->addOrderAddress('billing', $billingAddress)
            ->addOrderAddress('shipping', $shippingAddress)
            ->setComment('This order was automatically created by SkyHub import process.');

        $products = $this->getProducts((array) $this->arrayExtract($data, 'items'));
        if (empty($products)) {
            throw new \Exception(__('The SkyHub products cannot be matched with Magento products.'));
        }

        /** @var array $productData */
        foreach ((array) $products as $productData) {
            $creator->addProduct($productData);
        }

        /** @var SalesOrder $order */
        $order = $creator->create();

        if (!$order) {
            return false;
        }

        $order->setData('bseller_skyhub', true);
        $order->setData('bseller_skyhub_code', $code);
        $order->setData('bseller_skyhub_channel', $channel);
        $order->setData('bseller_skyhub_json', json_encode($data));

        $this->orderRepository->save($order);

        $order->setData('is_created', true);

        return $order;
    }


    /**
     * @param array      $skyhubOrderData
     * @param SalesOrder $order
     *
     * @return $this
     */
    protected function updateOrderStatus(array $skyhubOrderData, SalesOrder $order)
    {
        $skyhubStatusCode = $this->arrayExtract($skyhubOrderData, 'code');
        $skyhubStatusType = $this->arrayExtract($skyhubOrderData, 'status/type');

        /**
         * @todo Update this code to get the correct processor.
         */
        $this->salesOrderStatusProcessor()
            ->processOrderStatus($skyhubStatusCode, $skyhubStatusType, $order);

        return $this;
    }


    /**
     * @param array $items
     *
     * @return array
     */
    protected function getProducts(array $items)
    {
        $products = [];

        foreach ($items as $item) {
            $parentSku    = $this->arrayExtract($item, 'product_id');
            $childSku     = $this->arrayExtract($item, 'id');
            $qty          = $this->arrayExtract($item, 'qty');

            $price        = (float) $this->arrayExtract($item, 'original_price');
            $specialPrice = (float) $this->arrayExtract($item, 'special_price');

            $finalPrice = $price;
            if (!empty($specialPrice)) {
                $finalPrice = $specialPrice;
            }

            if (!$productId = $this->getProductIdBySku($parentSku)) {
                continue;
            }

            $data = [
                'product_id'    => (int)    $productId,
                'product_sku'   => (string) $parentSku,
                'qty'           => (float)  ($qty ? $qty : 1),
                'price'         => (float)  $price,
                'special_price' => (float)  $specialPrice,
                'final_price'   => (float)  $finalPrice,
            ];

            if ($childId = $this->getProductIdBySku($childSku)) {
                $data['children'][] = [
                    'product_id'  => (int)    $childId,
                    'product_sku' => (string) $childSku,
                ];
            };

            $products[] = $data;
        }

        return $products;
    }


    /**
     * @param string $sku
     *
     * @return bool|CatalogModelDir\Product
     */
    protected function getProductBySku($sku)
    {
        try {
            /** @var CatalogModelDir\Product $product */
            $product = $this->productRepository->get($sku);
            return $product;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * @param string $sku
     *
     * @return false|int
     */
    protected function getProductIdBySku($sku)
    {
        /** @var CatalogModelDir\ResourceModel\Product $resource */
        $resource  = $this->objectManager()->create(CatalogModelDir\ResourceModel\Product::class);
        $productId = $resource->getIdBySku($sku);

        return $productId;
    }


    /**
     * @param array $data
     *
     * @return Customer
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    protected function getCustomer(array $data)
    {
        $email = $this->arrayExtract($data, 'email');

        try {
            /** @var Customer $customer */
            $customer = $this->customerRepository->get($email);
            $customer->setStore($this->getStore());
            $customer->loadByEmail($email);
        } catch (\Exception $e) {
            $this->createCustomer($data, $customer);
        }

        return $customer;
    }


    /**
     * @param array    $data
     * @param Customer $customer
     *
     * @return Customer
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    protected function createCustomer(array $data, Customer $customer)
    {
        $customer->setStore($this->getStore());

        $dateOfBirth = $this->arrayExtract($data, 'date_of_birth');
        $email       = $this->arrayExtract($data, 'email');
        $gender      = $this->arrayExtract($data, 'gender');
        $name        = $this->arrayExtract($data, 'name');
        $phones      = $this->arrayExtract($data, 'phones', []);

        /** @var DataObject $nameObject */
        $nameObject = $this->breakName($name);

        $customer->setFirstname($nameObject->getData('firstname'));
        $customer->setLastname($nameObject->getData('lastname'));
        $customer->setMiddlename($nameObject->getData('middlename'));
        $customer->setEmail($email);
        $customer->setDob($dateOfBirth);

        $this->setPersonTypeInformation($data, $customer);

        /** @var string $phone */
        foreach ($phones as $phone) {
            $customer->setTelephone($phone);
            break;
        }

        switch ($gender) {
            case 'male':
                $customer->setGender(1);
                break;
            case 'female':
                $customer->setGender(2);
                break;
        }

        $this->customerRepository->save($customer);

        /** @var DataObject $billing */
        if ($billing = $this->arrayExtract($data, 'billing_address')) {
            $address = $this->createCustomerAddress($billing);
            $address->setCustomer($customer);
        }

        /** @var DataObject $billing */
        if ($shipping = $this->arrayExtract($data, 'shipping_address')) {
            $address = $this->createCustomerAddress($shipping);
            $address->setCustomer($customer);
        }

        return $customer;
    }


    /**
     * @param DataObject $addressObject
     *
     * @return CustomerAddress
     */
    protected function createCustomerAddress(DataObject $addressObject)
    {
        /** @var CustomerAddress $address */
        $address = $this->addressFactory->create();

        /**
         * @todo Create customer address algorithm based on $addressObject.
         */
        return $address;
    }


    /**
     * @param Store $store
     *
     * @return $this
     */
    protected function simulateStore(Store $store)
    {
        $this->storeManager()->setCurrentStore($store);
        return $this;
    }


    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStore()
    {
        return $this->storeManager()->getStore();
    }


    /**
     * @param string $code
     *
     * @return int
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getOrderId($code)
    {
        /** @var \BitTools\SkyHub\Model\ResourceModel\Sales\Order $orderResource */
        $orderResource = $this->objectManager()->create(\BitTools\SkyHub\Model\ResourceModel\Sales\Order::class);
        $orderId       = $orderResource->getEntityIdBySkyhubCode($code);

        return $orderId;
    }


    /**
     * @param string $code
     *
     * @return string | null
     */
    protected function getOrderIncrementId($code)
    {
        /**
         * @todo Check if this is really necessary.
         */
        $useDefaultIncrementId = $this->getSkyHubModuleConfig('use_default_increment_id', 'cron_sales_order_queue');

        if (!$useDefaultIncrementId) {
            return $code;
        }

        return null;
    }

    /**
     * @param $data
     * @param $customer
     *
     * @return void
     */
    protected function setPersonTypeInformation($data, $customer)
    {
        /**
         * @todo Check this entire method.
         */

        //get the vat number
        $vatNumber = $this->arrayExtract($data, 'vat_number');
        //the taxvat is filled anyway
        $customer->setTaxvat($vatNumber);
        //check if is a PJ customer (if not, it's a PF customer)
        $customerIsPj = $this->customerIsPj($vatNumber);

        //get customer mapped attributes
        $mappedCustomerAttributes = $this->getMappedAttributes();

        //if the store has the attribute "person_type" mapped
        if (isset($mappedCustomerAttributes['person_type'])) {
            $personTypeAttributeId = $mappedCustomerAttributes['person_type']->getAttributeId();
            $personTypeAttribute = $this->getAttributeById($personTypeAttributeId);

            if ($customerIsPj) {
                $personTypeAttributeValue = $this->getAttributeMappingOptionMagentoValue('person_type', 'legal_person');
            } else {
                $personTypeAttributeValue = $this->getAttributeMappingOptionMagentoValue('person_type', 'physical_person');
            }
            $customer->setData($personTypeAttribute->getAttributeCode(), $personTypeAttributeValue);
        }

        if ($customerIsPj) {
            //set the mapped PJ attribute value on customer if exists
            if (isset($mappedCustomerAttributes['cnpj'])) {
                $mappedAttribute = $mappedCustomerAttributes['cnpj'];
                $attribute = $this->getAttributeById($mappedAttribute->getAttributeId());
                $customer->setData($attribute->getAttributeCode(), $vatNumber);
            }
        } else {
            //set the mapped PF attribute value on customer if exists
            if (isset($mappedCustomerAttributes['cpf'])) {
                $mappedAttribute = $mappedCustomerAttributes['cpf'];
                $attribute = $this->getAttributeById($mappedAttribute->getAttributeId());
                $customer->setData($attribute->getAttributeCode(), $vatNumber);
            }
        }

        //set the mapped IE attribute value on customer if exists
        if (isset($mappedCustomerAttributes['ie'])) {
            $mappedAttribute = $mappedCustomerAttributes['ie'];
            $attribute = $this->getAttributeById($mappedAttribute->getAttributeId());
            $customer->setData($attribute->getAttributeCode(), $this->arrayExtract($data, 'state_registration'));
        }

        //set the mapped IE attribute value on customer if exists
        if (isset($mappedCustomerAttributes['social_name'])) {
            $mappedAttribute = $mappedCustomerAttributes['social_name'];
            $attribute = $this->getAttributeById($mappedAttribute->getAttributeId());
            $customer->setData($attribute->getAttributeCode(), $this->arrayExtract($data, 'name'));
        }
    }
}
