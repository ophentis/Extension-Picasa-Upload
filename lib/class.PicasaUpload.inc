<?php

	$clientLibraryPath = EXTENSIONS . '/picasa_upload/lib/';
	$oldpath = set_include_path(get_include_path() . PATH_SEPARATOR . $clientLibraryPath);
	
	// ini_set("display_errors", true);
	// ini_set("display_startup_errors", true);
	// error_reporting(E_ALL);
	
	require_once 'Zend/Loader.php';
	Zend_Loader::loadClass('Zend_Gdata_Photos');
	Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
	Zend_Loader::loadClass('Zend_Gdata_AuthSub');
	Zend_Loader::loadClass('Zend_Gdata_Photos_AlbumQuery');

	class PicasaUpload {
		const TOKEN_NAME = 'picasa-upload';
		
		private $service = null;
		public $error = '';
		
		public function connectByAccount($username,$password){
			try {
				//create service by username and password
				$service = Zend_Gdata_Photos::AUTH_SERVICE_NAME;
				$client = Zend_Gdata_ClientLogin::getHttpClient($username, $password, $service);
				$this->service = new Zend_Gdata_Photos($client);
			} catch(Exception $e) {
        $this->error = $e->getMessage();
				return false;
			}
			return true;
		}
		
		public function connectByToken() {
			try {
				//create service by auth sub token
				$service = new Zend_Gdata_Photos(self::getAuthSubHttpClient());
			} catch(Exception $e) {
        $this->error = $e->getMessage();
				return false;
			}
			return true;
		}
		
		public function upload($username,$album,$photo,$title='',$tag='') {
			if(!$this->service) {
				return false;
			}
			
			try{
				$fd = $this->service->newMediaFileSource($photo['tmp_name']);
				$fd->setContentType($photo['type']);
				 
				$entry = new Zend_Gdata_Photos_PhotoEntry();
				$entry->setMediaSource($fd);
				
				if( empty($title) ) {
					$title = $photo['name'];
				}
				$entry->setTitle($this->service->newTitle($title));
				 
				$albumQuery = new Zend_Gdata_Photos_AlbumQuery();
				$albumQuery->setUser($username);
				$albumQuery->setAlbumName($album);
				
				$albumEntry = $this->service->getAlbumEntry($albumQuery);

				$this->service->insertPhotoEntry($entry, $albumEntry);
			} catch( Exception $e ) {
				$this->error = $e->getMessage();
				return false;
			}
			
			return true;
		}
		
		/**
		 *	create picasa web alubm authorization link url
		 */
		public static function getAuthSubUrl($next) {
			$scope = 'http://picasaweb.google.com/data';
			$secure = false;
			$session = true;
			
			$url = Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
			return General::sanitize($url);
		}
		
		public static function getAuthSubToken($token = false) {
			if($token) {
				$token = urldecode($token);
				try {
					$token = Zend_Gdata_AuthSub::getAuthSubSessionToken($token);
					Session::write(PicasaUpload::TOKEN_NAME, $token);
				} catch(Zend_Gdata_App_AuthException $e) {
					$token = Session::read(PicasaUpload::TOKEN_NAME);
				}
			} else {
				$token = Session::read(PicasaUpload::TOKEN_NAME);
			}
			return $token;
		}
		
		private static function getAuthSubHttpClient() {
			$token = self::getAuthSubToken();
			
			$client = Zend_Gdata_AuthSub::getHttpClient($token);
			return $client;
		}
	}
