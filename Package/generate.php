<?php

namespace Package;

require_once dirname(__FILE__) . '/abstract.php';

class PackageGenerate extends PackageAbstract
{
    
    public function generate()
    {
        if (!file_exists($this->getKeyFilePath())) {
            touch($this->getKeyFilePath());
        }
        
        $this->initFiles();
        
        file_put_contents($this->getKeyFilePath(), $this->encrypt($this->files));
    }
}(new PackageGenerate())->generate();
