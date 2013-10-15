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



uuse ("shield");

function TREE_Create()
{
  $tree["tree_version"] = "1.0";
  return $tree;
}

function TREE_AddObject(&$tree, $id="")
{
  if (!$id) $id = N_GUID();
  $tree["objects"][$id]["x"] = "x";
  return $id;
}

function TREE_DeleteObject(&$tree, $id)
{
  if ($parent = $tree["objects"][$id]["parent"]) {
    unset ($tree["objects"][$parent]["children"][$id]);
  }
  unset ($tree["objects"][$id]);
}

function &TREE_AccessObject(&$tree, $id)
{
  return $tree["objects"][$id];
}

function TREE_AllObjects (&$tree)
{
  $res = array();
  $list = $tree["objects"];
  if (is_array($list)) {
    reset($list);
    while (list ($key)=each($list)) {
      $res[$key]=$key;
    }
  }
  return $res;
}

function TREE_AllRootObjects (&$tree)
{
  $res = array();
  $list = $tree["objects"];
  if (is_array($list)) {
    reset($list);
    while (list ($key)=each($list)) {
      if (!$tree["objects"][$key]["parent"]) $res[$key]=$key;
    }
  }
  return $res;
}

function TREE_ConnectObject (&$tree, $parent, $child)
{
  if ($parent && $child) {
    if ($tree["objects"][$child]["parent"]) {
      unset ($tree["objects"][ $tree["objects"][$child]["parent"] ]["children"][$child]);
    }
    $tree["objects"][$child]["parent"] = $parent;
    $tree["objects"][$parent]["children"][$child] = $child;
  }
}

function TREE_MoveObjectDown (&$tree, $object_id)
{
  $parent_id = $tree["objects"][$object_id]["parent"];
  if (!$parent_id) return;
  $children = $tree["objects"][$parent_id]["children"];
  $newchildren = array();
  reset ($children);
  while (list($id)=each($children)) {
    if ($id==$object_id) {
      $skip=true;
    } else {
      $newchildren[$id] = N_GUID();
      if ($skip) {
        $skip = false;
        $newchildren[$object_id] = N_GUID();
      }
    }
  }
  if ($skip) $newchildren[$object_id] = N_GUID();
  $tree["objects"][$parent_id]["children"] = $newchildren;  
}

function nbspForTree()
{ // returns a "&nbsp;" if browser is IE (for treecontrol)
  if (N_IE()) $ret = "&nbsp;";
  if (N_Mozilla()) $ret = "";
  if (N_Opera()) $ret = "&nbsp;";
  return $ret;
}

function TREE_MoveObjectUp (&$tree, $object_id)
{
  $parent_id = $tree["objects"][$object_id]["parent"];
  if (!$parent_id) return;
  $children = $tree["objects"][$parent_id]["children"];
  $newchildren = array();
  reset ($children);
  $prev = "top";
  while (list($id)=each($children)) {
    if ($id == $object_id) $above = $prev;
    $prev = $id;
  }
  reset ($children);
  if ($above=="top") $newchildren[$object_id] = N_GUID();
  while (list($id)=each($children)) {
    if ($id==$above) $newchildren[$object_id] = N_GUID();
    if ($id!=$object_id) $newchildren[$id] = N_GUID();
  }
  $tree["objects"][$parent_id]["children"] = $newchildren;  
}

function TREE_SubTree (&$tree, $list, $root, $active, $action, $openchilds, $dynamic, $ignorewhenprefix = "")
{
  // LF 20071120: added new feature "foldercolors" + some refactoring
  // BCB 20080114: removed &{head}, changed ids into style classes, localized UL-decision
  // LF 20080205: added support for "half-dynamic" trees: the "open" part of the tree (active node and
  //              all its ancestors) is collapsible, the rest of the tree is static.  Html-structure is 
  //              the same as dynamic tree (<ul> and <li>, not &nbsp and <br>).
  if ($dynamic && $dynamic != "half") $dynamic = "full";

  global $treelevel;
  
  $treelevel++;
  $source_indent = str_repeat(' ',$treelevel);
  $indent = str_repeat('<span style="padding-left: 16px;"></span>', $treelevel-1);
  $res = "";
  reset($list);
  while (list ($object_id)=each($list)) {
    $id = $object_id; // qqq
    eval ('$url = "'.$action.'";');
    $object = TREE_AccessObject ($tree, $object_id);    

    if ($object["x"]=="x" and TREE_Visible($object_id, $object, $ignorewhenprefix, $tree)) {

      $open = false;
      $walk = $active;
      while ($walk) {
        if ($openchilds || $walk!=$active)
          if ($walk==$object_id) 
            $open = true;
        $walk = $tree["objects"][$walk]["parent"];
      }
      if ($dynamic == "full" || ($dynamic == "half" && $open)) {
        if ($open) {
          $style = "fh_minus";
          $wraptag = '<ul class="foldinglist">';
        } else {
          $style = "fh_plus";
          $wraptag = '<ul class="foldinglist" style="display:none">';
        }
        $wraptag .= "\r\n";
        $wrapend = "</ul>";
      } elseif ($dynamic == "half") { // not open
        $style = "fh_plusstatic";
        $wraptag = "";
        $wrapend = "";
      } else {
        $style = "<img border=0 width=16 height=16 src=\"/ufc/rapid/openims/plus.gif\"><img border=0 width=4 height=16 src=\"/ufc/rapid/openims/blank_small.gif\">";
        // no minus, it's not collapsible anyway
        $wraptag = "";
        $wrapend = "";
      }
      $class = "ims_navigation";
      if ($object_id==$active) $class = "ims_active";
      $foldericon = "/ufc/rapid/openims/openfolder.gif";
      if ($object["hassecuritysection"]=="yes") $foldericon = "/ufc/rapid/openims/openfolder_secure.gif";
      // Foldercolors: Each folder can have its own color.
      $foldercolorstyle = ""; 
      global $myconfig;
      if ($myconfig[IMS_SuperGroupName()]["foldercolors"]) {
        if ($object["foldercolor"]) {
          $foldercolorstyle = 'style="color: '.$object["foldercolor"].'"';
        } else { 
          // make sure folder remains black, even when active or on mouseover
          $foldercolorstyle = 'style="color: black"';
        }
      }                             // for +/- sign
      if ($object["children"] and TREE_ChildrenVisible($tree, $object, $ignorewhenprefix)) {
       global $myconfig;
       if ($myconfig[IMS_Supergroupname()]["nobr"]=="yes") 
       {
        if ($dynamic) {
          $res .= $source_indent.'<li class="'.$style.'">'.nbspForTree().'<a title="'.$object["longtitle"].'" class="'.$class.'" '.$foldercolorstyle.' href="'.$url.'"><img id="folder_'.$id.'" width=16 height=16 border=0 src="'.$foldericon.'">&nbsp;'.$object["shorttitle"].'</a></li>';
        } else {
          $res .= $indent.'<a class="'.$class.'" title="'.$object["longtitle"].'" '.$foldercolorstyle.' href="'.$url.'">'.$style.'<img id="folder_'.$id.'" width=16 height=16 border=0 src="'.$foldericon.'">&nbsp;'.$object["shorttitle"].'</a><br>';
        }
       }
       else
       {
        if ($dynamic) {
          $res .= $source_indent.'<li class="'.$style.'">'.nbspForTree().'<nobr><a title="'.$object["longtitle"].'" class="'.$class.'" '.$foldercolorstyle.' href="'.$url.'"><img id="folder_'.$id.'" width=16 height=16 border=0 src="'.$foldericon.'">&nbsp;'.$object["shorttitle"].'</a></nobr></li>';
        } else {
          $res .= '<nobr>'.$indent.'<a class="'.$class.'" title="'.$object["longtitle"].'" '.$foldercolorstyle.' href="'.$url.'">'.$style.'<img id="folder_'.$id.'" width=16 height=16 border=0 src="'.$foldericon.'">&nbsp;<nobr>'.$object["shorttitle"].'</nobr></a></nobr><br>';
        }
       }
        $res .= "\r\n";
        $res .= DHTML_AddDropTarget ("folder_".$id);
        if ($open || ($dynamic == "full")) { // half-dynamic tree only loads visible (open) children, not invisible children
          $subtree = TREE_SubTree($tree, $object["children"], false, $active, $action, $openchilds, $dynamic, $ignorewhenprefix);
          if ($subtree) {
            $res .= $source_indent.$wraptag . $subtree . $source_indent.$wrapend."\r\n";
          }
        }
      } else { // no children
       if ($myconfig[IMS_SupergroupName()]["nobr"]=="yes") 
       {
        if ($dynamic) {
          $res .= $source_indent.'<li class="fh_blank">'.nbspForTree().'<a title="'.$object["longtitle"].'" class="'.$class.'" '.$foldercolorstyle.' href="'.$url.'"><img id="folder_'.$id.'" width=16 height=16 border=0 src="'.$foldericon.'">&nbsp;'.$object["shorttitle"].'</a></li>';
        } else {
          $res .= $indent.'<a class="'.$class.'" title="'.$object["longtitle"].'" '.$foldercolorstyle.' href="'.$url.'"><img border=0 width=20 height=16 src="/ufc/rapid/openims/blank_small.gif"><img id="folder_'.$id.'" width=16 height=16 border=0 src="'.$foldericon.'">&nbsp;'.$object["shorttitle"].'</a><br>';
        }
       }
       else
       {
        if ($dynamic) {
          $res .= $source_indent.'<li class="fh_blank">'.nbspForTree().'<nobr><a title="'.$object["longtitle"].'" class="'.$class.'" '.$foldercolorstyle.' href="'.$url.'"><img id="folder_'.$id.'" width=16 height=16 border=0 src="'.$foldericon.'">&nbsp;'.$object["shorttitle"].'</a></nobr></li>';
        } else {
          $res .= '<nobr>'.$indent.'<a class="'.$class.'" title="'.$object["longtitle"].'" '.$foldercolorstyle.' href="'.$url.'"><img border=0 width=20 height=16 src="/ufc/rapid/openims/blank_small.gif"><img id="folder_'.$id.'" width=16 height=16 border=0 src="'.$foldericon.'">&nbsp;'.$object["shorttitle"].'</a></nobr><br>';
        }
       }
         
       $res .= "\r\n";

/* BCB has become unnecessary
        if ($dynamic) {
          if ($open) {
            $res .= '<ul class="foldinglist">';
          } else {
            $res .= '<ul class="foldinglist" style="display:none">';
          }
          $res .= "\r\n";
        }
*/
      }
      $res .= DHTML_AddDropTarget ("folder_".$id);
    } // if ($object["x"]=="x")
  }
  if ($root && $dynamic) {
    if ($res) $res = '<ul class="foldinglist">'."\r\n" . $res . "\r\n</ul>\r\n";
  }
  $treelevel--;
  return $res;
}

function AddEndOfUL($strin) //thb
// BCB not called anymore
{  //smart add "</ul>". If previous line is a "<ul>" then remove "<ul>" and don't add "</ul>"
  $lines = explode("\n", $strin);
  $lastline = $lines[ count($lines)-2 ];

  if( !strpos($lastline, "<ul")) {
    $ret = $strin . "</ul>\n"; // no "<ul..", so add "</ul>"
  } else {
    // "<ul..." found, so remove that line
    $lines[ count($lines)-2 ] = "";
    $ret = implode ("\n", $lines );
  }
  return $ret;
}

function TREE_CreateDHTML (&$tree, $action, $active, $openchilds=false, $alternateroot="", $ignorewhenprefix="")
{

  $limit = 500;
  global $myconfig;
  if ($myconfig[IMS_Supergroupname()]["folderblock"]["limit"].""!="") {
    $limit = $myconfig[IMS_Supergroupname()]["folderblock"]["limit"];
  }

  //20130115 KvD CORE-50 allow onclick on webkit based browsers
  $wbk = (strpos(strtolower($_SERVER["HTTP_USER_AGENT"]),"webkit")!==false);

  
  if (count ($tree["objects"]) > $limit) {
    if (N_IE() || N_Mozilla()) { 
      $dynamic = "half";
    } else {
      $dynamic = false;
    }
  } else {
  if (N_IE() || N_Mozilla() || $wbk) {  // CORE-50: webkit as well
      $dynamic = "full";
    } else {
      $dynamic = false;
    }
  }

    uuse ("skins");
    $res .= SKIN_CSS();
    // BCB 20080111 shortened & separated style and script; applied classes; modernized javascript
    $res .= '
<STYLE type="text/css">
.fh_plus  { cursor:pointer; cursor:hand; list-style-image:url(/ufc/rapid/openims/plus.gif); padding: 0px; }
.fh_plusstatic { cursor:pointer; cursor:hand; list-style-image:url(/ufc/rapid/openims/plus.gif); padding: 0px; }
.fh_blank { cursor:pointer; cursor:hand; list-style-image:url(/ufc/rapid/openims/blank_small.gif); padding: 0px; }
.fh_minus { cursor:pointer; cursor:hand; list-style-image:url(/ufc/rapid/openims/minus.gif); padding: 0px; }
.foldinglist { margin-left:16px; padding: 0 }
';
    // Firefox enforces a 5px distance between the list image and the list item. Both the list image (plus/minus)
    // and the folder icon (part of the content of the list item) are 16 px. In IE they line up perfectly, in 
    // Firefox, the list image is too far to the left. Workaround:
    // 1. Move the folder icon and text in the list item 5 px to the left. Dont use * because this can match 
    //    the same element multiple times and move it too much. Use "a" because both folder icon and text are a's.
    // 2. The list images and folder icons line up OK now, but the entire list is 5 px to the left compared with IE.
    //    Use padding to move it to the right.
    // 3. But only do that for the "outer" list, not for all the nested list inside it.
    // Now that IE and Firefox have the same distance between list image and list item, we can increase the
    // distance for both using a padding-left on the "li".
    if (N_Mozilla()) {
      $res .= '
.foldinglist a { position: relative; left: -5px; }
.foldinglist { padding-left: 5px; }
.foldinglist .foldinglist { padding-left: 0; }
';
    }
    $res .='
</style>
    ';
  if ($dynamic) {
    $res .= '
<script type="text/javascript" language="JavaScript1.2">
<!--
  img1=new Image();
  img1.src="/ufc/rapid/openims/plus.gif";
  img2=new Image();
  img2.src="/ufc/rapid/openims/minus.gif";
  img3=new Image();
  img3.src="/ufc/rapid/openims/blank_small.gif";
  img4=new Image();
  img4.src="/ufc/rapid/openims/folder.gif";
  img5=new Image();
  img5.src="/ufc/rapid/openims/openfolder.gif";
  function checkcontained(e) {
    var foldercontent;
    if (!e) var e = window.event;
    var cur = e.target || e.srcElement;
    i=0;
    var iscontained = (cur.className=="fh_plus" || cur.className=="fh_minus");
    if (iscontained) {
      if (cur.getElementsByTagName) {
        foldercontent = cur.getElementsByTagName("UL")[0];
      }
      if (!foldercontent) {
        foldercontent = cur;
        while (foldercontent = foldercontent.nextSibling) if (foldercontent.nodeName=="UL") break;
      }
      if (!foldercontent) foldercontent = cur.parentNode.nextSibling;
      if (foldercontent.style.display=="none") {
        foldercontent.style.display="";
        cur.style.listStyleImage="url(/ufc/rapid/openims/minus.gif)";
      } else {
        foldercontent.style.display="none";
        cur.style.listStyleImage="url(/ufc/rapid/openims/plus.gif)";
      }
    }
    if (cur.className=="fh_plusstatic") {
      // make the plus-image clickable (go to the same location as the folderimage+text behind it)
      if (cur.getElementsByTagName("A")) {
        var url = cur.getElementsByTagName("A")[0].href;
        window.location = url;
      }
    }
  }
  var ns6 = document.getElementById && !document.all;
  var ie4 = document.all && navigator.userAgent.indexOf("Opera")==-1;
//20130115 KvD CORE-50 allow onclick on webkit based browsers
  var wbk = navigator.userAgent.toLowerCase().indexOf("webkit")!=-1;

  if (ie4||ns6||wbk)
    document.onclick = checkcontained;
//-->
</script>
';
  }

  $list = TREE_AllRootObjects ($tree);
  if ($alternateroot) {
    $list = array();
    $list[$alternateroot] = $alternateroot;
  }
  $res .= TREE_SubTree ($tree, $list, true, $active, $action, $openchilds, $dynamic, $ignorewhenprefix);
  uuse("forms");
  $res = FORMS_ML_Filter($res);

  return $res;
}

function TREE_TERRA_WalkDirectory (&$tree, $folder_id, $foldercode, $filecode)
{
  uuse ("terra");
  global $tree_terra_list;
  global $tree_terra_counter;
  $tree_terra_list = array();
  TREE_WalkDirectory ($tree, $folder_id, '
    global $tree_terra_list;
    array_push ($tree_terra_list, array("folder_id"=>$folder_id));
  ', ($filecode ? '
    global $tree_terra_list;
    global $tree_terra_counter;
    array_push ($tree_terra_list, array("file_id"=>$file_id));
    $tree_terra_counter++;
  ' : ""), // LF: if $filecode is empty, then give TREE_WalkDirectory an empty $filecode as well, 
           //     to prevent unnecessary query and object loading
    true); // tell TREE_WalkDirectory not to load the objects, just give us the keys
  $specs["title"] = "BULK";
  $specs["list"] = $tree_terra_list;
  $specs["input"]["filecode"] = $filecode;
  $specs["input"]["foldercode"] = $foldercode;
  $specs["step_code"] = '
    if ($value["folder_id"]) {
      $folder_id = $value["folder_id"];
      if ($input["foldercode"]) eval ($input["foldercode"]);    
    } else {
      $file_id = $value["file_id"];
      $file = &IMS_AccessObject (IMS_SuperGroupName(), $file_id);
      if ($input["filecode"]) eval ($input["filecode"]);    
    }
  ';
  TERRA_Multi ($specs);
  if (!$tree_terra_counter) $tree_terra_counter = 0;
  return $tree_terra_counter;
} // qqq

function TREE_WalkDirectory (&$tree, $folder_id, $foldercode, $filecode, $onlythekeys = false)
{
  $folder = &TREE_AccessObject($tree, $folder_id);
  if ($filecode) { // LF: skip query if $filecode is empty for better performance
    $files = MB_TurboSelectQuery ("ims_".IMS_SuperGroupName()."_objects", array(
      '$record["directory"]' => $folder_id,
      '$record["published"]=="yes" || $record["preview"]=="yes"' => true
    ), '$record["shorttitle"]');
    if (is_array($files)) foreach ($files as $file_id => $dummy) {
      if (!$onlythekeys) $file = &IMS_AccessObject (IMS_SuperGroupName(), $file_id);
      if ($filecode) eval ($filecode);    
    }
  }
  $children = $folder["children"];  
  if (is_array($children)) foreach ($children as $child => $dummy) {
    TREE_WalkDirectory ($tree, $child, $foldercode, $filecode, $onlythekeys);
  }
  if ($foldercode) eval ($foldercode);
}

function TREE_WalkDirectoryAndCopy($sgn, $srctree, &$desttree, $srcfolder_id, $destfolder_id, $foldercode = "", $filecode = "", $guidcode = "", $destfolderorig_id = "", $onlythekeys = false) {
  // Recursively walk a (part of a) tree and simultaneously create a copy of the tree structure.
  // $scrtree: sourcetree (created with CASE_TreeRef oid)
  // $desttree: sourcetree (created with &CASE_TreeRef oid)
  // $srcfolder_id: id of source folder
  // $destfolder_id: id of destination folder (WILL BE OVERWRITTEN)
  // $foldercode: code to execute for each folder. available: $sgn, $srcfolder_id, $destfolder_id
  // $filecode: code to execute for each document. available: $sgn, $file_id, $file, $destfolder_id
  // $guidcode: code that generates folder_id's for the new folders. default: N_Guid(). available: $childid
  // $destfolderorig_id: DO NOT USE this parameter (leave blank, use empty string); it is used internally to prevents loops.
  // $onlythekeys: set to true if you do not want use to load the object before eval'ing filecode

  if (!$destfolderorig_id) $destfolderorig_id = $destfolder_id;

  $srcfolder = &TREE_AccessObject($srctree, $srcfolder_id);
  $destfolder = &TREE_AccessObject($desttree, $destfolder_id);

  // Copy all properties except x, children, and parent.
  // Beware: This copies the fact that a folder has local security (tree manipulation), 
  // but not the security connections (groups, users) themselves; you should do this in $foldercode.
  foreach ($srcfolder as $property => $value) {
    if (!($property == "children" || $property == "parent" || $property == "x" || ($property == "hassecuritysection" && $value != "yes"))) {
      $destfolder[$property] = $srcfolder[$property];
    }
  }
  
  if ($filecode) {
    $files = MB_TurboSelectQuery ("ims_".$sgn."_objects", array(
      '$record["directory"]' => $srcfolder_id,
      '$record["published"]=="yes" || $record["preview"]=="yes"' => true
    ), '$record["shorttitle"]');
    if (is_array($files)) foreach ($files as $file_id => $dummy) {
      if (!$onlythekeys) $file = &IMS_AccessObject ($sgn, $file_id);
      if ($filecode) eval ($filecode);
    }
  }
  
  $children = $srcfolder["children"];
  if (is_array($children)) foreach ($children as $childid => $dummy) {
    if ($childid == $destfolderorig_id) continue; // prevent cycles in tree
    if ($guidcode) {
      eval('$guid = '.$guidcode.';');
    } else {
      $guid = N_GUID();
    }
    $id = TREE_AddObject ($desttree, substr($destfolder_id, 0, strpos ($destfolder_id, ")")+1).$guid);
    TREE_ConnectObject ($desttree, $destfolder_id, $id);
    TREE_WalkDirectoryAndCopy($sgn, $srctree, $desttree, $childid, $id, $foldercode, $filecode, $guidcode, $destfolderorig_id, $onlythekeys);
  }
  if ($foldercode) eval ($foldercode);  
}

function TREE_TERRA_WalkDirectoryAndCopy ($sgn, &$srctree, &$desttree, $srcfolder_id, $destfolder_id, $foldercode = "", $filecode = "", $guidcode = "") {
  // Same as TREE_WalkDirectoryAndCopy, but $filecode en $foldercode are executed in the background.
  uuse ("terra");
  global $tree_terra_list;
  global $tree_terra_counter;
  $tree_terra_list = array();
  TREE_WalkDirectoryAndCopy ($sgn, $srctree, $desttree, $srcfolder_id, $destfolder_id, '
    global $tree_terra_list;
    array_push ($tree_terra_list, array("srcfolder_id"=>$srcfolder_id,
           "destfolder_id"=>$destfolder_id));
  ', ($filecode ? '
    global $tree_terra_list;
    global $tree_terra_counter;
    array_push ($tree_terra_list, array("file_id"=>$file_id, 
           "destfolder_id"=>$destfolder_id));
    $tree_terra_counter++;
  ' : ""),
    $guidcode,
    "",
    true); // only the keys
  $specs["title"] = "BULK";
  $specs["list"] = $tree_terra_list;
  $specs["input"]["filecode"] = $filecode;
  $specs["input"]["foldercode"] = $foldercode;
  $specs["input"]["sgn"] = $sgn;
  $specs["step_code"] = '
    if ($value["srcfolder_id"]) {
      $sgn = $input["sgn"];
      $srcfolder_id = $value["srcfolder_id"];
      $destfolder_id = $value["destfolder_id"];
      if ($input["foldercode"]) eval ($input["foldercode"]);    
    } else {
      $sgn = $input["sgn"];
      $file_id = $value["file_id"];
      $destfolder_id = $value["destfolder_id"];
      $file = &IMS_AccessObject ($sgn, $file_id);
      if ($input["filecode"]) eval ($input["filecode"]);    
    }
  ';
  TERRA_Multi ($specs);
  return $tree_terra_counter;
} 

function TREE_CopyDirectoryTree($sgn, $from, $to, $under = true, $shortcutmode = "copyshortcuts", $background = true, $copyinheritedsecurity = false) {
  // Copies a DMS folderstructure from directory $from to directory $to.
  // Local security will be copied.
  // All documents in the folderstructure will be copied.

  // $from: id of source folder
  // $to: id of destination folder

  // under:
  //   true -> create a new directory under $to and copy to this new directory
  //   false -> copy to $to (overwrite shorttitle/longtitle/security etc.)

  // shortcutmode: what to do when encountering a shortcut in the source treestructure
  //   copyconnectedfiles -> copy the file that links to
  //   copyshortcuts -> create a new shortcut (so the file will now have several shortcuts to it)
  //   skipshortcuts -> don't do anything with shortcuts
  
  // background:
  //  If set to a true value, the function returns as soon as the $desttree has been edited.  Copying the
  //  local security sections and copying files is done in a background process.  The number of files
  //  to be copied is returned.

  // copyinheritedsecurity:
  //  If set to true, then if $from has inherited local security from a parent/ancestor folder,
  //  this security is copied to $to. You probably do not want to use this for templates.
  //  NOTE: This does not work for foldertree security, only for security connections for users and security connections for groups.
 
  $srctree = CASE_TreeRef($sgn, $from);
  $desttree = &CASE_TreeRef($sgn, $to);

  if ($under) {
    if (substr ($to, 0, 1) == "(") {
      if ($guidcode) {
        eval('$guid = '.$guidcode.';');
      } else {
        $guid = N_GUID();
      }
      $newid = TREE_AddObject ($desttree, substr ($to, 0, strpos ($to, ")")+1).$guid);
    } else {
      $newid = TREE_AddObject ($desttree);
    }
    TREE_ConnectObject ($desttree, $to, $newid);
    $to = $newid;
  }
  
  $foldercode = '
    $foldercode_srctree = CASE_TreeRef($sgn, $srcfolder_id);
    $foldercode_srcfolder = TREE_AccessObject($foldercode_srctree, $srcfolder_id);
    if ($foldercode_srcfolder["hassecuritysection"] == "yes") {
      // Copy security connections for users
      $persons = "shield_" . $sgn . "_users";
      $specs["slowselect"][\'!$record["groups_secsec"]["\' . $srcfolder_id . \'"]\'] = false;
      $l = MB_TurboMultiQuery($persons, $specs);
      foreach($l as $user) {
        $o = &MB_Ref($persons, $user);
        $o["groups_secsec"][$destfolder_id] = $o["groups_secsec"][$srcfolder_id];
      }

      // Copy security connections for groups
      $secconnectionstable = "shield_" . $sgn . "_localsecurity_connections";
      $secconnectionsfrom = MB_Load($secconnectionstable, $srcfolder_id);
      if($secconnectionsfrom) {
        $secconnectionsto = MB_Load($secconnectionstable, $destfolder_id);
        foreach($secconnectionsfrom as $groupfrom=>$groupsto) {
          $secconnectionsto[$groupfrom] = $groupsto;
        }
        MB_Save($secconnectionstable, $destfolder_id, $secconnectionsto);
      }

      // Copy foldertree security
      $foldersecfrom = MB_Load("shield_".$sgn."_customfolderview", $srcfolder_id);
      SHIELD_ConnectFolderView ($sgn, $destfolder_id, $foldersecfrom);
    }
  ';
  $filecode = '
    uuse("ims");
    uuse("search");
    uuse("files");

    if (FILES_IsShortcut($sgn, $file_id)) {
      if ("'.$shortcutmode.'" == "copyconnectedfiles") $file_id = FILES_Base ($sgn, $file_id);
    }

    if (FILES_IsShortcut ($sgn, $file_id)) {
      if ("'.$shortcutmode.'" != "skipshortcuts") {
        IMS_NewDocumentShortcut ($sgn, $destfolder_id, FILES_Base ($sgn, $file_id));
      }
    } else {
      $sourceobject = &MB_Ref ("ims_".$sgn."_objects", $file_id);
      $newdocid = IMS_NewDocumentObject ($sgn, $destfolder_id);
      $object = &IMS_AccessObject ($sgn, $newdocid);
      N_CopyDir ("html::".$sgn."/preview/objects/".$newdocid."/", "html::".$sgn."/preview/objects/".$file_id."/");
      $object["shorttitle"] = $sourceobject["shorttitle"];
      $object["longtitle"] = $sourceobject["longtitle"];
      $object["workflow"] = $sourceobject["workflow"];
      $object["executable"] = $sourceobject["executable"];
      $object["filename"] = $sourceobject["filename"];
      foreach ($sourceobject as $file_id => $value) {
        if (strpos (" ".$file_id, "meta_")) {
          $object[$file_id] = $sourceobject[$file_id];
        }
      }
      $object["directory"] = $destfolder_id;
      IMS_ArchiveObject ($sgn, $newdocid, "'.SHIELD_CurrentUser().'", true);
      SEARCH_AddPreviewDocumentToDMSIndex ($sgn, $newdocid);
    }
    global $copytree_counter;
    $copytree_counter++;
    if ($copytree_counter >= 100) { MB_Flush(); $copytree_counter = 0; }
  ';

  if ($copyinheritedsecurity) {
    $sectionfrom = SHIELD_SecuritySectionForFolder($sgn, $from);
    if ($sectionfrom && ($sectionfrom != $from) && ($sectionfrom != SHIELD_SecuritySectionForFolder($sgn, $to))) {
      $tofolder = &TREE_AccessObject($desttree, $to);
      SHIELD_AddSecurityToFolder ($sgn, $to);
      // Copy security connections for users
      $persons = "shield_" . $sgn . "_users";
      $specs["slowselect"]['!$record["groups_secsec"]["' . $sectionfrom . '"]'] = false;
      $l = MB_TurboMultiQuery($persons, $specs);
      foreach($l as $user) {
        $o = &MB_Ref($persons, $user);
        $o["groups_secsec"][$to] = $o["groups_secsec"][$sectionfrom];
      }
      // Copy security connections for groups
      $secconnectionstable = "shield_" . $sgn . "_localsecurity_connections";
      $secconnectionsfrom = &MB_Ref($secconnectionstable, $sectionfrom);
      if($secconnectionsfrom) {
        $secconnectionsto = &MB_Ref($secconnectionstable, $to);
        foreach($secconnectionsfrom as $groupfrom=>$groupsto) {
          $secconnectionsto[$groupfrom] = $groupsto;
        }
      }
    }
  }
  
  if ($background) {
    $count = TREE_TERRA_WalkDirectoryAndCopy($sgn, $srctree, $desttree, $from, $to, $foldercode, $filecode);
  } else {
    $count = TREE_WalkDirectoryAndCopy($sgn, $srctree, $desttree, $from, $to, $foldercode, $filecode);
  }

  return $count;
}

function TREE_Path (&$tree, $folder_id) {
  $folder = &TREE_AccessObject($tree, $folder_id);
  if ($folder["parent"]) {
    $path = TREE_Path ($tree, $folder["parent"]);
    $path[count($path)+1]["id"] = $folder_id;
    $path[count($path)]["shorttitle"] = $folder ["shorttitle"];
    $path[count($path)]["longtitle"] = $folder ["longtitle"];
  } else {
    $path[1]["id"] = $folder_id;
    $path[1]["shorttitle"] = $folder ["shorttitle"];
    $path[1]["longtitle"] = $folder ["longtitle"];
  }  
  return $path;
}

function TREE_Test()
{
  $tree = TREE_Create();

  $obj1_id = TREE_AddObject ($tree, "1");
  $obj1 = &TREE_AccessObject ($tree, $obj1_id);  
  $obj1["shorttitle"]="Obj1";
  $obj1["longtitle"]="This is object 1";





  $obj2_id = TREE_AddObject ($tree, "2");
  $obj2 = &TREE_AccessObject ($tree, $obj2_id);  
  $obj2["shorttitle"]="Obj2";
  $obj2["longtitle"]="This is object 2";

  $obj3_id = TREE_AddObject ($tree, "3");
  $obj3 = &TREE_AccessObject ($tree, $obj3_id);  
  $obj3["shorttitle"]="Obj3";
  $obj3["longtitle"]="This is object 3";

  $obj11_id = TREE_AddObject ($tree, "11");
  $obj11 = &TREE_AccessObject ($tree, $obj11_id);  
  $obj11["shorttitle"]="Obj11";
  $obj11["longtitle"]="This is object 11";

  $obj111_id = TREE_AddObject ($tree, "111");
  $obj111 = &TREE_AccessObject ($tree, $obj111_id);  
  $obj111["shorttitle"]="Obj111";
  $obj111["longtitle"]="This is object 111";

  $obj112_id = TREE_AddObject ($tree, "112");
  $obj112 = &TREE_AccessObject ($tree, $obj112_id);  
  $obj112["shorttitle"]="Obj112";
  $obj112["longtitle"]="This is object 112";

  $obj1121_id = TREE_AddObject ($tree, "1121");
  $obj1121 = &TREE_AccessObject ($tree, $obj1121_id);  
  $obj1121["shorttitle"]="Obj1121";
  $obj1121["longtitle"]="This is object 1121";

  $obj1122_id = TREE_AddObject ($tree, "1122");
  $obj1122 = &TREE_AccessObject ($tree, $obj1122_id);  
  $obj1122["shorttitle"]="Obj1122";
  $obj1122["longtitle"]="This is object 1122";

  $obj1123_id = TREE_AddObject ($tree, "1123");
  $obj1123 = &TREE_AccessObject ($tree, $obj1123_id);  
  $obj1123["shorttitle"]="Obj1123";
  $obj1123["longtitle"]="This is object 1123";

  $obj113_id = TREE_AddObject ($tree, "113");
  $obj113 = &TREE_AccessObject ($tree, $obj113_id);  
  $obj113["shorttitle"]="Obj113";
  $obj113["longtitle"]="This is object 113";

  $obj12_id = TREE_AddObject ($tree, "12");
  $obj12 = &TREE_AccessObject ($tree, $obj12_id);  
  $obj12["shorttitle"]="Obj12";
  $obj12["longtitle"]="This is object 12";

  $obj13_id = TREE_AddObject ($tree, "13");
  $obj13 = &TREE_AccessObject ($tree, $obj13_id);  
  $obj13["shorttitle"]="Obj13";
  $obj13["longtitle"]="This is object 13";

  $obj21_id = TREE_AddObject ($tree, "21");
  $obj21 = &TREE_AccessObject ($tree, $obj21_id);  
  $obj21["shorttitle"]="Obj21";
  $obj21["longtitle"]="This is object 21";


  TREE_ConnectObject ($tree, $obj1_id, $obj11_id);
  TREE_ConnectObject ($tree, $obj11_id, $obj111_id);
  TREE_ConnectObject ($tree, $obj11_id, $obj112_id);
  TREE_ConnectObject ($tree, $obj112_id, $obj1121_id);
  TREE_ConnectObject ($tree, $obj112_id, $obj1122_id);
  TREE_ConnectObject ($tree, $obj112_id, $obj1123_id);
  TREE_ConnectObject ($tree, $obj11_id, $obj113_id);
  TREE_ConnectObject ($tree, $obj1_id, $obj12_id);
  TREE_ConnectObject ($tree, $obj1_id, $obj13_id);
  TREE_ConnectObject ($tree, $obj2_id, $obj21_id);

  echo TREE_CreateDHTML ($tree, 'http://www.google.com/$id', $obj112_id);

  TREE_DeleteObject ($tree, $obj1123_id);

  echo TREE_CreateDHTML ($tree, 'http://www.google.com/$id', $obj112_id);
}

function TREE_Visible($object_id, $object, $ignorewhenprefix = "", $tree = null)
{
  if ($object["hassecuritysection"] == "yes")
  {
    $viewtable = "shield_" . IMS_SupergroupName() . "_customfolderview";
    $vobj = MB_Ref($viewtable, $object_id);
    if (!$vobj) return true; // no groups defined for custom folderview means everybody gets in
    if (SHIELD_CurrentUser(IMS_SuperGroupName()) == "unknown") return false; // prevent login e.g. for quality handbooks
    return SHIELD_ValidateAccess_List(IMS_SuperGroupName(), SHIELD_CurrentUser(IMS_SuperGroupName()), $vobj, $object_id, "", $object_id);  
  }
  else if ($ignorewhenprefix)
  {
    $obj = TREE_AccessObject($tree, $object_id);
    $len  = strlen($ignorewhenprefix);
    $titl = $obj["shorttitle"];
    if (strtoupper(substr($titl, 0, $len)) == strtoupper($ignorewhenprefix))
      return false;
    else
      return true;
  }  else
  {
    return true;
  }
}

function TREE_ChildrenVisible($tree, $objectparent, $ignorewhenprefix = "")
{
  $children = $objectparent["children"];
  foreach ($children as $child)
  {
    $object_id = $child;
    $object = TREE_AccessObject($tree, $object_id);
    if (TREE_Visible($object_id, $object, ignorewhenprefix, $tree))
      return true;
  }
  return false;
}

?>