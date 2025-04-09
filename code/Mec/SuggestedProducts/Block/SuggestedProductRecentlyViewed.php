<?php
namespace Mec\SuggestedProducts\Block;

class SuggestedProductRecentlyViewed extends \Magento\Framework\View\Element\Template
{
    protected $recentlyViewed;
    protected $session;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Reports\Block\Product\Viewed $recentlyViewed,
        \Magento\Framework\Session\SessionManagerInterface $session,
        array $data = []
    ) {
        $this->recentlyViewed = $recentlyViewed;
        $this->session = $session;
        parent::__construct( $context, $data );
    }

    public function getMostRecentlyViewed(){
        echo 'inside 5';
        $visitor = $this->session->getVisitorData();
        $visitor_id = $visitor['visitor_id'];
        return $visitor_id;
        //return $this->recentlyViewed->getItemsCollection()->getData();
    }
}