<?php

namespace Vovayatsyuk\Alsoviewed\Observer;

class LogRelations implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Vovayatsyuk\Alsoviewed\Model\ResourceModel\LogFactory
     */
    protected $logFactory;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Vovayatsyuk\Alsoviewed\Model\ResourceModel\LogFactory $logFactory
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Vovayatsyuk\Alsoviewed\Model\ResourceModel\LogFactory $logFactory
    ) {
        $this->session = $session;
        $this->scopeConfig = $scopeConfig;
        $this->logFactory = $logFactory;
    }

    /**
     * Save product relations to log table
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productId = $observer->getControllerAction()->getRequest()->getParam('id');
        $viewedIds = $this->getRecentlyViewedProductIds();

        if ($productId && !in_array($productId, $viewedIds)) {
            if (count($viewedIds)) {
                $this->logFactory->create()->insertRelations($productId, $viewedIds);
            }
            $this->addRecentlyViewedProductId($productId);
        }
    }

    /**
     * Add product id to the session stack
     *
     * @param integer $id Product ID
     */
    protected function addRecentlyViewedProductId($id)
    {
        $ids = $this->getRecentlyViewedProductIds();
        $limit = $this->scopeConfig->getValue('alsoviewed/session/limit');
        $offset = count($ids) - $limit;
        if ($offset > 0) {
            $ids = array_slice($ids, $offset);
        }
        $ids[] = $id;
        $this->session->setAlsoviewedProductIds($ids);
        return $this;
    }

    /**
     * Get recentrly viewed product ids array
     *
     * @return array
     */
    protected function getRecentlyViewedProductIds()
    {
        $ids = $this->session->getAlsoviewedProductIds();
        if (!$ids) {
            return [];
        }
        return $ids;
    }
}