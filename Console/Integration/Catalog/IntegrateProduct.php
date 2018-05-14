<?php

namespace BitTools\SkyHub\Console\Integration\Catalog;

use BitTools\SkyHub\Helper\Context;
use BitTools\SkyHub\Integration\Integrator\Catalog\Product as ProductIntegrator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IntegrateProduct extends AbstractCatalog
{
    
    /** @var string */
    const INPUT_KEY_PRODUCT_ID = 'product_id';
    
    /** @var ProductRepositoryInterface */
    protected $productRepository;
    
    /** @var ProductIntegrator */
    protected $productIntegrator;
    
    
    /**
     * IntegrateCategory constructor.
     *
     * @param null                       $name
     * @param Context                    $context
     * @param ProductRepositoryInterface $productRepository
     * @param ProductIntegrator          $productIntegrator
     */
    public function __construct(
        $name = null,
        Context $context,
        ProductRepositoryInterface $productRepository,
        ProductIntegrator $productIntegrator
    )
    {
        parent::__construct($name, $context);
        
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
     * @return int|null|void
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productIds = array_unique($input->getOption(self::INPUT_KEY_PRODUCT_ID));
        $productIds = array_filter($productIds);
        $storeId     = $input->getOption(self::INPUT_KEY_STORE_ID);
        
        $this->prepareStore($storeId);
    
        $style = new SymfonyStyle($input, $output);
        
        /** @var int $productId */
        foreach ($productIds as $productId) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->getProduct($productId);
            
            if (!$product) {
                continue;
            }
            
            /** @var \SkyHub\Api\Handler\Response\HandlerInterfaceSuccess|\SkyHub\Api\Handler\Response\HandlerInterfaceException $response */
            $response = $this->productIntegrator->createOrUpdate($product);
            
            if ($response && $response->success()) {
                $style->success(__('Product ID %1 was successfully integrated.', $productId));
                continue;
            }
            
            if ($response && $response->exception()) {
                $style->error(__('Product ID %1 was not integrated. Message: %2', $productId, $response->message()));
                continue;
            }
            
            $style->warning(__('Something went wrong on this integration...'));
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
            $product = $this->productRepository->get($productId);
    
            return $product;
        } catch (NoSuchEntityException $e) {
        } catch (\Exception $e) {
        }
        
        return null;
    }
}
