<?
/***.-*.-..*.-..***-.--*---*..-*.-.***-...*.-*...*.***.-*.-.*.***-...*.*.-..*---*-.*--.***-*---***..-*...*.-.-.-***
 *                                                                                                                * 
 *       This sourcecode file is part of OpenIMS CE (Community Edition).                                          *
 *       OpenIMS CE (Community Edition) is a program developed by OpenSesame ICT B.V.                             *
 *       Copyright (C) 2001-2011 OpenSesame ICT B.V. Meerwal 13, NL-3432ZV, Nieuwegein.                           *
 *                                                                                                                *
 *       This program is free software; you can redistribute it and/or modify it under                            *
 *       the terms of the GNU General Public License version 3 as published by the                                *
 *       Free Software Foundation with the addition of the following permission added                             *
 *       to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK                             *
 *       IN WHICH THE COPYRIGHT IS OWNED BY OpenSesame ICT, OpenSesame ICT DISCLAIMS                              *
 *       THE WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.                                                  *
 *                                                                                                                *
 *       This program is distributed in the hope that it will be useful, but WITHOUT                              *
 *       ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS                            *
 *       FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more                                   *
 *       details.                                                                                                 *
 *                                                                                                                *
 *       You should have received a copy of the GNU General Public License along with                             *
 *       this program; if not, see http://www.gnu.org/licenses or write to the Free                               *
 *       Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA                                   *
 *       02110-1301 USA.                                                                                          *
 *                                                                                                                *
 *       You can contact OpenSesame ICT B.V. at Meerwal 13, NL-3432 ZV, Nieuwegein                                *
 *       or at e-mail address info@osict.com.                                                                     *
 *                                                                                                                *
 *       The interactive user interfaces in modified source and object code versions                              *
 *       of this program must display Appropriate Legal Notices, as required under                                *
 *       Section 5 of the GNU General Public License version 3.                                                   *
 *                                                                                                                *
 *       In accordance with Section 7(b) of the GNU General Public License version 3,                             *
 *       these Appropriate Legal Notices must retain the display of the "OpenIMS" logo.                           *
 *       If the display of the logo is not reasonably feasible for technical reasons, the                         *
 *       Appropriate Legal Notices must display the words "Powered by OpenIMS".                                   *
 *                                                                                                                *
 *       Please note the OpenIMS EE (Enterprise Edition) license explicitly forbids                               *
 *       transfer of code or concepts from OpenIMS EE to OpenIMS CE.                                              *
 *                                                                                                                * 
 ***.-*.-..*.-..***-.--*---*..-*.-.***-...*.-*...*.***.-*.-.*.***-...*.*.-..*---*-.*--.***-*---***..-*...*.-.-.-***/



/* Benodigde klassen om Open_IMS metadata in .odt en .ods documenten te plaatsen */
class OpenOfficeZipFile{
	var $archivename, $oldname;
     var $dir;
	var $settings;
	var $dead = true;

	//reads the archive and lists any files
	function OpenOfficeZipFile($filename){

		if(!file_exists($filename))
			return false;

		$this->archivename = $filename;
		
          global $myconfig;

          $this->dir = N_Shellpath(TMP_DIR()."/".N_GUID()."/");
          

           if (!file_exists ($this->dir)) { // checked on windows and linux, it works for directories
             mkdir ($this->dir);
           }
           N_Chmod ($this->dir);

          //N_LOG("opendocreplace", "temp dir ".$this->dir);
		$this->settings = $this->getSettings($this->dir, $this->archivename);

		$olddir = getcwd();
		chdir($this->dir);

		exec($this->settings['unzip'], $dummy, $returnvar);
		if($returnvar == 1)
			return false;
		chdir($olddir);                    
		$this->dead = false;
		return true;
	}
	
	//builds commandline strings for zipping and unzipping between an archive and an output dir
	function getSettings($outputdir, $archivename){
		$s = array();
		//archive is updated, uses zip, includes subdirectories
		 
           global $myconfig;
           if(!$myconfig["unzip"]) {
             N_LOG("errors", "uuse_opendocmetareplace: unzipcommand not configured in myconfig.");
           } else {
             $s['unzip'] = '"'.$myconfig["unzip"].'" -uo '.escapeshellarg($archivename);
           }

           if(!$myconfig["zip"]) {
             N_LOG("errors", "uuse_opendocmetareplace: zipcommand not configured in myconfig.");
           } else {
             $s['zip'] = '"'.$myconfig["zip"].'" -u -r '.escapeshellarg($archivename);
             $s['delete'] = '"'.$myconfig["zip"].'" -d '.escapeshellarg($archivename);
           }

		$s['compression'] = array();			//for translating compression level
		$s['compression'][0] = "-0 ";			//no compression
		$s['compression'][1] = ""; 				//shrink(not supported, use default)
		$s['compression'][2] = "";				//reduce factor1
		$s['compression'][3] = "";				//reduce factor2
		$s['compression'][4] = "";				//reduce factor3
		$s['compression'][5] = "";				//reduce factor4
		$s['compression'][6] = "";				//implode
		$s['compression'][7] = "";				//reserved
		$s['compression'][8] = "";				//deflate
		$s['compression'][9] = "";				//deflate64
		$s['compression'][10] = "";				//PKWARE Data Compression Library Imploding(not suported)
		$s['compression'][11] = "";				//reserved
		$s['compression'][12] = "";				//bzip2 compression
		return $s;
	}
//-------------------------------Information Functions----------------------------------
	
	//glob
	function listfiles_trying($mask = '*', $recursive = false){
		if($this->dead)
			return false;
			
		if($this->dir == false)
			return array();
		$olddir = getcwd();
		chdir($this->dir);

          $list = array_keys(N_Tree ($this->dir,'/',$recursive)) ;

          if ($mask=='*') {
             $mustcontain = '';
          } else {
             $mustcontain = $mask;
          }

           $ret = array();
           foreach ($list as $filename) {
              if ($mustcontain) {
                if (stripos($filename,$mustcontain)) $ret[] = $filename;
              } else {
                $ret[] = $filename;
              }
           }
           return ($ret);

		/*$ret = $this->glob($mask);

		if($recursive){
			foreach($this->glob(dirname($mask) . '\*')  as $thing){
				if(is_dir($thing))
					$ret = N_array_merge($this->listfiles($thing . '\\'.basename($mask), $recursive, true), $ret);
			}
		}*/

		chdir($olddir);
		return $ret;
	}


	function listfiles($mask = '*', $recursive = false){
		if($this->dead)
			return false;

		if($this->dir == false)
			return array();
		$olddir = getcwd();
		chdir($this->dir);

		$ret = $this->glob($mask);

		if($recursive){
			foreach($this->glob(dirname($mask) . '\*')  as $thing){
				if(is_dir($thing))
					$ret = N_array_merge($this->listfiles($thing . '\\'.basename($mask), $recursive, true), $ret);
			}
		}
		chdir($olddir);
		return $ret;
	}

//-------------------------------Editing Functions----------------------------------

	//returns an extracted file or false if it doesn't exist or if its corrupted
	function extract($filename){
		if($this->dead)
			return false;

		if(file_exists($this->dir . $filename))
			return $this->file_get_contents($this->dir . $filename);
		else
			return false;
	}

	//replaces the archive by a new one without the specified files
	function delete($files){
		if($this->dead)
			return false;

		$olddir = getcwd();
		chdir($this->dir);
		foreach($files as $filename->$file){
			if(file_exists($this->dir . $filename)){
				unlink($this->dir . $filename);
				exec($this->settings['delete'] . $filename);
			}
		}
		chdir($olddir);
	}

	//replaces the archive by a new one with specified files included
	function add($files){
		if($this->dead)
			return false;

		$olddir = getcwd();
		chdir($this->dir);
		foreach($files as $filename=>$file){
               //N_LOG("opendocreplace", "binnen add ".  $this->dir . $filename);
               N_WriteFile($filename,$file['data']);

			//$this->file_put_contents($this->dir . $filename, $file['data']);
			//N_LOG("opendocreplace", "".$this->settings['zip'] . $this->settings['compression'][$file['compression']] . $filename);

			$ret = exec($this->settings['zip'] . $this->settings['compression'][$file['compression']] ." ". $filename);
		}
		chdir($olddir);
		return $ret;
	}

	function close($dir = false){
		if($this->dead)
			return;

		$dir = file_exists($dir) ? $dir : $this->dir;

		foreach($this->glob($dir . '/*') as $file){
               //N_LOG("opendocreplace","File: $file <br>");
			
			if(is_file($file))
				unlink($file);
			else if(is_dir($file)){
				$this->close($file);
				rmdir($file);
			}
		}
	}


	function glob($pattern){

		$path = dirname($pattern);

		if($path == '' || $path == '.')
			$path=getcwd();
		else
			$prefix = $path . '/';

		$pattern = basename($pattern);
		
		if(($handle = @opendir($path)) === false)
			return array();

		$out = array();
		$escape=array('$','^','.','{','}','(',')','[',']','|');
  		$chunks=explode(';',$pattern);

  		foreach($chunks as $pattern){
	  		while(strpos($pattern,'**') !== false)
   				$pattern = str_replace('**','*',$pattern);

   			foreach($escape as $probe)
   				$pattern = str_replace($probe,"\\$probe",$pattern);

   			$out[] = str_replace('?*','*',
   							str_replace('*?','*',
   								str_replace('*',".*",
   									str_replace('?','.{1,1}',
   										$pattern))));
		}

		while($dir = readdir($handle)){
			if($dir == '.' || $dir == '..')
				continue;
			foreach($out as $tester){
				if(eregi("^$tester$", $dir)){
					$output[] = $prefix . $dir;
					break;
				}
			}
  		}
		closedir($handle);
		return is_array($output) ? $output : array();

	}
	function file_put_contents($filename, $data){
          //N_LOG("opendocreplace","trying to write $filename") ;
          N_WriteFile($filename, $data);

          /*
if(!file_exists(dirname($filename)))
			mkdir(dirname($filename));
		$x = @fopen($filename, "w");
		fwrite($x, $data);
		fclose($x);
*/

	}
	function file_get_contents($filename){
          //N_LOG("opendocreplace","trying to read $filename") ;
          return N_ReadFile($filename) ;

/*		if(!file_exists($filename))
			return false;

		if (filesize($filename)==0) return "";
		$x = fopen($filename, "r");
		$ret = fread($x, filesize($filename));
		fclose($x);
		return $ret;
*/
	}
} // HD: classend

class XMLPARSER4OO{
	//Looks for an element with specified name
	function containsElement($parent, $tagname, $atom, $match = array()){
		if($atom)
			$pattern = '|<'.$tagname . '.*?/>|is';
		else{
			$pattern = '|(<'.$tagname . '.*?>)';
			$pattern .= '(.*?)';
			$pattern .= '(</'.$tagname . '.*?>)|is';
		}
		return preg_match($pattern, $parent, $match);
	}

	//Runs preg_replace on elements with specified name
	function replaceElement($parent, $tagname, $atom, $callback){
		if(is_array($callback) && sizeOf($callback == 2))
			$callback = $callback[0] . '::' . $callback[1];
				
		if($atom){
			$pattern = '|<'.$tagname . '.*?/>|ise';
			return	preg_replace(
				$pattern,			//the pattern
				$callback.'("\\0")',//replacement function
		    	$parent				//the subject string
			);
		}else{
			$pattern = '|(<'.$tagname . '.*?>)';
			$pattern .= '(.*?)';
			$pattern .= '(</'.$tagname . '.*?>)|ise';
			return	preg_replace(
				$pattern,			//the pattern
				$callback.'("\\1", "\\2", "\\3")',			//replacement function
		    	$parent				//the subject string
			);
		}
		
	}
	
	//creates an xml element
	function createElement($tagname, $attributes, $content){
		$element = '<'.$tagname;
		foreach($attributes as $name=>$val)
			$element .= ' ' . XMLPARSER4OO::createAttribute($name, $val);
		if($content === false)
			$element .= ' />';
		else{
			$element .= '>';
			$element .= $content;
			$element .= '</'. $tagname . '>';
		}
		return $element;
	}
	//Creates a String representing an xml attribute
	function createAttribute($name, $value){
		return $name . '="'.$value . '"';
	}
	
	function &toStruct($xmlstring){
		$parent = new Element();							//document container
		$parent->name = '';
		while(strlen($xmlstring) > 0){
			$start = strpos($xmlstring, '<');
			$end = strpos($xmlstring, '>', $start);
			if($start === false || $end === false)
				break;
			
			$parent->addContent(substr($xmlstring, 0, $start));		//add string as content
				
			unset($tag);
			$tag = XMLPARSER4OO::strToElement(trim(substr($xmlstring, $start +1, ($end-$start)-1)));
			
			if(is_string($tag))
				$parent->addContent('<'.$tag.'>');					//not converted to element
			else{ 
				if($tag->type == 'closing'){
					if($tag->name == $parent->name)					//closes parent element
						$parent =& $parent->parent;
				}else{
					$parent->addContent($tag);
					if($tag->type != 'atom'){
						$tag->parent =& $parent;
						$parent =& $tag;
					}
				}
			}

			$xmlstring = substr($xmlstring, $end+1);
		}
		
		return $parent;
	}

	//creates element object from xml string
	function strToElement($str){
		
		if(substr($str, 0, 1) == '?' || substr($str, 0, 1) == '!')		//return specifications or doctype as string
			return $str;
		
		$tag = new Element();

		if(substr($str, strlen($str)-1) == '/'){						//look for / suffix
			$tag->type = 'atom';
			$str = trim(substr($str, 0, strlen($str)-1));
		}else if(substr($str, 0, 1) == '/'){							//look for / prefix
			$tag->type = 'closing';
			$str = trim(substr($str, 1));
		}
		
		if($x = strpos($str, ' ')){
			$tag->name = substr($str, 0, $x);							//use first word as name
			$str = substr($str, $x);
			while(preg_match('|(.*?) *?= *?"(.*?)"|is', $str, $match)){	//find attributes with preg match
				$tag->addAttr(trim($match[1]), $match[2]);
				$str = substr($str, strlen($match[0]));
			}
		}else
			$tag->name = $str;
		return $tag;
	}
} // HD: classend



class Manifest extends XMLPARSER4OO{
	var $xml;
	var $files;
	function Manifest($xml){
		$this->xml = &$this->toStruct($xml);
		if(!($manifest = &$this->xml->findElement('manifest:manifest')))
			return false;
		
		$this->files = &$manifest->getAllElements('manifest:file-entry');
	}
	function exists($path){
		foreach($this->files as $file){
			if($file->getAttribute('manifest:full-path') == $path)
				return true;
		}
		return false;
	}
	function getFileType($path){
		foreach($this->files as $file){
			if($file->getAttribute('manifest:full-path') == $path)
				return $file->getAttribute('manifest:media-type');
		}
		return false;
	}
	function getEncryption($path){
		foreach($this->files as $file){
			if($file->getAttribute('manifest:full-path') == $path){
				return $file->getElement('manifest:encryption-data');
			}
		}
		return false;
	}
	function add($file, $filetype){
		$manifest = &$this->xml->findElement('manifest:manifest');
		if(!$manifest)
			return false;
		foreach($manifest->contents as $key=>$content){
			if(is_object($content) && $content->name == 'manifest:file-entry'){
				if($content->getAttribute('manifest:full-path') == $file){
					$manifest->contents[$key]->addAttr('manifest:media-type', $filetype);
					return;
				}
			}
		}
		$manifest->addContent(' '.$this->createElement('manifest:file-entry', array('manifest:media-type' => $filetype, 'manifest:full-path' => $file), false)."\n");
	}
	function toString(){
		return $this->xml->toString();
	}
} // HD: classend


class OpenDocument extends OpenOfficeZipFile{
	var $manifest = false;
	var $fields;
	
	function OpenDocument($file){
		if(OpenOfficeZipFile::OpenOfficeZipFile($file) === false){
               //N_LOG("opendocreplace",'Could not open file: '.$file);
			
			return false;
		}
		
		if(!$manifest = $this->extract('META-INF/manifest.xml')){
               //N_LOG("opendocreplace",'Manifest file not found <br />');
			
			return false;
		}
		
		if(!($this->manifest = new Manifest($manifest)))
			return false;
	}
	
	function getFileType($path){
		return $this->manifest->getFileType($path);
	}
	
	//Warning! the manifest file is not entered in the manifest so you can only load it with extract
	function readFile($file){
		if($this->manifest->exists($file) && !$this->manifest->getEncryption($file))
			return $this->extract($file);
		else
			return false;
	}
	
	function addFile($file, $filetype, $data){
		//add to manifest
		$this->manifest->add($file, $filetype);
		
		//add with manifest to archive
		return $this->add(array(
			$file =>array('data'=> $data, 'compression'=>8, 'overwrite'=>true),
			'META-INF/manifest.xml'=>array('data'=>$this->manifest->toString(), 'compression'=>8, 'overwrite'=>true)
		));
	}
} // HD: classend

class Element{
	var $name = 'unknown';
	var $type = 'Element';
	var $attributes = array();
	var $contents = array();
	
	function addAttr($name, $value){
		$this->attributes[$name] = $value;
	}
	
	function addContent($content){
		if($content != '')
			$this->contents[] =& $content;
	}
	function toString($seperators = array('elementSuffix' => '', 'contentPrefix' => '')){
		if($this->name == '' && sizeOf($this->attributes) == 0){
			$element = '';
			foreach($this->contents as $content){
				if(is_string($content))
					$element .= $seperator['contentPrefix'] . $content;
				else
					$element .= $seperator['contentPrefix'] . $content->toString();
			}
			return $element;
		}

		$element = '<'.$this->name;
		
		foreach($this->attributes as $name=>$val)
			$element .= ' ' . $name . '="' . $val . '"';	
			
		if($this->type == 'atom')
			return $element . ' />' . $seperator['elementSuffix'];
		else{
			$element .= '>' . $seperator['elementSuffix'];
			foreach($this->contents as $content){
				if(is_string($content))
					$element .= $seperator['contentPrefix'] . $content;
				else
					$element .= $seperator['contentPrefix'] . $content->toString();
			}
			return $element . '</'. $this->name . '>' . $seperator['elementSuffix'];
		}
	}
	function &getElement($name){
		if($this->name == $name)
			return $this;
		foreach($this->contents as $key=>$content){
			if(is_object($content) && $content->name == $name)
				return $this->contents[$key];
		}
		return false;
	}
	function &getAllElements($name){
		$e = array();
		if($this->name == $name)
			$e[] = &$this;
		foreach($this->contents as $key=>$content){
			if(is_object($content) && $content->name == $name)
				$e[] = &$this->contents[$key];
		}
		return $e;
	}
	function &findElement($name){
		if($e = &$this->getElement($name))
			return $e;
		foreach($this->contents as $key=>$content){
			if(is_object($content) && $e = &$this->contents[$key]->getElement($name))
				return $e;
		}
		return false;
	}
	function getAttribute($name){
		if(isset($this->attributes[$name]))
			return $this->attributes[$name];
		else
			return false;
	}
} // HD: classend
 
class OpenDoc_MetaReplacer{
	var $opendoc;
	var $fields;

	//replaces metadata $fields in $file
	function staticReplace($file, $fields){
          uuse("search");

        //N_LOG("opendocreplace","inhoud data van $file binnen aanroep staticReplace","".print_r($fields,1)); // qqq


		if(sizeOf($fields) == 0)										//there are no fields to replace
			return true;
			
		if(!($x = new OpenDoc_MetaReplacer($file)))						//failed to create object
			return false;
		$ret = $x->replaceMetaTags($fields);							//replace
		$x->close();
		return $ret;
	}

	//constructor
	function OpenDoc_MetaReplacer($file){
		$this->opendoc = new OpenDocument($file);						//open document
		if($this->opendoc == false || !$this->opendoc->manifest)		//illegal opendoc(needs a manifest)
			return false;
	}
	function close(){
		$this->opendoc->close();
	}

	function replaceMetaTags($fields){

		$this->fields = $fields;

          //N_LOG("opendocreplace","entering replaceMetaTags","".print_r($fields,1));

		if($this->opendoc == false || !$this->opendoc->manifest)		//illegal opendoc
			return false;

		if(sizeOf($fields) == 0)										//no fields have to be changed
			return false;


	     //$ffilelist = $this->opendoc->listfiles('*.xml', true);
          $ffilelist = $this->opendoc->listfiles('*.xml', true);

          //N_LOG("opendocreplace","filelist","".print_r($ffilelist,1)); // qqq

		foreach($ffilelist as $filename){	//for each xml file

               //N_LOG("opendocreplace",'reading ' .$filename);

			global $docs;
			$docs++;															//<----------------------------

			$contentchanged = false;									//no changes have been made yet

			$file = $this->opendoc->readFile($filename);				//obtain file contents

			if(!$file)													//on failure proceed to the next file
				continue;

			$contents = $this->slice($file, $this->multi_stripos($file, array('<office:body', '<office:master-styles')));
				$body = $contents['<office:body'];
				$contents['<office:body'] = $this->slice($body, $this->multi_stripos($body, array('<office:text', '<office:spreadsheet')));

					$text = $contents['<office:body']['<office:text'];
					$spreadsheet = $contents['<office:body']['<office:spreadsheet'];

				$master_styles = $contents['<office:master-styles'];
				$contents['<office:master-styles'] = $this->slice($master_styles, $this->multi_stripos($master_styles, array('<style:header', '<style:footer')));

					$header = $contents['<office:master-styles']['<style:header'];
					$footer = $contents['<office:master-styles']['<style:footer'];

			if($text != ''){
				//N_LOG("opendocreplace","text found");

				$newcontent = $this->textDocReplace($text, $fields);
				if($newcontent != $text){
					$contents['<office:body']['<office:text'] = $newcontent;
					$contentchanged = true;							//if the new content is different turn on the flag for saving
				}
			}

			if($spreadsheet != ''){
				//N_LOG("opendocreplace","spreadsheet found");
				$dir = dirname($filename);
				$dir = ($dir == '.') ? '' : $dir . "/";
				$this->addSpreadSheetFunction($dir);						//add a macro
			}

			if($header != ''){
				//N_LOG("opendocreplace","header found");
				$newcontent = $this->textDocReplace($header, $fields);
				if($newcontent != $header){
					$contents['<office:master-styles']['<style:header'] = $newcontent;
					$contentchanged = true;
				}
			}
			if($footer != ''){
				//N_LOG("opendocreplace","footer found");
				$newcontent = $this->textDocReplace($footer, $fields);
				if($newcontent != $footer){
					$contents['<office:master-styles']['<style:footer'] = $newcontent;
					$contentchanged = true;
				}
			}
			if($contentchanged){		//replace file only if the contents has changed
				//N_LOG("opendocreplace","adding file");
				//write new file
				$file = $this->array2str($contents);
				if(!$this->opendoc->add(array($filename=>array('data'=>$file, 'compression'=>8, 'overwrite'=>true))))  {
					//N_LOG("opendocreplace","failed to add file",$file);
				}
			}


		}
	}
	function multi_stripos($haystack, $needles){
		$ret = array();
		foreach($needles as $needle){
			$ret[$needle] = $this->stripos($haystack, $needle);
			if($ret[$needle] === false)
				$ret[$needle] = strlen($haystack);
			else
				$ret[$needle] = strpos($haystack, '>', $ret[$needle])+1;
		}

		return $ret;
	}
	function slice($text, $pos_array){
		asort($pos_array);
		$pos_array = array_reverse($pos_array);
		$ret = array();
		$lastval = strlen($text);
		foreach($pos_array as $key=>$pos){
			$ret[$key] = substr($text, $pos, $lastval-$pos);
			$lastval = $pos;
		}
		$ret['init'] = substr($text, 0, $lastval);
		return $ret;
	}
	function array2str($a){
		$a = array_reverse($a);
		$str = '';
		foreach($a as $value){
			if(is_array($value))
				$str .= $this->array2str($value);
			else
				$str .= $value;
		}
		return $str;
	}

	//--------------------------------------------text documents-----------------------------------------
	function textDocReplace($text, $fields){
          //N_LOG("opendocreplace","replacing text element $text","".print_r($fields,1));
														//<---------------------

		$newcontent = $this->replaceText($text);					//replace meta tags

		if($newcontent != $text){									//if anything has changed declare variables
			if(XMLPARSER4OO::containsElement($newcontent, 'text:variable-decls', false))		//if declerations already exist
				$text = XMLPARSER4OO::replaceElement($newcontent, 'text:variable-decls', false, '\$this->replaceDeclerations');		//insert declarations of metadata
			else{
				$declContents = '';
				foreach($fields as $key=>$field)
					$declContents .= XMLPARSER4OO::createElement('text:variable-decl', array('office:value-type'=>'string', 'text:name'=>$this->fixText($key)), false);

				$decl = XMLPARSER4OO::createElement('text:variable-decls', array(), $declContents);
				$newcontent = $decl . $newcontent;												//add new delerations
				//N_LOG("opendocreplace","changemade: declerations added(".htmlspecialchars($decl).")");
			}
		}
		return $newcontent;
	}


	function replaceDeclerations($tag_open, $contents, $tag_close){
		foreach($this->fields as $key=>$field){
			if(!XMLPARSER4OO::containsElement($contents, 'text:variable-decl .*?text:name="'. OpenDoc_MetaReplacer::fixText($key).'"', true)){	//check for eatch variable name
				$contents .= XMLPARSER4OO::createElement('text:variable-decl', array('office:value-type'=>'string', 'text:name'=>OpenDoc_MetaReplacer::fixText($key)), false);
			}
		}
		return $tag_open . $contents . $tag_close;
	}

	function replaceText($text){
		//N_LOG("opendocreplace","replaceText($text)");												//<-----------------
		//replaces values in already inserted fields
		$field = '|(<text:variable-set .*?text:name=")(.*?)(".*?>).*?(</text:variable-set>)|ise';
		$text = preg_replace($field, '\$this->replaceField("\\0", "\\2")', $text);

		//replaces tags by new variable fields
		$metatag = '|\[\[\[' . '(.*?)' . '\]\]\]|ise';
		$text = preg_replace(
				$metatag,							//the pattern
				"\$this->replaceTag('\\0', '\\1')",	//callback function for replacing tags
		    	$text								//the subject string
		);
		return $text;
	}


	function replaceField($match, $key_xml){
		//echo "replaceField(".htmlspecialchars($match .", " . $key_xml).")<br>\n";	//<--------------

		$key = OpenDoc_MetaReplacer::dehtmlspecialchars($key_xml);			//change back xml characters in the key
		if(!isset($this->fields[$key]))
			return $match;
		else{
			global $replacements;
			$replacements++;
			//echo "changemade: field value changed(".htmlspecialchars(XMLPARSER4OO::createElement('text:variable-set', array('text:name'=>$key_xml, "office:value-type"=> "string"), OpenDoc_MetaReplacer::fixText($this->fields[$key]))).")<br>\n";//<----
			return XMLPARSER4OO::createElement('text:variable-set', array('text:name'=>$key_xml, "office:value-type"=> "string"), OpenDoc_MetaReplacer::fixText($this->fields[$key]));
		}
	}


	function replaceTag($whole, $tagname){
		//N_LOG("opendocreplace","replaceTag(". htmlspecialchars($whole .", ". $tagname) .")");

		$key = OpenDoc_MetaReplacer::dehtmlspecialchars($tagname);
		if(!isset($this->fields[$key]))
			return $whole;
		else{
			global $replacements;
			$replacements++;
			//echo "changemade: tag replaced(".htmlspecialchars(XMLPARSER4OO::createElement("text:variable-set", array("text:name"=>$tagname, "office:value-type"=> "string"), OpenDoc_MetaReplacer::fixText($this->fields[$key]))).")<br>\n";		//<----
			return XMLPARSER4OO::createElement("text:variable-set", array("text:name"=>$tagname, "office:value-type"=> "string"), OpenDoc_MetaReplacer::fixText($this->fields[$key]));
		}
	}
	function dehtmlspecialchars($tekst) {
		$tekst = str_replace("&quot;",'"',$tekst);
		$tekst = str_replace("&lt;",'<',$tekst);
		$tekst = str_replace("&gt;",'>',$tekst);
		$tekst = str_replace("&amp;",'&',$tekst);
		return $tekst;
	}

	//--------------------------------------------spreadsheet documents-----------------------------------------

	function addSpreadSheetFunction($dir){
	     //echo("opendocreplace","Adding spreadsheet macro");

		$this->opendoc->manifest->add($dir . "Basic/", "");
		//----------------------------------Basic/script-lc.xml--------------------

		//echo "adding to script-lc.xml<br>";
		if($this->insertElement($dir ."Basic/script-lc.xml", "library:libraries", "library:library",
			array("library:name"=>"Standard", "library:link"=>"false"), false) == false){

			$lib = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$lib .= '<!DOCTYPE library:libraries PUBLIC "-//OpenOffice.org//DTD OfficeDocument 1.0//EN" "libraries.dtd">' . "\n";
			$lib .= XMLPARSER4OO::createElement("library:libraries", array("xmlns:library" => "http://openoffice.org/2000/library", "xmlns:xlink" => "http://www.w3.org/1999/xlink"),
				XMLPARSER4OO::createElement("library:library", array("library:name"=>"Standard", "library:link"=>"false"), false));

			$this->opendoc->addFile($dir."Basic/script-lc.xml", "text/xml", $lib);
		}

		$this->opendoc->manifest->add($dir."Basic/Standard/", "");
		//----------------------------------Basic/Standard/script-lb.xml--------------------

		//echo "adding to script-lb.xml<br>";
		if($this->insertElement($dir."Basic/Standard/script-lb.xml", 'library:library', 'library:element',
			array("library:name"=>"OpenIMS"), false) == false){

			$lib = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$lib .= '<!DOCTYPE library:library PUBLIC "-//OpenOffice.org//DTD OfficeDocument 1.0//EN" "library.dtd">' . "\n";
			$lib .= XMLPARSER4OO::createElement("library:library",
				array("xmlns:library" => "http://openoffice.org/2000/library", "library:name" => "Standard", "library:readonly"=>"false", "library:passwordprotected"=>"false"),
				XMLPARSER4OO::createElement("library:element", array("library:name"=>"OpenIMS"), false));

			$this->opendoc->addFile($dir."Basic/Standard/script-lb.xml", "text/xml", $lib);
		}

		//----------------------------------Basic/Standard/OpenIMS.xml--------------------

		//echo "adding OpenIMS.xml<br>";
		$basic = "\nPublic Function OPENIMS_GETMETA(FieldName As String)\n";
		$basic .= "	OPENIMS_GETMETA = &quot;Wrong key&quot;\n";
		foreach($this->fields as $key=>$value) { 
                      $value = implode(explode("\n",$value),' ');          
                      $value = implode(explode("\r",$value),' ');  
                      $basic .= "	   if FieldName = &quot;".$this->fixText($key, true)."&quot; then OPENIMS_GETMETA = &quot;".$this->fixText($value, true)."&quot;\n";
                }

		$basic .= "End Function\n";

		$lib = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$lib .= '<!DOCTYPE script:module PUBLIC "-//OpenOffice.org//DTD OfficeDocument 1.0//EN" "module.dtd">' . "\n";
		$lib .= XMLPARSER4OO::createElement('script:module',
			array("xmlns:script"=>"http://openoffice.org/2000/script", "script:name"=>"OpenIMS", "script:language"=>"StarBasic"),
			$basic);

		$this->opendoc->addFile($dir."Basic/Standard/OpenIMS.xml", "text/xml", $lib);
	}

	function insertElement($path, $parents, $elementname, $attributes, $content){
		$parents = explode('->', $parents);
		if(($file = $this->opendoc->readFile($path)) == false)
			return false;

		$lib = XMLPARSER4OO::toStruct($file);
		$parent =& $lib;
		foreach($parents as $p){
			if($p != ''){
				if(!($parent =& $parent->getElement($p)))
					return false;
			}
		}
		foreach($parent->getAllElements($elementname) as $element){
			$equals = true;
			foreach($attributes as $name=>$value){
				if($element->getAttribute($name) != $value)
					$equals = false;
			}
			if($equals)
				return true;
		}
		$parent->addContent(XMLPARSER4OO::createElement($elementname, $attributes, $content));

		$lib = $lib->toString();
		$this->opendoc->addFile($path, "text/xml", $lib);
		return true;
	}

	//-------------------------------------------------------------------------------------
	//stripos support for php4
	function stripos($haystack,$needle,$offset = 0){
   		return(strpos(strtolower($haystack),strtolower($needle),$offset));
	}

	function fix_accents($text) {

        $accents =array(138,140,142,154,156,158, 159,165,181,192,193,194,195,196,197,198,199,200,
      201,202,203,204,205,206,207,208,209,210,211,212,213,214,216,217,218,219,220,221,223,224,225,
      226,227,228,229,230,231,232,233,234,235,236,237,238,239,240,241,242,243,244,245,246,248,249,
      250,251,252,253,255);

        $res = "" ;
        for ($i = 0; $i < strlen($text); $i++) {
          if (in_array(ord($text[$i]),$accents)) {
             $res .= '&#'.ord($text[$i]).';';
          } else {
             $res .= $text[$i] ;
          }
        }
       return $res;
     }

	//htmlspecial chars and escape chars in basic
	function fixText($text, $basic = false){
          //N_LOG("opendocreplace","at start of fixText: ".htmlentities($text));
		if($basic)
		   $text = str_replace(array("\n", '"'), array("", "\" & Chr$(34) & \""), $text);
                $text = N_html2utf($text);// added ANTZ-25
		$text = htmlspecialchars($text, ENT_QUOTES);
//          $text = OpenDoc_MetaReplacer::fix_accents($text);// removed ANTZ-25
          
          //N_LOG("opendocreplace","at end of fixText: ".htmlentities($text));
		return $text;
	}
} // HD: classend

?>