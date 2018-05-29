<?php

namespace BitTools\SkyHub\Console\Integration\Catalog;

use BitTools\SkyHub\Helper\Context;
use BitTools\SkyHub\Integration\Integrator\Catalog\Product as ProductIntegrator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IntegrateProduct extends AbstractCatalog
{
    
    /** @var string */
    const INPUT_KEY_PRODUCT_ID = 'product_id';
    
    /** @var ProductRepositoryInterface */
    protected $productRepository;
    
    /** @var ProductIntegrator */
    protected $productIntegrator;
    
    
    /**
     * IntegrateProduct constructor.
     *
     * @param Context                    $context
     * @param ProductRepositoryInterface $productRepository
     * @param ProductIntegrator          $productIntegrator
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ProductIntegrator $productIntegrator
    )
    {
        parent::__construct($context);
        
        $this->productRepository = $productRepository;
        $this->productIntegrator = $productIntegrator;
    }
    
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('skyhub:integrate:product')
            ->setDescription('Integrate products from store catalog.')
            ->setDefinition([
                new InputOption(
                    self::INPUT_KEY_PRODUCT_ID,
                    'p',
                    InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                    'The product IDs for integration.'
                ),
                $this->getStoreIdOption(),
            ]);
        
        parent::configure();
    }
    
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function processExecute(InputInterface $input, OutputInterface $output, StoreInterface $store)
    {
        $productIds = $this->getProductIds($input, $output);
        
        /** @var int $productId */
        foreach ($productIds as $productId) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->getProduct($productId);
        
            if (!$product) {
                $this->style()->error(__('The product ID %1 does not exist.', $productId));
                continue;
            }
        
            /** @var \SkyHub\Api\Handler\Response\HandlerInterfaceSuccess|\SkyHub\Api\Handler\Response\HandlerInterfaceException $response */
            $response = $this->productIntegrator->createOrUpdate($product);
            
            if (false == $response) {
                $this->style()->warning(__('The product ID %1 cannot be integrated.', $productId));
                continue;
            }
        
            if ($response->success()) {
                $this->style()->success(__('Product ID %1 was successfully integrated.', $productId));
                continue;
            }
        
            if ($response->exception()) {
                $this->style()
                    ->error(__('Product ID %1 was not integrated. Message: %2', $productId, $response->message()));
                continue;
            }
    
            $this->style()->warning(__('Something went wrong on this integration...'));
        }
    }
    
    
    /**
     * @param integer $productId
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    protected function getProduct($productId)
    {
        try {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($productId, false, $this->getStoreId());
    
            return $product;
        } catch (NoSuchEntityException $e) {
        } catch (\Exception $e) {
        }
        
        return null;
    }
    
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return array
     */
    protected function getProductIds(InputInterface $input, OutputInterface $output)
    {
        $productIds = array_unique($input->getOption(self::INPUT_KEY_PRODUCT_ID));
        $productIds = array_filter($productIds);
        
        return (array) $productIds;
    }
}
