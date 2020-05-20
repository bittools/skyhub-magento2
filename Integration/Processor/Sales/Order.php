<?php

namespace BitTools\SkyHub\Integration\Processor\Sales;

use BitTools\SkyHub\Api\OrderRepositoryInterface as SkyhubOrderRepositoryInterface;
use BitTools\SkyHub\Integration\Context as IntegrationContext;
use BitTools\SkyHub\Integration\Processor\AbstractProcessor;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Model\Data\AddressFactory;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model as CatalogModelDir;
use Magento\Store\Model\Store;
use BitTools\SkyHub\Integration\Support\Sales\Order\CreateFactory as OrderCreatorFactory;
use BitTools\SkyHub\Helper\Sales\Order as OrderHelper;
use BitTools\SkyHub\Integration\Processor\Sales\Order\Status as StatusProcessor;
use BitTools\SkyHub\Api\Data\OrderInterfaceFactory;
use BitTools\SkyHub\Helper\Customer\Attribute\Mapping as CustomerAttributeMappingHelper;
use BitTools\SkyHub\Exceptions\UnprocessableException;

class Order extends AbstractProcessor
{

    use \BitTools\SkyHub\Traits\Customer;

    /** @var string */
    const ADDRESS_TYPE_BILLING  = \BitTools\SkyHub\Integration\Support\Sales\Order\Create::ADDRESS_TYPE_BILLING;

    /** @var string */
    const ADDRESS_TYPE_SHIPPING = \BitTools\SkyHub\Integration\Support\Sales\Order\Create::ADDRESS_TYPE_SHIPPING;


    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var SkyhubOrderRepositoryInterface */
    protected $skyhubOrderRepository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var CustomerRepositoryInterface */
    protected $customerRepository;

    /** @var AddressRepositoryInterface */
    protected $addressRepository;

    /** @var AddressFactory */
    protected $addressFactory;

    /** @var CountryFactory */
    protected $countryFactory;

    /** @var CustomerInterfaceFactory */
    protected $customerFactory;

    /** @var RegionFactory */
    protected $regionFactory;

    /** @var RegionInterfaceFactory */
    protected $regionDataFactory;

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var OrderCreatorFactory */
    protected $orderCreatorFactory;

    /** @var OrderHelper */
    protected $orderHelper;

    /** @var StatusProcessor */
    protected $statusProcessor;

    /** @var OrderInterfaceFactory */
    protected $orderFactory;

    /** @var CustomerAttributeMappingHelper  */
    protected $customerAttributeMappingHelper;

    /** @var array|AddressInterface[] */
    protected $addresses = [
        self::ADDRESS_TYPE_BILLING  => null,
        self::ADDRESS_TYPE_SHIPPING => null,
    ];

    /**
     * @var array
     */
    protected $regions = array();

    /**
     * @var bool
     */
    protected $foundByTaxvat = false;

    public function __construct(
        IntegrationContext $integrationContext,
        OrderRepositoryInterface $orderRepository,
        SkyhubOrderRepositoryInterface $skyhubOrderRepository,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        AddressFactory $addressFactory,
        CountryFactory $countryFactory,
        CustomerInterfaceFactory $customerFactory,
        RegionFactory $regionFactory,
        RegionInterfaceFactory $regionDataFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderCreatorFactory $orderCreatorFactory,
        OrderHelper $orderHelper,
        StatusProcessor $statusProcessor,
        OrderInterfaceFactory $orderFactory,
        CustomerAttributeMappingHelper $customerAttributeMappingHelper
    )
    {
        parent::__construct($integrationContext);

        $this->orderRepository       = $orderRepository;
        $this->skyhubOrderRepository = $skyhubOrderRepository;
        $this->productRepository     = $productRepository;
        $this->customerRepository    = $customerRepository;
        $this->addressRepository     = $addressRepository;
        $this->addressFactory        = $addressFactory;
        $this->countryFactory        = $countryFactory;
        $this->customerFactory       = $customerFactory;
        $this->regionFactory         = $regionFactory;
        $this->regionDataFactory     = $regionDataFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderCreatorFactory   = $orderCreatorFactory;
        $this->orderHelper           = $orderHelper;
        $this->statusProcessor       = $statusProcessor;
        $this->orderFactory          = $orderFactory;
        $this->customerAttributeMappingHelper = $customerAttributeMappingHelper;
    }

    /**
     * @param array $data
     *
     * @return bool|SalesOrder
     *
     * @throws Exception
     * @throws UnprocessableException
     */
    public function createOrder(array $data)
    {
        try {
            /** @var SalesOrder $order */
            $order = $this->processOrderCreation($data);
        }  catch (UnprocessableException $e) {
            $this->eventManager()
                ->dispatch('bittools_skyhub_order_import_exception', [
                    'exception' => $e,
                    'order_data' => $data,
                ]);

            throw $e;
        } catch (Exception $e) {
            $this->eventManager()
                ->dispatch('bittools_skyhub_order_import_exception', [
                    'exception' => $e,
                    'order_data' => $data,
                ]);

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
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     * @throws Exception
     * @throws UnprocessableException
     */
    protected function processOrderCreation(array $data)
    {
        $order   = null;
        $code    = $this->arrayExtract($data, 'code');
        $channel = $this->arrayExtract($data, 'channel');
        $orderId = $this->getOrderId($code);
        $status = $this->statusProcessor->getStateBySkyhubStatusType(
            $this->arrayExtract($data, 'status/type')
        );

        if ($orderId) {
            /**
             * @var SalesOrder $order
             *
             * Order already exists.
             */
            $order = $this->orderRepository->get($orderId);
        } else if ($status == SalesOrder::STATE_CANCELED) {
            $exceptText = $code . " Order doesn't create, because status is " . SalesOrder::STATE_CANCELED;
            throw new UnprocessableException($exceptText);
        }

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

        /** @var CustomerInterface $customer */
        $customer = $this->getCustomer($customerData);

        $paymentMethod   = \BitTools\SkyHub\Model\Payment\Method\Standard::CODE;

        $shippingCarrier = \BitTools\SkyHub\Model\Shipping\Carrier\Standard::CODE;
        $shippingTitle   = (string) $this->arrayExtract($data, 'shipping_method');
        $shippingTitle  = $this->getShippingMethodConfig($shippingTitle, $channel);
        $shippingCost    = (float)  $this->arrayExtract($data, 'shipping_cost', 0.0000);
        $discountAmount  = (float)  $this->arrayExtract($data, 'discount', 0.0000);
        $interestAmount  = (float)  $this->arrayExtract($data, 'interest', 0.0000);

        if ($order && $this->foundByTaxvat) {
            $order
                ->setCustomerEmail($customer->getEmail())
                ->setCustomerFirstname($customer->getFirstname())
                ->setCustomerMiddlename($customer->getMiddlename())
                ->setCustomerLastname($customer->getLastname())
                ->setCustomerGender($customer->getGender());

            $this->updateOrderAddressData($customer, $order->getBillingAddress(), $this->getBillingAddress());
            $this->updateOrderAddressData($customer, $order->getShippingAddress(), $this->getShippingAddress());

            $this->orderRepository->save($order);
        }

        if ($order) {
            return $order;
        }

        /** @var \BitTools\SkyHub\Integration\Support\Sales\Order\Create $creator */
        $creator = $this->orderCreatorFactory->create();

        $info = new DataObject(['send_confirmation' => 0]);

        if ($incrementId = $this->orderHelper->getNewOrderIncrementId($code)) {
            $info->setData('increment_id', $incrementId);
        }

        $creator->setOrderInfo($info)
            ->setCustomer($customer)
            ->setShippingMethod($shippingTitle, $shippingCarrier, (float) $shippingCost)
            ->setPaymentMethod($paymentMethod)
            ->setDiscountAmount($discountAmount)
            ->setInterestAmount($interestAmount)
            ->setSkyhubData($data)
            ->addOrderAddress($this->getBillingAddress(), self::ADDRESS_TYPE_BILLING)
            ->addOrderAddress($this->getShippingAddress(), self::ADDRESS_TYPE_SHIPPING)
            ->setComment(__('This order was automatically created by SkyHub import process.'));

        $products = $this->getProducts((array) $this->arrayExtract($data, 'items'));
        if (empty($products)) {
            throw new Exception(__('The SkyHub products cannot be matched with Magento products.'));
        }

        /** @var array $productData */
        foreach ((array) $products as $productData) {
            $creator->addProduct($productData);
        }

        $order = null;
        try {
            /** @var SalesOrder $order */
            $order = $creator->create();
        } catch (Exception $exception) {
            $this->logger()->critical($exception);

            /**
             * An exception can be thrown here but in some cases the order might be created.
             * If the order was not created let's throw the exception again and hand over the exception treatment.
             */
            if (!$this->validateCreatedOrder($order)) {
                throw $exception;
            }
        }

        if (!$this->validateCreatedOrder($order)) {
            return false;
        }

        $order->setData('is_created', true);

        return $order;
    }

    /**
     * Return Config methodShipping
     *
     * @return bool|array
     */
    protected function getMethodShippingConfig()
    {
        $config = $this->helperContext()->scopeConfig()->getValue('bittools_skyhub/method_shipping/marketplaces');
        if (!$config) {
            return false;
        }
        return json_decode($config, true);
    }

    /**
     * Return Method Shipping Default
     *
     * @param string $shippingMethod
     * @return string
     */
    protected function getShippingMethodConfig($shippingMethod, $channel)
    {
        $config = $this->getMethodShippingConfig();
        if (!$config) {
            return $shippingMethod;
        }

        foreach ($config as $value) {
            if ($channel != $value['channel']) {
                continue;
            }
            return $value['method_shipping_default'];
        }
        return $shippingMethod;
    }

    /**
     * @param CustomerInterface $customer
     * @param OrderAddressInterface $orderAddress
     * @param AddressInterface $address
     */
    protected function updateOrderAddressData(
        CustomerInterface $customer,
        OrderAddressInterface $orderAddress,
        AddressInterface $address
    ) {
        $orderAddress
            ->setEmail($customer->getEmail())
            ->setFirstname($address->getFirstname())
            ->setMiddlename($address->getMiddlename())
            ->setLastname($address->getLastname())
            ->setStreet($address->getStreet())
            ->setTelephone($address->getTelephone())
            ->setPostcode($address->getPostcode())
            ->setCity($address->getCity())
            ->setRegion($address->getRegion()->getRegionCode())
            ->setRegionId($address->getRegionId());
    }

    /**
     * @param array      $skyhubOrderData
     * @param SalesOrder $order
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function updateOrderStatus(array $skyhubOrderData, SalesOrder $order)
    {
        $skyhubStatusCode = $this->arrayExtract($skyhubOrderData, 'code');
        $skyhubStatusType = $this->arrayExtract($skyhubOrderData, 'status/type');

        $this->statusProcessor->processOrderStatus($skyhubStatusCode, $skyhubStatusType, $order);

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
     * @return false|int
     */
    protected function getProductIdBySku($sku)
    {
        /** @var CatalogModelDir\ResourceModel\Product $resource */
        $resource  = $this->objectManager()->create(CatalogModelDir\ResourceModel\Product::class);
        return $resource->getIdBySku($sku);
    }

    /**
     * @param string $email
     * @param string $taxvat
     * @param int $storeId
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @return CustomerInterface
     */
    protected function getCustomerByEmailOrTaxvat($email, $taxvat, $storeId = null)
    {
        $this->foundByTaxvat = false;
        $websiteId = $this->getStore($storeId)->getWebsiteId();

        try {
            return $this->customerRepository->get($email, $websiteId);
        } catch(NoSuchEntityException $e) {
        }

        try {
            $customer = $this->customerRepository->get($taxvat . '@email.com.br', $websiteId);
        } catch(NoSuchEntityException $e) {
            $this->searchCriteriaBuilder
                ->addFilter('taxvat', $taxvat)
                ->addFilter('website_id', $websiteId);

            $customers = $this->customerRepository->getList($this->searchCriteriaBuilder->create())->getItems();

            if (empty($customers)) {
                throw $e;
            }

            $customer = $customers[0];
        }

        $this->foundByTaxvat = true;

        return $customer;
    }

    /**
     * @param array $data
     * @param null  $storeId
     *
     * @return CustomerInterface
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     * @throws Exception
     */
    protected function getCustomer(array $data, $storeId = null)
    {
        $email = $this->arrayExtract($data, 'email');
        $taxvat = $this->arrayExtract($data, 'vat_number');

        try {
            $customer = $this->getCustomerByEmailOrTaxvat($email, $taxvat, $storeId);

            if ($this->foundByTaxvat) {
                $this->setCustomerData($customer, $data);
            }

            if ($billing = $this->arrayExtract($data, 'billing_address')) {
                $address = $this->addCustomerAddress($billing, $customer, self::ADDRESS_TYPE_BILLING);
                $this->pushAddress($address, self::ADDRESS_TYPE_BILLING);
                $this->getBillingAddress()->setIsDefaultBilling(1);
            }

            if ($shipping = $this->arrayExtract($data, 'shipping_address')) {
                $address = $this->addCustomerAddress($shipping, $customer, self::ADDRESS_TYPE_SHIPPING);
                $this->pushAddress($address, self::ADDRESS_TYPE_SHIPPING);
                $this->getShippingAddress()->setIsDefaultShipping(1);
            }

            $addressIds = [];
            $addresses = [];
            foreach ($this->addresses as $address) {
                if (!$address) {
                    continue;
                }
                $addressIds[] = $address->getId();
                $addresses[] = $address;
            }

            $addressesDataBase = $customer->getAddresses();
            foreach ($addressesDataBase as $address) {
                if (in_array($address->getId(), $addressIds)) {
                    continue;
                }
                $address->setIsDefaultBilling(0);
                $address->setIsDefaultShipping(0);
                $addresses[] = $address;
            }

            $customer->setAddresses($addresses);
            $this->customerRepository->save($customer);

        } catch (NoSuchEntityException $e) {
            $customer = $this->createCustomer($data, $storeId);
        } catch (Exception $e) {
            $this->logger()->critical($e);
            throw $e;
        }

        return $customer;
    }

    /**
     * @param CustomerInterface $customer
     * @param array $data
     *
     * @throws LocalizedException
     */
    protected function setCustomerData(CustomerInterface $customer, array $data)
    {
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

        if (!$customer->getEmail() || strpos($customer->getEmail(), '@') === false) {
            $customer->setEmail($customer->getTaxvat() . '@email.com.br');
        }

        /** @var string $phone */
        foreach ($phones as $phone) {
            $customer->setData('telephone', $phone);
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
    }

    /**
     * @param array $data
     * @param array $storeId
     *
     * @return CustomerInterface
     *
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    protected function createCustomer(array $data, $storeId = null)
    {
        $customer = $this->customerFactory->create();
        $customer->setStoreId($this->getStore($storeId)->getId());

        $this->setCustomerData($customer, $data);

        /** @var DataObject $billing */
        if ($billing = $this->arrayExtract($data, 'billing_address')) {
            $this->addCustomerAddress($billing, $customer, self::ADDRESS_TYPE_BILLING);
        }

        /** @var DataObject $billing */
        if ($shipping = $this->arrayExtract($data, 'shipping_address')) {
            $this->addCustomerAddress($shipping, $customer, self::ADDRESS_TYPE_SHIPPING);
        }

        $billingAddress = $this->getBillingAddress()->setIsDefaultBilling(1);
        $shippingAddress = $this->getShippingAddress()->setIsDefaultShipping(1);

        $customer->setAddresses([$billingAddress, $shippingAddress]);

        $customer = $this->customerRepository->save($customer);

        return $customer;
    }

    /**
     * @param DataObject        $addressObject
     * @param CustomerInterface $customer
     * @param string            $type
     *
     * @return AddressInterface
     */
    protected function addCustomerAddress(DataObject $addressObject, CustomerInterface $customer, $type)
    {
        /** @var AddressInterface $address */
        $address = $this->addressFactory->create();
        $addressExist = false;
        /** @var AddressInterface $currentAddress */
        foreach ($customer->getAddresses() ?: [] as $currentAddress) {
            if ($currentAddress->getPostcode() === $addressObject->getData('postcode')) {
                $addressExist = true;
                $address = $currentAddress;
                break;
            }

            if ($currentAddress->getPostcode() === '00000000') {
                $address = $currentAddress;
                break;
            }
        }

        $address->setVatId($customer->getTaxvat());

        $streetLinesCount = (int) $this->helperContext()
            ->scopeConfig()
            ->getValue('customer/address/street_lines');

        $street         = $addressObject->getData('street');
        $number         = $addressObject->getData('number');
        $neighborhood   = $addressObject->getData('neighborhood');
        $postcode       = $addressObject->getData('postcode');
        $complement     = $this->getComplement($addressObject);
        $phone          = $addressObject->getData('phone');
        $country        = $addressObject->getData('country');
        $city           = $addressObject->getData('city');

        /**
         * The customer configuration can be set to use 2 fields only.
         */
        $street = $this->prepareAddressStreetLines(
            $this->removeLineTabString($street),
            $this->removeLineTabString($number),
            $this->removeLineTabString($neighborhood),
            $this->removeLineTabString($complement ?: ''),
            $streetLinesCount
        );

        if (strlen($country) > 2) {
            $country = $this->countryFactory->create()->loadByCode($country)->getId();
        }

        $address->setFirstname($customer->getFirstname())
            ->setLastname($customer->getLastname())
            ->setTelephone($phone)
            ->setStreet($street)
            ->setCity($city)
            ->setRegion($this->getRegion($addressObject))
            ->setRegionId($this->getRegion($addressObject)->getRegionId())
            ->setPostcode($postcode)
            ->setCountryId($country ?: 'BR');

        if (!$addressExist) {
            $this->pushAddress($address, $type);
        }

        return $address;
    }

    /**
     * Remove \n\t\r the string
     *
     * @param string $value
     * @return string
     */
    protected function removeLineTabString(string $value = null): string
    {
        if (empty($value)) {
            return '';
        }

        $value = str_replace("\n", ' ', $value);
        $value = str_replace("\t", '', $value);
        $value = str_replace("\r", '', $value);
        return $value;
    }

    /**
     * @param DataObject $addressObject
     *
     * @return RegionInterface
     */
    protected function getRegion(DataObject $addressObject)
    {
        if (!isset($this->regions[$addressObject->getData('region')])) {
            $regionModel = $this->regionFactory->create();
            $region = $regionModel->loadByCode($addressObject->getData('region'), 'BR');

            if (!$region->getId()) {
                $region = $regionModel->loadByCode('AC', 'BR');
            }

            $this->regions[$addressObject->getData('region')] = $this->regionDataFactory->create()
                ->setRegion($region->getName())
                ->setRegionCode($region->getCode())
                ->setRegionId($region->getRegionId());
        }

        return $this->regions[$addressObject->getData('region')];
    }

    /**
     * @param DataObject $addressObject
     *
     * @return string|null
     */
    protected function getComplement(DataObject $addressObject)
    {
        if ($addressObject->getData('complement')) {
            return $addressObject->getData('complement');
        }

        if($addressObject->getData('reference')) {
            return $addressObject->getData('reference');
        }

        return $addressObject->getData('detail');
    }

    /**
     * @param AddressInterface $address
     * @param string           $type
     *
     * @return $this
     */
    protected function pushAddress(AddressInterface $address, $type)
    {
        $this->addresses[$type] = $address;
        return $this;
    }


    /**
     * @return AddressInterface|mixed
     */
    protected function getBillingAddress()
    {
        /** @todo Create a logic to retrieve this address when address was not created in this process. */
        $address = $this->addresses[self::ADDRESS_TYPE_BILLING];

        if (empty($address)) {
            $address = $this->addresses[self::ADDRESS_TYPE_SHIPPING];
        }

        return $address;
    }


    /**
     * @return AddressInterface|mixed
     */
    protected function getShippingAddress()
    {
        /** @todo Create a logic to retrieve this address when address was not created in this process. */
        $address = $this->addresses[self::ADDRESS_TYPE_SHIPPING];

        if (empty($address)) {
            $address = $this->addresses[self::ADDRESS_TYPE_BILLING];
        }

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
     * @param int|null $storeId
     *
     * @throws NoSuchEntityException
     *@return \Magento\Store\Api\Data\StoreInterface
     *
     */
    protected function getStore($storeId = null)
    {
        return $this->storeManager()->getStore($storeId);
    }


    /**
     * @param string   $skyhubCode
     * @param null|int $storeId
     *
     * @return int|bool
     *
     * @throws LocalizedException
     */
    protected function getOrderId($skyhubCode, $storeId = null)
    {
        /** @var \BitTools\SkyHub\Model\ResourceModel\Order $orderResource */
        $orderResource = $this->objectManager()->create(\BitTools\SkyHub\Model\ResourceModel\Order::class);
        $orderId       = $orderResource->getOrderId($skyhubCode, $this->getStore($storeId)->getId());

        return $orderId;
    }


    /**
     * @param string $code
     *
     * @return string | null
     */
    protected function getOrderIncrementId($code = null)
    {
        /**
         * @todo Check if this is really necessary.
         */
        $useDefaultIncrementId = $this->helperContext()
            ->configContext()
            ->general()
            ->getSkyHubModuleConfig('use_default_increment_id', 'cron_sales_order_import');

        if (!$useDefaultIncrementId) {
            return $code;
        }

        return null;
    }


    /**
     * @param array             $data
     * @param CustomerInterface $customer
     *
     * @throws LocalizedException
     */
    protected function setPersonTypeInformation(array $data, CustomerInterface $customer)
    {
        //get the vat number
        $vatNumber = $this->arrayExtract($data, 'vat_number');

        //the taxvat is filled anyway
        $customer->setTaxvat($vatNumber);

        //check if is a PJ customer (if not, it's a PF customer)
        $customerIsPj = $this->customerIsPj($vatNumber);

        //get customer mapped attributes
        $mappedCustomerAttributes = $this->customerAttributeMappingHelper->getMappedAttributes();

        //if the store has the attribute "person_type" mapped
        if (isset($mappedCustomerAttributes['person_type'])) {
            $personTypeAttribute = $mappedCustomerAttributes['person_type']->getAttribute();

            if ($customerIsPj) {
                $personTypeAttributeValue = $this->customerAttributeMappingHelper
                    ->getAttributeMappingOptionMagentoValue($mappedCustomerAttributes['person_type']->getId(), 'legal_person');
            } else {
                $personTypeAttributeValue = $this->customerAttributeMappingHelper
                    ->getAttributeMappingOptionMagentoValue($mappedCustomerAttributes['person_type']->getId(), 'physical_person');
            }
            if($personTypeAttributeValue && $personTypeAttributeValue->getId()) {
                $customer->setCustomAttribute($personTypeAttribute->getAttributeCode(), $personTypeAttributeValue->getMagentoValue());
            }
        }

        if ($customerIsPj) {
            //set the mapped PJ attribute value on customer if exists
            if (isset($mappedCustomerAttributes['cnpj'])) {
                $attribute = $mappedCustomerAttributes['cnpj']->getAttribute();
                $customer->setCustomAttribute($attribute->getAttributeCode(), $vatNumber);
            }
        } else {
            //set the mapped PF attribute value on customer if exists
            if (isset($mappedCustomerAttributes['cpf'])) {
                $attribute = $mappedCustomerAttributes['cpf']->getAttribute();
                $customer->setCustomAttribute($attribute->getAttributeCode(), $vatNumber);
            }
        }

        //set the mapped IE attribute value on customer if exists
        if (isset($mappedCustomerAttributes['ie'])) {
            $attribute = $mappedCustomerAttributes['ie']->getAttribute();
            $customer->setCustomAttribute($attribute->getAttributeCode(), $this->arrayExtract($data, 'state_registration'));
        }

        //set the mapped IE attribute value on customer if exists
        if (isset($mappedCustomerAttributes['social_name'])) {
            $attribute = $mappedCustomerAttributes['social_name']->getAttribute();
            $customer->setCustomAttribute($attribute->getAttributeCode(), $this->arrayExtract($data, 'name'));
        }
    }


    /**
     * @param \Magento\Sales\Api\Data\OrderInterface|null $order
     *
     * @return bool
     */
    protected function validateCreatedOrder(\Magento\Sales\Api\Data\OrderInterface $order = null)
    {
        if (!$order) {
            return false;
        }

        if (!$order->getEntityId()) {
            return false;
        }

        return true;
    }
}
