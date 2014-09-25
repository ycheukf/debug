<?php
namespace YcheukfDebug\Model;

class Debug{
	static $em=null;
	static $sm=null;
	static $triggerDebugflag=true;
	static $bDumpFlag=true;

	static function setEventManager($em){
		self::$em = $em;
	}
	static function setServiceManager($sm){
		self::$sm = $sm;
	}

	static function setTriggerDebugflag($b){
		self::$triggerDebugflag = $b;
	}
	static function setDumpFlag($b){
		self::$bDumpFlag = $b;
	}
	/** debug 函数
	@param mix data 调试的数据
	@param string method 写文件方法,传'w'则覆盖,传'a'则续写
	@param string memo 摘要信息
	@param array aCustomParam 自定义参数 xmp=>是否使用xmp来渲染
	@author feng
	@return bool
	*/
	static function dump($data, $memo='None', $aCustomParam=array('datatag'=>'xmp'),$method="a", $cacheFile=null)
	{
		/********************配置区域***************************/
		$sLocalFile = dirname(__FILE__)."/../../../config/module.config.php";
		$sGlobalFile = dirname(__FILE__)."/../../../../../../config/autoload/ycfdebug.global.php";
		$aDebugConfig = file_exists($sGlobalFile) ? require($sGlobalFile) : require($sLocalFile);
		$cacheFile = is_null($cacheFile) ? $aDebugConfig['debugconfig']['cachepath'] : $cacheFile;//debug文件存放地址
		$sCacheKey = basename($cacheFile);
		$debugFlag = $aDebugConfig['debugconfig']['enable'] && self::$triggerDebugflag;//调试标识. 0=>不记录, 1=>记录
		$sJqueryPath = dirname(__FILE__)."/jquery.min.js";
		$sAdapter = $aDebugConfig['debugconfig']['adapter'];
		$aAllowIps = isset($aDebugConfig['debugconfig']['allowips']) ? $aDebugConfig['debugconfig']['allowips'] : array();
		/********************配置区域 end***************************/

		//使用了限制ip功能 && 来源是远程IP
		if(!empty($aAllowIps) && isset($_SERVER['REMOTE_ADDR']) && preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $_SERVER['REMOTE_ADDR'])){
//			var_dump($aAllowIps);
            $bReturn = false;
            foreach($aAllowIps as $reg){
                if(preg_match('/'.str_replace('.', '\.', $reg).'/i', $_SERVER['REMOTE_ADDR']))$bReturn = true;
            }
//            var_dump($bReturn);
			if($bReturn)return false;
		}
		if($debugFlag == 0 && $method=='a')return false;
		if(self::$bDumpFlag == false)return false;
		/********************zf 2 event***************************/
		/****
		* 本段逻辑有两个用处.
		* 1: 当调用该event时, 从event中所调用本函数被停止, 防止无限嵌套
		* 2: 从事件中取回是否需要继续执行后面代码的状态. 这个功能在调用API的时候有用
		**/
		if(!is_null(self::$em)){
			$sEventName = 'YcheukfDebugDump';
			if(in_array($sEventName, self::$em->getEvents())){
				self::setTriggerDebugflag(false);
				$oStopedResponce = self::$em->trigger($sEventName, self::$sm, array($data, $memo, $aCustomParam,$method),function($r) {return $r;});
				self::setTriggerDebugflag(true);
				if($oStopedResponce->first() === true)return false;
			}
		}
		/********************zf 2 event end***************************/
		switch($sAdapter){
			default:
			case 'file':
				if(!file_exists($cacheFile)){
					if(!file_exists(dirname($cacheFile)))
						mkdir(dirname($cacheFile), 777, true);
					$fp = fopen($cacheFile, 'w');
					fwrite($fp, "ok");
					fclose($fp);
				}
				if($debugFlag == 0 && $method=='w'){
					file_put_contents($cacheFile, "<html><head></head><body>['debugconfig']['enable'] 's value  is FALSE in this module config.php, set TRUE when debuging </body></html>");
					return false;
				}
			break;
			case 'memcache':
				$oMemcache = new \Memcache;
				foreach($aDebugConfig['debugconfig']['memcache_config']['servers'] as $aRow){
//					var_dump($aRow);
					$bFlag = $oMemcache->addServer($aRow[0], $aRow[1], $aRow[2]);
//					break;
				}
				if($debugFlag == 0 && $method=='w'){
					$oMemcache->set($sCacheKey, "<html><head></head><body>['debugconfig']['enable'] 's value  is FALSE in this module config.php, set TRUE when debuging </body></html>", false, 1000000);
					return false;
				}
			break;
			case 'redis':
				$oRedisCache = new \Redis();
				foreach($aDebugConfig['debugconfig']['memcache_config']['servers'] as $aRow){
//					var_dump($aRow);
					$bFlag = $oRedisCache->connect($aRow[0], $aRow[1]);
//					break;
				}
				if($debugFlag == 0 && $method=='w'){
					$oRedisCache->set($sCacheKey, "<html><head></head><body>['debugconfig']['enable'] 's value  is FALSE in this module config.php, set TRUE when debuging </body></html>");
					return false;
				}
			break;
		}

		if(isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI'])){
			if(preg_match("/.*FengruzhuoDebug.*/i", $_SERVER['REQUEST_URI'])){
				return false;
			}
		}
		$DebugFilePath = $_SERVER["PHP_SELF"];
		if($method=='w'){
			switch($sAdapter){
				default:
				case 'file':
					$sJquery = file_get_contents($sJqueryPath);
				break;
				case 'memcache':
					$sJquery = $oMemcache->get($aDebugConfig['debugconfig']['memcache_config']['jquery_keyname']);
					if(!$sJquery){
						$sJquery = file_get_contents($sJqueryPath);
						$oMemcache->set($aDebugConfig['debugconfig']['memcache_config']['jquery_keyname'], $sJquery, false, 1000000);
					}
				break;
				case 'redis':
					$sJquery = $oRedisCache->get($aDebugConfig['debugconfig']['memcache_config']['jquery_keyname']);
					if(!$sJquery){
						$sJquery = file_get_contents($sJqueryPath);
						$oRedisCache->set($aDebugConfig['debugconfig']['memcache_config']['jquery_keyname'], $sJquery);
					}
				break;
			}

			$oldContent = '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>DEBUGING LOG</title><script type="text/javascript">'.$sJquery.'</script>';
			$sStyle = <<<EOT
<style type="text/css">
body {
	margin: 0px;
	padding: 10px;
	height: 100%;
}

body, th, td {
	font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #333;
}
div.info{
	width: 880px;
	word-wrap: break-word;
}
.clear{
	clear: both;
	display: block;
}
</style>
EOT;
			$sScript = <<<EOT
			<script>
			\$(function() {
				var _oMemo = {all:{label:'all', total:$("div.block").length}};
				\$("div.block").each(function(){
					if(typeof _oMemo[$(this).attr('_k')] == 'undefined')
						_oMemo[$(this).attr('_k')] = {};
					if(typeof _oMemo[$(this).attr('_k')]['total'] == 'undefined'){
						_oMemo[$(this).attr('_k')]['total'] = 1;
						_oMemo[$(this).attr('_k')]['label'] = $(this).attr('_l');
					}else
						_oMemo[$(this).attr('_k')]['total'] += 1;
				})
				var sUl = "";
				for(var k in _oMemo){
					sUl += '<li><a _k="'+k+'" href="javascript:void()" >'+_oMemo[k]['label']+'('+_oMemo[k]['total']+')</a></li>';
				}
				$('div#tabs').html("<ul>"+sUl+"</ul><div  style=\"position:absolute;top:10px;right:20px;\" class='allinfoSwith'><a href='javascript:void()' >All Fold/Unfold</a></div>");
				$('div#tabs li a').click(function(){
					var _showK = $(this).attr('_k');
					var sAnchor = location.href.substring(location.href.lastIndexOf('#'))
					location.href = location.href.replace(sAnchor, "#"+_showK);
					if(_showK == 'all'){
						$('div.block').show();
					}else{
						$('div.block').hide();
						$('div.block[_k="'+_showK+'"]').show();
					}
				});
				if(location.href.lastIndexOf('#') != -1){
					var sAnchor = location.href.substring(location.href.lastIndexOf('#'))
					$('div#tabs li a[_k="'+sAnchor.replace("#", "")+'"]').click()
				}
				$('div.block span.infoswitch a').click(function(){
					var _o = $(this).parents('div.block').find('div.info').eq(0);
						_o.toggle();
				});
				var allinfoSwithIndex = 0;
				$('div.allinfoSwith a').click(function(){
					allinfoSwithIndex%2==0 ? $('div.info').hide() : $('div.info').show();
					allinfoSwithIndex++;
				});
//				\$( "#tabs" ).tabs();
			});
			</script>
EOT;
			$oldContent .=$sStyle.'</head><body>
			<div>
				<b>\\'.__CLASS__."::".__FUNCTION__."(\$var, 'memo')".';</b>
				<p><b>\YcheukfCommon\Lib\Functions::debug($var, \'memo\');</b>
				<p>use the above code in your code as var_dump(), the output will be rewrote to this file instead of printing directly. current adapter:'.$sAdapter.'
			</div>
			<hr>
			<div id="tabs"></div></body></html>'.$sScript;
		}else{
			switch($sAdapter){
				default:
				case 'file':
					$oldContent = (file_exists($cacheFile)) ? file_get_contents($cacheFile) : "";
				break;
				case 'memcache':
					$oldContent = $oMemcache->get($sCacheKey);
				break;
				case 'redis':
					$oldContent = $oRedisCache->get($sCacheKey);
				break;
			}
		}
		$sBlockHTML = "\n\n\n<div class='block' _k='".md5($memo)."' _l='".$memo."'><span style='display:none'><------orderIndex-------></span>";
		$orderIndex = substr_count($oldContent, '<------orderIndex------->');

		list($em, $es) = explode(' ', empty($time) ? microtime() : $time);
        $timespan = (float)$em + (float)$es;

		 $str = $sBlockHTML;
		 $str .= "<span  class='no' style='color:blue;'>NO</span>:\t".++$orderIndex."\n\n";
		 $str .= "<span  style='color:blue;'>Timespan</span>:\t".$timespan."\n\n";
		 $str .= "\t<span  style='color:blue;'>Date</span>:\t".date("Y-m-d H:i:s")."\n";
		 $str .= "\t<span  style='color:blue;'>File</span>:\t".$DebugFilePath."\n";
		 $str .= "<br/><span class='memo' style='color:blue;'>Memo</span>:\t".$memo."<br>\n";
		 $str .= "----------------------------------------<span class='infoswitch'><a  href='javascript:void()' >Fold/Unfold</a></span> <a href='#tabs' >top</a>\n<div class='info'>";
		 $Tab = "";
		ob_start();
		if(is_string($data))
			echo $data;
		elseif(is_array($data))
			var_export($data);
		else{
			var_dump($data);
			unset($aCustomParam['datatag']);
		}
		$a = ob_get_contents();
		ob_end_clean();
		if(isset($aCustomParam['datatag']))
			$str .= "<".$aCustomParam['datatag'].">";
		$str .= $a;
		if(isset($aCustomParam['datatag']))
			$str .= "</".$aCustomParam['datatag'].">";
		 $str .= "</div>\n<hr></div>\n\n\n";

		$oldContent = str_replace("</body>", $str."</body>", $oldContent);

		switch($sAdapter){
			default:
			case 'file':
				file_put_contents($cacheFile, $oldContent);
			break;
			case 'memcache':
				$oldContent = $oMemcache->set($sCacheKey, $oldContent, false, 20000);
			break;
			case 'redis':
				$oldContent = $oRedisCache->set($sCacheKey, $oldContent);
			break;
		}
		return 1;
	}
}
