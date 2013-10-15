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



// oct 2010
// tvdb en jh 
// geeft overzicht van documenten en webpages waar iemand aan gewerkt heeft
// niet perse als laatste
// hiervoor wordt hulptabel "ims_<sgn>_userhistory" gebruikt
// deze wordt voor nieuwe wijzigingen gevuld met trigger
// en oude wijzigingen kunnen aangemaakt worden met 'MakeUserHistory'
// in de achtergrond
// alles onder een switch $myconfig[<sgn>]["userhistory"] == "yes"

uuse("files");
uuse("forms");
uuse("ims");
uuse("terra");

function USERHISTORY_MakeUserHistory()
{
  $terraspecs = array();
  $sgn = IMS_SupergroupName();
  $table = "ims_" . $sgn . "_objects";
  $chtable = "ims_" . $sgn . "_userhistory";
  MB_DeleteTable($chtable);

  $terraspecs["tables"] = array($table);
  $terraspecs["input"]["sgn"] = $sgn;
  
  $terraspecs["step_code"]= '

    $sgn = $input["sgn"];
    $chtable = "ims_" . $sgn . "_userhistory";
    $utable = "shield_" . $sgn . "_users";
    $allusers = MB_Query($utable);

    $obj = MB_Ref($table, $key);

    $his = array_reverse($obj["history"]);

    foreach ($allusers as $usr)
    {
      $chrec = &MB_Ref($chtable, $usr."#".$key);
      
      reset($his);
      foreach ($his as $hkey => $hval)
      {
        if ($hval["author"] == $usr)
        {
          // indien record niet bestaat of te oud is, dan aanmaken
          if($chrec["time"] < $hval["when"])
          {
            $chrec["time"] = $hval["when"];
            $chrec["user"] = $hval["author"];
            $chrec["key"] = $key;
          }
          // record bestaat al, ook stoppen met deze user
          break;
        }
      }
    }
    ';

  TERRA_Multi_Tables($terraspecs);
}

function USERHISTORY_MakeUserHistoryUrl()
{
  $forms = array();
  $forms["formtemplate"] = '<table><tr><td><font face="arial" size=2><b>' . ML("Gebruikershistorie aanmaken", "Generate user history") . '</b></td></tr>
                                   <tr><td>&nbsp;</td></tr>
                                   <tr><td><font face="arial" size=2>' . 
                                   ML("Gebruikershistorie wordt in de achtergrond aangemaakt.", "User history is processed in the background.") . 
                                   '</td></tr><tr><td>&nbsp;</td></tr><tr><td><center>[[[ok]]] [[[cancel]]]</center></td></tr></table>';
  $forms["postcode"] = 'uuse("userhistory");USERHISTORY_MakeUserHistory();';
  return FORMS_URL($forms);
}

// hulpfuncties voor ShowUserHistory
function USERHISTORY_ChangeTypeInUrl($old, $new, $url, $slice)
{
  if (strpos($url, "&type=" . $old) !== false)
    $newurl = str_replace("&type=" . $old, "&type=" . $new, $url);
  else
    $newurl = $url . "&type=" . $new;

  $newurl = USERHISTORY_ChangeSliceInUrl($slice, 1, $newurl);

  return $newurl;
}

function USERHISTORY_ChangeSliceInUrl($old, $new, $url)
{
  if (strpos($url, "&slice=" . $old) !== false)
    $newurl = str_replace("&slice=" . $old, "&slice=" . $new, $url);
  else
    $newurl = $url . "&slice=" . $new;

  return $newurl;
}

// geeft niets terug als object in prullebak zit
function USERHISTORY_GetObjectType($key)
{
  $sgn = IMS_SupergroupName();
  $table = "ims_" . $sgn . "_objects";
  $obj = MB_Ref($table, $key);

  if ($obj["published"] == "yes" or $obj["preview"] == "yes")
    return $obj["objecttype"];  
}

// main function ShowUserHistory
function USERHISTORY_ShowUserHistory()
{
  if($_GET['user'])
    $user = $_GET['user'];
  else
    $user = SHIELD_CurrentUser();

  if ($_GET['type'])
    $type = $_GET['type'];
  if ($type != "webpage")
    $type = "document";

  if ($_GET['slice'])
    $slice = $_GET['slice'];
  else
    $slice = 1;

  $page = 40;
  $sgn = IMS_SupergroupName();

  $utable = "shield_".$sgn . "_users";
  $allusers = MB_Query($utable);
  MB_Multiload($utable, $allusers);

  $contuh .= '<div style="float:right;">';
  $contuh .= ML("Gebruiker","User").": ";

  // als je van gebruiker veranderd, wil je terug naar documenten en eerste blz
  $xurl = N_MyFullUrl(); 
  if ($type=="webpage")  
    $xurl = USERHISTORY_ChangeTypeInUrl("webpage", "document", $xurl, $slice);
  else
    $xurl = USERHISTORY_ChangeSliceInUrl($slice, 1, $xurl);

  $contuh .= "<select onchange='document.location.href=\"".N_AlterURL(str_replace("user","old",$xurl),"old","")."&user=\"+escape(this.value)'>";

  foreach($allusers as $a => $b)
  {
    $u = MB_Load($utable,$a);
    $contuh .= "<option value='".$a."' ";
    if ($user == $a) $contuh .=" selected ";
    $contuh .= ">".$u["name"]."</option>";
  }

  $contuh .= "</select>";
  $contuh .= "</div>"; 

  $chtable = "ims_" . $sgn . "_userhistory";
  $table = "ims_" . $sgn . "_objects";

  $selectspec = array();
  $selectspec['select']['$record["user"]'] = $user;
  $selectspec['slowselect']['USERHISTORY_GetObjectType($record["key"])'] = $type;
  $selectspec['rsort'] = '$record["time"]';
  $selectspec['value'] = '$record["time"]';
  $from = ($slice - 1) * $page + 1;
  $to = $slice * $page;
  $selectspec['slice'] = array($from, $to);

  $result = MB_TurboMultiQuery($chtable, $selectspec);

  T_Start("ims");

  if ($type == "document")
  {
    echo "<strong>".ML("Documenten", "Documents")."</strong>";
  }
  else
  {
    $url = USERHISTORY_ChangeTypeInUrl("webpage", "document", N_MyFullUrl(), $slice);
    echo '<a href="' . $url . '" title="' . ML("Documenten", "Documents") . '">' . ML("Documenten", "Documents") . '</a>';
  }
  echo "&nbsp;|&nbsp;";
  if ($type == "webpage")
  {
    echo "<strong>".ML("Webpagina's", "Webpages")."</strong>";
  }
  else
  {
    $url = USERHISTORY_ChangeTypeInUrl("document", "webpage", N_MyFullUrl(), $slice);
    echo '<a href="' . $url . '" title="' . ML("Webpagina's", "Webpages") . '">' . ML("Webpagina's", "Webpages") . '</a>';
  }
  echo "<br><br>";

  T_NewRow();
  T_Next();
  echo "<strong>". ML("Gewijzigd", "Changed"). "</strong>";
  T_Next();
  echo "<strong>". ML("Titel", "Title") . "</strong>";
  T_Next();
  echo "&nbsp;";

  foreach ($result as $k => $v)// $k is nu de sleutel van chtable, dwz user#key
  {

    $ko = N_KeepAfter($k, "#");
    $o = MB_Load($table, $ko);
  
    T_NewRow();
    if($o["objecttype"] == "webpage")
    {
      $ico = "/openims/ico_htm.gif";
      $site = IMS_Object2Site($sgn, $ko); 
      $url = "/" . $site . "/". $ko . ".php?activate_preview=yes";
    }
    else
    {
      $ico = FILES_Icon($sgn, $ko, false, "preview");
      $url = FILES_DMSURL($sgn, $ko);
    }
    echo '<img src="'.$ico.'">';
    T_Next();
    echo N_VisualDate($v, true);
    T_Next();

    echo '<a href="'.$url.'">';
    if($o["objecttype"]=="webpage")
      echo FORMS_ML_Filter($o["parameters"]["preview"]["longtitle"] == "" ? $o["parameters"]["preview"]["shorttitle"] : $o["parameters"]["preview"]["longtitle"]);
    else
      echo $o["longtitle"] == "" ? $o["shorttitle"] : $o["longtitle"];
    echo '</a>';
    T_Next();

    echo '<a href="/openims/openims.php?mode=history&back=' . urlencode($goto) . '&object_id=' . $ko . '">';
    echo ML("Link content historie", "Link content history");
    echo '</a>';

  }

  $prev = ($slice > 1); 
  $next = (count($result) == $page);

  if ($prev or $next)
    T_NewRow();

  if ($prev)
  {
    $urlpag = USERHISTORY_ChangeSliceInUrl($slice, $slice - 1, N_MyFullUrl());
    echo '<a href="'.$urlpag.'" title="'.ML("Vorige", "Previous").'">'.ML("Vorige", "Previous").'</a> ';
  } 

  if ($prev and $next)
    echo "&nbsp;";

  if ($next)
  {
    $urlpag = USERHISTORY_ChangeSliceInUrl($slice, $slice + 1, N_MyFullUrl());
    echo '<a href="'.$urlpag.'" title="'.ML("Volgende", "Next").'">'.ML("Volgende", "Next").'</a> ';
  }

  $contuh .= TS_End();

  return $contuh;
}

?>