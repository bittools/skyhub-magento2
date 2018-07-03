<?php

namespace Package;

define('PACKAGE_ROOT', dirname(__FILE__));
define('MODULE_ROOT', dirname(PACKAGE_ROOT));

abstract class PackageAbstract
{
    const DIFF_NONEXISTENT    = 'nonexistent';
    const DIFF_MODIFIED       = 'modified';
    const DIFF_NEW            = 'new';

    /** @var string */
    protected $encryptMethod  = "AES-256-CBC";
    
    /** @var string */
    protected $secretKey      = 'MY SECRET KEY';
    
    /** @var string */
    protected $secretIv       = 'MY SECRET IV 123';
    
    /** @var array */
    protected $files = [];
    
    /** @var array */
    protected $readableFileExtensions = [
        'php', 'xml', 'json'
    ];
    
    
    /**
     * @return string
     */
    protected function getKeyFilePath()
    {
        return PACKAGE_ROOT . DIRECTORY_SEPARATOR . '.generated';
    }
    
    
    /**
     * @param array $currentFiles
     * @param array $originalFiles
     *
     * @return array
     */
    protected function compareFiles(array $currentFiles, array $originalFiles)
    {
        $differences = [
            self::DIFF_NEW         => array_diff(array_keys($currentFiles), array_keys($originalFiles)),
            self::DIFF_NONEXISTENT => array_diff(array_keys($originalFiles), array_keys($currentFiles)),
            self::DIFF_MODIFIED    => [],
        ];
    
        foreach ($currentFiles as $currentFile => $currentFileInfo) {
            if (!in_array($currentFile, array_keys($originalFiles))) {
                continue;
            }
            
            $modified         = false;
            $originalFileInfo = $originalFiles[$currentFile];
    
            /**
             * Check if file size is different.
             */
            if ($originalFileInfo['size'] != $currentFileInfo['size']) {
                $modified = true;
            }
    
            /**
             * Check if file modified time is different.
             */
            if ($originalFileInfo['modified_time'] != $currentFileInfo['modified_time']) {
                $modified = true;
            }
            
            if (true === $modified) {
                $differences[self::DIFF_MODIFIED][$currentFile] = $currentFileInfo;
            }
        }
        
        return $differences;
    }
    
    
    /**
     * @return $this
     */
    protected function initFiles(array $extensions = [])
    {
        $this->files = [];
        $packageDir  = dirname(__FILE__);
        
        /** @var string $file */
        foreach ((array) $this->loadFiles($extensions) as $file) {
            if (false !== strpos($file, $packageDir)) {
                continue;
            }
            
            $this->files[$this->getFileRelativePath($file)] = $this->getFileInfo($file);
        }
        
        return $this;
    }
    
    
    /**
     * @param string $file
     *
     * @return mixed
     */
    protected function getFileRelativePath($file)
    {
        return str_replace(MODULE_ROOT, null, $file);
    }
    
    
    /**
     * @param $filePath
     *
     * @return bool|mixed
     */
    protected function getFileInfo($filePath)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return false;
        }
        
        $info = (array) pathinfo($filePath);
    
        $info['size']          = filesize($filePath);
        $info['modified_time'] = filemtime($filePath);
        
        return (array) $info;
    }
    
    
    /**
     * @param array $data
     *
     * @return string
     */
    protected function encrypt(array $data)
    {
        $encrypted = json_encode($data);
        $encrypted = openssl_encrypt($encrypted, $this->encryptMethod, $this->secretKey, 0, $this->secretIv);
        
        return $encrypted;
    }
    
    
    /**
     * @param string $encrypted
     *
     * @return array
     */
    protected function decrypt($encrypted)
    {
        $decrypted = openssl_decrypt($encrypted, $this->encryptMethod, $this->secretKey, 0, $this->secretIv);
        $decrypted = json_decode($decrypted, true);
        
        return (array) $decrypted;
    }
    
    
    /**
     * @return null|string
     */
    protected function getCurrentHash()
    {
        if (!file_exists($this->getKeyFilePath())) {
            return false;
        }
        
        return (string) file_get_contents($this->getKeyFilePath());
    }
    
    
    /**
     * @param string $string
     *
     * @return mixed
     */
    protected function clearString($string)
    {
        return str_replace([" ", "\n", "\t", "\r", "\0", "\x0B"], null, $string);
    }
    
    
    /**
     * @return array
     */
    protected function loadFiles(array $extensions = [])
    {
        return glob($this->getFilePattern($extensions), GLOB_BRACE);
    }
    
    
    /**
     * @param array $extensions
     *
     * @return string
     */
    protected function getFilePattern(array $extensions = [])
    {
        $dirs = [];
    
        $extensions = array_merge($this->readableFileExtensions, $extensions);
        
        foreach ($extensions as $extension) {
            $dirs[] = MODULE_ROOT."/*.{$extension}";
            $dirs[] = MODULE_ROOT."/**/*.{$extension}";
        }
        
        return "{" . implode(',', $dirs) . "}";
    }
}
