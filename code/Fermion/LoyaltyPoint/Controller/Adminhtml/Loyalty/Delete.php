<?php
namespace Fermion\LoyaltyPoint\Controller\Adminhtml\Loyalty;

class Delete extends \Fermion\LoyaltyPoint\Controller\Adminhtml\Loyalty {
    public function execute() {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Fermion\LoyaltyPoint\Model\Loyalty');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the item.'));
                $this->_redirect('fermion/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                error_log($e->getMessage());
                $this->_redirect('fermion/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        $this->_redirect('fermion/*/');
    }
}
