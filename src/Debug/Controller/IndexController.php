<?php
namespace YcheukfDebug\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $this->layout('debug/layout');
		$aConfig = $this->getServiceLocator()->get('configuration');

        if (!is_file($aConfig['debugconfig']['cachepath'])) {
            echo "debug file '".$aConfig['debugconfig']['cachepath']."' is not exists. ";
        }else
			echo file_get_contents($aConfig['debugconfig']['cachepath']);		
    }
}
