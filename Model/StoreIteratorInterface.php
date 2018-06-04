<?php

namespace BitTools\SkyHub\Model;

use Magento\Store\Api\Data\StoreInterface;

interface StoreIteratorInterface
{
    
    const REGISTRY_KEY = 'skyhub_store_iterator_iterating';
    
    
    /**
     * @return array
     */
    public function getStores();
    
    
    /**
     * @param string $class
     * @param string $object
     * @param array  $params
     *
     * @return $this
     */
    public function iterate($object, $method, array $params = []);
    
    
    /**
     * @param object         $subject
     * @param string         $method
     * @param array          $params
     * @param StoreInterface $store
     * @param bool           $force
     *
     * @return mixed
     */
    public function call($subject, $method, array $params = [], StoreInterface $store, $force = false);
    
    
    /**
     * This method should simulate the store.
     *
     * @param StoreInterface $store
     *
     * @return $this
     */
    public function simulateStore(StoreInterface $store);
    
    
    /**
     * @return StoreInterface
     */
    public function getCurrentStore();
    
    
    /**
     * @return StoreInterface
     */
    public function getPreviousStore();
    
    
    /**
     * @return StoreInterface
     */
    public function getInitialStore();
    
    
    /**
     * Checks if the Store Iterator is already iterating in the moment.
     *
     * @return boolean
     */
    public function isIterating();
}
