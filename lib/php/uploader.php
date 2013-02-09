<?php
/**
 * File uploader
 */

/**
 * A PHP file uploader, it includes many security checks although it is very minimalistic
 *
 * This Class allows you to very easily upload files. It checks filesize, generates a new name
 * if you want, or uses whatever name you specify.
 * @author Alejandro U. Alvarez
 * @version 2.0
 * @package Files
 */
class Uploader{
	/** Max allowed size, in bytes
	 */
	private $maxSize;
	/** Allowed extensions, CSV
	 */
	private $allowedExt;
	/** File information array
	 */
	private $fileInfo = array();
	
	/**
	 * Class constructor, use it to set the maximum size and the allowed extensions
	 * @param int Maximum size in bytes
	 * @param string Comma separated list of allowed extensions. Ex. 'gif,png,jpeg'
	 */
	function Uploader($maxSize,$allowedExt){
		$this->maxSize = $maxSize;
		$this->allowedExt = $allowedExt;
	}
	
	/**
	 * Check whether the uploaded file meets the requirements
	 * @param string Name used in the file upload HTML field
	 * @return boolean Whether the file is valid
	 * @access private
	 */
	private function check($uploadName){
		global $sess;
		if(isset($_FILES[$uploadName])){
			$this->fileInfo['ext'] = substr(strrchr($_FILES[$uploadName]["name"], '.'), 1);
			$this->fileInfo['name'] = basename($_FILES[$uploadName]["name"]);
			$this->fileInfo['size'] = $_FILES[$uploadName]["size"];
			$this->fileInfo['temp'] = $_FILES[$uploadName]["tmp_name"]; 
			if(!getimagesize($_FILES[$uploadName]['tmp_name'])){
				$sess->set_msg(_('Formato incorrecto, únicamente se permiten "').$this->allowedExt.'"');
				return false; //failed ext
			}
			$exts = explode(',',$this->allowedExt);
			// Comprobamos el type tambien
			$types = explode('/',$_FILES[$uploadName]['type']);
			if($types[0]!=='image' || !in_array($types[1],$exts)){
				$sess->set_msg(_('Formato incorrecto, únicamente se permiten "').$this->allowedExt.'"');
				return false; //failed ext
			}
			if($this->fileInfo['size']<$this->maxSize){
				if(strlen($this->allowedExt)>0){
					if(in_array($this->fileInfo['ext'],$exts)){
						return true;
					}
					$sess->set_msg(_('Formato incorrecto, únicamente se permiten "').$this->allowedExt.'"');
					return false; //failed ext
				}
				$sess->set_msg(_('Lo siento pero no he podido procesar la subida, intentalo mas tarde'));
				return false; //All ext allowed
			}else{
				if($this->maxSize < 1000000){
					$rsi = round($this->maxSize/1000,2).' Kb';
				}else if($this->maxSize < 1000000000){
					$rsi = round($this->maxSize/1000000,2).' Mb';
				}else{
					$rsi = round($this->maxSize/1000000000,2).' Gb';
				}
				$sess->set_msg(_('El archivo es demasiado grande, el tamaño máximo es "').$rsi.'"');
				return false; //failed size
			}
		}
		$sess->set_msg(_('Ha ocurrido algo raro! Por favor intentalo mas tarde'));
		return false; //Either form not submitted or file/s not found
	}
	
	/** 
	 * Upload the file, call directly after the constructor
	 *
	 * If any errors appear during the file upload it will call Session:set_msg() to notify the user
	 * and then return false;
	 * If the file name already exists it will change it to a random name, until its not taken
	 * @param string Upload field name
	 * @param stirng Directory where uploaded files should be stored
	 * @param string File name to store it with, leave blank if you want a random name
	 * @return boolean Whether the file could be uploaded.
	 */
	function upload($name,$dir,$fname=false){
		global $sess;
		if(!is_dir($dir)){
			$sess->set_msg(_('No he podido procesar la imagen, intetalo de nuevo mas tarde'));
			return false; //Directory doesn't exist! 
		}
		if($this->check($name)){
			//Process upload. All info stored in array fileinfo:
			//Dir OK, keep going:
			//Get a new filename:
			if(!$fname) $this->fileInfo['fname'] = $sess->generateRandStr(15).'.'.$this->fileInfo['ext'];
			else $this->fileInfo['fname'] = $fname;
			while(file_exists($dir.$this->fileInfo['fname'])){
				$this->fileInfo['fname'] = $sess->generateRandStr(15).'.'.$this->fileInfo['ext'];
			}
			// Unique name gotten
			// Move file:
			if(@move_uploaded_file($this->fileInfo['temp'], $dir.$this->fileInfo['fname'])){
				//Done
				return true;
			}else{
				$sess->set_msg(_('Aunque todo se hizo bien, no se pudo guardar el archivo, intentalo mas tarde.'));
				return false; //File not moved
			}
		}else{
			return false;
		}
	}

};
?>