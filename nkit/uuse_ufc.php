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



// UFC: URL Function Calls library


function UFC_HandleEverything()
{
  Header ("HTTP/1.1 200");
  Header ("Status: 200"); // fastcgi

// N_DIE ("qqq");

  $data = explode ("/", getenv("REQUEST_URI"));
  for ($i=3; $i<count($data)-1; $i++) {
    $params[$i-2] = urldecode ($data[$i]);
  }
  UFC_Handler (urldecode($data[2]), urldecode($data[count($data)-1]), $params);
}

function UFC_HTTPCaching ()
{
  global $HTTP_HOST, $myconfig;
  if ($myconfig["nohttpcaching"][$HTTP_HOST] == "yes") return false;
  return true;
}

function UFC_MakeSecure ($relurl)
{
  global $myconfig;
  if ($myconfig["securelinks"]!="yes") return $relurl;
  $list = MB_TurboMultiQuery ("shield_securelinks", array("select" => array('$record'=>$relurl)));
  if ($list) {
    reset($list);
    list($key) = each($list);
  } else {
    MB_Save ("shield_securelinks", $key=N_GUID(), $relurl);
  }
  $data = explode ("/", $relurl);
  return "/ufc/sec/$key/".$data[count($data)-1];
}

function UFC_DecodeSecure ($key)
{
  return MB_Load ("shield_securelinks", $key);
}

function UFC_Handler ($handler, $name, $params)
{
  global $myconfig;
  $handler = strtolower($handler); // zorgt er voor dat het "file2" (of "sec", "custom", "imput" etc.)  gedeelte uit de link ../ufc/file2/... niet hoofdletter gevoelig is.

  // It is very unlikely that this function sends HTML to the browser.
  // Therefore, debug=speed might be a bad idea.
  // So disable this iff triggered by a cookie (but allow it if triggered by the debug=speed url parameter).
  global $ims_speed, $debug;
  if ($ims_speed == "yes") {
    $ims_speed = "";
    $debug = "";
  }

  if ($handler=="sec") {
    $url = UFC_DecodeSecure ($params[1]);
    $params = array();
    $data = explode ("/", $url);
    for ($i=3; $i<count($data)-1; $i++) {
      $params[$i-2] = urldecode ($data[$i]);
    }
    return UFC_Handler ($data[2], $data[count($data)-1], $params);
  }
  if ($handler=="static") {
    // You can put the "static" handler in front of every other handler.
    //   /ufc/static/$extraparam/$handler/$params/$name.
    // The output generated is the same as if you had called:
    //   /ufc/$handler/$params/$name
    // But the output generated will also be saved to the /ufc/static directory
    // in your DocumentRoot, so that subsequent request for the url will be handled by
    // Apache, without PHP/OpenIMS.
    //
    // The url MUST be constructed in such a way, that, whenever the content changes, the url changes.
    // This is what you can use the $extraparam for: a timestamp or md5 based on the content.
    //
    // Example: /ufc/static/$timestamp/thumb/$sgn/$id/$xmax/$ymax/$name.
    ob_start();
    $extraparam = $params[1];
    $newhandler = $params[2];
    $newparams = array();
    for ($i = 3; $i <= count($params); $i++) {
      $newparams[$i - 2] = $params[$i];
    }
    UFC_Handler($newhandler, $name, $newparams);
    $content = ob_get_contents();
    ob_end_clean();
    if ($content) {
      $path = "html::/ufc/static/" . $extraparam . "/" . $newhandler . "/" . implode("/", $newparams);
      N_WriteFile($path."/".$name, $content);
    }
    echo $content;
  }
  if ($handler=="custom") {
    uuse ("flex");
    $custom = FLEX_LocalComponents (IMS_SuperGroupname(), "customufc");
    if ($custom) {
      foreach ($custom as $id => $specs) {
        if ($specs["handle"]==$params[1]) {
          eval ($specs["code_ufc"]);
        }
      }
    }
  } else if ($handler=="test") {
    echo "OpenIMS URL Function Calls<br>";
    echo "Handler: test<br>";
    echo "Name: $name<br>";
    echo "Params:<br>";
    N_EO ($params);
  } else if ($handler=="gadgetcontent") {
    uuse ("gadgets");
    GADGETS_ContentGenerator ($name, $params);
  } else if ($handler=="gadget") {
    uuse ("gadgets");
    GADGETS_Generator ($name, $params);
  } else if ($handler=="eval") {
    $specs = SHIELD_Decode ($params[1]);
    $input = $specs["input"];
    $code = $specs["code"];
    eval ($code);
  } else if ($handler=="rawhtml") {
    echo SHIELD_Decode ($params[1]);
  } else if ($handler=="rawfile") {
    // Always use the "Save as / Open With" dialog
    N_TransferFile(SHIELD_Decode($params[1]), $name, $attachment = true);
  } else if ($handler=="rapid2") {
    $path = "html::";
    $first = true;
    foreach ($params as $dir) {
      if ($first) {
        $first = false;
      } else {
        $path .= "/".$dir;
      }
    }
    $path .= "/".$name;
    $file = N_CleanPath ($path);
    $inopenims = true && strpos(" ".$path, "html::/openims/");
    if ($inopenims || (!strpos ($path, ".emz") && UFC_HTTPCaching ())) {
      header("Expires: ".gmdate("D, d M Y H:i:s", time()+24*3600) . " GMT");
      $e_tag = md5($file);
      $file_mtime = gmdate("D, d M Y H:i:s", @filemtime($file))." GMT";
      if($_SERVER["HTTP_IF_NONE_MATCH"] == $e_tag || $_SERVER["HTTP_IF_MODIFIED_SINCE"] == $file_mtime)
      {
        header("HTTP/1.1 304 Not Modified");
        header("Status: 304 Not Modified"); // fastcgi
        header("Cache-Control: store, cache");
        header("Pragma: cache");
        header("Last-Modified: ".$file_mtime."");
        header("ETag: ".$e_tag."");
        N_Exit();
        exit;
      }
      // If for whatever reason the server is unable the read the filemtime, it will probably also fail to transfer the file.
      // We do NOT want the browser to cache this failed result. Example of what could go wrong: custom CMS component creates 
      // link to file that does not exist. The error is noticed and the file is created, but on reload, the browser just 
      // resends the ETag received for the non-existent file, and receives a 304 back. Problem solved by not sending that ETag.
      if (@filemtime($file)) {
        header("Cache-Control: store, cache");
        header("Pragma: cache");
        header("Last-Modified: " . $file_mtime);
        header("ETag: ".$e_tag."");
      }
    }
    if (strpos ($file, "..")) {
      N_Log ("transdeny", $file);
    } else if (strpos ($file, "_sites")) {
      N_TransferFile ($file, $name);
    } else if (strpos ($file, ".gif")) {
      N_TransferFile ($file, $name);
    } else if (strpos ($file, ".jpg")) {
      N_TransferFile ($file, $name);
    } else if (strpos ($file, ".png")) {
      N_TransferFile ($file, $name);
    } else if (strpos ($file, ".css")) {
      N_TransferFile ($file, $name);
    } else if (strpos ($file, ".js")) {
      N_TransferFile ($file, $name);
    } else if (strpos ($file, ".ico")) {
      N_TransferFile ($file, $name);
    } else {
      N_Log ("transdeny", $file);
    }
  } else if ($handler=="rapid") {
    $path = "html::";
    foreach ($params as $dir) {
      $path .= "/".$dir;
    }
    $path .= "/".$name;
    $file = N_CleanPath ($path);
    $inopenims = true && strpos(" ".$path, "html::/openims/");
    if ($inopenims || (!strpos ($path, ".emz") && UFC_HTTPCaching ())) {
      header("Expires: ".gmdate("D, d M Y H:i:s", time()+24*3600) . " GMT");
      $e_tag = md5($file);
      $file_mtime = gmdate("D, d M Y H:i:s", @filemtime($file))." GMT";
      if($_SERVER["HTTP_IF_NONE_MATCH"] == $e_tag || $_SERVER["HTTP_IF_MODIFIED_SINCE"] == $file_mtime)
      {
        header("HTTP/1.1 304 Not Modified");
        header("Status: 304 Not Modified"); // fastcgi
        header("Cache-Control: store, cache");
        header("Pragma: cache");
        header("Last-Modified: ".$file_mtime."");
        header("ETag: ".$e_tag."");
        N_Exit();
        exit;
      }
      if (@filemtime($file)) {
        header("Cache-Control: store, cache");
        header("Pragma: cache");
        header("Last-Modified: " . $file_mtime);
        header("ETag: ".$e_tag."");
      }
    }
    if (strpos ($file, "..")) {
      N_Log ("transdeny", $file);
    } else if (strpos ($file, "_sites")) {
      N_TransferFile ($file, $name);
    } else if (strpos ($file, ".gif")) {
      N_TransferFile ($file, $name);
    } else if (strpos ($file, ".jpg")) {
      N_TransferFile ($file, $name);
    } else if (strpos ($file, ".png")) {
      N_TransferFile ($file, $name);
    } else if (strpos ($file, ".css")) {
      N_TransferFile ($file, $name);
    } else if (strpos ($file, ".js")) {
      N_TransferFile ($file, $name);
    } else if (strpos ($file, ".ico")) {
      N_TransferFile ($file, $name);
    } else {
      N_Log ("transdeny", $file);
    }
  } else if ($handler=="file2") {

    $supergroupname = $params[1];
    $user_id = $params[2];
    $object_id = $params[3];
    $version = $params[4];

    if (!FILES_Exists ($supergroupname, $object_id)) die ("");

    IMS_SignalDatachange ($supergroupname, $object_id, true); // update metadata in document if specialdocumentdata has changed

    $doc = FILES_TrueFileName ($supergroupname, $object_id);
    SHIELD_SimulateUser ($user_id);
    N_ObjectLog ($supergroupname, "document", $object_id, "read", array (
        "readtype" => "hyperlink",
        "version" => $version,
    ));
    if ($version=="pu") {
      $doc = FILES_TrueFileName ($supergroupname, $object_id, "published");
      $ext = strtolower (FILES_FileType ($supergroupname, $object_id, "published"));
      if ($ext != FILES_FileType($name)) { // This might happen if custom code links to $object["filename"], which is the preview filename.
        $url = "/ufc/" .$handler . "/" . $params[1] . "/" . $params[2] . "/" . $params[3] . "/" . $params[4] . "/" . substr($name, 0, strlen($name) - strlen(FILES_FileType($name))) . $ext;
        N_Redirect($url, 302); // somehow, a normal redirect causes a security warning, but a header redirect does not
      }
      if (UFC_HTTPCaching () && ($ext == "gif" || $ext == "jpg" || $ext == "png" || $ext == "css" || $ext == "js")) { //
        header("Expires: ".gmdate("D, d M Y H:i:s", time()+1*3600) . " GMT");
        $file = N_CleanPath ("html::$supergroupname/objects/$object_id/$doc");
        $e_tag = md5($file);
        $file_mtime = gmdate("D, d M Y H:i:s", @filemtime($file))." GMT"; // LF20101215: suppress warning about "stat failed" for non-yet-published document. (Because a warning will mess up the http headers, so the browser invents its own "Last-Modified" timestamp using the local clock, which leads to eternal caching if the local clock is X seconds ahead of the server server AND the programmer publishes the document X seconds after seeing the warning. This has actually happened.)
        if($_SERVER["HTTP_IF_NONE_MATCH"] == $e_tag || $_SERVER["HTTP_IF_MODIFIED_SINCE"] == $file_mtime)
        {
          header("HTTP/1.1 304 Not Modified");
          header("Status: 304 Not Modified"); // fastcgi
          header("Cache-Control: store, cache");
          header("Pragma: cache");
          header("Last-Modified: ".$file_mtime."");
          header("ETag: ".$e_tag."");
          N_Exit();
          exit;
        } else if($ext=="doc" || $ext=="docx") { //if not already locally, and it is a word file disable caching, for embedded word objects updates
          $file = N_CleanPath ("html::$supergroupname/objects/$object_id/$doc");
          $e_tag = md5($file);
          header("ETag: ".$e_tag."");
          header("Pragma: public");
          header("Expires: 0");
          header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
          header("Cache-Control: public"); 
          $file_mtime = gmdate("D, d M Y H:i:s", @filemtime($file))." GMT"; // LF20101215: suppress warning about "stat failed" for non-yet-published document. (Because a warning will mess up the http headers, so the browser invents its own "Last-Modified" timestamp using the local clock, which leads to eternal caching if the local clock is X seconds ahead of the server server AND the programmer publishes the document X seconds after seeing the warning. This has actually happened.)
          header("Last-Modified: ".$file_mtime."");
        } else {
          header("Cache-Control: store, cache");
          header("Pragma: cache");
          header("Last-Modified: " . $file_mtime);
          header("ETag: ".$e_tag."");
        }
      }
      N_TransferFile ("html::$supergroupname/objects/$object_id/$doc", $name);
    } else if ($version=="pupdf") {
      $doc = FILES_TrueFileName ($supergroupname, $object_id, "published");
      $ext = strtolower (FILES_FileType ($supergroupname, $object_id, "published"));
// (let neeviadocformats handle this) if (($ext=="doc") || ($ext=="xls") || ($ext=="vsd") || ($ext=="ppt") || ($ext=="docx") || ($ext=="xlsx") || ($ext=="vsdx") || ($ext=="pptx") || ($ext=="pdf") || ($ext=="dwg")) {
        uuse ("word");
// Begin PDF conversie instellingen (Neevia) 07-08-2008 JdV
        if ($myconfig["neevia"]=="yes") {
          FLEX_LoadSupportFunctions ($supergroupname);
          if (!function_exists("WORD_NeeviaPdfConversionSettings")) {
            $internal_component = FLEX_LoadImportableComponent ("support", "72123be43e587d10edfbd89319867f62");
            $internal_code = $internal_component["code"];
            eval ($internal_code);
          }
        }
// Einde PDF conversie instellingen (Neevia) 07-08-2008 JdV
        $path = WORD_PDF ("html::$supergroupname/objects/$object_id/$doc");
        // put in in the queue for dcache
        uuse("dcache");
        // DCACHE_putinfilequeue($supergroupname, $path."/doc.pdf");
        DCACHE_putinpdfconversionqueue($supergroupname, $object_id);
        $converted = WORD_GiveConvertedPDF($path);
        N_TransferData ($converted, $name);
//      } else {
//        N_TransferFile ("html::$supergroupname/objects/$object_id/$doc", $name);
//      }

    } else if ($version=="prpdf") {
      $ext = strtolower (FILES_FileType ($supergroupname, $object_id, "preview"));
// (let neeviadocformats       if (($ext=="doc") || ($ext=="xls") || ($ext=="vsd") || ($ext=="ppt") || ($ext=="docx") || ($ext=="xlsx") || ($ext=="vsdx") || ($ext=="pptx") || ($ext=="pdf") || ($ext=="dwg")|| ($ext=="msg")) {
        uuse ("word");
// Begin PDF conversie instellingen (Neevia) 07-08-2008 JdV
        if ($myconfig["neevia"]=="yes") {
          FLEX_LoadSupportFunctions ($supergroupname);
          if (!function_exists("WORD_NeeviaPdfConversionSettings")) {
            $internal_component = FLEX_LoadImportableComponent ("support", "72123be43e587d10edfbd89319867f62");
            $internal_code = $internal_component["code"];
            eval ($internal_code);
          }
        }
// Einde PDF conversie instellingen (Neevia) 07-08-2008 JdV
        $path = WORD_PDF ("html::$supergroupname/preview/objects/$object_id/$doc");
        $converted = WORD_GiveConvertedPDF($path);
        N_TransferData ($converted, $name);
//      } else {
//        N_TransferFile ("html::$supergroupname/preview/objects/$object_id/$doc", $name);
//      }
    } else if ($version=="pr") {
      $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
      $ext = strtolower (FILES_FileType ($supergroupname, $object_id, "preview"));
      if ($ext != FILES_FileType($name)) { // This might happen if custom code links to $object["filename"], which is the preview filename.
        $url = "/ufc/" .$handler . "/" . $params[1] . "/" . $params[2] . "/" . $params[3] . "/" . $params[4] . "/" . substr($name, 0, strlen($name) - strlen(FILES_FileType($name))) . $ext;
        N_Redirect($url, 302); // somehow, a normal redirect causes a security warning, but a header redirect does not
      }
      N_TransferFile ("html::$supergroupname/preview/objects/$object_id/$doc", $name);
    } else if (($version=="prhtml") ||($version=="puhtml")) {
      $ext = strtolower (FILES_FileType ($supergroupname, $object_id, ($version=="puhtml" ? "published" : "preview")));
      switch($ext) {
        case "doc": case "docx":
        break;
        case "vsd": case "vsdx":
        uuse ("visio");
        switch($version) {
        case "prhtml":
          $path = VISIO_HTML ("html::$supergroupname/preview/objects/$object_id/$doc");
          N_TransferFile ($path."/".$name, $name);
          break;
        case "puhtml":
          $path = VISIO_HTML ("html::$supergroupname/objects/$object_id/$doc");
          N_TransferFile ($path."/".$name, $name);
          break;
        }
        break;
      default:
        N_TransferFile ("html::$supergroupname/objects/$object_id/$doc", $name);
      }
    } else {
      $doc = FILES_TrueFileName ($supergroupname, $object_id, $version);
      N_TransferFile ("html::$supergroupname/objects/history/$object_id/$version/$doc", $name);
    }
  } else if ($handler=="file") {
    $supergroupname = $params[1];
    $object_id = $params[2];
    $version = $params[3];

    if (!FILES_Exists ($supergroupname, $object_id)) die ("");

    $doc = FILES_TrueFileName ($supergroupname, $object_id);
    N_ObjectLog ($supergroupname, "document", $object_id, "read", array (
        "readtype" => "hyperlink",
        "version" => $version,
    ));
    if ($version=="pu") {
      $doc = FILES_TrueFileName ($supergroupname, $object_id, "published");
      $ext = strtolower (FILES_FileType ($supergroupname, $object_id, "published"));
      if ($ext != FILES_FileType($name)) { // This might happen if custom code links to $object["filename"], which is the preview filename, not the published filename
        $url = "/ufc/" .$handler . "/" . $params[1] . "/" . $params[2] . "/" . $params[3] . "/" . substr($name, 0, strlen($name) - strlen(FILES_FileType($name))) . $ext;
        N_Redirect($url, 302); // somehow, a normal redirect causes a security warning, but a header redirect does not
      }
      if (UFC_HTTPCaching () && ($ext == "gif" || $ext == "jpg" || $ext == "png" || $ext == "css" || $ext == "js")) {
        header("Expires: ".gmdate("D, d M Y H:i:s", time()+1*3600) . " GMT");
        $file = N_CleanPath ("html::$supergroupname/objects/$object_id/$doc");
        $e_tag = md5($file);
        $file_mtime = gmdate("D, d M Y H:i:s", @filemtime($file))." GMT";
        if($_SERVER["HTTP_IF_NONE_MATCH"] == $e_tag || $_SERVER["HTTP_IF_MODIFIED_SINCE"] == $file_mtime)
        {
          header("HTTP/1.1 304 Not Modified");
          header("Status: 304 Not Modified"); // fastcgi
          header("Cache-Control: store, cache");
          header("Pragma: cache");
          header("Last-Modified: ".$file_mtime."");
          header("ETag: ".$e_tag."");
          N_Exit();
          exit;
        }
        header("Cache-Control: store, cache");
        header("Pragma: cache");
        header("Last-Modified: " . $file_mtime);
        header("ETag: ".$e_tag."");
      }
      N_TransferFile ("html::$supergroupname/objects/$object_id/$doc", $name);
    } else if ($version=="pupdf") {
      $doc = FILES_TrueFileName ($supergroupname, $object_id, "published");
      $ext = strtolower (FILES_FileType ($supergroupname, $object_id, "published"));
// (let neeviadocformats       if (($ext=="doc") || ($ext=="xls") || ($ext=="vsd") || ($ext=="ppt") || ($ext=="docx") || ($ext=="xlsx") || ($ext=="vsdx") || ($ext=="pptx") || ($ext=="pdf") || ($ext=="dwg")) {
        uuse ("word");
        $path = WORD_PDF ("html::$supergroupname/objects/$object_id/$doc");
        // put in in the queue for dcache
        uuse("dcache");
        // DCACHE_putinfilequeue($supergroupname, $path."/doc.pdf");
        DCACHE_putinpdfconversionqueue($supergroupname, $object_id);
        $converted = WORD_GiveConvertedPDF($path);
        N_TransferData ($converted, $name);
//      } else {
//        N_TransferFile ("html::$supergroupname/objects/$object_id/$doc", $name);
//      }
    } else if ($version=="prpdf") {
      $ext = strtolower (FILES_FileType ($supergroupname, $object_id, "preview"));
// (let neeviadocformats       if (($ext=="doc") || ($ext=="xls") || ($ext=="vsd") || ($ext=="ppt") || ($ext=="docx") || ($ext=="xlsx") || ($ext=="vsdx") || ($ext=="pptx") || ($ext=="pdf") || ($ext=="dwg")) {
        uuse ("word");
        $path = WORD_PDF ("html::$supergroupname/preview/objects/$object_id/$doc");
        $converted = WORD_GiveConvertedPDF($path);
        N_TransferData ($converted, $name);
//      } else {
//        N_TransferFile ("html::$supergroupname/preview/objects/$object_id/$doc", $name);
//      }
    } else if ($version=="pr") {
      $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
      $ext = strtolower (FILES_FileType ($supergroupname, $object_id, "preview"));
      if ($ext != FILES_FileType($name)) { // This might happen if custom code links to $object["filename"], which is the preview filename, not the published filename
        $url = "/ufc/" .$handler . "/" . $params[1] . "/" . $params[2] . "/" . $params[3] . "/" . substr($name, 0, strlen($name) - strlen(FILES_FileType($name))) . $ext;
        N_Redirect($url, 302); // somehow, a normal redirect causes a security warning, but a header redirect does not
      }
      N_TransferFile ("html::$supergroupname/preview/objects/$object_id/$doc", $name);
    } else {
      $doc = FILES_TrueFileName ($supergroupname, $object_id, $version);
      N_TransferFile ("html::$supergroupname/objects/history/$object_id/$version/$doc", $name);
    }
  } else if ($handler=="thumb") {
	uuse("thumb") ;
    //1 sgn;2 id; 3 xmax; 4 ymax
    $check = &IMS_AccessObject ($params[1], $params[2]);

    if ($check["published"]=="yes") {
      $doc = FILES_TrueFileName ($params[1], $params[2], "published");
      $doctype = FILES_FileType ($params[1], $params[2], "published");
      $ims_path = "html::".$params[1]."/objects/".$params[2]."/$doc";
    } else {
      $doc = FILES_TrueFileName ($params[1], $params[2], "preview"); 
      $doctype = FILES_FileType ($params[1], $params[2], "preview");
      $ims_path = "html::".$params[1]."/preview/objects/".$params[2]."/$doc";
    }
    $filename = N_CleanPath ($ims_path);
	$thumb_cache_key = DFC_Key("thumbnail", MD5( N_FileMD5($ims_path) . N_FileTime ( $filename ) ) ,$params[3],$params[4]);
    $thumb_cached_location = N_CleanPath(getenv("DOCUMENT_ROOT")."/tmp/thumb")."/".$thumb_cache_key;
    uuse("word");
    $isPDFconvertable = WORD_isConvertableToPDFwithCurrentSettings( $params[1] , $doctype );

    if ( file_exists ($thumb_cached_location) && N_FileSize($thumb_cached_location)>0) {
      // er bestaat al een thumbnail van dit plaatje in dit formaat
      $outputstream = N_ReadFile($thumb_cached_location);
    } elseif (($doctype=="pdf") ||
              ($doctype=="eps") ||
              ($doctype=="tif") ||
              ($doctype=="tiff") || 
              ( $isPDFconvertable ) ) { //Als het een pdf/eps/tif/tiff is   
       if ( $isPDFconvertable and $doctype != "pdf") { // ADDED JG

//        $pdffile = N_GetPage ("http://localhost/ufc/file2/".$params[1]."//".$params[2]."/prpdf/file.pdf"); // ADDED JG
        uuse("word");
// Begin PDF conversie instellingen (Neevia) 07-08-2008 JdV
        if ($myconfig["neevia"]=="yes") {
          FLEX_LoadSupportFunctions ($supergroupname);
          if (!function_exists("WORD_NeeviaPdfConversionSettings")) {
            $internal_component = FLEX_LoadImportableComponent ("support", "72123be43e587d10edfbd89319867f62");
            $internal_code = $internal_component["code"];
            eval ($internal_code);
          }
        }
// Einde PDF conversie instellingen (Neevia) 07-08-2008 JdV

        $path = WORD_PDF ( $filename );
        $converted = WORD_GiveConvertedPDF($path);
        $tmp_pdf = TMP_Directory() . '/file.pdf';
        
		N_WriteFile ( $tmp_pdf , $converted ); // ADDED JG

        $filename = N_CleanPath( $tmp_pdf ); // ADDED JG
		       } // ADDED JG
      $binary_object_data = THUMB_pdfToThumbnail($filename, $params[4], $params[3]);
      if($binary_object_data)
      {
        N_WriteFile($thumb_cached_location,$binary_object_data) ;
        $outputstream = $binary_object_data;
      } else { //foutieve generatie, bijvoorbeeld omdat convert stuk is
        $pixel = imagecreatetruecolor(1,400);
        $white = imagecolorallocate($pixel, 255, 255, 255);
        imagefill($pixel, 0, 0, $white);
        header("Content-type: image/jpeg");
        imagejpeg($pixel);
        die();
      }      
    } else { //als het geen pdf/eps/tif/tiff is
      // hier komt de logica om het plaatje te bouwen, en op disk te schrijven
      if(!THUMB_imagesSupported()) N_DIE ("GD needed but not available");
      if(!($image = THUMB_loadImage($filename))) N_DIE ("Failed to load image: $filename");
      if(!($thumbnail = THUMB_toThumbnail($image, $params[4], $params[3]))) N_DIE ("Failed to create thumbnail: $filename");
      uuse("tmp") ;
      $bounce = N_CleanPath (TMP_DIR()."/".N_GUID().".dat");
      // imagejpeg requires a path to write jpeg data
      if (!imagejpeg($thumbnail, $bounce)) N_DIE ("Failed to save thumbnail: $filename");
      $binary_object_data = N_ReadFile($bounce) ;
      if (!$binary_object_data) N_DIE ("Failed to load thumbnail: $filename");
      imageDestroy($image);
      imageDestroy($thumbnail);
      N_WriteFile($thumb_cached_location,$binary_object_data) ;
      $outputstream = $binary_object_data;
    }
    if ($outputstream) {
        header("Content-type: image/jpeg");
        echo $outputstream ;
    }
  } else if ($handler=="doc2url") {
    uuse ("lib");
    LIB_Doc2URLHandler ($name, $params);
  } else if ($handler=="bpmsurl") {

    // usage: /ufc/bpmsurl/<<supergroupname>>/<<objectid>>
    $supergroupname = $params[1];
    $case_id = $name;

    $processes = MB_Query ("shield_".$supergroupname."_processes");
    foreach ($processes as $process_id) {
      $mcase = MB_Ref ("process_".$supergroupname."_cases_".$process_id, $case_id);
      if ($mcase) {
        $case = $mcase;
        $myprocess = $process_id;
      }
    }
    $url = "/openims/openims.php?mode=bpms&submode=inprocess&theprocess=$myprocess&thestage=".$case["stage"]."&thecase=$case_id";
    N_Redirect ($url);

  } else if ($handler=="url") {
    // usage: /ufc/url/<<supergroupname>>/<<objectid>>/<<filename>>
    $supergroupname = $params[1];
    $key = $params[2];

    $object = &IMS_AccessObject ($supergroupname, $key);
    if($object) {
      if ($object["objecttype"]=="document") {

        $submode = "documents";
        $thecase = "";
        $rootfolder ="";
        $currentfolder =$object["directory"];
        $act = "";
        $wfl ="";
        $activeuser ="";
        $commandurl = "/openims/openims.php?mode=dms&submode=$submode&thecase=$thecase&rootfolder=$rootfolder&" .
        "currentfolder=$currentfolder&currentobject=$key&act=$act&wfl=".urlencode($wfl)."&activeuser=".urlencode($activeuser);
        N_Redirect($commandurl); /* Redirect browser */
      }
    }
  }
}

?>