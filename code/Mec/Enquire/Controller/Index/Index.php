<?php

namespace Mec\Enquire\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Mec\Enquire\Model\Enquire;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;

class Index extends \Magento\Framework\App\Action\Action
{


    protected $transportBuilder;
    protected $storeManager;
    protected $inlineTranslation;
    protected $formKeyValidator;
    protected $enquireModel;
    protected $logger;
    protected $resource;

    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        Validator $formKeyValidator,
        Enquire $enquireModel,
        LoggerInterface $logger,
        ResourceConnection $resource
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->formKeyValidator = $formKeyValidator;
        $this->enquireModel = $enquireModel;
        $this->logger = $logger;
        $this->resource = $resource;
        parent::__construct($context);
    }

    public function execute()
    {
        $request = $this->getRequest();
        
        // Validate form key
        if (!$this->formKeyValidator->validate($request)) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh and try again.'));
            return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
        }

        // Get the IP address of the user
        $ipAddress = $this->getRealIpAddress();

        // Rate-Limiting Check (Limit: 5 submissions per 60 seconds)
        if ($this->isRateLimited($ipAddress)) {
            $this->messageManager->addErrorMessage(__('You have exceeded the maximum number of submissions. Please try again later.'));
            return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
        }

        $data = $request->getPostValue();
        
        if (!empty($data['customer_name']) && !empty($data['customer_email']) && !empty($data['customer_phone'])) {
            $model = $this->_objectManager->create('Mec\Enquire\Model\Enquire');
            $model->setData($data);

            try {
                $model->save();
                
                $this->sendEmail($data);
                $this->sendEmailToCustomer($data);

                $this->logSubmission($ipAddress);

                $this->messageManager->addSuccessMessage(__('Your enquiry has been submitted successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while saving your enquiry.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Please fill in all required fields.'));
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }

    // Check if the user is rate-limited (Max 5 submissions in the last 60 seconds)
    private function isRateLimited($ipAddress)
    {
        $connection = $this->resource->getConnection();
        $currentTime = date('Y-m-d H:i:s');
        $cutoffTime = date('Y-m-d H:i:s', strtotime($currentTime . ' - 60 seconds'));

        // Query to check the number of submissions in the last 60 seconds
        $sql = "SELECT COUNT(*) AS submission_count
                FROM enquiry_rate_limit_log
                WHERE ip_address = :ip_address AND timestamp > :cutoff_time";
        
        $binds = [
            'ip_address' => $ipAddress,
            'cutoff_time' => $cutoffTime
        ];

        $result = $connection->fetchRow($sql, $binds);

        return (isset($result['submission_count']) && $result['submission_count'] >= 5); // Limit to 5 submissions in 60 seconds
    }

    // Log the submission in the database
    private function logSubmission($ipAddress)
    {
        $connection = $this->resource->getConnection();
        
        $sql = "INSERT INTO enquiry_rate_limit_log (ip_address, timestamp) 
                VALUES (:ip_address, :timestamp)";
        
        $binds = [
            'ip_address' => $ipAddress,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $connection->query($sql, $binds);
    }

    public function getRealIpAddress()
    {
        $ipAddress = '';

        // Check for 'X-Forwarded-For' header (this header is commonly used by proxies like Cloudflare, AWS, etc.)
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // If there are multiple IPs, the first one is usually the real client IP
            $ipAddress = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }

        // Check for 'X-Real-IP' header
        if (empty($ipAddress) && isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ipAddress = $_SERVER['HTTP_X_REAL_IP'];
        }

        // Fallback to 'REMOTE_ADDR' if no proxies are involved
        if (empty($ipAddress)) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }

        return $ipAddress;
    }

    private function sendEmail($data)
    {
        $templateId = '16'; // template id
        // $fromEmail = 'support@aashniandco.com';  // sender Email id
        $fromEmail = 'customercare@aashniandco.com';  // sender Email id
        $fromName = 'Enquiry';             // sender Name
        $toEmail = ['nayaabp@fermion.in', 'adnand@fermion.in']; // receiver email id

        try {
            // template variables pass here
            $templateVars = [
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'product_sku'   => $data['sku'],
                'product_name' => $data['designer_name'],
                'query'        => $data['query'],
                'country'        => $data['country'],
                'country_code'        => $data['country_code'],

            ];

            $storeId = $this->storeManager->getStore()->getId();
            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
	    $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    private function sendEmailToCustomer($data)
    {
        $templateId = '15'; // template id
        // $fromEmail = 'support@aashniandco.com';  
        $fromEmail = 'customercare@aashniandco.com';  
        $fromName = 'AASHNI+CO';             
        $toEmail = trim($data['customer_email']); 

        try {
            // template variables pass here
            $templateVars = [
                'customer_name' => $data['customer_name'],
                'product_sku'   => $data['sku'],
		'product_name' => $data['designer_name'],
		'product_short_desc' => $data['product_desc']

            ];

            $storeId = $this->storeManager->getStore()->getId();
            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }
    
}
              	    
                                       
