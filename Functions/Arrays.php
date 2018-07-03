<?php

namespace BitTools\SkyHub\Functions;

trait Arrays
{
    
    /**
     * @param array                   $data
     * @param string                  $index
     * @param mixed|array|bool|string $default
     * @param bool                    $shiftOriginal
     *
     * @return mixed|array|bool|string
     */
    protected function arrayExtract(array $data, $index, $default = false, $shiftOriginal = false)
    {
        if (false !== strpos($index, '/')) {
            $parts = explode('/', $index);
            
            foreach ($parts as $index) {
                $data = $this->arrayExtract($data, $index);
                
                if (!is_array($data)) {
                    if (empty($data)) {
                        return $default;
                    }
                    
                    if (true === $shiftOriginal) {
                        $this->arrayUnset($data, $index);
                    }
                    
                    return $data;
                }
            }
        }
        
        if (!$this->arrayIndexExists($data, $index)) {
            return $default;
        }
        
        $value = $data[$index];
        
        if (true === $shiftOriginal) {
            $this->arrayUnset($data, $index);
        }
        
        return $value;
    }
    
    
    /**
     * @param array            $data
     * @param array|string|int $indexes
     *
     * @return mixed
     */
    protected function arrayUnset(array &$data, $indexes)
    {
        $indexes = (array) $indexes;
        
        foreach ($indexes as $index) {
            unset($data[$index]);
        }
        
        return $data;
    }
    
    
    /**
     * @param array                   $data
     * @param string                  $index
     * @param mixed|array|bool|string $default
     *
     * @return mixed|array|bool|string
     */
    protected function arrayExtractNoEmpty(array $data, $index, $default = false)
    {
        if (!$this->arrayIsNotEmpty($data, $index)) {
            return $default;
        }
        
        return $data[$index];
    }
    
    
    /**
     * @param array  $data
     * @param string $index
     *
     * @return bool
     */
    protected function arrayIsNotEmpty(array $data, $index)
    {
        return (bool) ($this->arrayIndexExists($data, $index) && $data[$index]);
    }
    
    
    /**
     * @param array  $data
     * @param string $index
     *
     * @return bool
     */
    protected function arrayIndexExists(array $data, $index)
    {
        return (bool) isset($data[$index]);
    }
}
