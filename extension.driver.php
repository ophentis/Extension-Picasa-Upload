<?php

	require EXTENSIONS . '/picasa_upload/lib/class.PicasaUpload.inc';
	
	Class Extension_Picasa_Upload extends Extension {
	
		const UPLOAD_FILE_NAME = 'picasa';
	
		public function about() {
			return array(
				'name' => 'Picasa photo controller',
				'version' => '0.1a',
				'release-date' => '2011-04-21',
				'author' => array(
					'name' => 'Wiily',
					'website' => 'http://1009design.com',
					'email' => 'willy@1009design.com'
				),
				'description' => 'upload or download photos from Picasa.'
			);
		}
	
		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				),
				array(
					'page' => '/system/preferences/',
					'delegate' => 'Save',
					'callback' => '__SavePreferences'
				),
				array(
					'page' => '/blueprints/events/new/',
					'delegate' => 'AppendEventFilter',
					'callback' => 'appendEventFilter'
				),						
				array(
					'page' => '/blueprints/events/edit/',
					'delegate' => 'AppendEventFilter',
					'callback' => 'appendEventFilter'
				),						
				array(
					'page' => '/frontend/',
					'delegate' => 'EventPreSaveFilter',
					'callback' => 'eventPreSaveFilter'
				),
			);
		}
		
		public function appendEventFilter($context) {
			$context['options'][] = array(
				'picasa-upload',
				@in_array('picasa-upload', $context['selected']),
				'upload photo to picasa.'
			);
		}
	
		public function eventPreSaveFilter($context) {
			if(!in_array('picasa-upload', $context['event']->eParamFILTERS)) {
				return;
			}
			$filter_result = array('test'=>'willy');
			
			$message = '';
			
			$owner = '1009design001';
			$album = 'memorial';
			$title = !empty($context['fields']['title']) ? $context['fields']['title'] : '';
			$tag = !empty($context['fields']['tag']) ? $context['fields']['tag'] : '';
			$photo = $context['fields']['photo'];
			
			$authSubToken = PicasaUpload::getAuthSubToken();
			if( empty($authSubToken) ) {
				$config = $context['parent']->Configuration;
			
				$username = $config->get('username','picasa-upload');
				$password = $config->get('password','picasa-upload');
			
				$picasa = new PicasaUpload();
				$picasa->connectByAccount($username,$password);
				
				$success = $picasa->upload($username, $album, $photo, $title, $tag);
				
				$message = ($success) ? __('Upload success.') : $picasa->error;
			} else {
				/*
				$picasa = new PicasaUpload();
				$picasa->connectByToken();
				
				$success = $picasa->upload($username, $album, $photo);
				
				$message = ($success) ? __('Upload success.') : __('Upload failed.');
				*/
			}
			
			
			$context['messages'][] = array('Picasa Upload', false, $message);
		}
		
		public function appendPreferences($context) {

			// Create preference group
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', __('Picasa Upload')));
			
			$config = Symphony::Configuration();
			
			// Append settings
			$text = $config->get('username','picasa-upload');
			$usernameInput = Widget::Input('settings[picasa-upload][username]', $text, 'text');
			$usernameLabel = Widget::Label('Username:',$usernameInput);
			
			$text = $config->get('password','picasa-upload');
			$passwordInput = Widget::Input('settings[picasa-upload][password]', $text, 'password');
			$passwordLabel = Widget::Label('Password:',$passwordInput);
			
			$group->appendChild($usernameLabel);
			$group->appendChild($passwordLabel);
			
			// Append new preference group			
			$context['wrapper']->appendChild($group);
		}	
		
		/**
		 * Save preferences
		 *
		 * @param array $context
		 *  delegate context
		 */
		public function __SavePreferences($context) {

			if(!is_array($context['settings'])) {
				$context['settings'] = array('highrise' => array('username' => '', 'password' => ''));
			}
			
		}
	}
