<?php
namespace Eleadtech\AlphabetOption\Plugin\Controller\Product\Attribute\Save;

class OrderAlphabet {
    protected $request;
    protected $alphabetOptionService;
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Eleadtech\AlphabetOption\Service\AlphabetOption $alphabetOptionService
    )
    {
        $this->request = $request;
        $this->alphabetOptionService = $alphabetOptionService;
    }
    public function afterExecute($subject,$result)
    {
        if($this->alphabetOptionService->getHelper()->isEnabled()){
            $attributeId = $this->request->getParam("attribute_id");
            if(!empty($attributeId)){
                $this->alphabetOptionService->updatePositionByAttributeId($attributeId);
            }
        }
        return $result;
    }
}
