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
		$sCachePath = isset($_GET['filename']) && !empty($_GET['filename']) ? $_GET['filename'] : $aConfig['debugconfig']['cachepath'];

		switch($sAdapter){
			default:
			case 'file':
				if (!is_file($sCachePath)) {
					echo "debug file '".$sCachePath."' is not exists. ";
				}else
					echo file_get_contents($sCachePath);
				break;
			case 'memcache':
				$oMemcache = new \Memcache;
				foreach($aConfig['debugconfig']['memcache_config']['servers'] as $aRow){
					$bFlag = $oMemcache->addServer($aRow[0], $aRow[1], $aRow[2]);
				}
				echo $oMemcache->get(basename($sCachePath));

				break;
		}
        return $this->response;
    }
}
