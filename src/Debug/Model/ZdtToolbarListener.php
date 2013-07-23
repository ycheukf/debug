<?php
namespace FengruzhuoDebug\Model;
use ZendDeveloperTools\Listener\ToolbarListener;
use Zend\EventManager\ListenerAggregateInterface;
use ZendDeveloperTools\ProfilerEvent;
use Zend\View\Model\ViewModel;
use BjyProfiler\Db\Profiler\Query;

class ZdtToolbarListener extends ToolbarListener implements ListenerAggregateInterface{


		private function _setAdapterDebugMsg($sm, $DbCollector, $sAdapter, $slaveFlag=0){
			//				if ($oAdapter instanceof Dmlib\MasterSlaveAdapter\DmAdapter){
			$aServices = $sm->getRegisteredServices();
			$aCanonicalNames = $sm->getCanonicalNames();

			if($sm->has($sAdapter) && isset($aCanonicalNames[$sAdapter]) && in_array($aCanonicalNames[$sAdapter], $aServices['instances'])){
				$oAdapter = $sm->get($sAdapter);
//				if ($oAdapter instanceof Dmlib\MasterSlaveAdapter\DmAdapter){
					$aQuery = $slaveFlag ? $oAdapter->getSlaveAdapter()->getProfiler()->getQueryProfiles() : $oAdapter->getProfiler()->getQueryProfiles();
					$aSql = array();
					if(count($aQuery)){
						foreach ($aQuery as $profile){
							$query = $profile->toArray();
							$sSqlTmp = "[".$query['elapsed']."]<p>".$query['sql'];
							if(count($query['parameters'])){
								$sSqlTmp .= "<ul>";
								foreach($query['parameters'] as $k=>$v)
									$sSqlTmp .= "<li>".($k ."=>". $v);
								$sSqlTmp .= "</ul>";
							}
							$aSql[] = "<p>".$sSqlTmp;
						}
					}
					if(count($aSql))
						FengruzhuoDebug::_(join("<hr>",$aSql), 'a', '[sql]'.$sAdapter.($slaveFlag?" slave": " master") , array('datatag'=>'div'));
				}
//			}
		}
    public function onCollected(ProfilerEvent $event){

        $app = $event->getApplication();
        $sm  = $app->getServiceManager();
		$DbCollector = $sm->get('ZendDeveloperTools\DbCollector');

		$this->_setAdapterDebugMsg($sm, $DbCollector, 'Dmlib\Db\Adapter\Adapter');
		$this->_setAdapterDebugMsg($sm, $DbCollector, 'Dmlib\Db\Adapter\Adapter', 1);


		$this->_setAdapterDebugMsg($sm, $DbCollector, 'Zend\Db\Adapter\Adapter');
		$this->_setAdapterDebugMsg($sm, $DbCollector, 'Zend\Db\Adapter\Adapter', 1);
//		var_export($oAdapter->getSlaveAdapter()->getProfiler()->getQueryProfiles());

//		$oAdapter = $sm->get('Zend\Db\Adapter\Adapter');
//		var_export($oAdapter->getProfiler()->getQueryProfiles());
//		var_export($oAdapter->getSlaveAdapter()->getProfiler()->getQueryProfiles());
//		var_export(get_class($DbCollector));

		//-----add by feng start------
//        $entries     = $this->renderEntries($event);
//        $toolbarView = new ViewModel(array('entries' => $entries));
//        $toolbarView->setTemplate('zend-developer-tools/toolbar/toolbar');
//        $toolbarCss  = new ViewModel(array(
//            'position' => $this->options->getToolbarPosition(),
//        ));
//        $toolbarCss->setTemplate('zend-developer-tools/toolbar/style');
//        $style       = $this->renderer->render($toolbarCss);
//        $toolbar     = $this->renderer->render($toolbarView);


		//消除同时加载多个toolbar时候的重叠
//		$uid = uniqid();
//		$style = preg_replace("|position: fixed;|si", "", $style);
//		$style = str_replace("zend-developer-toolbar", "zend-developer-toolbar-".$uid, $style);
//		$toolbar = str_replace("zend-developer-toolbar", "zend-developer-toolbar-".$uid, $toolbar);
	
		//db query
//		preg_match_all("|.*<div class=\"zdt-toolbar-entry\">.*(<div class=\"zdt-toolbar-detail zdt-toolbar-dbquery-detail.+?</div>\s+?</div>\s+?)|si", $toolbar, $aMatch);
//		$toolbar = $aMatch[1][0];


//		FengruzhuoDebug::_($toolbar, 'a', 'sql query', array('datatag'=>'div'));
//        $serviceManager = $event->getApplication()->getServiceManager();
//		$aConfig = $serviceManager->get('configuration');
//		$sContent = file_get_contents($aConfig['debugconfig']['cachepath']);
//        $sContent    = preg_replace('/<\/body>/i', $toolbar . "\n</body>", $sContent, 1);
//        $sContent    = preg_replace('/<\/head>/i', $style . "\n</head>", $sContent, 1);
//		$fw = fopen($aConfig['debugconfig']['cachepath'], "w");
//		fwrite($fw, $sContent);
//		fclose($fw);
		//-----add by feng end------
	}

}
