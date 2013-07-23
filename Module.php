<?php
namespace FengruzhuoDebug;
use FengruzhuoDebug\Model\Debug;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\EventManager\StaticEventManager;
use Zend\EventManager\SharedEventManager;
use Zend\View\Model\ViewModel;

class Module 
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function setAttach($sm, $em){
		$em->attach(
			array('FengruzhuoDebugSetProfiler'), 
			function($e) use($sm) {
				$oDbAdapter = $sm->get('fengruzhuo_debug_db_adapter');

				//add profiler to adapter
				$oProfiler = new \FengruzhuoDebug\Model\Profiler;
				$oProfiler->setConnectionParameters($oDbAdapter->getDriver()->getConnection()->getConnectionParameters());
				$oDbAdapter->setProfiler($oProfiler);

				//add profiler to slaver adapter if exist
				if(method_exists($oDbAdapter, 'getSlaveAdapter')){
					$oProfiler = new \FengruzhuoDebug\Model\Profiler;
					$oProfiler->setConnectionParameters($oDbAdapter->getSlaveAdapter()->getDriver()->getConnection()->getConnectionParameters());
					$oDbAdapter->getSlaveAdapter()->setProfiler($oProfiler);
				}
			}, 
			-100
		);

		/**
		  * use the below code to add profiler when using master-slaver adatper

			$em->clearListeners('FengruzhuoDebugSetProfiler');
			$em->attach(
				array('FengruzhuoDebugSetProfiler'), 
				function($e) {
					//set profiler code
				}, 
				100
			);	
		*/
	}


	public function onBootstrap(MvcEvent $event){
        if (PHP_SAPI === 'cli') return;
		$aConfig = $this->getConfig();
		if(is_string($_SERVER['REQUEST_URI']) && (//ignore some request
			preg_match("/.*".str_replace("/", "\/", $aConfig['router']['routes']['debug']['options']['route']).".*/i", $_SERVER['REQUEST_URI'])||
			preg_match("/favicon.ico/i", $_SERVER['REQUEST_URI'])
			)
		){
			
		}else{
			$app = $event->getApplication();
			$em  = $app->getEventManager();
//			$sem = $em->getSharedManager();
			$sm  = $app->getServiceManager();

			$this->setAttach($sm, $em);
			$em->trigger('FengruzhuoDebugSetProfiler', $this);

			if($sm->get('request')->isXmlHttpRequest() == false){
				\FengruzhuoDebug\Model\Debug::dump($sm->get('request')->getRequestUri(), '[inline]---[request]---http start', array('datatag'=>'xmp'), 'w');
			}else
				\FengruzhuoDebug\Model\Debug::dump($sm->get('request')->getRequestUri(), '[inline]---[request]---ajax start');
			
		}
	}
    public function init(ModuleManagerInterface $manager)
	{
        if (PHP_SAPI === 'cli') return;
	}
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/Debug',
                ),
            ),
        );
    }

}
