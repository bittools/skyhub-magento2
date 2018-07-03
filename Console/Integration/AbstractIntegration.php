<?php

namespace BitTools\SkyHub\Console\Integration;

use BitTools\SkyHub\Console\AbstractConsole;
use Magento\Framework\App\Area;
use Magento\Store\Api\Data\StoreInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractIntegration extends AbstractConsole
{
    
    /** @var string */
    const INPUT_KEY_STORE_ID = 'store_id';

    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return $this
     */
    protected function beforeExecute(InputInterface $input, OutputInterface $output, StoreInterface $store)
    {
        $this->style = new SymfonyStyle($input, $output);
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
    protected function afterExecute(InputInterface $input, OutputInterface $output, StoreInterface $store)
    {
        return $this;
    }
    
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    abstract protected function processExecute(InputInterface $input, OutputInterface $output, StoreInterface $store);
    
    
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
            
            $this->beforeExecute($input, $output, $store);
            $this->processExecute($input, $output, $store);
            $this->afterExecute($input, $output, $store);
        }
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
}
