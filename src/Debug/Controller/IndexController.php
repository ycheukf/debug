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
//					var_dump($aRow);
					$bFlag = $oMemcache->addServer($aRow[0], $aRow[1], $aRow[2]);
					break;
				}
				echo $oMemcache->get(basename($sCachePath));

				break;
			case 'redis':
				$oRedisCache = new \Redis;
				foreach($aConfig['debugconfig']['memcache_config']['servers'] as $aRow){
//					var_dump($aRow);
					$bFlag = $oRedisCache->connect($aRow[0], $aRow[1]);
//                    $oRedisCache->select($aRow[2]);
					break;
				}
				echo $oRedisCache->get(basename($sCachePath));

				break;
			default:
				die("unknow adapter:".$sAdapter);
				break;
		}
        return $this->response;
    }
}
