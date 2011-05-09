<?php

	require_once(TOOLKIT . '/class.datasource.php');
	require_once(EXTENSIONS . '/picasa_upload/lib/class.PicasaUpload.inc');
	
	Class DatasourcePicasaToken extends Datasource{
		
		function __construct(&$parent, $env=NULL, $process_params=true){
			parent::__construct($parent, $env, $process_params);
		}
		
		function about(){
			return array(
				 'name' => 'Picasa AuthSub Token',
				 'version' => '1.2',
				 'release-date' => '2011-04-24');	
		}
		
		function grab(&$param_pool){
			
			$url = self::getCurrentUrl($this->_env['param']);
			
			$authSubToken = PicasaUpload::getAuthSubToken();
			if( empty($authSubToken) && isset($this->_env['param']['url-token']) ) {
				$authSubToken = PicasaUpload::getAuthSubToken($this->_env['param']['url-token']);
			}
			
			$next = self::getCurrentUrl($this->_env['param']);
			$authorized = empty($authSubToken) ? 'false' : 'true';
						
			$element = new XMLElement(PicasaUpload::TOKEN_NAME);
			$element->setAttribute('url',PicasaUpload::getAuthSubUrl($next));
			$element->setAttribute('auth',$authorized);
			
			return $element;
		}
		
		private static function getCurrentUrl($param) {
			$query = $_GET;
			unset($query['symphony-page']);
			unset($query['token']);
			$query = self::createQueryString($query);
			if( !empty($query) ) {
				$query = '?' . $query;
			}
			
			$url = $param['root'] . $param['parent-path'] . $param['current-page'] . $query;
			
			return $url;
		}
		
		private static function createQueryString($array) {
			$temp = array();
			foreach($array as $k => $v) {
				$temp[] = $k . '=' . urlencode($v);
			}
			$temp = implode('&',$temp);
			return $temp;
		}
		
	}

?>