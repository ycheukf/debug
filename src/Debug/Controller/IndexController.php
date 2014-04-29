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
		$sAdapter = $aConfig['debugconfig']['adapter'];

		switch($sAdapter){
			default:
			case 'file':
				if (!is_file($aConfig['debugconfig']['cachepath'])) {
					echo "debug file '".$aConfig['debugconfig']['cachepath']."' is not exists. ";
				}else
					echo file_get_contents($aConfig['debugconfig']['cachepath']);
				break;
			case 'memcache':
				$oMemcache = new \Memcache;
				foreach($aConfig['debugconfig']['memcache_config']['servers'] as $aRow){
					$bFlag = $oMemcache->addServer($aRow[0], $aRow[1], $aRow[2]);
				}
				echo $oMemcache->get($aConfig['debugconfig']['memcache_config']['debug_keyname']);

				break;
		}
        return $this->response;
    }
}
