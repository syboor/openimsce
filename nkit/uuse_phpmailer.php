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



uuse("ims");
uuse("files");
uuse("search");
uuse("shield");

// include phpmailer class
require_once(getenv("DOCUMENT_ROOT")."/openims/libs/phpmailer/class.phpmailer.php");
 


function PHPMAILER_SendSimpleBulkMail($mailinglist, $from, $fromname, $subject, $content, $sgn, $html=0) {

   // instantiate class & basic props
   $mail = new PHPMailer();
   $mail->SetLanguage("en", getenv("DOCUMENT_ROOT")."/openims/libs/phpmailer/language/");

   if ($html) $mail->IsHTML(true);

   // set from
   $mail->From = $from;
   $mail->FromName = $fromname;


   if ($html) {

      // get list of images to be embedded in html e-mail
      $images = PHPMAILER_ParseContent($content, $sgn);
      $elements= array();
      $elements["content"] = $content;
      $elements["images"] = $images;

      // replace images by embedded image
      foreach ($elements["images"] as $key => $image) {
         $elements["content"] = str_replace($image["original"], "cid:".$image["cid"], $elements["content"]);
         if (!$mail->AddEmbeddedImage($image["file"], $image["cid"], $image["cid"], "base64", $image["type"]))
         {
           //echo $mail->ErrorInfo;
         }
      }

      // make alternative text body for non-html e-mail clients
      $altbody = $elements["content"];
      $altbody = str_replace("<br>", chr(10), $altbody);
      $altbody = str_replace("<br />", chr(10), $altbody);
      $altbody = SEARCH_HTML2TEXT($altbody);

   } 

   // set subject
   $mail->Subject = $subject;

   // loop through mailinglist and send individual e-mails
   foreach ($mailinglist as $email => $name) {
      
      // set adress
      if ($name . "" == "") $name = $email;

      $mail->ClearAddresses();
      $mail->AddAddress($email, $name);

      if ($html) {

         // set bodies
         $tmp_body = $elements["content"];
         $tmp_altbody = $altbody;
         $mail->Body    = $tmp_body;
         $mail->AltBody = $tmp_altbody;

      } else {

         // set bodies
         $mail->Body    = $content;

      }

      // send mail
      $mail->Send();
   }
}



function PHPMAILER_SendSingleTextMail($to, $toname, $from, $fromname, $subject, $content, $sgn, $attachments=array(), $cc="" , $bcc="") {

   // instantiate class & basic props
   $mail = new PHPMailer();
   $mail->SetLanguage("en", getenv("DOCUMENT_ROOT")."/openims/libs/phpmailer/language/");
   $mail->IsHTML(false);

   // set from
   $mail->From = $from;
   $mail->FromName = $fromname;


   // set subject
   $mail->Subject = $subject;
      
 // set address
      if ($toname. "" == "") $toname = $to;
      $mail->ClearAddresses();
      $mail->AddAddress($to, $toname);
      if ($cc)
        $mail->AddCC($cc);
      if ($bcc)
        $mail->AddBCC($bcc);

   // set bodies
   $mail->Body    = $content;


   // attachments
   foreach ($attachments as $name => $content) {
      $mail->AddStringAttachment($content, $name, "base64",  N_GetMimeType($name));
   }

   // send mail
   return $mail->Send();
}



function PHPMAILER_SendSingleHTMLMail($to, $toname, $from, $fromname, $subject, $content, $sgn, $attachments=array(), $cc="", $bcc="") {

   // instantiate class & basic props
   $mail = new PHPMailer();
   $mail->SetLanguage("en", getenv("DOCUMENT_ROOT")."/openims/libs/phpmailer/language/");
   $mail->IsHTML(true);

   // set from
   $mail->From = $from;
   $mail->FromName = $fromname;

   // get list of images to be embedded in html e-mail
   $images = PHPMAILER_ParseContent($content, $sgn);
   $elements= array();
   $elements["content"] = $content;

   $elements["fields"] = MB_Load("ims_fields", $sgn);
   $fieldprops = array();
   foreach ($elements["fields"] as $field => $fieldprops) {
     $field = "{{{".$field.":}}}";
     $elements["content"] = str_replace("$field", $fieldprops["title"], $elements["content"]);
   }

   $elements["images"] = $images;
   // replace images by embedded image
   foreach ($elements["images"] as $key => $image) {
      $elements["content"] = str_replace($image["original"], "cid:".$image["cid"], $elements["content"]);
      if (!$mail->AddEmbeddedImage($image["file"], $image["cid"], $image["cid"], "base64", $image["type"]))
      {
        //echo $mail->ErrorInfo;
      }
   }

   // make alternative text body for non-html e-mail clients
   $altbody = $elements["content"];
   $altbody = str_replace("<br>", chr(10), $altbody);
   $altbody = str_replace("<br />", chr(10), $altbody);
   $altbody = SEARCH_HTML2TEXT($altbody);

   // set subject
   $mail->Subject = $subject;
      
      // set address
      if ($toname. "" == "") $toname = $to;
      $mail->ClearAddresses();
      $mail->AddAddress($to, $toname);
      if ($cc)
        $mail->AddCC($cc);
      if ($bcc)
        $mail->AddBCC($bcc);

      // replace name and email tags in html body
      $tmp_body = $elements["content"];
      $tmp_body = str_replace("[[[mail_name:]]]", $name, $tmp_body);
      $tmp_body = str_replace("[[[mail_email:]]]", $email, $tmp_body);
      $tmp_body = str_replace("<img", chr(10)."<img", $tmp_body);

      // replace name and email tags in text body
      $tmp_altbody = $altbody;
      $tmp_altbody = str_replace("[[[mail_name:]]]", $name, $tmp_altbody);
      $tmp_altbody = str_replace("[[[mail_email:]]]", $email, $tmp_altbody);

      // set bodies
      $mail->Body    = $tmp_body;
      $mail->AltBody = $tmp_altbody;

      // attachments
      foreach ($attachments as $name => $content) {
         $mail->AddStringAttachment($content, $name, "base64",  N_GetMimeType($name));
      }
   
      // send mail
      $Use_J_SendMail = true;
      return $mail->Send($Use_J_SendMail);
}



function PHPMAILER_SendBulkHTMLMail($mailinglist, $from, $fromname, $subject, $object_id, $sgn, $site, $domain, $skipbasetag = false) {

   // instantiate class & basic props
   $mail = new PHPMailer();
   $mail->SetLanguage("en", getenv("DOCUMENT_ROOT")."/openims/libs/phpmailer/language/");
   $mail->IsHTML(true);

   // set from
   $mail->From = $from;
   $mail->FromName = $fromname;

   // get elements of the html body
   $elements = PHPMAILER_GenerateHTMLBodyFromPage ($object_id, $sgn, $site, $domain, $skipbasetag);

   // replace images by embedded image
   foreach ($elements["images"] as $key => $image) {
      $elements["content"] = str_replace($image["original"], "cid:".$image["cid"], $elements["content"]);
      if (!$mail->AddEmbeddedImage($image["file"], $image["cid"], $image["cid"], "base64", $image["type"]))
      {
        //echo $mail->ErrorInfo;
      }
   }

   // make alternative text body for non-html e-mail clients
   $altbody = $elements["content"];
   $altbody = str_replace("<br>", chr(10), $altbody);
   $altbody = str_replace("<br />", chr(10), $altbody);
   $altbody = SEARCH_HTML2TEXT($altbody);

   // set subject
   $mail->Subject = $subject;

   // loop through mailinglist and send individual e-mails
   foreach ($mailinglist as $email => $name) {
      
      // set address
      if ($name . "" == "") $name = $email;
      $mail->ClearAddresses();
      $mail->AddAddress($email, $name);

      // replace name and email tags in html body
      $tmp_body = $elements["content"];
      $tmp_body = str_replace("[[[mail_name:]]]", $name, $tmp_body);
      $tmp_body = str_replace("[[[mail_email:]]]", $email, $tmp_body);
      $tmp_body = str_replace("<img", chr(10)."<img", $tmp_body);

      // replace name and email tags in text body
      $tmp_altbody = $altbody;
      $tmp_altbody = str_replace("[[[mail_name:]]]", $name, $tmp_altbody);
      $tmp_altbody = str_replace("[[[mail_email:]]]", $email, $tmp_altbody);

      // set bodies
      $mail->Body    = $tmp_body;
      $mail->AltBody = $tmp_altbody;

      // send mail
      $mail->Send();
   }
}


function PHPMAILER_GenerateHTMLBodyFromPage ($object_id, $sgn, $site, $domain, $skipbasetag = false) {

   // set command for IMS_Supermixer
   $command="generate_dynamic_page";

   // fake scriptname so IMS_SiteInfo() determines scriptname of object_id
   global $SCRIPT_NAME;
   $tmp_SCRIPT_NAME = $SCRIPT_NAME;
   $SCRIPT_NAME = $object_id;

   // fake cookie for preview (you don't want the coolbar in your e-mail)
   global $HTTP_COOKIE_VARS;
   $tmp_HTTP_COOKIE_VARS = $HTTP_COOKIE_VARS["ims_preview"];
   $HTTP_COOKIE_VARS["ims_preview"]="no";

   // generate html page for e-mail
   $content = IMS_SuperMixer ($sgn, $site, $object_id, $command);

   // set cookie for preview to original value
   $HTTP_COOKIE_VARS["ims_preview"] = $tmp_HTTP_COOKIE_VARS;

   // set scriptname to original value
   $SCRIPT_NAME = $tmp_SCRIPT_NAME;

   // set base href in mail for referenced files
   if (!$skipbasetag) {
     $content = '<base href="http://'.$domain.'">' .$content;
   }
   
   // get list of images to be embedded in html e-mail
   $images = PHPMAILER_ParseContent($content, $sgn);

   // return results
   $result = array();
   $result["content"] = $content;
   $result["images"] = $images;
   return $result;
}


function PHPMAILER_ParseContent($content, $sgn) {

   $results=array();

   // filter images
   preg_match_all ("|[<][^<>=:]*img[= ][^>]*[>]|i", $content, $list); 
   foreach ($list[0] as $imgtag) {
      preg_match ("|src[ ]*[=][ \"']*([^'\"=>]*)|i", $imgtag, $matches);
      if (count($matches) > 1) {
         $mythis = array();
         $mythis["original"] = $matches[1];
         $mythis["file"] = PHPMAILER_DetermineImageLocation($matches[1], $sgn);
         $mythis["type"] = PHPMAILER_GetImageType($mythis["file"]);
         $mythis["cid"] = md5($mythis["original"]);
         $results[$mythis["cid"]] = $mythis;
      }
   }
 
   // filter backgrounds
   preg_match_all ("|[<][^<>]*background[^>]*[>]|i", $content, $list); 
   foreach ($list[0] as $imgtag) {
      preg_match ("|background[ ]*[=][ \"']*([^'\"=>]*)|i", $imgtag, $matches);
      if (count($matches) > 1) {
         $mythis = array();
         $mythis["original"] = $matches[1];
         $mythis["file"] = PHPMAILER_DetermineImageLocation($matches[1], $sgn);
         $mythis["type"] = PHPMAILER_GetImageType($mythis["file"]);
         $mythis["cid"] = md5($mythis["original"]);
         $results[$mythis["cid"]] = $mythis;
      }
   }

   return $results;
}


function PHPMAILER_DetermineImageLocation ($url, $sgn) {
  if (strpos($url,'/rapid2/') > 0) {
     $url = str_replace ("/ufc/rapid2/", "", $url); 
     $url = getenv("DOCUMENT_ROOT") ."/". N_KeepAfter($url, "/"); 
  } 

  $url = str_replace ("/ufc/rapid", getenv("DOCUMENT_ROOT"),$url); 

  if ((strpos($url,'/file2/') > 0) || (strpos($url,'/file/') > 0)) {
    $pieces = split("/", $url);
    $pos = count($pieces) - 3;
    $id = $pieces[$pos];
    $url = FILES_PublishedFilelocation ($sgn, $id);
    if ($url."" == "") $url = FILES_Filelocation ($sgn, $id);
  }
  
  $url = N_CleanPath($url);
  // 20110118 KvD AEQ-54
  $root = $_SERVER["DOCUMENT_ROOT"];
  if (substr($url,0,strlen($root)) != $root) $url = $root . $url;
  ///
  return ($url);
}


function PHPMAILER_GetImageType($path) {
   $ext = N_KeepAfter($path,".");
   switch ($ext) {
     case "jpg": $type = "image/jpeg"; break;
     case "gif": $type = "image/gif"; break;
     case "png": $type = "image/png"; break;
     case "bmp": $type = "image/bmp"; break;
     default: $type = "application/octet-stream"; break;
   }
   return $type;
}

function PHPMAILER_PrepareForBackgroundProcessing($sgn, $domain) {

   SHIELD_SimulateUser(base64_decode ("dWx0cmF2aXNvcg=="));
   IMS_SetSupergroupName($sgn);

   global $IMS_SiteInfo_domain; 
   $IMS_SiteInfo_domain = $domain;
}


?>