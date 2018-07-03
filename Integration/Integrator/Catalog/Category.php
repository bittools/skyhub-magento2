<?php

namespace BitTools\SkyHub\Integration\Integrator\Catalog;

use BitTools\SkyHub\Integration\Context;
use \Magento\Catalog\Model\Category as CategoryModel;
use BitTools\SkyHub\Integration\Transformer\Catalog\CategoryFactory;

class Category extends AbstractCatalog
{
    
    /** @var string */
    protected $eventType = 'catalog_category';
    
    /** @var CategoryValidation */
    protected $validator;
    
    /** @var CategoryFactory */
    protected $transformerFactory;
    
    
    /**
     * Category constructor.
     *
     * @param Context            $context
     * @param CategoryValidation $categoryValidation
     */
    public function __construct(
        Context $context,
        CategoryValidation $categoryValidation,
        CategoryFactory $transformerFactory
    ) {
        parent::__construct($context);
        
        $this->validator          = $categoryValidation;
        $this->transformerFactory = $transformerFactory;
    }
    
    
    /**
     * @param CategoryModel $category
     *
     * @return bool|\SkyHub\Api\Handler\Response\HandlerInterface
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createOrUpdate(CategoryModel $category)
    {
        $exists = $this->categoryExists($category->getId());
        
        if (true == $exists) {
            /** Update Category */
            return $this->update($category);
        }
        
        /** Create Category */
        $response = $this->create($category);
        
        if ($response && $response->success()) {
            $this->registerCategoryEntity($category->getId());
        }
        
        return $response;
    }
    
    
    /**
     * @param CategoryModel $category
     *
     * @return bool|\SkyHub\Api\Handler\Response\HandlerInterface
     */
    public function create(CategoryModel $category)
    {
        if (!$this->validator->canIntegrateCategory($category)) {
            return false;
        }
        
        /** @var \SkyHub\Api\EntityInterface\Catalog\Category $interface */
        $interface = $this->categoryTransformer()
            ->convert($category);
        
        $this->eventMethod = 'create';
        $this->eventParams = [
            'category'  => $category,
            'interface' => $interface,
        ];
        
        $this->beforeIntegration();
        $response = $interface->create();
        $this->eventParams[] = $response;
        $this->afterIntegration();
        
        return $response;
    }
    
    
    /**
     * @param CategoryModel $category
     *
     * @return bool|\SkyHub\Api\Handler\Response\HandlerInterface
     */
    public function update(CategoryModel $category)
    {
        if (!$this->validator->canIntegrateCategory($category)) {
            return false;
        }
        
        /** @var \SkyHub\Api\EntityInterface\Catalog\Category $interface */
        $interface = $this->categoryTransformer()
            ->convert($category);
        
        $this->eventMethod = 'update';
        $this->eventParams = [
            'category'  => $category,
            'interface' => $interface,
        ];
        
        $this->beforeIntegration();
        $response = $interface->update();
        $this->eventParams[] = $response;
        $this->afterIntegration();
        
        return $response;
    }
    
    
    /**
     * @param int $categoryId
     *
     * @return bool|\SkyHub\Api\Handler\Response\HandlerInterface
     */
    public function delete($categoryId)
    {
        /** @var \SkyHub\Api\EntityInterface\Catalog\Category $interface */
        $interface = $this->api()->category()->entityInterface();
        $interface->setCode((int) $categoryId);
        
        $this->eventMethod = 'delete';
        
        $this->beforeIntegration();
        $response = $interface->delete();
        $this->afterIntegration();
        
        return $response;
    }
    
    
    /**
     * @return \BitTools\SkyHub\Integration\Transformer\Catalog\Category
     */
    protected function categoryTransformer()
    {
        /** @var \BitTools\SkyHub\Integration\Transformer\Catalog\Category $transformer */
        $transformer = $this->transformerFactory->create();
        return $transformer;
    }
}
