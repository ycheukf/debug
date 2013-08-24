<?php
namespace YcheukfDebug\Model;

class Profiler extends \BjyProfiler\Db\Profiler\Profiler
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
		$oProfile = end($this->profiles);
		$aInfo = $oProfile->toArray();
		$aDebugInfo = array(
			"type" => __FUNCTION__,
			"connection_dsn" => $this->aConnectionParameters['dsn'],
			"time" => "",
			"sql" => $aInfo['sql'],
			"datas" => $aInfo['parameters']->getNamedArray()
		);
//		\YcheukfDebug\Model\Debug::dump($this->_fmtTableView($aDebugInfo), "[inline]---[db]---".__CLASS__, array());
		\YcheukfDebug\Model\Debug::dump($aDebugInfo, "[inline]---[db]---".__CLASS__);
		return $aReturn;
	 }
    /**
     * @return Profiler
     */
    public function profilerFinish()
    {
        $aReturn = parent::profilerFinish();
		$oProfile = end($this->profiles);
		$aInfo = $oProfile->toArray();
		$aDebugInfo = array(
			"type" => __FUNCTION__,
			"connection_dsn" => $this->aConnectionParameters['dsn'],
			"time" => $aInfo['elapsed'],
			"sql" => $aInfo['sql'],
			"datas" => $aInfo['parameters']->getNamedArray()
		);

		\YcheukfDebug\Model\Debug::dump($aDebugInfo, "[inline]---[db]---".__CLASS__);
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
