<?php
namespace FengruzhuoDebug\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $this->layout('debug/layout');
		$aConfig = $this->getServiceLocator()->get('configuration');
		include($aConfig['debugconfig']['cachepath']);		
    }
}
