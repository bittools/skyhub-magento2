<?php

namespace BitTools\SkyHub\Console\Integration\Catalog;

use BitTools\SkyHub\Helper\Context;
use Magento\Framework\App\Area;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCatalog extends Command
{
    
    /** @var string */
    const INPUT_KEY_STORE_ID = 'store_id';
    
    /** @var string */
    const INPUT_KEY_LIMIT = 'limit';
    
    /** @var Context */
    protected $context;
    
    /** @var StyleInterface */
    private $style;
    
    
    /**
     * AbstractCatalog constructor.
     *
     * @param null|string $name
     * @param Context     $context
     */
    public function __construct(Context $context, $name = null)
    {
        parent::__construct(null);
        
        $this->context = $context;
    }
    
    
    /**
     * @param null|int|string $storeId
     *
     * @return $this
     */
    protected function prepareStore($storeId = null)
    {
        if (is_null($storeId)) {
            return $this;
        }
        
        try {
            $this->context->storeManager()->setCurrentStore($this->getStore($storeId));
            $this->context->appState()->setAreaCode(Area::AREA_ADMINHTML);
            
        } catch (\Exception $e) {
            $this->context->logger()->critical($e);
        }
        
        return $this;
    }
    
    
    /**
     * @param null|int|string $storeId
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStore($storeId = null)
    {
        return $this->context->storeManager()->getStore($storeId);
    }
    
    
    /**
     * @param null|int $storeId
     *
     * @return int
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreId($storeId = null)
    {
        return $this->getStore($storeId)->getId();
    }
    
    
    /**
     * @return InputOption
     */
    protected function getStoreIdOption()
    {
        return new InputOption(
            self::INPUT_KEY_STORE_ID,
            's',
            InputOption::VALUE_OPTIONAL,
            'The store ID',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
    }
    
    
    /**
     * @return InputOption
     */
    protected function getLimitOption()
    {
        return new InputOption(
            self::INPUT_KEY_LIMIT,
            'l',
            InputOption::VALUE_OPTIONAL,
            'The limit for the process.',
            500
        );
    }
    
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return $this
     */
    protected function beforeExecute(InputInterface $input, OutputInterface $output)
    {
        $this->style = new SymfonyStyle($input, $output);
        
        // $storeId = $input->getOption(self::INPUT_KEY_STORE_ID);
        // $this->prepareStore($storeId);
        
        return $this;
    }
    
    
    /**
     * @return StyleInterface
     */
    protected function style()
    {
        return $this->style;
    }
    
    
    /**
     * @return $this
     */
    protected function afterExecute(InputInterface $input, OutputInterface $output)
    {
        return $this;
    }
    
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    abstract protected function processExecute(InputInterface $input, OutputInterface $output);
    
    
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
        /** @var \Magento\Store\Model\Store $store */
        foreach ($this->getStores($input) as $store) {
            $this->prepareStore($store);
            
            $this->beforeExecute($input, $output);
            $this->processExecute($input, $output);
            $this->afterExecute($input, $output);
        }
    }
    
    
    /**
     * @param InputInterface $input
     *
     * @return array|\Magento\Store\Api\Data\StoreInterface[]
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStores(InputInterface $input)
    {
        $storeId = $input->getOption(self::INPUT_KEY_STORE_ID);
        
        if (!empty($storeId)) {
            return [$this->getStore($storeId)];
        }
        
        return $this->context->storeManager()->getStores();
    }
}
