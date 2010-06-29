<?php

/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

  class validate_FileType extends validate_AbstractValidator {
  	private $mimeTypes = array();
  	private $extensions = array();
  	private $extensionText = '';
  	private $fileType = array(
  								// images
  								'jpg'	=> array('image/jpeg', 'image/jpg', 'image/jp_', 'application/jpg', 'application/x-jpg', 'image/pjpeg', 'image/pipeg', 'image/vnd.swiftview-jpeg', 'image/x-xbitmap'),
  								'gif'	=> array('image/gif', 'image/x-xbitmap', 'image/gi_'),
  								'png'	=> array('image/png','application/png','application/x-png'),
  								'bmp'	=> array('image/bmp', 'image/x-bmp', 'image/x-bitmap', 'image/x-xbitmap', 'image/x-win-bitmap', 'image/x-windows-bmp', 'image/ms-bmp', 'image/x-ms-bmp', 'application/bmp', 'application/x-bmp', 'application/x-win-bitmap', 'application/preview'),

  								// text formats
  								'txt'	=> array('text/plain', 'application/txt', 'browser/internal', 'text/anytext', 'widetext/plain', 'widetext/paragraph'),
  								'css'	=> array('text/css','application/css-stylesheet'),
  								'html'	=> array('text/html','text/plain'),
  								'php'	=> array('application/x-httpd-php', 'text/php', 'application/php', 'magnus-internal/shellcgi', 'application/x-php'),
  								'sql'	=> array('text/plain', 'application/txt', 'browser/internal', 'text/anytext', 'widetext/plain', 'widetext/paragraph'),


  								// application specific
  								'pdf'	=> array('application/pdf', 'application/x-pdf', 'application/acrobat', 'applications/vnd.pdf', 'text/pdf', 'text/x-pdf'),
  								'rtf'	=> array('application/rtf', 'application/x-rtf', 'text/rtf', 'text/richtext', 'application/msword', 'application/doc', 'application/x-soffice'),
  								// microsoft office
  								'doc'	=> array('application/msword', 'application/doc', 'appl/text', 'application/vnd.msword', 'application/vnd.ms-word', 'application/winword', 'application/word', 'application/x-msw6', 'application/x-msword', 'zz-application/zz-winassoc-doc', 'application/vnd.ms-office'),
  								'xls'	=> array('application/msexcel', 'application/x-msexcel', 'application/x-ms-excel', 'application/vnd.ms-excel', 'application/x-excel', 'application/x-dos_ms_excel', 'application/xls', 'application/x-xls', 'zz-application/zz-winassoc-xls'),
								// open office
  								'odt'	=> array('application/vnd.oasis.opendocument.text', 'application/x-vnd.oasis.opendocument.text'),
								'odf'	=> array('application/vnd.oasis.opendocument.formula', 'application/x-vnd.oasis.opendocument.formula'),

  								// video
  								'mp4'	=> array('video/mp4'),
  								'mpeg'	=> array('video/mpeg', 'video/mpg', 'video/x-mpg', 'video/mpeg2', 'application/x-pn-mpg', 'video/x-mpeg', 'video/x-mpeg2a', 'audio/mpeg', 'audio/x-mpeg', 'image/mpg'),
  								'mpg'	=> array('video/mpeg', 'video/mpg', 'video/x-mpg', 'video/mpeg2', 'application/x-pn-mpg', 'video/x-mpeg', 'video/x-mpeg2a', 'audio/mpeg', 'audio/x-mpeg', 'image/mpg'),
  								'avi'	=> array('video/avi', 'video/msvideo', 'video/x-msvideo', 'image/avi', 'video/xmpg2', 'application/x-troff-msvideo', 'audio/aiff', 'audio/avi'),
  								'mov'	=> array('video/quicktime', 'video/x-quicktime', 'image/mov', 'audio/aiff', 'audio/x-midi', 'audio/x-wav', 'video/avi'),
  								'qt'	=> array('video/quicktime', 'audio/aiff', 'audio/x-wav', 'video/flc'),
  								'3gp'	=> array('audio/3gpp', 'video/3gpp'),
  								'flv'	=> array('video/x-flv'),

  								// audio
  								'mp3'	=> array('audio/mpeg', 'audio/x-mpeg', 'audio/mp3', 'audio/x-mp3', 'audio/mpeg3', 'audio/x-mpeg3', 'audio/mpg', 'audio/x-mpg', 'audio/x-mpegaudio'),
  								'm4a'  	=> array('audio/mp4'),
  								'wma'  	=> array('audio/x-ms-wma'),
  								'wav'  	=> array('audio/wav', 'audio/x-wav', 'audio/wave', 'audio/x-pn-wav'),
  								'ogg'  	=> array('audio/x-ogg', 'application/x-ogg'),
  							);

 	public function __construct($types) {
 		$this->extensions = $types;
 		foreach($types as $type) {
 			$this->getMimeType($type);
 			if(!empty($this->extensionText))$this->extensionText .= '/';
 			$this->extensionText .= ''.$type;
 		}
 	}
 	public function getMimeType($extension) {

 		if(array_key_exists($extension, $this->fileType)) {
 			if(!is_array($this->fileType[$extension]))
 				$this->mimeTypes[] =  $this->fileType[$extension];
 			else {
 				foreach($this->fileType[$extension] as $mimeType) {
 					$this->mimeTypes[] = $mimeType;
 				}
 			}
 		} else throw new Exception('unknown filetype: '.$extension);
 	}
 	public function getExtensionFromMime($mime) {
 		$extensionArr = array();
 		foreach($this->fileType as $extension => $mimeTypeArr)
 			if(in_array($mime, $mimeTypeArr)) $extensionArr[] = $extension;

		return $extensionArr;
 	}
 	public function validate($data) {

 		// test if the host supports finfo
 		try {
 			$supportsFinfo = class_exists('finfo');
 		} catch(loader_ClassNotFoundException  $e) {
	 		$supportsFinfo = false;
 		}

 		if($data == '') {
 			return true;

		// if finfo class exists then use that for mime validation
 		} elseif($supportsFinfo && $data['tmp_name']) {

			$finfo = new finfo(FILEINFO_MIME);
			$mime = $finfo->file($data['tmp_name']);
			if(strpos($mime,';')) $mime = substr($mime,0,strpos($mime,';'));
 			if(in_array($mime, $this->mimeTypes)) return true;

 			$incorrectExtensionArr = $this->getExtensionFromMime($mime);
 		// reply on the browsers mime type : not always present & secruity vunrebility
 		} elseif(isset($data['type'])) {
 			if(in_array($data['type'], $this->mimeTypes)) return true;
 			$incorrectExtensionArr = $this->getExtensionFromMime($data['type']);
 		}

 		throw new Exception(sf('File should be a valid %s%s', $this->extensionText, count($incorrectExtensionArr)?sf('(not a %s)', implode('/', $incorrectExtensionArr)):''));

 	}


 }
?>
