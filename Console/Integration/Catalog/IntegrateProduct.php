<?php

namespace BitTools\SkyHub\Console\Integration\Catalog;

use BitTools\SkyHub\Integration\Integrator\Catalog\Product as ProductIntegrator;
use Magento\Store\Api\Data\StoreInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IntegrateProduct extends AbstractCatalog
{
    
    /** @var string */
    const INPUT_KEY_PRODUCT_ID = 'product_id';
    
    
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
            /** @var ProductIntegrator $productIntegrator */
            $productIntegrator = $this->objectManager()->create(ProductIntegrator::class);
            
            /** @var \SkyHub\Api\Handler\Response\HandlerInterfaceSuccess|\SkyHub\Api\Handler\Response\HandlerInterfaceException $response */
            $response = $productIntegrator->createOrUpdateById($productId);
            
            if (false == $response) {
                $this->style()->warning(__('The product ID %1 does not exist or cannot be integrated.', $productId));
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
