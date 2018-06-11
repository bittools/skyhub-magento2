<?php

namespace BitTools\SkyHub\Console\Integration\Catalog;

use BitTools\SkyHub\Integration\Integrator\Catalog\Product\Attribute as ProductAttributeIntegrator;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Api\Data\StoreInterface;
use SkyHub\Api\Handler\Response\HandlerInterfaceException;
use SkyHub\Api\Handler\Response\HandlerInterfaceSuccess;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IntegrateProductAttribute extends AbstractCatalog
{
    
    /** @var string */
    const INPUT_KEY_ATTRIBUTE_CODE = 'attribute';
    
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;
    
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('skyhub:integrate:product-attribute')
            ->setDescription('Integrate product attributes from store catalog.')
            ->setDefinition([
                new InputOption(
                    self::INPUT_KEY_ATTRIBUTE_CODE,
                    'a',
                    InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                    'The product\'s attribute IDs for integration.'
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
        $attributes = $this->getAttributes($input);
        
        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            
            /** @var ProductAttributeIntegrator $integrator */
            $integrator = $this->objectManager()->create(ProductAttributeIntegrator::class);
            
            /** @var HandlerInterfaceSuccess|HandlerInterfaceException $response */
            $response = $integrator->createOrUpdate($attribute);
            
            if (false == $response) {
                $this->style()
                    ->warning(__('The attribute %1 cannot be integrated.', $code));
                continue;
            }
        
            if ($response->success()) {
                $this->style()->success(__('Attribute %1 was successfully integrated.', $code));
                continue;
            }
        
            if ($response->exception()) {
                $this->style()
                    ->error(__('Attribute %1 was not integrated. Message: %2', $code, $response->message()));
                continue;
            }
    
            $this->style()->warning(__('Something went wrong on this integration...'));
        }
    }
    
    
    /**
     * @param InputInterface  $input
     *
     * @return array
     */
    protected function getAttributes(InputInterface $input)
    {
        $attributeCodes = array_unique($input->getOption(self::INPUT_KEY_ATTRIBUTE_CODE));
        $attributeCodes = array_filter($attributeCodes);
    
        /** @var SearchCriteriaBuilder $builder */
        $builder = $this->context->objectManager()->create(SearchCriteriaBuilder::class);
        $builder->addFilter('attribute_code', $attributeCodes, 'in');
    
        /** @var \Magento\Framework\Api\SearchCriteria $result */
        $searchCriteria = $builder->create();
        
        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->objectManager()->create(AttributeRepositoryInterface::class);
        
        /** @var \Magento\Framework\Api\SearchResults $result */
        $result = $attributeRepository->getList(Product::ENTITY, $searchCriteria);
        
        return $result->getItems();
    }
}
