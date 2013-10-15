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



/*********************************************************/
/*** class vcard for export in vcard format in OpenIMS ***/
/*** Developer: Dennis Bouwmeester                     ***/
/*********************************************************/

class vcard { 
  var $mydata;  
  var $myfilename; 
  var $myclass;  
  var $myrevisiondate; 
  var $mycard; 

  /**************************************************************/
  /*** function vcard constructor, set all properties to null ***/
  /**************************************************************/
  function vcard() { 
    $this->mydata = array( 
       "displayname"=>null 
      ,"firstname"=>null 
      ,"lastname"=>null 
      ,"additionalname"=>null 
      ,"nameprefix"=>null 
      ,"namesuffix"=>null 
      ,"nickname"=>null 
      ,"title"=>null 
      ,"role"=>null 
      ,"department"=>null 
      ,"company"=>null 
      ,"workpobox"=>null 
      ,"workextendedaddress"=>null 
      ,"workaddress"=>null 
      ,"workcity"=>null 
      ,"workstate"=>null 
      ,"workpostalcode"=>null 
      ,"workcountry"=>null 
      ,"homepobox"=>null 
      ,"homeextendedaddress"=>null 
      ,"homeaddress"=>null 
      ,"homecity"=>null 
      ,"homestate"=>null 
      ,"homepostalcode"=>null 
      ,"homecountry"=>null 
      ,"officetel"=>null 
      ,"hometel"=>null 
      ,"celltel"=>null 
      ,"faxtel"=>null 
      ,"pagertel"=>null 
      ,"email1"=>null 
      ,"email2"=>null 
      ,"url"=>null 
      ,"photo"=>null 
      ,"birthday"=>null 
      ,"timezone"=>null 
      ,"sortstring"=>null 
      ,"note"=>null 
      ); 
    return true; 
  } 

  /****************************************************/
  /*** function generate to create content of vcard ***/
  /****************************************************/
  function generate() { 

    if (!$this->myclass) { $this->myclass = "PUBLIC"; } 
    if (!$this->mydata['displayname']) { 
      $this->mydata['displayname'] = trim($this->mydata['firstname']." ".$this->mydata['lastname']); 
    } 
    if (!$this->mydata['sortstring']) { $this->mydata['sortstring'] = $this->mydata['lastname']; } 
    if (!$this->mydata['sortstring']) { $this->mydata['sortstring'] = $this->mydata['company']; } 
    if (!$this->mydata['timezone']) { $this->mydata['timezone'] = date("O"); } 
    if (!$this->myrevisiondate) { $this->myrevisiondate = date('Y-m-d H:i:s'); } 
     
    $this->mycard = "BEGIN:VCARD\r\n"; 
    $this->mycard .= "VERSION:3.0\r\n"; 
    $this->mycard .= "CLASS:".$this->myclass."\r\n"; 
    $this->mycard .= "PRODID:\r\n"; 
    $this->mycard .= "REV:".$this->myrevisiondate."\r\n"; 
    $this->mycard .= "FN:".$this->mydata['displayname']."\r\n"; 
    $this->mycard .= "N:" 
      .$this->mydata['lastname'].";" 
      .$this->mydata['firstname'].";" 
      .$this->mydata['additionalname'].";" 
      .$this->mydata['nameprefix'].";" 
      .$this->mydata['namesuffix']."\r\n"; 
    if ($this->mydata['nickname']) { $this->mycard .= "NICKNAME:".$this->mydata['nickname']."\r\n"; } 
    if ($this->mydata['title']) { $this->mycard .= "TITLE:".$this->mydata['title']."\r\n"; } 
    if ($this->mydata['company']) { $this->mycard .= "ORG:".$this->mydata['company']; } 
    if ($this->mydata['department']) { $this->mycard .= ";".$this->mydata['department']; } 
    $this->mycard .= "\r\n"; 
       
    if ($this->mydata['workpobox'] 
        || $this->mydata['workextendedaddress'] 
        || $this->mydata['workaddress'] 
        || $this->mydata['workcity'] 
        || $this->mydata['workstate'] 
        || $this->mydata['workpostalcode'] 
        || $this->mydata['workcountry']) { 
        $this->mycard .= "ADR;TYPE=work:" 
                       .$this->mydata['workpobox'].";" 
                       .$this->mydata['workextendedaddress'].";" 
                       .$this->mydata['workaddress'].";" 
                       .$this->mydata['workcity'].";" 
                       .$this->mydata['workstate'].";" 
                       .$this->mydata['workpostalcode'].";" 
                       .$this->mydata['workcountry']."\r\n"; 
    } 

    if ($this->mydata['homepobox'] 
        || $this->mydata['homeextendedaddress'] 
        || $this->mydata['homeaddress'] 
        || $this->mydata['homecity'] 
        || $this->mydata['homestate'] 
        || $this->mydata['homepostalcode'] 
        || $this->mydata['homecountry']) { 
        $this->mycard .= "ADR;TYPE=home:" 
                       .$this->mydata['homepobox'].";" 
                       .$this->mydata['homeextendedaddress'].";" 
                       .$this->mydata['homeaddress'].";" 
                       .$this->mydata['homecity'].";" 
                       .$this->mydata['homestate'].";" 
                       .$this->mydata['homepostalcode'].";" 
                       .$this->mydata['homecountry']."\r\n"; 
    } 
    
    if ($this->mydata['email1']) { $this->mycard .= "EMAIL;TYPE=internet,pref:".$this->mydata['email1']."\r\n"; } 
    if ($this->mydata['email2']) { $this->mycard .= "EMAIL;TYPE=internet:".$this->mydata['email2']."\r\n"; } 
    if ($this->mydata['officetel']) { $this->mycard .= "TEL;TYPE=work,voice:".$this->mydata['officetel']."\r\n"; } 
    if ($this->mydata['hometel']) { $this->mycard .= "TEL;TYPE=home,voice:".$this->mydata['hometel']."\r\n"; } 
    if ($this->mydata['celltel']) { $this->mycard .= "TEL;TYPE=cell,voice:".$this->mydata['celltel']."\r\n"; } 
    if ($this->mydata['faxtel']) { $this->mycard .= "TEL;TYPE=work,fax:".$this->mydata['faxtel']."\r\n"; } 
    if ($this->mydata['pagertel']) { $this->mycard .= "TEL;TYPE=work,pager:".$this->mydata['pagertel']."\r\n"; } 
    if ($this->mydata['url']) { $this->mycard .= "URL;TYPE=work:".$this->mydata['url']."\r\n"; } 
    if ($this->mydata['birthday']) { $this->mycard .= "BDAY:".$this->mydata['birthday']."\r\n"; } 
    if ($this->mydata['role']) { $this->mycard .= "ROLE:".$this->mydata['role']."\r\n"; } 
    if ($this->mydata['note']) { $this->mycard .= "NOTE:".$this->mydata['note']."\r\n"; } 
    $this->mycard .= "TZ:".$this->mydata['timezone']."\r\n"; 
    $this->mycard .= "END:VCARD\r\n"; 
  } 

  /*************************************************************/   
  /*** function url for generating url to vcard for download ***/
  /*************************************************************/   
  function url() { 

     if (!$this->mycard) { $this->generate(); } 
     if (!$this->myfilename) { $this->myfilename = trim($this->mydata['displayname']); } 
     $this->myfilename = str_replace(" ", "_", $this->myfilename); 

     uuse ("lib");
     $url = LIB_Doc2URL ($this->mycard, $this->myfilename.".vcf");
     return $url;
  }

} 
// HD: classend

/*************************************************************/   
/*** example for usage                                     ***/
/*************************************************************/  
/*
uuse("vcard"); 
$vc = new vcard(); 
$vc->mydata['firstname'] = "Dennis"; 
$vc->mydata['lastname'] = "Bouwmeester"; 
$vc->mydata['nickname'] = "pengo"; 
$vc->mydata['company'] = "OpenSesame ICT B.V."; 
$vc->mydata['title'] = "Developer"; 
$vc->mydata['workaddress'] = "Meerwal 13"; 
$vc->mydata['workcity'] = "Nieuwegein"; 
$vc->mydata['workstate'] = ""; 
$vc->mydata['workpostalcode'] = "3432 ZV"; 
$vc->mydata['homeaddress'] = "Kaasjeskruid 51"; 
$vc->mydata['homecity'] = "Amersfoort"; 
$vc->mydata['homestate'] = ""; 
$vc->mydata['homepostalcode'] = "3824 NW"; 
$vc->mydata['officetel'] = ""; 
$vc->mydata['celltel'] = ""; 
$vc->mydata['faxtel'] = ""; 
$vc->mydata['email1'] = "dennis.bouwmeester@osict.com"; 
$vc->mydata['url'] = "http://www.osict.com"; 
$vc->mydata['birthday'] = "1978-08-25"; 
echo '<a target="_new" href="'.$vc->url().'">Download vcard</a>'; 
*/
/*************************************************************/ 

?>