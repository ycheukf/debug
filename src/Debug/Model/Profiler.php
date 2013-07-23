<?php
namespace FengruzhuoDebug\Model;

class Profiler extends \Zend\Db\Adapter\Profiler\Profiler
{
    /**
     *
     * @var bool
     */
    protected $aConnectionParameters = array();

      /**
     * @param array $aConnectionParameters
     */
	  public function setConnectionParameters($aConnectionParameters)
	{
		  $this->aConnectionParameters = $aConnectionParameters;
	}
      /**
     */
	  public function getConnectionParameters()
	{
		  return $this->aConnectionParameters;
	}


      /**
     * @param string|StatementContainerInterface $target
     * @throws \Zend\Db\Adapter\Exception\InvalidArgumentException
     * @return Profiler
     */
	  public function profilerStart($target)
	{
        $aReturn = parent::profilerStart($target);
		$aInfo = $this->getLastProfile();
		$aDebugInfo = array(
			"type" => __FUNCTION__,
			"connection_dsn" => $this->aConnectionParameters['dsn'],
			"time" => "",
			"sql" => $aInfo['sql'],
			"datas" => $aInfo['parameters']->getNamedArray()
		);
//var_dump(\PDO::ATTR_DRIVER_NAME);
//var_dump($target->getParameterContainer());
		\FengruzhuoDebug\Model\Debug::dump($this->_fmtTableView($aDebugInfo), "[inline]---[db]---".__CLASS__, array());
		return $aReturn;
	 }
    /**
     * @return Profiler
     */
    public function profilerFinish()
    {
        $aReturn = parent::profilerFinish();
		$aInfo = $this->getLastProfile();
		$aDebugInfo = array(
			"type" => __FUNCTION__,
			"connection_dsn" => $this->aConnectionParameters['dsn'],
			"time" => $aInfo['elapse'],
			"sql" => $aInfo['sql'],
			"datas" => $aInfo['parameters']->getNamedArray()
		);

		\FengruzhuoDebug\Model\Debug::dump($this->_fmtTableView($aDebugInfo), "[inline]---[db]---".__CLASS__, array());
		return $aReturn;	
    }

	private function _fmtTableView($aDebugInfo){
		$sHTML = "";
		foreach($aDebugInfo as $k => $v){
			if(is_array($v)){
				$sTmp = "";
				foreach($v as $k2 => $v2)
					$sTmp .= $k2." => ". $v2."   ;";
			}else{
				$sTmp = $v;
			}
			$sHTML .= "<li><div style='display:inline-block;width:140px;bloc'>".$k."</div>".$sTmp."</li>";
		}
		return "<ul>".$sHTML."</ul>";
	}
}
