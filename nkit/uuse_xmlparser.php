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


   
//###################################################################################
//# XMLPARSER_unserialize: takes raw XML as a parameter (a string)
//# and returns an equivalent PHP data structure
//###################################################################################
function & XMLPARSER_unserialize(&$xml){
	$xml_parser = &new XMLPARSER();
	$data = &$xml_parser->parse($xml);
	$xml_parser->destruct();
	return $data;
}
//###################################################################################
//# XMLPARSER_serialize: serializes any PHP data structure into XML
//# Takes one parameter: the data to serialize. Must be an array.
//###################################################################################
function & XMLPARSER_serialize(&$data, $level = 0, $prior_key = NULL){
	if($level == 0){ ob_start(); echo '<?xml version="1.0" ?>',"\n"; }
	while(list($key, $value) = each($data))
		if(!strpos($key, ' attr')) #if it's not an attribute
			#we don't treat attributes by themselves, so for an empty element
			# that has attributes you still need to set the element to NULL

			if(is_array($value) and array_key_exists(0, $value)){
				XMLPARSER_serialize($value, $level, $key);
			}else{
				$tag = $prior_key ? $prior_key : $key;
				echo str_repeat("\t", $level),'<',$tag;
				if(array_key_exists("$key attr", $data)){ #if there's an attribute for this element
					while(list($attr_name, $attr_value) = each($data["$key attr"]))
						echo ' ',$attr_name,'="',htmlspecialchars($attr_value),'"';
					reset($data["$key attr"]);
				}

				if(is_null($value)) echo " />\n";
				elseif(!is_array($value)) echo '>',htmlspecialchars($value),"</$tag>\n";
				else echo ">\n",XMLPARSER_serialize($value, $level+1),str_repeat("\t", $level),"</$tag>\n";
			}
	reset($data);
	if($level == 0){ $str = &ob_get_contents(); ob_end_clean(); return $str; }
}
//###################################################################################
//# XMLPARSER class: utility class to be used with PHP's XML handling functions
//###################################################################################
class XMLPARSER{
	var $parser;   #a reference to the XML parser
	var $document; #the entire XML structure built up so far
	var $parent;   #a pointer to the current parent - the parent will be an array
	var $stack;    #a stack of the most recent parent at each nesting level
	var $last_opened_tag; #keeps track of the last tag opened.

	function XMLPARSER(){
 		$this->parser = &xml_parser_create();
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'open','close');
		xml_set_character_data_handler($this->parser, 'data');
	}
	function destruct(){ xml_parser_free($this->parser); }
	function & parse(&$data){
		$this->document = array();
		$this->stack    = array();
		$this->parent   = &$this->document;
		return xml_parse($this->parser, $data, true) ? $this->document : NULL;
	}
	function open(&$parser, $tag, $attributes){
		$this->data = ''; #stores temporary cdata
		$this->last_opened_tag = $tag;
		if(is_array($this->parent) and array_key_exists($tag,$this->parent)){ #if you've seen this tag before
			if(is_array($this->parent[$tag]) and array_key_exists(0,$this->parent[$tag])){ #if the keys are numeric
				#this is the third or later instance of $tag we've come across
				$key = XMLPARSER_count_numeric_items($this->parent[$tag]);
			}else{
				#this is the second instance of $tag that we've seen. shift around
				if(array_key_exists("$tag attr",$this->parent)){
					$arr = array('0 attr'=>&$this->parent["$tag attr"], &$this->parent[$tag]);
					unset($this->parent["$tag attr"]);
				}else{
					$arr = array(&$this->parent[$tag]);
				}
				$this->parent[$tag] = &$arr;
				$key = 1;
			}
			$this->parent = &$this->parent[$tag];
		}else{
			$key = $tag;
		}
		if($attributes) $this->parent["$key attr"] = $attributes;
		$this->parent  = &$this->parent[$key];
		$this->stack[] = &$this->parent;
	}
	function data(&$parser, $data){
		if($this->last_opened_tag != NULL) #you don't need to store whitespace in between tags
			$this->data .= $data;
	}
	function close(&$parser, $tag){
		if($this->last_opened_tag == $tag){
			$this->parent = $this->data;
			$this->last_opened_tag = NULL;
		}
		array_pop($this->stack);
		if($this->stack) $this->parent = &$this->stack[count($this->stack)-1];
	}
}  // HD: classend

function XMLPARSER_count_numeric_items(&$array){
	return is_array($array) ? count(array_filter(array_keys($array), 'is_numeric')) : 0;
}
?>