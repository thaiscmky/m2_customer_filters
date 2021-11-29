<?php
/**
 * @author      Thais Cailet <thaiscmky@users.noreply.github.com>
 * @package     ThaisCmky_CustomerFilters
 * @copyright   Copyright (c) 2021 Thais Cailet (https://thaiscmky.github.io/)
 */
namespace ThaisCmky\CustomerFilters\Controller\Productlist;

use Magento\Ui\Controller\Adminhtml\AbstractAction;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Render
 */
class Render extends AbstractAction
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if ($this->_request->getParam('namespace') === null) {
            return $this->_redirect('noroute');
        }
        $component = $this->factory->create($this->getRequest()->getParam('namespace'));
        $this->prepareComponent($component);
        $this->getResponse()->appendBody((string)$component->render());

        return $this->getResponse();
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }
}
