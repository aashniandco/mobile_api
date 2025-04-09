<?php
namespace Fermion\NativeApp\Model\ResourceModel\NativeTokens\Collection;

use Magento\Framework\ObjectManagerInterface;

class NativeTokenFactory
{
    protected $objectManager;
    protected $instanceName;

    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = '\\Fermion\\NativeApp\\Model\\ResourceModel\\NativeTokens\\Collection'
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
