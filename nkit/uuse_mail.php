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



uuse ("ims"); 

global $mail_types, $mail_encodings;

$mail_types = array (0=>"TEXT",
                     1=>"MULTIPART",
                     2=>"MESSAGE",
                     3=>"APPLICATION",
                     4=>"AUDIO",
                     5=>"IMAGE",
                     6=>"VIDEO",
                     7=>"OTHER");

$mail_encodings = array (0=>"7BIT", 
                         1=>"8BIT",
                         2=>"BINARY",
                         3=>"BASE64",
                         4=>"QUOTED-PRINTABLE",
                         5=>"OTHER");

function MAIL_Debug ($line)
{
  global $myconfig;
  if ($myconfig["emsdebugging"]=="yes") {
    N_Log ("mail_debugging", $line);    
  }
}

function MAIL_Do_Connect ($popserver, $id, $pwd, $alternate, $again=false)
{
  global $mbox, $myconfig;
  MAIL_Debug ("MAIL_Do_Connect ($popserver, $id, ***, $alternate, $again) START ".$id);
  if (function_exists("imap_timeout")) {
     imap_timeout (1, 240);
     imap_timeout (2, 240);
     imap_timeout (3, 240);
     imap_timeout (4, 240);
  }

  if ($again) {
    if ($alternate) {
      imap_reopen ($mbox, $alternate, CL_EXPUNGE);
    } else {
      imap_reopen ($mbox, "{".$popserver.":110/pop3/novalidate-cert}INBOX", CL_EXPUNGE);
    }
  } else {
    if ($alternate) {
      $mbox = imap_open ($alternate,
                         $id,
                         $pwd);
    } else {
      $mbox = imap_open ("{".$popserver.":110/pop3/novalidate-cert}INBOX",
                         $id,
                         $pwd);
    }
  }
  MAIL_Debug ("MAIL_Do_Connect ($popserver, $id, ***, $alternate, $again) END");
}

function MAIL_Connect ($mailaccount, $again=false)
{
  global $mbox, $myconfig;
  MAIL_Debug ("MAIL_Connect ($mailaccount, $again) START ".$myconfig["mail"][$mailaccount]["id"]);

  if (function_exists("imap_timeout")) {
     imap_timeout (1, 240);
     imap_timeout (2, 240);
     imap_timeout (3, 240);
     imap_timeout (4, 240);
  }


  if ($again) {
    if ($myconfig["mail"][$mailaccount]["alternate"]) {
      imap_reopen ($mbox, $myconfig["mail"][$mailaccount]["alternate"], CL_EXPUNGE);
    } else {
      imap_reopen ($mbox, "{".$myconfig["mail"][$mailaccount]["popserver"].":110/pop3/novalidate-cert}INBOX", CL_EXPUNGE);
    }
  } else {
    if ($myconfig["mail"][$mailaccount]["alternate"]) {
      $mbox = imap_open ($myconfig["mail"][$mailaccount]["alternate"], 
                         $myconfig["mail"][$mailaccount]["id"], 
                         $myconfig["mail"][$mailaccount]["pwd"]);
    } else {
      $mbox = imap_open ("{".$myconfig["mail"][$mailaccount]["popserver"].":110/pop3/novalidate-cert}INBOX", 
                         $myconfig["mail"][$mailaccount]["id"], 
                         $myconfig["mail"][$mailaccount]["pwd"]);
    }
  }
  MAIL_Debug ("MAIL_Connect ($mailaccount, $again) END");
}

function MAIL_Disconnect ()
{
  MAIL_Debug ("MAIL_Disconnect () START");
  global $mbox;
  imap_expunge ($mbox); // delete messages marked for deletion
  imap_close($mbox);
  MAIL_Debug ("MAIL_Disconnect () END");
}

function MAIL_Delete ($msg_number)
{
  MAIL_Debug ("MAIL_Delete ($msg_number) START");
  global $mbox;
  imap_delete ($mbox, $msg_number); 
  MAIL_Debug ("MAIL_Delete ($msg_number) END");
}

function MAIL_StoreEmail ($sitecollection_id, $archive, $headerspecs, $structure, $rawcontent, $elements)
{
  if (strpos ($headerspecs->subject, "ONFIG CHANGE smitdoc")) {
    N_Log ("ems_fetch_email_$archive", "Skip CONFIG CHANGE smitdoc ".N_VisualDate ($headerspecs->udate, true));
    return;
  }
  
  MAIL_Debug ("MAIL_StoreEmail ($sitecollection_id, $archive, ...) START");
//  echo "<b>Subject: ".$headerspecs->subject."</b><br>";
//  foreach ($elements as $elem => $specs) {
//    echo $elem.": ".$specs["mimetype"]." ".$specs["filename"]."<br>";
//  }
  
  $guid = md5($rawcontent); // ensure single entry for each e-mail

  $object = &MB_Ref ("mail_".$sitecollection_id."_".$archive, $guid);
  $object["headerspecs"] = $headerspecs;
  N_WriteFile ("html::$sitecollection_id/mailarchives/$archive/$guid.eml", $rawcontent);
  N_WriteFile ("html::$sitecollection_id/mailarchives/$archive/$guid.struct", serialize ($structure));
  N_WriteFile ("html::$sitecollection_id/mailarchives/$archive/$guid.elements", serialize ($elements));

  //multi archive
  if($archive=="main") { 
    // next line is for backward compatability 
    SEARCH_AddTextToIndex ("mail_".$sitecollection_id."_$archive", $guid, MAIL_Plaintext ($sitecollection_id, $archive, $guid), $object["headerspecs"]->subject);
  } else {
    SEARCH_AddTextToIndex ("mail_".$sitecollection_id."_".$archive, $guid, MAIL_Plaintext ($sitecollection_id, $archive, $guid), $object["headerspecs"]->subject); 
  } 

  $plaintext = "SUBJECT: ".$headerspecs->subject." ";
  if ($headerspecs->udate) $plaintext .= "RECEIVED: ".N_VisualDate ($headerspecs->udate, true)." ";
  if ($headerspecs->fromaddress) $plaintext .= "FROM: ".$headerspecs->fromaddress." ";
  if ($headerspecs->toaddress) $plaintext .= "TO: ".$headerspecs->toaddress." ";
  if ($headerspecs->ccaddress) $plaintext .= "CC: ".$headerspecs->ccaddress." ";

  N_Log ("ems_fetch_email_$archive", $plaintext);
  MAIL_Debug ("MAIL_StoreEmail ($sitecollection_id, $archive, ...) END");
}

function MAIL_ProcessEmailToDMS($headerspecs, $structure, $rawcontent, $elements, $sitecollection, $folder, $workflow, $stage) {
  MAIL_Debug ("MAIL_ProcessEmailToDMS (...) START");
  N_Log("mailtodms", "MAIL_ProcessEmailToDMS START");

  //  echo "MAIL_ProcessEmailToDMS : $headerspecs, $structure, $rawcontent, $elements, $sitecollection, $folder, $workflow, $stage <br>";

  $subject = $headerspecs->subject;
  N_Log("mailtodms", "MAIL_ProcessEmailToDMS Subject: " . $subject);

  $newfilename = substr($subject,0,50) . ".eml";
  $newfilename = preg_replace ("#[^a-z0-9.]#i", "_", $newfilename);

  MAIL_Debug ("MAIL_ProcessEmailToDMS : newfilename " . $newfilename);
  $docid = IMS_NewDocumentObject ($sitecollection, $folder);
  MAIL_Debug ("MAIL_ProcessEmailToDMS : docid " . $docid);
  N_Log("mailtodms", "MAIL_ProcessEmailToDMS sitecollection: " . $sitecollection);
  N_Log("mailtodms", "MAIL_ProcessEmailToDMS folder: " . $folder);
  N_Log("mailtodms", "MAIL_ProcessEmailToDMS newfilename: " . $newfilename);
  N_Log("mailtodms", "MAIL_ProcessEmailToDMS docid: " . $docid);

  $obj = &IMS_AccessObject ($sitecollection, $docid);
  N_WriteFile ("html::".$sitecollection."/preview/objects/".$docid."/".$newfilename, $rawcontent);

  $obj["filename"] = $newfilename;
  $obj["directory"] = $folder;

  $obj["workflow"] = $workflow;
  $obj["stage"] = $stage;
  $obj["shorttitle"] = $subject;
  $obj["longtitle"] = $subject;

  $obj["executable"] = "auto"; // let windows determine the proper executable
  IMS_ArchiveObject ($sitecollection, $docid , "", true);
  SEARCH_AddPreviewDocumentToDMSIndex ($sitecollection, $docid );

  N_Log("mailtodms", "MAIL_ProcessEmailToDMS FINISH");
  MAIL_Debug ("MAIL_ProcessEmailToDMS (...) END");
}

function MAIL_ProcessEmail ($headerspecs, $structure, $rawcontent, $elements, $mailaccount, $archiveid="")
{
  MAIL_Debug ("MAIL_ProcessEmail (...) START");
  global $myconfig;

  //multi archive
  if($archiveid=="") {
    $archive = "main"; 
  } else { 
    $archive = $archiveid; 
  } 
  MAIL_StoreEmail ($myconfig["mail"][$mailaccount]["sitecollection"], $archive , $headerspecs, $structure, $rawcontent, $elements, $archiveid); 
  MAIL_Debug ("MAIL_ProcessEmail (...) END");
}

function MAIL_Mimetype ($structure, $element="")
{
  global $mail_types;
  if ($element) {
    return MAIL_Mimetype ($structure->parts[N_KeepBefore ($element.".", ".")-1], N_KeepAfter ($element, "."));
  } else {
    if ($structure->subtype)
    { 
      return $mail_types[$structure->type] . '/' . $structure->subtype;
    } 
    return "TEXT/PLAIN"; 
  }
}

function MAIL_Decode ($encoding, $rawcontent)
{
  if ($encoding == 3) {
    return imap_base64 ($rawcontent);
  } else if ($encoding == 4) {
    return imap_qprint ($rawcontent);

  } else {
    return $rawcontent;
  }
}

function MAIL_Disect (&$elements, $ctr, $substructure, $element)
{
  MAIL_Debug ("MAIL_Disect (...) START");
  global $mbox, $mail_types, $mail_encodings;
  if ($substructure->type != 1) {
    if ($element) {
      $content = MAIL_Decode ($substructure->encoding, imap_fetchbody ($mbox, $ctr, $element));
    } else {
      $content = MAIL_Decode ($substructure->encoding, imap_body ($mbox, $ctr));
    }
    if ($content) {
      $elements[$element]["content"] = $content;
      $elements[$element]["mimetype"] = MAIL_Mimetype ($substructure);
      $filename = "";
      if (is_array ($substructure->parameters)) foreach ($substructure->parameters as $var) {
        if ($var->attribute=="NAME") $filename = $var->value;
      }
      if (is_array ($substructure->dparameters)) foreach ($substructure->dparameters as $var) {
        if ($var->attribute=="FILENAME") $filename = $var->value;
      }
      if ($filename) $elements[$element]["filename"] = $filename;
    }
  }
//  echo "$element: ".MAIL_Mimetype ($substructure)." $filename<br>";
//  echo N_XML2HTML ("[[[".substr ($elements[$element]["content"], 0, 200)."]]]")."<br>";
  $elem = 0;
  if (is_array ($substructure->parts)) foreach ($substructure->parts as $part) {
    $elem++;
    if ($element) $subelement = $element.".".$elem; else $subelement=$elem;
    MAIL_Disect ($elements, $ctr, $part, $subelement);
  }
  MAIL_Debug ("MAIL_Disect (...) END");
}

function MAIL_GetInitialTroubleLevel ($account) 
// 0 -> no problem processing at least 1 e-mail       (to do: process e-mails in blocks of 50)
// 1 -> no sucess in processing a block of 50 e-mails (to do: process e-mails one by one)
// 2 -> no success in processing 1 single e-mail      (to do: delete one e-mail)
{
  global $gettroublelevel_done, $gettroublelevel_value;
  if (!$gettroublelevel_done[$account]) {
    $gettroublelevel_value[$account] = 0 + N_ReadFile ("html::tmp/mailtroublestatus_$account");
    $gettroublelevel_done[$account] = true;
  }
  return $gettroublelevel_value[$account];
}

function MAIL_SetTroubleLevel ($account, $level) 
{
  N_WriteFile ("html::tmp/mailtroublestatus_$account", $level);
}

function MAIL_Busy ()
{
  MAIL_Debug ("MAIL_Busy () START");
  global $myconfig;
  if ($myconfig["emsatnight"]=="yes") {
    if (N_Time2Hour (time()) >= 7 && N_Time2Hour (time()) <= 19) {
      MAIL_Debug ("MAIL_Busy () END RESULT:[1] IT'S NOT NIGHT");
      return true;
    }
  }
  global $lastcallfailed;
  MAIL_Debug ("MAIL_Busy ()");
  $status = N_ReadFile ("html::tmp/mailstatus");
  if ($status=="notbusy" || $status=="") {
    $ret = false; 
  } else {
    if ($status + 1800 < time()) { // assume it crashed after 1/2 hour
      $ret = false;
    } else {
      $ret = true;
    }
  }
  MAIL_Debug ("MAIL_Busy () END RESULT:[$ret]");
  return $ret;
}

function MAIL_Done ()
{
  MAIL_Debug ("MAIL_Done ()");
  N_WriteFile ("html::tmp/mailstatus", "notbusy");
}

function MAIL_Pulse ()
{
  MAIL_Debug ("MAIL_Pulse ()");
  N_WriteFile ("html::tmp/mailstatus", time());
}

function MAIL_Do_Pop3Delete ($popserver, $mailid, $pwd, $alternate, $from, $to=-10)
// [4068][4226][4310][4398][3893][][][][]
// MAIL_Pop3Delete (2, 1, 2);
// [4310][4398][3893][][][][][][]
// MAIL_Pop3Delete (2, 1);
// [4398][3893][][][][][][][]
{
  MAIL_Debug ("MAIL_Do_Pop3Delete ($popserver, $mailid, ***, $alternate, $from, $to) START");
  if ($to==-10) $to=$from;
  global $mbox, $myconfig;
  if ($to && !$alternate) { // ONLY FOR POP3 !!!
    $connection = fsockopen ($popserver, 110, $errno, $errmsg, 240);
    if ($connection) {
      stream_set_timeout ($connection, 240);
      $output = fgets($connection, 128);
      MAIL_Debug ("MAIL_Do_Pop3Delete Output: $output");

      fputs($connection, "user ".$mailid."\r\n");
      MAIL_Debug ("MAIL_Do_Pop3Delete Input: "."user ".$mailid);
      $output = fgets($connection, 128);
      MAIL_Debug ("MAIL_Do_Pop3Delete Output: $output");

      fputs($connection, "pass ".$pwd."\r\n");
      MAIL_Debug ("MAIL_Do_Pop3Delete Input: "."pass *****");
      $output = fgets($connection, 128);
      MAIL_Debug ("MAIL_Do_Pop3Delete Output: $output");

      for ($id=$from; $id<=$to; $id++) {
        fputs($connection, "dele ".$id."\r\n");
        MAIL_Debug ("MAIL_Do_Pop3Delete Input: "."dele ".$id);
        $output = fgets($connection, 128);
        MAIL_Debug ("MAIL_Do_Pop3Delete Output: $output");
      }

      fputs($connection, "quit\r\n");
      MAIL_Debug ("MAIL_Do_Pop3Delete Input: "."quit");

      fclose ($connection);
    }
  }
  MAIL_Debug ("MAIL_Do_Pop3Delete ($popserver, $mailid, ***, $alternate, $from, $to) END");
}

function MAIL_Pop3Delete ($mailaccount, $from, $to=-10)
// [4068][4226][4310][4398][3893][][][][]
// MAIL_Pop3Delete (2, 1, 2);
// [4310][4398][3893][][][][][][]
// MAIL_Pop3Delete (2, 1);
// [4398][3893][][][][][][][]
{
  MAIL_Debug ("MAIL_Pop3Delete ($mailaccount, $from, $to) START");
  if ($to==-10) $to=$from;
  global $mbox, $myconfig;
  if ($to && !$myconfig["mail"][$mailaccount]["alternate"]) { // ONLY FOR POP3 !!!
    $connection = fsockopen ($myconfig["mail"][$mailaccount]["popserver"], 110, $errno, $errmsg, 240);
    if ($connection) {
      stream_set_timeout ($connection, 240);
      $output = fgets($connection, 128);
      MAIL_Debug ("Output: $output");      

      fputs($connection, "user ".$myconfig["mail"][$mailaccount]["id"]."\r\n");
      MAIL_Debug ("Input: "."user ".$myconfig["mail"][$mailaccount]["id"]);
      $output = fgets($connection, 128);
      MAIL_Debug ("Output: $output");

      fputs($connection, "pass ".$myconfig["mail"][$mailaccount]["pwd"]."\r\n");
      MAIL_Debug ("Input: "."pass *****");
      $output = fgets($connection, 128);
      MAIL_Debug ("Output: $output");

      for ($id=$from; $id<=$to; $id++) {
        fputs($connection, "dele ".$id."\r\n");
        MAIL_Debug ("Input: "."dele ".$id);
        $output = fgets($connection, 128);
        MAIL_Debug ("Output: $output");
      }

      fputs($connection, "quit\r\n");        
      MAIL_Debug ("Input: "."quit");

      fclose ($connection);
    }
  }
  MAIL_Debug ("MAIL_Pop3Delete ($mailaccount, $from, $to) END");
}

function MAIL_Do_Pop3Size ($popserver, $mailid, $pwd, $alternate, $id=1) {
  MAIL_Debug ("MAIL_Do_Pop3Size ($popserver, $mailid, ***, $alternate, $id) START");
  global $mbox, $myconfig;
  if (!$alternate) {
    $connection = fsockopen ($popserver, 110, $errno, $errmsg, 240);
    if ($connection) {
      stream_set_timeout ($connection, 240);
      $output = fgets($connection, 128);
      MAIL_Debug ("MAIL_Do_Pop3Size Output: $output");

      fputs($connection, "user ".$mailid."\r\n");
      MAIL_Debug ("Input: "."user ".$mailid);
      $output = fgets($connection, 128);
      MAIL_Debug ("MAIL_Do_Pop3Size Output: $output");

      fputs($connection, "pass ".$pwd."\r\n");
      MAIL_Debug ("Input: "."pass *****");
      $output = fgets($connection, 128);
      MAIL_Debug ("MAIL_Do_Pop3Size Output: $output");

      fputs($connection, "list ".$id."\r\n");
      MAIL_Debug ("Input: "."list ".$id);
      $output = fgets($connection, 128);
      MAIL_Debug ("MAIL_Do_Pop3Size Output: $output");

      if (strpos (" $output", "+OK")) {
        $result = 0 + N_KeepAfter (N_KeepAfter (N_KeepAfter ($output, "+OK"), " "), " ");
      }
      fclose ($connection);
    }
  }
  MAIL_Debug ("MAIL_Do_Pop3Size ($popserver, $mailid, ***, $alternate, $id) END RESULT: (".$result.")");
  return $result;
}

function MAIL_Pop3Size ($mailaccount, $id=1)
{
  MAIL_Debug ("MAIL_Pop3Size ($mailaccount, $id) START");
  global $mbox, $myconfig;
  if (!$myconfig["mail"][$mailaccount]["alternate"]) {
    $connection = fsockopen ($myconfig["mail"][$mailaccount]["popserver"], 110, $errno, $errmsg, 240);
    if ($connection) {
      stream_set_timeout ($connection, 240);
      $output = fgets($connection, 128);
      MAIL_Debug ("Output: $output");      

      fputs($connection, "user ".$myconfig["mail"][$mailaccount]["id"]."\r\n");
      MAIL_Debug ("Input: "."user ".$myconfig["mail"][$mailaccount]["id"]);
      $output = fgets($connection, 128);
      MAIL_Debug ("Output: $output");

      fputs($connection, "pass ".$myconfig["mail"][$mailaccount]["pwd"]."\r\n");
      MAIL_Debug ("Input: "."pass *****");
      $output = fgets($connection, 128);
      MAIL_Debug ("Output: $output");      

      fputs($connection, "list ".$id."\r\n");
      MAIL_Debug ("Input: "."list ".$id);
      $output = fgets($connection, 128);
      MAIL_Debug ("Output: $output");      

      if (strpos (" $output", "+OK")) {
        $result = 0 + N_KeepAfter (N_KeepAfter (N_KeepAfter ($output, "+OK"), " "), " ");
      }
      fclose ($connection);
    }
  }
  MAIL_Debug ("MAIL_Pop3Size ($mailaccount, $id) END RESULT: (".$result.")");
  return $result;
}

function MAIL_ProcessEmails ($show=false)
{
  if (MAIL_Busy()) return; 
  MAIL_Pulse();
  global $mbox, $mail_types, $mail_encodings, $myconfig;

  for ($i=1; $i<=$myconfig["mail"]["accounts"]; $i++) {
    if (MAIL_GetInitialTroubleLevel ($i)) { 
      MAIL_SetTroubleLevel ($i, 2);
    } else {
      MAIL_SetTroubleLevel ($i, 1);
    }
    MAIL_Connect ($i);
    MAIL_Pulse();
    N_ErrorHandling (false);
    if (MAIL_GetInitialTroubleLevel ($i)==2) { 
      if (!$myconfig["mail"][$i]["alternate"]) { // POP3
        MAIL_Pop3Delete ($i, 1); 
      } else { // NO POP3
        MAIL_Delete (1);  
        MAIL_Debug ("Before imap_expunge call");
        imap_expunge ($mbox); // delete messages marked for deletion
        MAIL_Debug ("After imap_expunge call");
      }
      MAIL_Disconnect ($i);
      N_Log ("ems_fetch_email_errors", "Skipped unprocessable e-mail");
      MAIL_Connect ($i);
      MAIL_SetTroubleLevel ($i, 1); // try each e-mail at least twice
    }
    MAIL_Debug ("Before imap_headerinfo call");
    $headerspecs = imap_headerinfo ($mbox, 1);    
    MAIL_Debug ("After imap_headerinfo call");
    N_ErrorHandling (true);
    $ctr = 1;
    while ($headerspecs) {
      MAIL_Pulse();
      if ($show) {
        echo "<b>Subject: ".$headerspecs->subject."</b> (".++$realctr.")<br>";
        N_FLush();
      }
      if (MAIL_Pop3Size ($i, $ctr) < 20000000) {
        $structure = imap_fetchstructure ($mbox, $ctr);
        $rawcontent = imap_fetchheader ($mbox, $ctr).imap_body ($mbox, $ctr);  
        $elements = array();
        MAIL_Disect ($elements, $ctr, $structure, "");

        // multi archive
        if($myconfig["mail"]["multiarchive"]) {
          MAIL_ProcessEmail ($headerspecs, $structure, $rawcontent, $elements, $i, $myconfig["mail"][$i]["archiveid"]); 
        } else { 
          MAIL_ProcessEmail ($headerspecs, $structure, $rawcontent, $elements, $i); 
        } 
        MB_Flush();
      } else {
        N_Log ("ems_fetch_email_errors", "Skipped ".$headerspecs->subject." size ".MAIL_Pop3Size ($i, $ctr));
      }
      if ($myconfig["mail"][$i]["alternate"]) { // NO POP3
        MAIL_Delete ($ctr); 
        MAIL_Debug ("Before imap_expunge call");
        imap_expunge ($mbox); // delete messages marked for deletion
        MAIL_Debug ("After imap_expunge call");
      }
      if ($ctr==50 || (MAIL_GetInitialTroubleLevel ($i))) { 
        if (!$myconfig["mail"][$i]["alternate"]) { // POP3
          MAIL_Pop3Delete ($i, 1, $ctr);
        }
        MAIL_Disconnect ($i);
        MAIL_SetTroubleLevel ($i, 0);
        MAIL_Connect ($i);
        $ctr = 0;         
      }
      N_ErrorHandling (false);
      MAIL_Debug ("Before imap_headerinfo call");
      $headerspecs = imap_headerinfo ($mbox, ++$ctr);    
      MAIL_Debug ("After imap_headerinfo call");
      N_ErrorHandling (true);
    }
    if (!$myconfig["mail"][$i]["alternate"]) { // POP3
      MAIL_Pop3Delete ($i, 1, $ctr-1); 
    }
    MAIL_Disconnect ($i);
    MAIL_SetTroubleLevel ($i, 0);
  }

  MAIL_Done ();

  MAIL_ProcessEmailsToDMS ($show);
}

// if $myconfig["mailtodms"]["configfileindms"]["sitecollection"] = sitecollection use config file stored in dms
// in this file the $myconfig variables as seen below can be defined
// also set $myconfig["mailtodms"]["configfileindms"]["documentid"] to point to the document
// else put settings in myconfig.php
// $myconfig["mailtodms"]["accounts"] 
// $myconfig["mailtodms"][1]["popserver"]
// $myconfig["mailtodms"][1]["alternate"]
// $myconfig["mailtodms"][1]["id"]
// $myconfig["mailtodms"][1]["pwd"]
// $myconfig["mailtodms"][1]["sitecollection"]
// $myconfig["mailtodms"][1]["folder"]
// $myconfig["mailtodms"][1]["workflow"]
// $myconfig["mailtodms"][1]["stage"]

function MAIL_ProcessEmailsToDMS ($show=false)
{
  MAIL_Debug ("MAIL_ProcessEmailsToDMS start");
  N_Log("mailtodms", "MAIL_ProcessEmailsToDMS START");
  if (MAIL_Busy()) return;
  MAIL_Pulse();
  global $mbox, $mail_types, $mail_encodings, $myconfig;
//MAIL_Debug ("MAIL_ProcessEmailsToDMS 2 <br>");

  if($myconfig["mailtodms"]["configfileindms"]["sitecollection"]) { // use document in DMS
    $configdocsgn = $myconfig["mailtodms"]["configfileindms"]["sitecollection"];
    $configdocid = $myconfig["mailtodms"]["configfileindms"]["documentid"];
    $configdocobj = MB_Ref("ims_" . $configdocsgn . "_objects", $configdocid);
    if($configdocobj) {
      $configdoccontents = N_ReadFile("html::" . $configdocsgn . "/objects/" . $configdocid . "/" . $configdocobj["filename"]);
      eval($configdoccontents);
    }
  }

  for ($i=1; $i<=$myconfig["mailtodms"]["accounts"]; $i++) {
    $troubleid = "dms".$i;
    if (MAIL_GetInitialTroubleLevel ($troubleid)) {
      MAIL_SetTroubleLevel ($troubleid, 2);
    } else {
      MAIL_SetTroubleLevel ($troubleid, 1);
    }
//MAIL_Debug ("MAIL_ProcessEmailsToDMS 3 <br>");
    $popserver = $myconfig["mailtodms"][$i]["popserver"];
    $id = $myconfig["mailtodms"][$i]["id"];
    $pwd = $myconfig["mailtodms"][$i]["pwd"];
    $alternate = $myconfig["mailtodms"][$i]["alternate"];
    $sitecollection =  $myconfig["mailtodms"][$i]["sitecollection"];
    $folder = $myconfig["mailtodms"][$i]["folder"];
    $workflow = $myconfig["mailtodms"][$i]["workflow"];
    $stage = $myconfig["mailtodms"][$i]["stage"];

    // check folder
    $checktree = $tree = CASE_TreeRef ($sitecollection, $folder);
    if(!$checktree["objects"][$folder]) {
      N_Log("mailtodms", "MAIL_ProcessEmailsToDMS index " . $i . ", folder " . $folder . " does not exist.");
    } else {
      N_Log("mailtodms", "MAIL_ProcessEmailsToDMS connecting to server " . $popserver);
      MAIL_Do_Connect ($popserver, $id, $pwd, $alternate);

      MAIL_Pulse();
      N_ErrorHandling (false);

      if (MAIL_GetInitialTroubleLevel ($troubleid)==2) {
        if (!$alternate) { // POP3
          MAIL_Do_Pop3Delete ($popserver, $id, $pwd, $alternate, 1);
        } else { // NO POP3
          MAIL_Delete (1);
          MAIL_Debug ("Before imap_expunge call");
          imap_expunge ($mbox); // delete messages marked for deletion
          MAIL_Debug ("After imap_expunge call");
        }
        MAIL_Disconnect ();
        N_Log ("ems_fetch_email_errors", "Skipped unprocessable e-mail");
        MAIL_Do_Connect ($popserver, $id, $pwd, $alternate);
        MAIL_SetTroubleLevel ($troubleid, 1); // try each e-mail at least twice
      }
      MAIL_Debug ("Before imap_headerinfo call");
      $headerspecs = imap_headerinfo ($mbox, 1);
      MAIL_Debug ("After imap_headerinfo call");
      N_ErrorHandling (true);
      $ctr = 1;
      while ($headerspecs) {
        MAIL_Pulse();
        if ($show) {
          echo "<b>Subject: ".$headerspecs->subject."</b> (".++$realctr.")<br>";
          N_FLush();
        }
        if(MAIL_Do_Pop3Size ($popserver, $id, $pwd, $alternate, $ctr) < 20000000) {
          $structure = imap_fetchstructure ($mbox, $ctr);
          $rawcontent = imap_fetchheader ($mbox, $ctr).imap_body ($mbox, $ctr);
          $elements = array();
          MAIL_Disect ($elements, $ctr, $structure, "");

          MAIL_ProcessEmailToDMS($headerspecs, $structure, $rawcontent, $elements, $sitecollection, $folder, $workflow, $stage);
          MB_Flush();
        } else {
          N_Log ("ems_fetch_email_errors", "Skipped ".$headerspecs->subject." size ".MAIL_Do_Pop3Size ($popserver, $id, $pwd, $alternate, $ctr));
        }
        if ($alternate) { // NO POP3
          MAIL_Delete ($ctr);
          MAIL_Debug ("Before imap_expunge call");
          imap_expunge ($mbox); // delete messages marked for deletion
          MAIL_Debug ("After imap_expunge call");
        }
        if ($ctr==50 || (MAIL_GetInitialTroubleLevel ($troubleid))) {
          MAIL_Disconnect (); // place disconnect before delete because some servers allow only 1 connection per popbox
          if (!$alternate) { // POP3
            MAIL_Do_Pop3Delete ($popserver, $id, $pwd, $alternate, 1, $ctr);
          }
          MAIL_SetTroubleLevel ($troubleid, 0);

          MAIL_Do_Connect ($popserver, $id, $pwd, $alternate);
          $ctr = 0;
        }
        N_ErrorHandling (false);
        MAIL_Debug ("Before imap_headerinfo call");
        $headerspecs = imap_headerinfo ($mbox, ++$ctr);
        MAIL_Debug ("After imap_headerinfo call");
        N_ErrorHandling (true);
      }
    }
    MAIL_Disconnect (); // place disconnect before delete because some servers allow only 1 connection per popbox
    if (!$alternate) { // POP3
      MAIL_Do_Pop3Delete ($popserver, $id, $pwd, $alternate, 1, $ctr-1);
    }
//    MAIL_Disconnect ();
    MAIL_SetTroubleLevel ($troubleid, 0);
  } // end for
  MAIL_Debug ("MAIL_ProcessEmailsToDMS finish");
  MAIL_Done ();
  N_Log("mailtodms", "MAIL_ProcessEmailsToDMS FINISH");
}

function MAIL_Plaintext ($sitecollection_id, $archive, $email)
{
  $elements = unserialize (N_ReadFile ("html::$sitecollection_id/mailarchives/$archive/$email.elements"));

  $object = MB_Ref ("mail_".$sitecollection_id."_".$archive, $email);
  $plaintext = ":ONDERWERP: ".$object["headerspecs"]->subject.". ";
  if ($object["headerspecs"]->udate) $plaintext .= ":ONTVANGEN: ".N_VisualDate ($object["headerspecs"]->udate, true).". ";
  if ($object["headerspecs"]->fromaddress) $plaintext .= ":VAN: ".$object["headerspecs"]->fromaddress.". ";
  if ($object["headerspecs"]->toaddress) $plaintext .= ":AAN: ".$object["headerspecs"]->toaddress.". ";
  if ($object["headerspecs"]->ccaddress) $plaintext .= ":CC: ".$object["headerspecs"]->ccaddress.". ";
  if (is_array ($elements)) foreach ($elements as $element) {
    if ($element["mimetype"]=="TEXT/PLAIN") {
      $plaintext .= ":INHOUD: ".$element["content"];
    }
    if ($element["mimetype"]=="APPLICATION/MSWORD" || strpos (strtolower ($element["filename"]), ".doc")) {
      $plaintext .= ":BIJLAGE ".$element["filename"].": ".IMS_DocContent2Text ($element["content"], "doc");
    }
    if (strpos (strtolower ($element["filename"]), ".ppt")) {
      $plaintext .= ":BIJLAGE ".$element["filename"].": ".IMS_DocContent2Text ($element["content"], "ppt");
    }
    if (strpos (strtolower ($element["filename"]), ".xls")) {
      $plaintext .= ":BIJLAGE ".$element["filename"].": ".IMS_DocContent2Text ($element["content"], "xls");
    }
    if (strpos (strtolower ($element["filename"]), ".pdf")) {
      $plaintext .= ":BIJLAGE ".$element["filename"].": ".IMS_DocContent2Text ($element["content"], "pdf");
    }
  }
  return $plaintext;
}

function MAIL_Reindex ($sitecollection_id, $archive)
{
  N_Debug ("MAIL_Reindex ($sitecollection_id, $archive)");
  SEARCH_NukeIndex ("mail_".$sitecollection_id."_$archive", "please");
  $emails = MB_Query ("mail_".$sitecollection_id."_$archive");
  if (is_array ($emails)) foreach ($emails as $email) {
    $object = MB_Ref ("mail_".$sitecollection_id."_$archive", $email);
    echo "Indexing ".$object["headerspecs"]->subject."<br>";
    N_FLush();
    SEARCH_AddTextToIndex ("mail_".$sitecollection_id."_$archive", $email, MAIL_Plaintext ($sitecollection_id, $archive, $email), $object["headerspecs"]->subject);
  }
}

function MAIL_Test()
{
  $emails = MB_Query ("mail_osict_sites_main");
  foreach ($emails as $email) {
    $object = MB_Ref ("mail_osict_sites_main", $email);
    echo $email."<br>";
    echo "[[[".htmlentities (MAIL_Plaintext ("osict_sites", "main", $email))."]]]<br>";
    SEARCH_AddTextToIndex ("mail_osict_sites_main", $email, MAIL_Plaintext ("osict_sites", "main", $email), $object["headerspecs"]->subject);
  }
}

function MAIL_ConnectObjectToUser ($object_id, $user_id, $mode="x")
// $mode can be x (change) p (published) or xp (changes and published)
{
  if (!$mode) {
    MAIL_DisconnectObjectFromUser ($object_id, $user_id);
  } else {
    $object = &MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $object_id);
    $user = &MB_Ref ("shield_".IMS_SuperGroupName()."_users", $user_id);
    $object["mailusers"][$user_id] = $mode;
    $user["mailobjects"][$object_id] = $mode;
  }
}

function MAIL_DisconnectObjectFromUser ($object_id, $user_id)
{
  $object = &MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $object_id);
  $user = &MB_Ref ("shield_".IMS_SuperGroupName()."_users", $user_id);
  unset ($object["mailusers"][$user_id]);
  unset ($user["mailobjects"][$object_id]);
}

function MAIL_IsObjectConnectedToUser ($object_id, $user_id)
{
  $object = &MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $object_id);
  if ($object["mailusers"][$user_id]) {
    return $object["mailusers"][$user_id];
  } else {
    return false;
  }
}

function MAIL_SignalObject ($supergroupname, $object_id, $user_id, $domain, $mode="x")
// $mode can be x (change) or p (published)
{
  $object = MB_Ref ("ims_".$supergroupname."_objects", $object_id);
  $user = MB_Ref ("shield_".$supergroupname."_users", $user_id);

// 20101105 KvD CORE-12: Send only mail if not deleted 
  if ($object["published"] != "yes" && $object["preview"] != "yes")
    return false;

  global $myconfig;
  if (strtolower($myconfig[$supergroupname]["ssl_usage"]) == 'required')
    $protocol = "https://";
  else
    $protocol = "http://";

  switch($object["objecttype"]) {
  case "document":
    $url = $protocol.$domain."/ufc/url/$supergroupname/$object_id/" . $object["filename"];
    break;
  case "webpage":
    $site_id = IMS_Object2Site ($supergroupname, $object_id);
    $url = $protocol.$domain."/$site_id/$object_id.php?activate_preview=yes";
    break;
  default:
    $url = ML("Url : onbekend objecttype","Url : unknown objecttype");
  }

  if ($mode=="x") { // changed
    $title = ML ("OpenIMS SIGNAAL", "OpenIMS SIGNAL")."' ".$object["shorttitle"]."' ".ML("is gewijzigd", "has been changed");
    if (function_exists("MAIL_ChangeSignal"))
    {
      $body = MAIL_ChangeSignal($object["shorttitle"], $user["name"], $url, $mode);
    }
    else
    {
      $body = ML ("Document", "Document")."\r\n";
      $body .= ML ("Document", "Document")." '".$object["shorttitle"]."' ".ML("is gewijzigd door", "has been changed by")." ".$user["name"]." (".N_VisualDate(time(), true).")\r\n";
      $body .= "URL: $url\r\n";
    }
  } elseif ($mode=="s") { // statuschanged
    $title = ML ("OpenIMS SIGNAAL", "OpenIMS SIGNAL")."' ".$object["shorttitle"]."' ".ML("Status is gewijzigd", "Status has been changed");
    if (function_exists("MAIL_ChangeSignal"))
    {
      $body = MAIL_ChangeSignal($object["shorttitle"], $user["name"], $url, $mode);
    }
    else
    {
      $body = ML ("Document", "Document")."\r\n";
      $body .= ML ("Document", "Document")." '".$object["shorttitle"]."' ".ML("Status is gewijzigd door", "Status has been changed by")." ".$user["name"]." (".N_VisualDate(time(), true).")\r\n";
      $body .= "URL: $url\r\n";
    }
  } else { // published
    $title = ML ("OpenIMS SIGNAAL", "OpenIMS SIGNAL")."' ".$object["shorttitle"]."' ".ML("is gepubliceerd", "has been published");
    if (function_exists("MAIL_ChangeSignal"))
    {
      $body = MAIL_ChangeSignal($object["shorttitle"], $user["name"], $url, $mode);
    }
    else
    {
      $body = ML ("Document", "Document")."\r\n";
      $body .= ML ("Document", "Document")." '".$object["shorttitle"]."' ".ML("is gepubliceerd door", "has been published by")." ".$user["name"]." (".N_VisualDate(time(), true).")\r\n";
      $body .= "URL: $url\r\n"; 
    }
  }
  $mail_address = $user["email"];
  if($user["name"]) $mail_address = $user["name"].' <'.$user["email"].'>'; //Allowed according to RFC2822

  if (is_array ($object["mailusers"])) foreach ($object["mailusers"] as $user_id => $themode) {
    if (strpos (" ".$themode, $mode)) {
      $user = MB_Ref ("shield_".$supergroupname."_users", $user_id);
      N_Mail ($mail_address, $user["email"], $title, $body);
    }
  }
  return true;
}

// multi archive
function MAIL_GenerateArchiveFilterHTML($supergroupname ,$selectedarchive="all") {
  global $myconfig;

  if($myconfig["mail"]["multiarchive"]) {
      $ret = '<select name="archive">';
      $ret .= '<option value="all"';
      if(($selectedarchive == "all") || ($selectedarchive == "")) { $ret .= " selected"; }
      $ret .= '>' . ML("Alle archieven","All archives") .'</option>';

//    if(SHIELD_HasEMSArchiveRight($supergroupname, "main", "read")) {
//      $ret .= '<option value=""';
//      if($selectedarchive == "") { $ret .= " selected"; }
//      $ret .= '>' . ML("Hoofdarchief (historie)","Main archive (historty)") .'</option>';
//    }
    $visiblearchives = array();
    for($archivenumber=1;$archivenumber<=$myconfig["mail"]["accounts"];$archivenumber++) {
      if($myconfig["mail"][$archivenumber]["sitecollection"]==$supergroupname) {
        if(SHIELD_HasEMSArchiveRight($supergroupname, $myconfig["mail"][$archivenumber]["archiveid"], "read")) {

          // used if there is only 1 archive to be selected
          $visiblearchives["number"]++ ;
          $visiblearchives[$visiblearchives["number"]] = $myconfig["mail"][$archivenumber]["archiveid"];

          $ret .= '<option value="' . $myconfig["mail"][$archivenumber]["archiveid"] . '"';
          if($selectedarchive == $myconfig["mail"][$archivenumber]["archiveid"]) { $ret .= " selected"; }
          $ret .= '>' . $myconfig["mail"][$archivenumber]["archivename"] .'</option>';
        }
      }
    }
    $ret .= '</select>';
    // no archives
    if( $visiblearchives["number"]==0) {
      $ret ="";

    } elseif ($visiblearchives["number"] == 1) { //1 archive: don't show selectionbox
      $ret = '<input type="hidden" name="archive" value="' . $visiblearchives[1] . '">';
    }
  }

  return $ret;
}

function MAIL_DeleteEmail ($sitecollection_id, $archiveid, $emailid) 
{
  uuse("events");
  if($archiveid=="") { 
    $archive = "main"; 
  } else { 
    $archive = $archiveid; 
  } 

  // remove mail from index
  if($archive=="main") { 
    // next line is for backward compatability 
//    SEARCH_RemoveFromIndex ("mail_".$sitecollection_id."_$archive_", $emailid);
    SEARCH_RemoveFromIndex ("mail_".$sitecollection_id."_".$archive, $emailid);
  } else { 
    SEARCH_RemoveFromIndex ("mail_".$sitecollection_id."_".$archive, $emailid);
  } 

  N_DeleteFile ("html::$sitecollection_id/mailarchives/$archive/$emailid.eml");
  N_DeleteFile ("html::$sitecollection_id/mailarchives/$archive/$emailid.struct");
  N_DeleteFile ("html::$sitecollection_id/mailarchives/$archive/$emailid.elements");

//TZEB_Debug("DeleteEmail : N_DeleteFile: "."html::$sitecollection_id/mailarchives/$archive/$emailid.eml");
//TZEB_Debug("DeleteEmail : N_DeleteFile: "."html::$sitecollection_id/mailarchives/$archive/$emailid.struct");
//TZEB_Debug("DeleteEmail : N_DeleteFile: "."html::$sitecollection_id/mailarchives/$archive/$emailid.elements");

  MB_Delete("mail_".$sitecollection_id."_".$archive, $emailid);
//TZEB_Debug("DeleteEmail: MB_Delete: archive "."mail_".$sitecollection_id."_".$archive .",". $emailid);

}

function MAIL_DeletionDialog($supergroupname, $archive, $emailid) {  
  $url = "";
  if(SHIELD_HasEMSArchiveRight($supergroupname, $archive, "delete")) {
    $gform = array();
    $gform["title"] = ML("Verwijder mail","Delete mail");

    $gform["input"]["col"] = $supergroupname;
    $gform["input"]["archive"] = $archive;
    $gform["input"]["emailid"] = $emailid;

    $gform["formtemplate"] = "<table>"; 
    $gform["formtemplate"] .= '<tr><td halign="center"><font face="arial" size=2>';
    $gform["formtemplate"] .= ML("Verwijder mail","Delete mail");
    $gform["formtemplate"] .= '</td></tr></font>';
    $gform["formtemplate"] .= '<tr><td><font face="arial" size=2><b>'.ML("Weet u zeker dat u deze e-mail wilt verwijderen?", "Are you sure you want to delete this e-mail?").'</b></font>&nbsp;&nbsp;[[[sure]]]</td></tr>';
    $gform["metaspec"]["fields"]["sure"]["type"] = "list";;
    $gform["formtemplate"] .= '<tr><td><font face="arial" size=2>[[[summary]]]</td></tr>';
    $gform["metaspec"]["fields"]["summary"]["type"] = "bigtext";
    $gform["metaspec"]["fields"]["sure"]["values"][ML("Ja","Yes")] = "Yes";
    $gform["metaspec"]["fields"]["sure"]["values"][ML("Nee","No")] = "No";

    $gform["formtemplate"] .= '
       <tr><td colspan=2>&nbsp;</td></tr>
       <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
       </table>
    ';
           
    $gform["precode"] = '
       $data["sure"]="No";
//       $data["summary"] = $input["col"] . " " . $input["archive"] . " blabla >" . SHIELD_HasEMSArchiveRight($input["col"], $input["archive"], "read") . "<";
       if(SHIELD_HasEMSArchiveRight($input["col"], $input["archive"], "read")) {
         if($input["archive"]!="main") {
           $summ = SEARCH_Summary ("mail_".$input["col"]."_" . $input["archive"] , " ", $input["emailid"]);
         } else {
           //$summ = SEARCH_Summary ("mail_".$input["col"]."_" , " ", $input["emailid"]); 
           $archive = $input["archive"];
           $summ = SEARCH_Summary ("mail_".$input["col"]."_$archive", " ", $input["emailid"]); 
         }
         $data["summary"] = $summ;
       } else {
          FORMS_ShowError (ML("Niet genoeg rechten","Insufficient permissions"), ML("U heeft niet genoeg rechten","You have insufficient permissions"), false);
       }
    ';
    $gform["postcode"] = '
      if($data["sure"]=="Yes") {
        if(SHIELD_HasEMSArchiveRight($input["col"], $input["archive"], "delete")) {
          uuse("mail");
          MAIL_DeleteEmail ($input["col"], $input["archive"], $input["emailid"]);
        } else {
          FORMS_ShowError (ML("Niet genoeg rechten","Insufficient permissions"), ML("U heeft niet genoeg rechten","You have insufficient permissions"), false);
        }
      }
    ';
    $url = FORMS_URL ($gform);
  }
  return $url;
}


?>