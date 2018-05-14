<?php

namespace BitTools\SkyHub\Console\Integration\Catalog;

use BitTools\SkyHub\Integration\Integrator\Catalog\Category as CategoryIntegrator;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IntegrateCategory extends Command
{
    
    /** @var string */
    const INPUT_KEY_CATEGORY_ID = 'category_id';
    
    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;
    
    /** @var CategoryIntegrator */
    protected $categoryIntegrator;
    
    
    /**
     * IntegrateCategory constructor.
     *
     * @param CategoryRepositoryInterface $categoryFactory
     * @param null|string                 $name
     */
    public function __construct(
        $name = null,
        CategoryRepositoryInterface $categoryRepository,
        CategoryIntegrator $categoryIntegrator
    )
    {
        parent::__construct($name);
        $this->categoryRepository = $categoryRepository;
        $this->categoryIntegrator = $categoryIntegrator;
    }
    
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('skyhub:integrate:category')
            ->setDescription('Integrate categories from store catalog.')
            ->setDefinition([
                new InputOption(
                    self::INPUT_KEY_CATEGORY_ID,
                    'c',
                    InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                    'The category IDs for integration.'
                )
            ]);
        
        parent::configure();
    }
    
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $categoryIds = array_unique($input->getOption(self::INPUT_KEY_CATEGORY_ID));
        $categoryIds = array_filter($categoryIds);
    
        $style = new SymfonyStyle($input, $output);
        
        /** @var int $categoryId */
        foreach ($categoryIds as $categoryId) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->getCategory($categoryId);
            
            if (!$category) {
                continue;
            }
            
            /** @var \SkyHub\Api\Handler\Response\HandlerInterfaceSuccess|\SkyHub\Api\Handler\Response\HandlerInterfaceException $response */
            $response = $this->categoryIntegrator->createOrUpdate($category);
            
            if ($response && $response->success()) {
                $style->success(__('Category ID %1 was successfully integrated.', $categoryId));
            }
            
            if ($response && $response->exception()) {
                $style->error(__('Category ID %1 was not integrated. Message: %2', $categoryId, $response->message()));
            }
        }
        
        $output->writeln($categoryId);
    }
    
    
    /**
     * @param integer $categoryId
     *
     * @return \Magento\Catalog\Model\Category|null
     */
    protected function getCategory($categoryId)
    {
        try {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->categoryRepository->get($categoryId);
    
            return $category;
        } catch (NoSuchEntityException $e) {
        } catch (\Exception $e) {
        }
        
        return null;
    }
}
