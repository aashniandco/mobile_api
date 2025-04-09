<?php
namespace Fermion\Pagelayout\Controller\Adminhtml\Report;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Fermion\Pagelayout\Helper\ReportHelper;

class GetResult extends Action
{
    protected $pageFactory;
    protected $productreport;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ReportHelper $ReportHelper
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->productreport = $ReportHelper;
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Fermion_Pagelayout::GetResult');
    }

    public function execute()
    {
        try {
            $request_param = $this->getRequest()->getParams();

            if (!isset($request_param["isAjax"]) || $request_param["isAjax"] != true) {
                throw new \Exception("This is not an AJAX request");
            }

            $filter = isset($request_param["filter"]) && !empty($request_param["filter"]) ? $request_param['filter'] : [];

            $response = $this->productreport->getResultForSalesresult($filter, $request_param);
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 1
            ];
        }
        echo json_encode($response);
    }
}
