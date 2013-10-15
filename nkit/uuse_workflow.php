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



// dec 2010
// jh
// pr10-242
// workflowstappen bepalen, en bijbehorende popups
// in aparte functie omdat deze nu ook
// in de documentenlijsten gekozen moeten kunnen worden

// apr 2011
// autocomplete optie toegevoegd, functies zijn van gva / bouwfonds overgenomen
// onder switch $myconfig[<sgn>]["autocompletealloc"] = "yes'
// en via parameter $autocompletealloc, zodat het niet werkt in eigenschappenscherm en zo
// ook bij weinig (MAX_LIST_SIZE) allowed users gewoon dropdownbox
// jh
 
uuse("shield");
uuse("ims");
uuse("forms");

function WORKFLOW_FK_EditUserCustom ($sgn, $currentobject, $table, $fieldname, $value, $acfield, $acfieldname, $specs, $autorisation="", $selectspec=array(), $slowselectspec=array()) 
{
  N_Debug ("WORKFLOW_FK_EditUserCustom ($sgn, $currentobject, $table, $fieldname, $value, ...)");
  if(!$currentobject) global $currentobject;
  global $debug; if ($debug=="yes") {
    N_EO ($specs);
    N_EO ($autofields);
  }
  uuse ("dhtml");
  reset ($specs);
  list ($dummy, $expr) = each($specs);
  $key = $value;
  $object = $record = MB_Load ($table, $key);
  eval ('$calcvalue = '.$expr.';');
  $form["title"] = ML("Kies", "Select");
  $form["input"]["specs"] = $specs;
  $form["input"]["sgn"] = $sgn;
  $form["input"]["field"] = "field_".$fieldname;
  $form["input"]["acfield"] = "field_".$acfieldname;
  $form["input"]["table"] = $table;
  $form["input"]["showfield"] = $showfield;
  $form["input"]["currentobject"] = $currentobject;
  $form["input"]["autorisation"] = $autorisation;
  $form["input"]["select"] = $selectspec;
  $form["input"]["slowselect"] = $slowselectspec;
  $form["metaspec"]["fields"]["filter"]["type"] = "string";
  $form["formtemplate"] = '
    <table>
      <tr><td><font face="arial" size=2><b>'.ML("Zoeken naar","Search for").':</b></font></td><td>[[[filter]]]</td></tr>
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
    </table>
  '; 

  // set focus on search filter field (only IE)
  if (N_IE()) {
     $form["formtemplate"] .= '
       <script language="javascript">
       document.forms[0].field_filter.focus(); 
      </script>
     '; 
  }


  $form["precode"] = '';
  
  $form["postcode"] = '
    $autorisation = $input["autorisation"];
    uuse ("dhtml");
    $form["title"] = ML("Kies", "Choose");
    $form["formtemplate"] = \'
      <table>
        <tr><td colspan=2>
    \';
    $exp = "";    
    foreach ($input["specs"] as $title => $expr) {
      if ($exp) {
        $exp .= " . " . $expr;
      } else {
        $exp = $expr;
      }
    }
    $result = array();
    if($autorisation && $input["currentobject"]) {
      $result = SHIELD_AssignableUsers($input["sgn"], $input["currentobject"], $autorisation);
      foreach($result as $key=>$name) {
        if($data["filter"] && (strpos(strtolower($name),strtolower($data["filter"])) === false)) unset($result[$key]);
      }
    } else {
      $result = MB_TurboMultiQuery ($input["table"], array (
        "filter" => array ($exp, $data["filter"]),
        "select" => $input["select"],
        "slowselect" => $input["slowselect"],
	    "value" => \'$record["name"]\'
      ));
    }
    if (count($result)==1) {

     
      reset ($result);
      $key = key($result);
      $calcvalue = current($result);
      echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ($input["acfield"], $calcvalue));
      echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ($input["field"], $key));
      $popuplevel = 1;
      if ($input["extrapostcode"]) eval ($input["extrapostcode"]);
      $value = $key;
      if ($input["extracode"]) eval ($input["extracode"]);
      N_Redirect ("closeme");

    } else {
      $evalcode = \'
        $subform = array();
        $subform["input"] = array("value" => $key, "calcvalue" => $value, "acfield" => $input["acfield"], "field" => $input["field"]);
        $subform["postcode"] = \\\'
          uuse ("dhtml");
          uuse ("tables");
          echo DHTML_EmbedJavaScript (DHTML_Parent_Parent_SetValue ($input["acfield"], $input["calcvalue"]));
          echo DHTML_EmbedJavaScript (DHTML_Parent_Parent_SetValue ($input["field"], $input["value"]));
          $popuplevel = 2;
          echo DHTML_EmbedJavaScript (DHTML_Parent_Parent_PerfectSize ());
          echo DHTML_EmbedJavaScript (DHTML_CloseParent ());
          $gotook="closeme";
        \\\';
        $url = FORMS_URL ($subform);
        echo \\\'<a class="ims_navigation" href="\\\'.$url.\\\'">\\\'.$value.\\\'</a>\\\';
      \';
      T_Start("ims");
      echo ML("Keuze","Choice");
      T_NewRow();
      foreach($result as $key=>$value) {
        eval($evalcode);
        T_NewRow();
      }
      $atable = TS_End();   
      $form["formtemplate"] .= $atable;
      $form["formtemplate"] .= \'
        </td></tr>
        <tr><td colspan=2>&nbsp;</td></tr>
        <tr><td colspan=2><center>[[[CANCEL]]]</center></td></tr>
      </table>
    \';
    $gotook = FORMS_URL ($form);

    }
  ';
  $url = FORMS_URL ($form);

  return DHTML_InvisiTable ('<font face="arial" size="2">', "</font>",
    $acfield,
    '&nbsp;<a title="'.ML("Kies","Select").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>',
    '&nbsp;<a title="'.ML("Kies","Select").'" class="ims_navigation" href="'.$url.'">'.ML("Kies","Select").'</a>'
  );
}

function WORKFLOW_GetAllocPhpCode()
{
   $phpstr = '
$params = explode(",",$flexparams);
$currentobject = $params[0];
$autorisatie = ($params[1])?$params[1]:"#reassign#";
$sgn = IMS_supergroupname();
$dyna = array();
$table = "shield_".$sgn."_users";
$column = "name";
$dyna["serverinput"] = Array("sgn"=>$sgn, "currentobject"=>$currentobject, "autorisatie"=>$autorisatie, "table"=>$table, "lookupfield"=>$column, 
                             "inputfieldname"=>$fieldname);
$dyna["phpcode"] = \'
  uuse("search"); // to use RemoveAccents
  uuse("flex");
  uuse("shield");
  FLEX_LoadSupportFunctions(IMS_SupergroupName());
  FLEX_LoadAllLowLevelFunctions();
  $result = "";
  $tussenresult = array();
  $sgn = "\'.$dyna["serverinput"]["sgn"].\'";
  $currentobject = "\'.$dyna["serverinput"]["currentobject"].\'";
  $autorisation = "\'.$dyna["serverinput"]["autorisatie"].\'";
  $theTable = "\'.$dyna["serverinput"]["table"].\'";
  $theColumn = "\'.$dyna["serverinput"]["lookupfield"].\'";
  $search = strtolower(SEARCH_REMOVEACCENTS($_GET["q"]));
  if($autorisation && $currentobject) {
    $tussenresult = SHIELD_AssignableUsers($sgn, $currentobject, $autorisation);
  }
 
  $list = MB_Allkeys($theTable);
  MB_MultiLoad($thetable,$list);
  foreach($list as $uid=>$dummy) {
    $value = MB_Fetch($theTable, $uid, $theColumn);
    if(strpos(strtolower(SEARCH_REMOVEACCENTS($value)),$search)!== false) $result[$uid] = $value;
  }
  foreach($result as $uid=>$fullname) {
    if($tussenresult) {
	  if(in_array($fullname,$tussenresult)) echo "$fullname|$uid\n";
	}
    else echo "$fullname|$uid\n";
  }
\';
$acp = new WORKFLOW_ACP( $sgn, $dyna, $value );
$acfield = $acp->renderField();
$acfieldname = $fieldname."_show";
$content = WORKFLOW_FK_EditUserCustom ($sgn, $currentobject,"shield_".IMS_SuperGroupName()."_users", $fieldname, $value, $acfield, $acfieldname, array (
  "Name" => \'$object["name"]\',
  "ID" => \'$key\'
), $autorisatie);
unset($acp);';

  return $phpstr;
}

if(!class_exists("WORKFLOW_ACP")) {
  class WORKFLOW_ACP{
    var $sgn = "";
    var $inputfieldname = "";
    var $table;
    var $lookupfield = "";
    var $value = null;
    var $dyna = array();
    var $dynalink = "";

    function WORKFLOW_ACP($p_sgn, $p_dyna, $p_value, $p_loadjquery = true) {
      // constructor: only initial functions 
N_Log ("acp", "initialize start");
      $this->sgn = $p_sgn;
      $this->dyna = $p_dyna;
      $this->inputfieldname = $this->dyna["serverinput"]["inputfieldname"];
      $this->table = $this->dyna["serverinput"]["table"];
      $this->lookupfield = $this->dyna["serverinput"]["lookupfield"];
      $this->value = $p_value;
      $this->loadjquery = $p_loadjquery;

      $this->setDynalink();
N_Log ("acp", "DHTML_LoadjQuery called end");
    }

    function renderField () {
N_Log ("acp", "render start");
      $showvalue = ($this->value != "") ? MB_Fetch($this->table, $this->value, $this->lookupfield): "";
      $html = "";
      $html .= $this->loadAutocomplete();
N_Log ("acp", "render middle");
      $html .= '<input size="30" id="field_'.$this->inputfieldname.'_show" type="text" name="field_'.$this->inputfieldname.'_show" value="' . htmlentities( $showvalue ) . '" />';
      $html .= '<input size="30" id="field_'.$this->inputfieldname.'" type="hidden" name="field_'.$this->inputfieldname.'" value="' . htmlentities( $this->value ) . '" />';
      $html .= $this->renderJavascript();
N_Log ("acp", "render end");
      return $html;
    }

    function renderJavascript() {
      $optionsString = 'max:0, minChars:4, matchSubset:1, matchContains:1, cacheLength:10, selectOnly:1, multiple:false, multipleSeparator:" "';
      $js = "";
      $js .= '<script language="javascript">';
      $js .= '
        $("document").ready(function(){
          // here you set events for fields
          $("#field_'.$this->inputfieldname.'_show").autocomplete( "'.$this->dynalink.'" , {'.$optionsString.'} );

          $("#field_'.$this->inputfieldname.'_show").result(function(event, data, formatted) {
            var hidden = $("#field_'.$this->inputfieldname.'");
              //hidden.val( (hidden.val() ? hidden.val() + ";" : hidden.val()) + data[1]);
              hidden.val(data[1]);
            });

        });';
      $js .= '</script>';
      return $js;
    }

    function setDynalink() {
      uuse("dyna");
      $sid = DYNA_CreateAPI ( $this->dyna["phpcode"] , $this->dyna["serverinput"] );
      SHIELD_FlushEncoded();
      $url = "/nkit/dyna.php?sid=$sid";
      $this->dynalink = $url;
    }

    function loadAutocomplete( )
    {

      $content = "";
N_Log ("acp", "DHTML_LoadjQuery called start");
        if( $this->loadjquery ) {
          uuse("dhtml");
          $content .= DHTML_LoadJquery(true);
          //$content .= DHG_LoadJquery($this->sgn, true);
        }
N_Log ("acp", "DHTML_LoadjQuery called middle");
        $content .= "<link rel='stylesheet' href='/openims/libs/jquery/plugins/jquery-autocomplete/jquery.autocomplete.css' />";
        $content .= "<script language='javascript' src='/openims/libs/jquery/external/bgiframe/jquery.bgiframe.js'></script>";
        $content .= "<script language='javascript' src='/openims/libs/jquery/plugins/jquery-autocomplete/jquery.autocomplete.js'></script>";
N_Log ("acp", "DHTML_LoadjQuery called stop");
      return $content;
    }
  }
}

function WORKFLOW_Options($supergroupname, $key, $object, $popup=true, $autocompleteallocmem=false)
{
  $options_arr = array();

    if (SHIELD_HasObjectRight ($supergroupname, $key, "view"))
    {
        $options = SHIELD_AllowedOptions ($supergroupname, $key);
        $workflow = &SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);

        global $myconfig;
        $customfield = false;
        if($myconfig[$supergroupname]["usecustomallocfieldinreassignform"])
        {
          $fields = MB_Load("ims_fields", $supergroupname);
          $thefield = $myconfig[$supergroupname]["usecustomallocfieldinreassignform"];
          $customfield = $fields[$thefield];
        }

        if (is_array($options))
        {
          reset ($options);
          while (list($option)=each($options)) 
          {
            if ($autocompleteallocmem)
            {
              $usrs = SHIELD_AssignableUsers($supergroupname, $key, $option);
              if (count($usrs) <= MAX_LIST_SIZE)
                $autocompletealloc = false;
              else
                $autocompletealloc = true;
            }
            else
            {
              $autocompletealloc = false;
            }

            if($customfield["type"] !== "list" or $autocompletealloc)
              $alloc = '[[[alloc:'.$key.','.$option.']]]';
            else 
              $alloc = '[[[alloc]]]';

            $signalcc = ($workflow["alloc"] && $workflow[$object["stage"]]["signalcc"] && $workflow[$object["stage"]]["signalcc"]["#".$option] && $workflow[$object["stage"]]["signalcc"]["#".$option]["enabled"]);
            if ($signalcc) $signalccfield = $workflow[$object["stage"]]["signalcc"]["#".$option]["defaultfield"];

            if ($workflow[$object["stage"]]["propertiesinworkflowstep"]) {
              $editproperties = $workflow[$object["stage"]]["propertiesinworkflowstep"]["#".$option];
            } else {
              $editproperties = array();
            }
            
            $form = array();
            $form["input"]["col"] = $supergroupname;
            $form["input"]["id"] = $key;
            $form["input"]["user"] = SHIELD_CurrentUser ($supergroupname);
            $form["input"]["opt"] = $option;
            $form["input"]["customfield"] = $customfield;
            $obj = IMS_AccessObject (IMS_SuperGroupName(), $key);
            $form["input"]["oldstage"] = $obj["stage"];
            $form["input"]["user_id"] = SHIELD_CurrentUser($supergroupname);
            $form["input"]["newstage"] = $newstage = $workflow[$object["stage"]]['#' . $option];
            $form["input"]["autocompletealloc"] = $autocompletealloc;
            $form["input"]["signalcc"] = $signalcc;
            $form["input"]["signalccfield"] = $signalccfield;
            $form["input"]["editproperties"] = $editproperties;

            //ericd 140710
            if ($myconfig[IMS_SuperGroupName()]["usefieldvalueforworkflowallocto"] == "yes")
            {
              $field = $workflow[$object["stage"]]["useforallocto"]["#".$option];
              $form["input"]["wfmetavalue"] = $object["meta_".$field];
            }

            $form["title"] = $option;
            if ($editproperties) foreach ($editproperties as $property => $propspecs) {
              $form["metaspec"]["fields"][$property] = MB_Fetch("ims_fields", IMS_SuperGroupName(), $property);
              if ($form["metaspec"]["fields"][$property]["type"] == "clone") {
                // Dereference clones, because otherwise custom validationcode will not work
                $title = $form["metaspec"]["fields"][$property]["title"];
                $required = $form["metaspec"]["fields"][$property]["required"];
                $form["metaspec"]["fields"][$property] = MB_Fetch("ims_fields", IMS_SuperGroupName(), $form["metaspec"]["fields"][$property]["source"]);
                $form["metaspec"]["fields"][$property]["title"] = $title; 
                $form["metaspec"]["fields"][$property]["required"] = $required; 
              }
              if ($propspecs["title"]) $form["metaspec"]["fields"][$property]["title"] = $propspecs["title"];
              if ($propspecs["validationcode"]) $form["metaspec"]["fields"][$property]["validationcode"] = $propspecs["validationcode"];
            }
            $form["metaspec"]["fields"]["deleteconceptmanual"]["type"] = "yesno";
            $form["metaspec"]["fields"]["comment"]["type"] = "text";
            $form["metaspec"]["fields"]["signal"]["type"] = "yesno";
            //GVA: velden volglijst
            $input["trackinglist"] = false;
            if ($myconfig[$supergroupname]["trackinglist"] == "yes")
            {
              $form["metaspec"]["fields"]["trackinglist"]["type"] = "yesno";
              $form["metaspec"]["fields"]["trackinglist_reminder"]["type"] = "list";
              $form["input"]["trackinglist"] = true;
            }
            $multiapprove = false;
            if ($myconfig[IMS_SupergroupName()]["multiapprove"] == "yes")
            {
              $newstage = $workflow[$object["stage"]]['#' . $option];
              if ($workflow["multiapprove"][$newstage]["choices"])
              {
                $multiapprove = true;
              }
            }
            $form["input"]["multiapprove"] = $multiapprove;
            if ($multiapprove)
            {
              $form["metaspec"]["fields"]["enddate"]["type"] = "date";
              $form["metaspec"]["fields"]["remind"]["type"] = "date";
              $form["metaspec"]["fields"]["savelist"]["type"] = "string";

              $form["metaspec"]["fields"]["distribution"]["type"] = "list";
              $form["metaspec"]["fields"]["distribution"]["method"] = "multi";
              $form["metaspec"]["fields"]["distribution"]["show"] = "visible";
              $utable = "shield_" . IMS_SupergroupName() . "_users";
              $us = MB_Query($utable);
              MB_MultiLoad($utable, $us);
                      
              $form["metaspec"]["fields"]["distributiongroup"]["type"] = "list";
              $form["metaspec"]["fields"]["distributiongroup"]["show"] = "visible";
              $gtable = "shield_" . IMS_SupergroupName() . "_groups";
              $gs = MB_Query($gtable);
              MB_MultiLoad($gtable, $gs);
              $gsf["-"] = "";
              foreach ($gs as $gskey => $dummy)
              {
                $gsval = MB_Ref($gtable, $gskey);
                if ($gsval["name"] and substr($gskey, 0 , 4) == "list")
                { 
                    $gsf[$gsval["name"]] = $gskey; 
                }
              }
              $form["metaspec"]["fields"]["distributiongroup"]["values"] = $gsf;

              $form["metaspec"]["fields"]["distributionlist"]["type"] = "list";
              $form["metaspec"]["fields"]["distributionlist"]["show"] = "visible";
              $cuobj = MB_Ref($utable, SHIELD_CurrentUser());            
              $lsf["-"] = "";
              $lsf = N_Array_Merge($lsf, $cuobj["multiapprove"]);
              $form["metaspec"]["fields"]["distributionlist"]["values"] = $lsf;

              $form["input"]["enddays"] = $workflow["multiapprove"][$newstage]["enddate"];
              $form["input"]["reminddays"] = $workflow["multiapprove"][$newstage]["remind"];
              $form["input"]["key"] = $key;

              $now = time();
              $now = $now - ($now % (24 * 3600)); // hele dagen van maken
              $form["input"]["now"] = $now;
            }

            if($customfield)
              $form["metaspec"]["fields"]["alloc"] = $customfield;
            else if ($autocompletealloc)
              $form["metaspec"]["fields"]["alloc"]["type"] = "code";
            else
              $form["metaspec"]["fields"]["alloc"]["type"] = "list";
            $form["formtemplate"] = '
                <table>';
            if ($editproperties) foreach ($editproperties as $property => $propspecs) {
              $form["formtemplate"] .= '
                <tr><td><font face="arial" size=2><b>{{{'.$property.'}}}:</b></font></td><td>[[['.$property.']]]</td></tr>
              ';
            }
            $publishing = $workflow["stages"]==$workflow[$object["stage"]]["#".$option];
            if (!$multiapprove && $workflow["alloc"] && ( !$publishing || $signalcc ) ) // WENS-305
            {
              if ( !$publishing )
              {
                $form["input"]["validateallocfield"] = "yes";
                $form["formtemplate"] .= '
                    <tr><td><font face="arial" size=2><b>'.ML("Toewijzen aan","Allocate to").':</b></font></td><td>'.$alloc.'</td></tr>';
              }
              if ($signalcc) {
                // Make the 'signal' checkbox toggle visibility of the 'signalcc' field using javascript.
                $form["metaspec"]["fields"]["signalcc"]["type"] = "list";
                $form["metaspec"]["fields"]["signalcc"]["method"] = "multi";
                $form["metaspec"]["fields"]["signalcc"]["show"] = "visible";
                $form["metaspec"]["fields"]["signal"]["type"] = "code";
                $form["metaspec"]["fields"]["signal"]["edit"] = '
                  $onchange = "document.getElementById(\'hideme_signalcc\').style.visibility = (this.checked ? \'visible\' : \'hidden\');";
                  $content = \'<input id="toggle_signalcc" type="checkbox" onclick="\'.$onchange.\'" name="field_\'.$fieldname.\'" \'.($value ? "checked " : "") . \'title="\'.$fieldtitletext.\'" alt="\'.$fieldtitletext.\'">\';
                  // Setting initial value: not here, because toggle_signalcc may not have been rendered yet
                ';
                $form["formtemplate"] .= '
                    <tr><td><font face="arial" size=2><b>'.ML("Signaal sturen","Send signal").':</b></font></td><td>[[[signal]]]</td></tr>
                    <tr><td><font face="arial" size=2><b>'.ML("Cc","Cc").':</b></font></td><td><div id="hideme_signalcc">[[[signalcc]]]</div>'
                ;
                $form["formtemplate"] .= DHTML_EmbedJavascript("if (!document.getElementById('toggle_signalcc').checked) document.getElementById('hideme_signalcc').style.visibility = 'hidden';");
                $form["formtemplate"] .= '</td></tr>';

              } else {
                $form["formtemplate"] .= '
                    <tr><td><font face="arial" size=2><b>'.ML("Signaal sturen","Send signal").':</b></font></td><td>[[[signal]]]</td></tr>';
              }
            }
	    if($myconfig[IMS_SuperGroupName()]["trackinglist"] == "yes") {
              $form["formtemplate"] .= '
                  <tr><td><font face="arial" size=2><b>'.ML("Zet op volglijst","Put on trackinglist").':</b></font></td><td>[[[trackinglist]]]</td></tr>
                  <tr><td><font face="arial" size=2><b>'.ML("Herinner mij na","Remind me after").':</b></font></td><td>[[[trackinglist_reminder]]]</td></tr>';		  
            }
            if (true)
             if ($workflow["deleteconcept"] == "manual" && $workflow["stages"]==$newstage) {
                $form["formtemplate"].= '
                <tr><td><font face="arial" size=2><b>'.ML("Verwijder alle historie versies","Delete all history versions").':</b></font></td><td>[[[deleteconceptmanual]]]</td></tr>
                <tr><td><font face="arial" size=2 color="red"><b>'.ML("LET OP ! (Dit is niet omkeerbaar)","Caution ! (This is not reverseable)").'</b></font></td></tr>
                ';
               }
              {
               $form["formtemplate"].= '
                  <tr><td colspan=2><font face="arial" size=2><b>'.ML("Opmerkingen","Comments").':</b></font></td></tr>
                  <tr><td colspan=2>[[[comment]]]</td></tr>
                  <tr><td colspan=2>&nbsp</td></tr> ';
              if ($multiapprove)
              {
                $form["formtemplate"] .= '
                  <tr><td><font face="arial" size=2><b>'.ML("Verloopdatum parallelle controle","Enddate for parallel control").':</b></font></td><td>[[[enddate]]]</td></tr>
                  <tr><td><font face="arial" size=2><b>'.ML("Rappel (dagen voor verloop)","Reminder (days before end)").':</b></font></td><td>[[[remind]]]</td></tr>
                  <tr><td><font face="arial" size=2><b>'.ML("Centrale distributielijst","Central distributionlist").':</b></font></td><td>[[[distributiongroup]]]</td></tr>
                  <tr><td><font face="arial" size=2><b>'.ML("Persoonlijke distributielijst","Personal distributionlist").':</b></font></td><td>[[[distributionlist]]]</td></tr>
                  <tr><td><font face="arial" size=2><b>'.ML("Personen kiezen","Choose persons").':</b></font></td><td>[[[distribution]]]</td></tr>
                  <tr><td><font face="arial" size=2><b>'.ML("Bewaar lijst als (Alleen losse personen worden toegevoegd)","Save list as (Only separate persons will be added)").':</b></font></td><td>[[[savelist]]]</td></tr>
                  <tr><td colspan=2>&nbsp</td></tr> 
                ';
              }
              $form["formtemplate"] .= '
                  <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                </table>
              ';
            }
            $form["precode"] = '
              uuse("workflow");
              global $myconfig;
              $supergroupname = $input["col"];
              $autocompletealloc = $input["autocompletealloc"];
              if($input["trackinglist"]) {
                uuse("trackinglist");
                $values = TRACKINGLIST_ReminderValues();
                $metaspec["fields"]["trackinglist_reminder"]["values"] = $values;
              }

              if ($input["editproperties"]) {
                $object = MB_Load("ims_{$supergroupname}_objects", $input["id"]);
                foreach ($input["editproperties"] as $property => $propspecs) {
                  $data[$property] = $object["meta_".$property];
                }
              }

              // gva+am: use with assignableuser filters
              $usf = SHIELD_UsersForMultiApprove(IMS_SupergroupName(), $input["key"]);
              foreach ($usf as $user=> $value)
              {
                if (!$value)
                  unset ($usf[$user]);
              }
              $usf = array_flip($usf);
              ksort ($usf);
              $metaspec["fields"]["distribution"]["values"] = $usf;

              $customfield  = $input["customfield"];
              $multiapprove = $input["multiapprove"];
              if ($multiapprove)
              {
                $now = $input["now"];
                if ($input["enddays"]) // indien aantal dagen niet ingevuld in editor wordt datum hier leeg
                {
                  $enddate = $now + $input["enddays"] * 24 * 3600;
                  $data["enddate"] = $enddate;
                  if ($input["reminddays"])
                    $data["remind"] = $enddate - $input["reminddays"] * 24 * 3600;
                }
              }
              if ($myconfig[IMS_SuperGroupName()]["signaldefaulttrue"] == "yes")
              {
                $data["signal"] = true;
              }
              $error = FORMS_BlindValidation (IMS_SuperGroupName(), $input["id"]);
              if ($error && SHIELD_HasObjectRight (IMS_SuperGroupName(), $input["id"], "edit"))
              {
                global $iampopup;
                $iampopup = true;
                FORMS_ShowError (ML("Er is een fout in de eigenschappen","The properties contain an error"), $error, "no");
              }
              if(($customfield["type"]=="list")||(!$customfield and !$autocompletealloc))
              {
                $users = SHIELD_AssignableUsers (IMS_SuperGroupName(), $input["id"], $input["opt"]);
                if ($myconfig[IMS_SuperGroupName()]["reassigndefaultstoempty"] == "yes")
                {
                  $metaspec["fields"]["alloc"]["values"][ML("Kies...", "Choose...")] = "";
                }
                foreach ($users as $user_id => $name)
                {
                  $metaspec["fields"]["alloc"]["values"][$name] = $user_id;
                }
              }
              else if (!$customfield and $autocompletealloc)
              {
                uuse("workflow");
                $metaspec["fields"]["alloc"]["edit"] = WORKFLOW_GetAllocPhpCode();
              }

              if ($input["signalcc"]) {
                // TODO: (requirements) should this be assignable users, or all users with "view" right?
                if (!$users) $users = SHIELD_AssignableUsers (IMS_SuperGroupName(), $input["id"], $input["opt"]);
                foreach ($users as $user_id => $name) {
                  $metaspec["fields"]["signalcc"]["values"][$name] = $user_id;
                }
                if ($input["signalccfield"]) {
                  $document = MB_Load("ims_".IMS_SuperGroupName()."_objects", $input["id"]);
                  $defaultccuser = $document["meta_".$input["signalccfield"]];
                  if ($users[$defaultccuser]) $data["signalcc"] = $defaultccuser;
                }
              }

              if ($myconfig[IMS_SuperGroupName()]["reassigndefaultstoempty"] != "yes")
                $data["alloc"] = $input["user"];

              //ericd 140710 AAA
              if ($myconfig[IMS_SuperGroupName()]["usefieldvalueforworkflowallocto"] == "yes")
              {
                if($users and array_key_exists($input["wfmetavalue"],$users))
                  $data["alloc"] = $input["wfmetavalue"];
                else
                  $data["alloc"] = $input["user"];
              }


            ';
            $form["postcode"] = '
              uuse("multiapprove");
              uuse("shield");
              uuse("files");

              $trackinglist = $input["trackinglist"];
              $multiapprove = $input["multiapprove"];
              $now = $input["now"];
              if ($input["validateallocfield"])
              {
                if (!$data["alloc"])
                  FORMS_ShowError(ML("Fout", "Error"), ML("Bij het veld &quot;Toewijzen aan:&quot; is geen gebruiker gekozen", "No user was chosen in the field &quot;Allocate to:&quot;"));
                $uso = MB_Load("shield_" . IMS_SupergroupName() . "_users", $data["alloc"]);
                if (!$uso)
                  FORMS_ShowError(ML("Fout", "Error"), ML("Bij het veld &quot;Toewijzen aan:&quot; is geen geldige gebruiker gekozen", "No valid user was chosen in the field &quot;Allocate to:&quot;"));
              }
              $cc = "";
              if ($data["signal"] && $data["signalcc"]) {
                $ccusers = explode(";", $data["signalcc"]);
                foreach ($ccusers as $ccuser_id) {
                  $ccuser = MB_Load("shield_".IMS_SuperGroupName()."_users", $ccuser_id);
                  if (!$ccuser["email"]) FORMS_ShowError(ML("Fout", "Error"), ML("De gebruiker %1 heeft geen email adres", "User %1 does not have an email address", N_HtmlEntities($ccuser["name"])));
                  $signalcc[$ccuser_id] = $ccuser["email"];
                }
              }

              $obj = &IMS_AccessObject (IMS_SuperGroupName(), $input["id"]);
              if ($multiapprove)
              {
                unset($obj["multiapprove"]);
                $obj["multiapprove"]["enddate"] = $data["enddate"] - ($data["enddate"] % (24 * 3600));  
                $obj["multiapprove"]["remind"] = $data["remind"] - ($data["remind"] % (24 * 3600));
                if ($data["enddate"] and $data["enddate"] < $now)
                  FORMS_ShowError (ML("FOUTMELDING", "ERROR"), ML("Verloopdatum te vroeg","Enddate too early"));
                if ($data["remind"] and $data["remind"] < $now)
                  FORMS_ShowError (ML("FOUTMELDING", "ERROR"), ML("Rappeldatum te vroeg","Reminder too early"));
                $uss = explode(";", $data["distribution"]);
                $count = 0;
                foreach ($uss as $usr)
                {
                   if ($usr)
                   {
                      $obj["multiapprove"][$usr] = "x"; 
                      $history["multiapprove"][$usr] = "x"; 
                      $count++;
                   }
                }
                $grp = $data["distributiongroup"];
                if ($grp)
                {
                  $grpobj = MB_Ref("shield_" . IMS_SupergroupName() . "_groups", $grp);
                  if ($grpobj)
                  {
                    $usg = $grpobj["users"];
                    foreach ($usg as $usgkey => $vink)
                    {
                      if ($vink == "x")
                      {
                        SHIELD_SimulateUser($usgkey);
                        if (SHIELD_HasObjectRight(IMS_SupergroupName(), $input["id"], "view", false) and $obj["multiapprove"][$usgkey] != "x")
                        {
                          $obj["multiapprove"][$usgkey] = "x"; 
                          $history["multiapprove"][$usgkey] = "x"; 
                          $count++;                        
                        }
                      }
                    }
                    SHIELD_SimulateUser($input["user"]);
                  }  
                }
                $lst = $data["distributionlist"];
                if ($lst)
                {
                  $usl = explode(";", $lst);
                  foreach ($usl as $uslval)
                  {
                    if ($uslval)
                    {
                      SHIELD_SimulateUser($uslval);
                      if (SHIELD_HasObjectRight(IMS_SupergroupName(), $input["id"], "view", false) and $obj["multiapprove"][$uslval] != "x")
                      {
                        $obj["multiapprove"][$uslval] = "x"; 
                        $history["multiapprove"][$uslval] = "x"; 
                        $count++;                        
                      }
                    }
                    SHIELD_SimulateUser($input["user"]);
                  }  
                }
                if ($count == 0)
                  FORMS_ShowError (ML("FOUTMELDING", "ERROR"), ML("Minimaal &eacute;&eacute;n gebruiker kiezen voor parallelle goedkeuring","Select at least one user for multiple approve"));
                $obj["multiapprove"]["date"] = $now;
                if ($data["savelist"])
                  MULTIAPPROVE_SaveDistributionlist($data["savelist"], $data["distribution"]);
              }
              if($trackinglist) {
                if($data["trackinglist"]) {
                  uuse("trackinglist");
                  TRACKINGLIST_AddDocument($input["col"], $input["id"], $data["trackinglist_reminder"]);
                }                
              }
              if ($obj["stage"] != $input["oldstage"]) {
                FORMS_ShowError (ML("FOUTMELDING", "ERROR"), ML("Status van document was al aangepast","Status has already been changed"));
              } else {
                if ($input["editproperties"]) {
                  // check for changes to the domunent properties

                  $ret = array(); $visualchange = false;
                  $allfields = MB_Ref("ims_fields", IMS_SuperGroupName());
                  foreach ($input["editproperties"] as $property => $propspecs) {
                    if ($obj["meta_".$property] != $data[$property]) {
                      $new_ret = array();
                      $new_ret["key"] = $property;
                      $new_ret["title"] = $allfields[$property]["title"]; // Use the title that would be used in the Property dialogue, not the title that may have been used in this dialogue.

                      // calculate old (visible) value
                      $new_ret["old"] = FORMS_ShowValue($obj["meta_".$property], $property, $data ,$obj);
                      if ($new_ret["old"] != $obj["meta_".$property]) $new_ret["oldinternal"] = $obj["meta_".$property];

                      // change property
                      $obj["meta_".$property] = $data[$property];

                      // calculate new (visible) value
                      $new_ret["new"] = FORMS_ShowValue($obj["meta_".$property] , $property, $data, $obj);
                      if ($new_ret["new"] != $obj["meta_".$property]) $new_ret["newinternal"] = $obj["meta_".$property];

                      if ($new_ret["old"] != $new_ret["new"]) $visualchange = true;
                      $ret[]=$new_ret;
                    }
                  }
                  // add history entry for changed properties
                  if($ret) {
                    global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
                    $time = time();
                    $guid = N_GUID();
                    // if ($myconfig[IMS_SuperGroupName()]["dmshistoryhidepropertychangewithsamevisualvalue"] == "yes" && !$visualchange)
                    // -> irrelevant: DMSUIF_Properties does some timestamp spoofing, but soon we will execute a workflow step which will
                    //    and should update the timestamp anyway.
                    $obj["history"][$guid]["when"] = $time;
                    $obj["history"][$guid]["author"] = SHIELD_CurrentUser (IMS_SuperGroupName());
                    $obj["history"][$guid]["type"] = "properties";
                    $obj["history"][$guid]["server"] = N_CurrentServer ();
                    $obj["history"][$guid]["http_host"] = getenv("HTTP_HOST");
                    $obj["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
                    $obj["history"][$guid]["server_addr"] = $SERVER_ADDR;
                    $obj["history"][$guid]["changes"] = $ret;
                  }
                } // if ($input["editproperties"])

                $history["user_id"] = $input["user"];
                $history["timestamp"] = time();
                $history["comment"] = $data["comment"];
                if ($multiapprove)
                {
                  $history["multiapprove"]["enddate"] = $data["enddate"];
                  $history["multiapprove"]["remind"] = $data["remind"];
                }    
                SHIELD_ProcessOption ($input["col"], $input["id"], $input["opt"], $history, $data["alloc"], $data["signal"], $signalcc);
                $workflow = SHIELD_AccessWorkflow($input["col"], $obj["workflow"]);
              
              if ($data["deleteconceptmanual"] || ($workflow["deleteconcept"] == "auto" && $workflow["stages"]==$input["newstage"])) 
	      {
                  $History = $obj["history"];
                  $HistDir = N_CleanPath("html::".IMS_SuperGroupName()."/objects/history/".$input["id"]);
				   
                if (is_dir($HistDir))
                {
                   if ($handle = opendir($HistDir)) 
                   {
                      // loop through directory.
                      while (false !== ($deletedir = readdir($handle))) 
                      {
                        $aantaldirs[] = $deletedir;
                      }
                      closedir($handle);
                    }
                  
                 if (count($aantaldirs)>1)
                 {   
                     $HistKeys = array();
                     foreach ($History as $keyHistory=>$valueHistory) 
                     {
   
                       if($valueHistory["type"] == "edit" || $valueHistory["type"] == "new") 
                       {
                         $HistKeys[] = $keyHistory;
                       }
                     }

                     $LastHistEntry = end($HistKeys);

                     foreach ($HistKeys as $key) 
                     {
                       if($key != $LastHistEntry && in_array($key,$aantaldirs)) 
                       {
                         $deletedir = N_CleanPath("html::".IMS_SuperGroupName()."/objects/history/".$input["id"]."/".$key);
                         MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($deletedir);  
                         rmdir($deletedir);
                         N_log("deleteHistory", "deletedir -- $deletedir");
                       }
                     }
                   }
                 }
              }

                if ($multiapprove)
                  MULTIAPPROVE_Mail("distributie", $input["id"], $obj, IMS_SupergroupName());
              }
            ';
            $url = FORMS_URL ($form, $popup);
            $options_arr[$url] = $option;

          }
        }
      }

  return $options_arr;
}

?>