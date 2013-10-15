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


//TODO: Add encoding to html_entity_decode calls for PHP 5.4

// make sure everything is available for precode and postcode etc.
uuse ("shield");
uuse ("tree");
uuse ("case"); 
uuse ("multilang"); 
uuse ("search");
uuse ("ims");
uuse ("flex");
uuse ("assets");
uuse ("tmp");
uuse ("dhtml");
uuse ("tables");
uuse ("jscal");

global $myconfig;
if ($myconfig[IMS_SuperGroupName()]["formsmaxlistsize"]) {
  define ("MAX_LIST_SIZE", $myconfig[IMS_SuperGroupName()]["formsmaxlistsize"]);
} else {
  define ("MAX_LIST_SIZE", 25);
}
 
/*
   Search for
     CODE_VIEW for view logic
     CODE_EDIT for html edit logic
     CODE_READ for form post read logic

Checklist auto:
   - eigenschappen DMS
   - eigenschappen CMS
   - CMS formulier
   - METADATA bij bestanden
*/

global $debugforms; // for forms debugging
global $ims_showforms; // cookie
if ($ims_showforms=="yes") {
  $debugforms = true;
} else {
  $debugforms = false;
}

global $maxlistsize;

function FORMS_BlindValidation ($supergroupname, $object_id)
{
  global $myconfig;
  $object = MB_Load ("ims_".$supergroupname."_objects", $object_id);
  $workflow = MB_Load ("shield_".$supergroupname."_workflows", $object["workflow"]);
  $allfields = MB_Ref ("ims_fields", $supergroupname);
  for ($i=1; $i<1000; $i++) { // fetch metadata fields from workflow definition
    if ($workflow["meta"][$i]) {
      $specs = $allfields[$workflow["meta"][$i]];
      for ($j=0; $j<10; $j++) { // allow 10 levels of cloning
        if ($specs["type"]=="clone") {
          $tmp_required = $specs["required"];
          $specs = $allfields[$specs["source"]];
          $specs["required"] = $tmp_required;
        }
      }
      $fields[$workflow["meta"][$i]] = $specs;
    }
  }
  if ($object["dynmeta"] && is_array ($object["dynmeta"])) {
    foreach ($object["dynmeta"] as $dummy => $field) {
      $fields[$field] = $allfields[$field]; 
    }
  }
  if ($object["objecttype"]=="webpage") { // fetch metadata fields from template definition (only for webpages)
    $template = MB_Load ("ims_".$supergroupname."_templates", $object["template"]);
    for ($i=1; $i<1000; $i++) {
      if ($template ["meta"][$i]) {
        $specs = $allfields[$template ["meta"][$i]];
        for ($j=0; $j<10; $j++) { // allow 10 levels of cloning
          if ($specs["type"]=="clone") {
            $tmp_required = $specs["required"];
            $specs = $allfields[$specs["source"]];
            $specs["required"] = $tmp_required;
          }
        }
        $fields[$template["meta"][$i]] = $specs;
      }
    } 
  }
  $fields = FORMS_EnhanceAllFieldspecs($fields);
  if ($object["objecttype"]=="webpage") {
    $data = $object["parameters"]["preview"];
    foreach ($fields as $name => $specs) {
      $input = $data["meta_".$name];
      // sbr 7-7-8
      if ($specs["required"]) {
        if ($input." "==" ") {
          if ($myconfig[$supergroupname]["requiredfieldbyname"] != "no")
            return $specs["title"].": ".ML("verplicht veld is niet ingevuld", "required field is empty");
          else
            return ML("Een of meer verplichte velden zijn niet ingevuld", "One or more required fields are empty");
        }
      }  
      if ($specs["validationcode"]) {
        $error = FORMS_Validate ($input, $data, $specs["validationcode"]);
        if ($error) return $specs["title"].": ".$error;
      }
    }
  } else if ($object["objecttype"]=="document") {
    if (!trim($object["shorttitle"])) {
      return ML("Document naam is leeg","Document name is empty");
    }
    $data = $object;
    foreach ($fields as $name => $specs) {
      $input = $data["meta_".$name];
      if ($specs["required"]) {
        if ($input." "==" ") {
          if ($myconfig[$supergroupname]["requiredfieldbyname"] != "no")
            return $specs["title"].": ".ML("verplicht veld is niet ingevuld", "required field is empty");
          else
            return ML("Een of meer verplichte velden zijn niet ingevuld", "One or more required fields are empty");
        }
      }
      if ($specs["validationcode"]) {
        $error = FORMS_Validate ($input, $data, $specs["validationcode"]);
        if ($error) return $specs["title"].": ".$error;
      }
    }
  }
} 

function FORMS_ShowValueHTML ($value, $specs, $data="", $object="")
{
  if (!is_array ($specs)) {
    $allfields = MB_Load ("ims_fields", IMS_SuperGroupName());
    $specs = $allfields[$specs];
  }
  for ($i=0; $i<10; $i++) { // allow 10 levels of cloning
    if ($specs["type"]=="clone") {
      $allfields = MB_Load ("ims_fields", IMS_SuperGroupName());
      $specs = $allfields[$specs["source"]];
    }
  }
  $specs = FORMS_EnhanceFieldspec($specs);
  $result = FORMS_ShowValue ($value, $specs, $data, $object);
  if ($specs["type"]=="text" || $specs["type"]=="smalltext" || $specs["type"]=="bigtext" || $specs["type"]=="txtml") {
    $result = N_XML2HTML ($result);
  }
  return $result;
}

function FORMS_ShowValueTEXT ($value, $specs, $data="", $object="")
{
  if (!is_array ($specs)) {
    $allfields = MB_Load ("ims_fields", IMS_SuperGroupName());
    $specs = $allfields[$specs];
  }
  for ($i=0; $i<10; $i++) { // allow 10 levels of cloning
    if ($specs["type"]=="clone") {
      $allfields = MB_Load ("ims_fields", IMS_SuperGroupName());
      $specs = $allfields[$specs["source"]];
    }
  }
  $specs = FORMS_EnhanceFieldspec($specs);
  $result = FORMS_ShowValue ($value, $specs, $data, $object);
  return $result;
}

function FORMS_ShowValue ($value, $specs, $data="", $object="", $language="") // CODE_VIEW
{
  // $language will be determined automatically, but you should specify it explicitly 
  // when calling FORMS_ShowValue in index expressions (so that each language will have its own index)

  if (!is_array ($specs)) {
    $allfields = MB_Load ("ims_fields", IMS_SuperGroupName());
    $oldspecs = $specs;
    $specs = $allfields[$specs];

    if (!$specs) {
       $specs = $allfields[N_KeepBefore($oldspecs,"__")];
    } 
  }

  for ($i=0; $i<10; $i++) { // allow 10 levels of cloning
    if ($specs["type"]=="clone") {
      $allfields = MB_Load ("ims_fields", IMS_SuperGroupName());
      $specs = $allfields[$specs["source"]];
    }
  }

  $specs = FORMS_EnhanceFieldspec($specs);

  if ($specs["type"]=="code") {
    $content = $value;
    eval ($specs["view"]); // No need for N_Eval because it's in a function and followed by a return
    return $content;
  } else if ($specs["type"]=="taxo") {
    return FORMS_TAXO_View ($fieldname, $value, $specs);
  } else if ($specs["type"]=="multilist") {
    $sourcefield = $specs["source"];
    $sourcefieldspecs = MB_Fetch("ims_fields", IMS_SuperGroupName(), $sourcefield);
    if ($sourcefieldspecs["type"] == "list") {
      $sourcefieldspecs["show"] = "visible";
      $sourcefieldspecs["method"] = "multi"; // shouldnt be necessary, but just in case
      return FORMS_MULTI_View ("dummy", $value, $sourcefieldspecs);
    } else {
      $showvalue = "";
      $keys = explode(";", $value);
      foreach ($keys as $key) {
        if ($key) {
          $visible = FORMS_ShowValue($key, $sourcefieldspecs, $data, $object, $language);
          if ($visible) {if ($showvalue) $showvalue .= "<br>";
            $showvalue .= $visible;
          }
        }
      }
      return $showvalue;
    }
  } else if ($specs["type"]=="fk") {
    if (strpos (" ".$specs["specs"]["fk"]["table"], "ims_") && strpos (" ".$specs["specs"]["fk"]["table"], "_objects")) { // CMS and DMS
      $expression = '$object["meta_'.$specs["specs"]["fk"]["field1"].'"].$object["parameters"]["published"]["'.$specs["specs"]["fk"]["field1"].'"]';
    } else if (strpos (" ".$specs["specs"]["fk"]["table"], "process_") && strpos (" ".$specs["specs"]["fk"]["table"], "_cases_")) { // BPMS
      // $expression = '$object["data"]["'.$specs["specs"]["fk"]["field1"].'"]';
      $expression = 'FORMS_ShowValue ($object["data"]["'.$specs["specs"]["fk"]["field1"].'"],"'.$specs["specs"]["fk"]["field1"].'")';
    } else { // raw tables
      $expression = '$object["'.$specs["specs"]["fk"]["field1"].'"]';
    } 
    return FORMS_FK_View ($specs["specs"]["fk"]["table"], $value, $expression);
  } else if ($specs["type"]=="listemb") { 
    $thespecs = $specs["specs"]["listemb"];
    $allthefields = MB_Load ("ims_fields", IMS_SuperGroupName());
    $theheads = array();
    $thefields = array();
    for ($i=1; $i<=7; $i++) { 
      if ($thespecs["field$i"]) {
        array_push ($thefields, $thespecs["field$i"]);
        array_push ($theheads, $allthefields[$thespecs["field$i"]]["title"]);
      }
    }
    $mdspecs = array (
      "title"=>$specs["title"],
      "form"=>$thespecs["form"],
      "table"=>$thespecs["table"],
      "fk_field"=>$thespecs["fkfield"],
      "heads"=>$theheads, 
      "fields"=>$thefields,
      "maxrows"=>$thespecs["maxrows"],
      "sort"=>$thespecs["field1"]
    );
    return FORMS_RMD_View($value, $mdspecs);
  } else if ($specs["type"]=="auto") {
    eval ($specs["calc"]); // No need for N_Eval because it's in a function and followed by a return
    return $value;
  } else if ($specs["type"]=="yesno") {
    if ($value) {
      return ML ("Ja", "Yes");
    } else {
      return ML ("Nee", "No");
    }
  } else if ($specs["type"]=="date") {
    if ($language) {
      $oldlang = ML_GetLanguage();
      ML_SetLanguage($language);
    }
    if ($value."" == "") return "";
    // NB Keep these formats synchronised with the values for the "datetime" field and with the values in openims.php!
    if ($specs["format"] == "numeric") {
      $date = N_Date ("d-m-Y", "m-d-Y", $value, array("de" => "d. m. Y"));
    } elseif ($specs["format"]=="shortnumeric") {
      $date = N_Date ("d-m-y", "m-d-y", $value, array("de" => "d. m. y"));
    } elseif ($specs["format"]=="reversednumeric") {
      $date = N_Date ("Y-m-d", "Y-m-d", $value);
    } elseif ($specs["format"]=="anniversary") {
      $date = N_Date ("j F", "j F", $value);
    } elseif ($specs["format"]=="visual") {
      $date = N_VisualDate($value);
    } else { // default
      $date = N_Date ("j F Y", "F jS Y", $value, array("de" => "j. F Y"));
    }
    if ($language) ML_SetLanguage($oldlang);
    return $date;
  } else if ($specs["type"]=="time") { 
    return N_Date ("G:i", "G:i", $value);
  } else if ($specs["type"]=="datetime") {
    if ($language) {
      $oldlang = ML_GetLanguage();
      ML_SetLanguage($language);
    }
    if ($value."" == "") return "";
    // NB Keep these formats synchronised with the values for the "date" filed and with the values in openims.php!
    if ($specs["format"] == "numeric") {
      $date = N_Date ("d-m-Y G:i", "m-d-Y G:i", $value, array("de" => "d. m. Y G:i"));
    } elseif ($specs["format"]=="shortnumeric") {
      $date =  N_Date ("d-m-y G:i", "m-d-y G:i", $value, array("de" => "d. m. y G:i"));
    } elseif ($specs["format"]=="reversednumeric") {
      $date = N_Date ("Y-m-d G:i", "Y-m-d G:i", $value);
    } elseif ($specs["format"]=="anniversary") {
      $date = N_Date ("j F", "j F", $value);
    } elseif ($specs["format"]=="visual") {
      $date = N_VisualDate($value, true);
    } else { // default
      $date = N_Date ("j F Y G:i", "F jS Y G:i", $value, array("de" => "j. F Y G:i"));
    }
    if ($language) ML_SetLanguage($oldlang);
    return $date;
  } else if ($specs["type"]=="hyperlink") {
    $hyperspec = unserialize ($value);
    return "<a title=\"".$hyperspec["title"]."\" href=\"".$hyperspec["url"]."\">".$hyperspec["text"]."</a>";
  } else if ($specs["type"]=="composite") {
    // $specs["fieldspec"] is not needed when the fields in $specs["fields"] are stored in ims_fields
    return FORMS_Composite_View($fieldname, $value, $specs["fields"], $specs["fieldspec"]);
  } else if ($specs["type"]=="image") {
    return ASSETS_ShowImage ($value);
  } else if ($specs["type"]=="diskfile") {
    return ASSETS_ShowDiskFile ($value);
  } else if ($specs["type"]=="html") {
    return ASSETS_ShowHTML (IMS_SuperGroupName(), $value);
  } else if ($specs["type"]=="list") { 
    if ($specs["method"]=="multi") {
      return FORMS_MULTI_View ($fieldname, $value, $specs);
    } else if ($specs["method"]=="other") {
      if ($specs["show"]=="visible") {
        if (is_array($specs["values"])) {
          $first = "";
          foreach ($specs["values"] as $visual => $internal) {
             if ($first == "") { $first = $visual; }
             if ($value == $internal) {
                return FORMS_ML_Filter($visual);
             }
          }
          return $value;
        }
      } else {
        return $value;
      }
    } else {
      $customfunc = "";
      if (substr($specs["method"], 0, 6)=="custom") {
        global $myconfig;
        $custommethod = substr($specs["method"], 6);
        $customfunc = $myconfig[IMS_SuperGroupName()]["customlistmethod"][$custommethod]["showfunc"];
      }
      if ($customfunc && is_callable($customfunc)) {
        return $customfunc($specs["values"], $value, $specs["show"]);
      } else {
        if ($specs["show"]=="visible") {
          if (is_array($specs["values"])) {
            $first = "";
            foreach ($specs["values"] as $visual => $internal) {
               if ($first == "") { $first = $visual; }
               if ($value == $internal) {
                  return FORMS_ML_Filter($visual);
               }
            }
            return $first;
          }
        } else {
          return $value;
        }
      }
    }
  } else if ($specs["type"] == "strml" || $specs["type"] == "txtml") {
    return FORMS_ML_Filter($value, $lang);
  } else {  
    return $value;
  }
}

function FORMS_AutoTemplate ($fields="")
{
  if (is_array($fields) && count($fields)) {
    $formtemplate = '
        <table>
    ';
    foreach ($fields as $id => $specs) {
      if ($specs["name"]) {
        $formtemplate .= '<tr><td><font face="arial" size=2><b>'.$specs["name"].':</b></font></td><td><font face="arial" size=2>[[['.$id.']]]</font></td></tr>';
      } else {
        $formtemplate .= '<tr><td><font face="arial" size=2><b>'.$id.':</b></font></td><td><font face="arial" size=2>[[['.$id.']]]</font></td></tr>';
      }
    }
    $formtemplate .= '
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
        </table>
    ';    
  } else if ($fields=="okcancel") {
    $formtemplate = '
        <table>
          <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
        </table>
    ';
  } else  {
    $formtemplate = '
        <table>
          <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
        </table>
    ';
  }
  return $formtemplate;
}

function FORMS_HandleForm()
{
  FLEX_LoadSupportFunctions(IMS_SuperGroupName());
  global $command;
  if ($command=="showsuperform") {
    FORMS_ShowSuperForm();
  } else if ($command=="handlesuperform") {
    FORMS_HandleSuperForm();
  } else if ($command=="execute") {
    global $encspec;
    $enc = SHIELD_Decode ($encspec);
    $input = $enc["input"];
    eval ($enc["code"]);
  }
}

function FORMS_GenerateInlineForm($form, $extraspecs = array()) 
{
  /* Show and/or handle an inline form.
   * Can be used to display a form inside a CMS page.
   * This function will return (html) content and will not flush, exit or otherwise behave as a "standalone" page.
   * It only redirects back to itself, not to form.php
   *
   * This function returns a string containing three types of output:
   * - If the form was successfully submitted, whatever was produced by the "postcode" (echo'd)
   *   (provided that "gotook" was left empty and that the postcode did not do any redirects).
   * - If there was an error while processing the submit, the error message.
   * - The html for the form (in some cases with previous user input).
   *   If the form was submitted and succesfully processed, the form will not be shown again,
   *     but this behaviour can be changed by setting $extraspecs["showformwithresult"] to true.
   *   If there was an error, the form is shown with previous user input. But the form will not 
   *     be shown if FORMS_ShowError was called with $allowretry = "no".
   * 
   */

  $content = "";
  $command = $_POST["command"];
  global $iaminline;
  $iaminline = true;

  if ($command == "handlesuperform") {
    global $runandcapture;
    $runandcapture_old = $runandcapture;
    $runandcapture = true;
    ob_start();
    FORMS_HandleForm();
    $result = ob_get_clean();
    $runandcapture = $runandcapture_old;
  }

  if ($command == "showsuperform" || $command == "" || $extraspecs["showformwithresult"]) {
    if ( !$form["formactionurl"] ) // by JG and MdG to enable another URL for ajax
      $form["formactionurl"] = N_MyFullUrl();
    if (!$form["gotook"]) $form["gotook"] = "nowhere";
    if (!$form["gotocancel"]) $form["gotocancel"] = "nowhere";
   
    $htmlform .= FORMS_GenerateSuperForm($form);
  }

  $template = $extraspecs["outputtemplate"];
  if (!$template) $template = "[[[result]]][[[form]]]";
  $template = IMS_CleanupTags($template);
  $template = IMS_TagReplace($template, "result", $result);
  $template = IMS_TagReplace($template, "error", ""); // obsolete, has become part of formtemplate
  $template = IMS_TagReplace($template, "form", $htmlform);

  $iaminline = false;

  return $template;
}

function FORMS_Test()
{
  $metaspec["fields"]["mini"]["type"] = "smallstring";
  $metaspec["fields"]["mini"]["default"] = "enter name";

  $metaspec["fields"]["name"]["type"] = "string";
  $metaspec["fields"]["name"]["default"] = "enter name";
  $metaspec["fields"]["name"]["title"] = "Name"; // used by composite field

  $metaspec["fields"]["required"]["type"] = "string";
  $metaspec["fields"]["required"]["title"] = "Required";
  $metaspec["fields"]["required"]["required"] = true;

  $metaspec["fields"]["email"]["type"] = "bigstring";
  $metaspec["fields"]["email"]["default"] = "enter e-mail";
  $metaspec["fields"]["email"]["title"] = "Email";

  $metaspec["fields"]["email2"]["type"] = "smallstrml";
  $metaspec["fields"]["email2"]["default"] = "kleine meertalige string";

  $metaspec["fields"]["email3"]["type"] = "strml";
  $metaspec["fields"]["email3"]["default"] = "meertalige string";

  $metaspec["fields"]["thefile1"]["type"] = "file";

  $metaspec["fields"]["thefile2"]["type"] = "file";

  $metaspec["fields"]["thefile3"]["type"] = "file";

  $metaspec["fields"]["list"]["type"] = "list";
  $metaspec["fields"]["list"]["default"] = "2";
  $metaspec["fields"]["list"]["show"] = "visible";
  $metaspec["fields"]["list"]["values"]["Keuze 1"] = "1";
  $metaspec["fields"]["list"]["values"]["Keuze 2"] = "2";
  $metaspec["fields"]["list"]["values"]["Keuze 3"] = "3";

  $metaspec["fields"]["list2"] = $metaspec["fields"]["list"];
  $metaspec["fields"]["list2"]["title"] = "List (method=radiohor)";
  $metaspec["fields"]["list2"]["method"] = "radiohor";

  $metaspec["fields"]["list3"] = $metaspec["fields"]["list"];
  $metaspec["fields"]["list3"]["title"] = "List (method=radiover)";
  $metaspec["fields"]["list3"]["method"] = "radiover";

  $metaspec["fields"]["list4"] = $metaspec["fields"]["list"];
  $metaspec["fields"]["list4"]["title"] = "List (method=other)";
  $metaspec["fields"]["list4"]["method"] = "other";

  $metaspec["fields"]["list5"]["type"] = "list";
  $metaspec["fields"]["list5"]["title"] = "List with empty value";
  $metaspec["fields"]["list5"]["show"] = "visible";
  $metaspec["fields"]["list5"]["values"]["&lt;geen&gt;"] = "";
  $metaspec["fields"]["list5"]["values"]["Sneezy"] = "sn";
  $metaspec["fields"]["list5"]["values"]["Sleepy"] = "sl";
  $metaspec["fields"]["list5"]["values"]["Dopey"] = "do";

  $metaspec["fields"]["list6"] = $metaspec["fields"]["list5"];
  $metaspec["fields"]["list6"]["title"] = "Required list with empty value";
  $metaspec["fields"]["list6"]["required"] = true;

  $metaspec["fields"]["multilist"] = $metaspec["fields"]["list"];
  $metaspec["fields"]["multilist"]["title"] = "Multivalued list (method=multi)";
  $metaspec["fields"]["multilist"]["method"] = "multi";

  $metaspec["fields"]["multilist2"]["type"] = "multilist";
  $metaspec["fields"]["multilist2"]["title"] = "Multivalued list (type=multilist)";
  $metaspec["fields"]["multilist2"]["source"] = "list"; // Known issue: FORMS_ShowValue will not work if the source is not in ims_fields.

  $metaspec["fields"]["longlist"]["type"] = "list";
  $metaspec["fields"]["longlist"]["title"] = "Long list";
  $metaspec["fields"]["longlist"]["show"] = "visible";
  foreach (N_Files("html::/openims") as $file) {
    $metaspec["fields"]["longlist"]["values"][N_KeepBefore($file, ".")] = $file;
  }
  $metaspec["fields"]["longlist2"]["type"] = "list";
  $metaspec["fields"]["longlist2"]["title"] = "Long list with empty value";
  $metaspec["fields"]["longlist2"]["show"] = "visible";
  $metaspec["fields"]["longlist2"]["values"]["Kies..."] = "";
  foreach (N_Files("html::/openims") as $file) {
    $metaspec["fields"]["longlist2"]["values"][N_KeepBefore($file, ".")] = $file;
  }

  $metaspec["fields"]["longmultilist"] = $metaspec["fields"]["longlist"];
  $metaspec["fields"]["longmultilist"]["title"] = "Long multivalued list (method=multi)";
  $metaspec["fields"]["longmultilist"]["method"] = "multi";

  $metaspec["fields"]["longmultilist2"]["type"] = "multilist";
  $metaspec["fields"]["longmultilist2"]["title"] = "Long multivalued list (type=multilist)";
  $metaspec["fields"]["longmultilist2"]["source"] = "longlist"; // Known issue: FORMS_ShowValue will not work if the source is not in ims_fields.

  $metaspec["fields"]["longmultilist3"] = $metaspec["fields"]["longlist2"];
  $metaspec["fields"]["longmultilist3"]["title"] = "Long multivalued list with empty value (method=multi)";
  $metaspec["fields"]["longmultilist3"]["method"] = "multi";

  $metaspec["fields"]["user"]["type"] = "fk";
  $metaspec["fields"]["user"]["title"] = "Foreign key";
  $metaspec["fields"]["user"]["specs"]["fk"]["table"] = "shield_".IMS_SuperGroupName()."_users";
  $metaspec["fields"]["user"]["specs"]["fk"]["field1"] = "name";

  $metaspec["fields"]["users"]["type"] = "multilist";
  $metaspec["fields"]["users"]["title"] = "Multivalued foreign key (will work ONLY with autocomplete)";
  $metaspec["fields"]["users"]["source"] = "user"; // Known issue: FORMS_ShowValue will not work if the source is not in ims_fields.

  $metaspec["fields"]["yes"]["type"] = "yesno";
  $metaspec["fields"]["yes"]["default"] = true;

  $metaspec["fields"]["no"]["type"] = "yesno";
  $metaspec["fields"]["no"]["default"] = false;

  $metaspec["fields"]["date"]["type"] = "date";
  $metaspec["fields"]["date"]["default"] = time();

  $metaspec["fields"]["time"]["type"] = "time";
  $metaspec["fields"]["time"]["default"] = time();

  $metaspec["fields"]["datetime"]["type"] = "datetime";
  $metaspec["fields"]["datetime"]["default"] = time();

  $metaspec["fields"]["text1"]["type"] = "smalltext";
  $metaspec["fields"]["text1"]["default"] = "smalltext";
  $metaspec["fields"]["text1"]["title"] = "Small text";

  $metaspec["fields"]["text2"]["type"] = "text";
  $metaspec["fields"]["text2"]["default"] = "text";

  $metaspec["fields"]["text3"]["type"] = "bigtext";
  $metaspec["fields"]["text3"]["default"] = "bigtext";

  $metaspec["fields"]["text4"]["type"] = "bigtxtml";
  $metaspec["fields"]["text4"]["default"] = "grote meertalige tekst";

  $metaspec["fields"]["error"]["type"] = "error";

  $metaspec["fields"]["composite"]["type"] = "composite";
  $metaspec["fields"]["composite"]["default"] = array("name" => "L. Flobbe", "email" => "l.flobbe@example.com");
  $metaspec["fields"]["composite"]["fields"] = array("name", "email", "text1");
  $metaspec["fields"]["composite"]["fieldspec"] = $metaspec["fields"]; // Any field not defined in ims_fields should be put in "fieldspec"!

  $formtemplate =  '<table>';
  $formtemplate .= '<tr><td>Small:</td><td>[[[mini]]]</td></tr>';
  $formtemplate .= '<tr><td>Name:</td><td>[[[name]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{required}}}:</td><td>[[[required]]]</td></tr>';
  $formtemplate .= '<tr><td>E-mail:</td><td>[[[email]]]</td></tr>';
  $formtemplate .= '<tr><td>Multilingual string:</td><td>[[[email2]]]</td></tr>';
  $formtemplate .= '<tr><td>Multilingual string:</td><td>[[[email3]]]</td></tr>';
  $formtemplate .= '<tr><td>List:</td><td>[[[list]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{list2}}}:</td><td>[[[list2]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{list3}}}:</td><td>[[[list3]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{list4}}}:</td><td>[[[list4]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{list5}}}:</td><td>[[[list5]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{list6}}}:</td><td>[[[list6]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{multilist}}}:</td><td>[[[multilist]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{multilist2}}}:</td><td>[[[multilist2]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{longlist}}}:</td><td>[[[longlist]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{longlist2}}}:</td><td>[[[longlist2]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{longmultilist}}}:</td><td>[[[longmultilist]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{longmultilist2}}}:</td><td>[[[longmultilist2]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{longmultilist3}}}:</td><td>[[[longmultilist3]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{user}}}:</td><td>[[[user]]]</td></tr>';
  $formtemplate .= '<tr><td>{{{users}}}:</td><td>[[[users]]]</td></tr>';
  $formtemplate .= '<tr><td>Yes:</td><td>[[[yes]]]</td></tr>';
  $formtemplate .= '<tr><td>No:</td><td>[[[no]]]</td></tr>';
  $formtemplate .= '<tr><td>File 1:</td><td>[[[thefile1]]]</td></tr>';
  $formtemplate .= '<tr><td>File 2:</td><td>[[[thefile2]]]</td></tr>';
  $formtemplate .= '<tr><td>File 3:</td><td>[[[thefile3]]]</td></tr>';
  $formtemplate .= '<tr><td>Error:</td><td>[[[error]]]</td></tr>';
  $formtemplate .= '<tr><td>Date:</td><td>[[[date]]]</td></tr>';
  $formtemplate .= '<tr><td>Time:</td><td>[[[time]]]</td></tr>';
  $formtemplate .= '<tr><td>Datetime:</td><td>[[[datetime]]]</td></tr>';
  $formtemplate .= '<tr><td>Small text:</td><td>[[[text1]]]</td></tr>';
  $formtemplate .= '<tr><td>Text:</td><td>[[[text2]]]</td></tr>';
  $formtemplate .= '<tr><td>Big text:</td><td>[[[text3]]]</td></tr>';
  $formtemplate .= '<tr><td>Multilingual text:</td><td>[[[text4]]]</td></tr>';
  $formtemplate .= '<tr><td>Composite:</td><td>[[[composite]]]</td></tr>';
  $formtemplate .= '</table>';
  $formtemplate .= '[[[ok]]] [[[cancel]]] <input type="submit" name="ok" value="OOK OK"><br>';

  $form ["metaspec"] = $metaspec;
  $form ["formtemplate"] = $formtemplate;
  $form ["title"] = "This is the title of the form";
  $form ["precode"] = '
    global $myconfig;
    $myconfig[IMS_SuperGroupName()]["autocompletefields"] = $input["autocompletefields"];
    $data = MB_Load ("test", "test");
    if (!is_array($data)) $data = array();
  ';
  $form ["postcode"] = '
    MB_Save ("test", "test", $data); 
    $gotook = "closeme"; // Do not refresh my code tester
  ';

  foreach (array("no", "yes") as $autocompletefields) {
    $form["input"]["autocompletefields"] = $autocompletefields;
    $url = FORMS_Url ($form);
    echo "<a href=\"$url\">Edit link (autocomplete = $autocompletefields)</a><br/>";
  }

  $form2 = $form;
  $form2["formtemplate"] = str_replace("[[[", "(((", str_replace("]]]", ")))", $formtemplate));
  $url2 = FORMS_Url ($form2);
  echo "<a href=\"$url2\">View link</a><br/>";

  $form3=array();
  $form3["postcode"] = 'MB_Delete("test", "test"); $gotook="closeme";';
  $url3 = FORMS_Url ($form3);
  echo "<a href=\"$url3\">Delete stored data</a><br/>";

  global $myconfig;
  // Changing this setting in precode/postcode is already too late, must be changed in siteconfig.
  echo 'Please change your config to <code>$myconfig["'.IMS_SuperGroupName().'"]["showerrorsinform"] = "'.($myconfig[IMS_SuperGroupName()]["showerrorsinform"] == "yes" ? "no" : "yes") . '";</code> and repeat this test.<br/>';

//  echo FORMS_GenerateSuperForm ($form); 
}

function FORMS_Integrate ($rec, $path, $data)
{
  if (is_array($data)) {
    reset ($data);
    while (list($key, $value)=each($data)) {
      eval ('$rec'.$path."[\"$key\"] = \$data[\"$key\"];");
    }
  }
  return $rec;
}

function FORMS_GenerateEditLink ($title, $content, $yellow, $table, $key, $path, $metaspec, $formtemplate, $width=400, $height=300, $left=200, $top=200, $style="ims_link") // obsolete
{
  $form["title"]=$title;
  $form["metaspec"]=$metaspec;
  $form["formtemplate"]=$formtemplate;
  $form["autoobject"]= MB_Load ($table, $key);
  $form["input"]["table"] = $table;
  $form["input"]["key"] = $key;
  $form["input"]["path"] = $path;
  $form["precode"] = '$rec = MB_Load ($input["table"], $input["key"]); eval (\'$data = $rec\'.$input["path"].";");';
  $form["postcode"] = '$rec = &MB_Ref ($input["table"], $input["key"]); uuse("forms"); $rec = FORMS_Integrate ($rec, $input["path"], $data);';
  $url = FORMS_URL ($form);
  return "<a class=\"$style\" title=\"$yellow\" href=\"$url\">$content</a>";
}


function FORMS_GenerateEditExecuteLink ($thecode, $input, $title, $content, $yellow, $table, $key, $path, $metaspec, $formtemplate, $width=400, $height=300, $left=200, $top=200, $style="ims_link" , $return_url = false )  // obsolete
{
  $form["title"]=$title;
  $form["metaspec"]=$metaspec;
  $form["formtemplate"]=$formtemplate;
  $form["autoobject"]= MB_Load ($table, $key);
  $form["input"] = $input;
  $form["input"]["table"] = $table;
  $form["input"]["key"] = $key;
  $form["input"]["path"] = $path;
  $form["precode"] = '$rec = MB_Load ($input["table"], $input["key"]); eval (\'$data = $rec\'.$input["path"].";");';
  $form["postcode"] = '
    uuse("forms"); 
    $rec = MB_Load ($input["table"], $input["key"]); 
    MB_Save ($input["table"], $input["key"], FORMS_Integrate ($rec, $input["path"], $data));
    $rec = &MB_Ref ($input["table"], $input["key"]); 
    '.$thecode.'
  ';
  $url = FORMS_URL( $form );
  if ( $return_url )
    return $url;
  return "<a class=\"$style\" title=\"$yellow\" href=\"$url\">$content</a>";
}

function FORMS_GenerateExecuteLink ($thecode, $input, $title, $content, $yellow, $metaspec, $formtemplate, $width=400, $height=300, $left=200, $top=200, $style="ims_link")  // obsolete
{
  $form["title"]=$title;
  $form["metaspec"]=$metaspec;
  $form["formtemplate"]=$formtemplate;
  $form["input"] = $input;
  $form["precode"] = "";
  $form["postcode"] = $thecode;
  $url = FORMS_URL ($form);
  return "<a class=\"$style\" title=\"$yellow\" href=\"$url\">$content</a>";
}

function FORMS_HandleSuperForm()
{
  foreach ($GLOBALS as $name => $value) {
    if (substr ($name, 0, 6)=="field_") {
      if (!is_array ($GLOBALS[$name])) $GLOBALS[$name] = addslashes (N_UTF2HTML (stripslashes ($GLOBALS[$name])));
    }
  }
  global $data, $input;
  global $encspec, $ok, $cancel, $trueok;

  $trueok = $ok; // allows detection of different OK buttons

  if (!$cancel) $ok=true; // handle enter key

  global $iampopup;
  $fullspec = SHIELD_Decode ($encspec);
  $input = $fullspec["input"];
  $metaspec = $fullspec["metaspec"];
  global $thereloaddata;

  if (is_array ($metaspec["fields"])) for ($i=0; $i<10; $i++) { // allow 10 levels of cloning
    foreach ($metaspec["fields"] as $name => $specs) {
      if ($specs["type"]=="clone") {
        $oldtitle = $metaspec["fields"][$name]["title"];
        $allfields = MB_Load ("ims_fields", IMS_SuperGroupName());
        $tmp_required = $specs["required"];
        $metaspec["fields"][$name] = $allfields[$specs["source"]];
        $metaspec["fields"][$name]["required"] = $tmp_required;
        if ($oldtitle) $metaspec["fields"][$name]["title"] = $oldtitle;
      }
    }
  }
  $metaspec["fields"] = FORMS_EnhanceAllFieldspecs($metaspec["fields"]);

  $postcode = $fullspec["postcode"];
  $iampopup = $fullspec["popup"];
  if ($fullspec["gotook"]) $gotook = $fullspec["gotook"]; else $gotook="closeme&refreshparent";
  if ($fullspec["gotocancel"]) $gotocancel = $fullspec["gotocancel"]; else $gotocancel="closeme";
  if ($fullspec["foundok"] && !$cancel) $ok = true;
  if ($ok) {
    $fields = $metaspec["fields"];
    if (!is_array($fields)) $fields = array();

    global $myconfig;
    if($myconfig[IMS_SuperGroupName()]["formfieldmaxrepeat"]) {
      $multiloopmax = $myconfig[IMS_SuperGroupName()]["formfieldmaxrepeat"];
    } else {
      $multiloopmax = 10;
    }
    for ($multiloop=1; $multiloop<=$multiloopmax; $multiloop++) { // thb, OK PRESSED (READ)

      reset ($fields);
      while (list ($field,)=each($fields)) {      

        if ($multiloop > 1) {
           $fieldname = $field."__".$multiloop;
        } else {
           $fieldname = $field;
        }

        if (IMS_TagCount ($fullspec["formtemplate"], $field) >= $multiloop) { // field has been used in this form

          if ($metaspec["fields"][$field]["type"]=="code") { // CODE_READ
            eval ('global $field_'.$fieldname.';');
            eval ('$value = $field_'.$fieldname.';');
            $value = str_replace(chr(160), chr(32), stripcslashes($value));
            $value = str_replace(chr(13).chr(10), chr(10), $value);
            $value = str_replace(chr(13), "", $value );
            $value = str_replace(chr(10), chr(13).chr(10), $value);
            //qqq eval ($metaspec["fields"][$field]["read"]);
            $thereloaddata = $data; // in case interpretation code calls FORMS_ShowError / FORMS_HandleErrors
            $value = N_Eval ($metaspec["fields"][$field]["read"], get_defined_vars(), "value");
            $data[$fieldname] = $value;

          } else if ($metaspec["fields"][$field]["type"]=="hyperlink") {
            eval ('global $field_url_'.$fieldname.';');
            eval ('$hyperspec["url"] = $field_url_'.$fieldname.';');
            eval ('global $field_title_'.$fieldname.';');
            eval ('$hyperspec["title"] = $field_title_'.$fieldname.';');
            eval ('global $field_text_'.$fieldname.';');
            eval ('$hyperspec["text"] = $field_text_'.$fieldname.';');
            $hyperspec["url"] = str_replace(chr(160), chr(32), stripcslashes($hyperspec["url"]));
            $hyperspec["url"] = str_replace(chr(13).chr(10), chr(10), $hyperspec["url"]);
            $hyperspec["url"] = str_replace(chr(13), "", $hyperspec["url"]);
            $hyperspec["url"] = str_replace(chr(10), chr(13).chr(10), $hyperspec["url"]);
            $hyperspec["title"] = str_replace(chr(160), chr(32), stripcslashes($hyperspec["title"]));
            $hyperspec["title"] = str_replace(chr(13).chr(10), chr(10), $hyperspec["title"]);
            $hyperspec["title"] = str_replace(chr(13), "", $hyperspec["title"]);
            $hyperspec["title"] = str_replace(chr(10), chr(13).chr(10), $hyperspec["title"]);
            $hyperspec["text"] = str_replace(chr(160), chr(32), stripcslashes($hyperspec["text"]));
            $hyperspec["text"] = str_replace(chr(13).chr(10), chr(10), $hyperspec["text"]);
            $hyperspec["text"] = str_replace(chr(13), "", $hyperspec["text"]);
            $hyperspec["text"] = str_replace(chr(10), chr(13).chr(10), $hyperspec["text"]);
            $data[$fieldname] = serialize ($hyperspec);

          } else if ($metaspec["fields"][$field]["type"]=="composite") {
            eval ('global $field_'.$fieldname.';');
            eval ('$servalue = $field_'.$fieldname.';');
            $servalue = str_replace(chr(160), chr(32), stripcslashes($servalue));
            $servalue = str_replace(chr(13).chr(10), chr(10), $servalue);
            $servalue = str_replace(chr(13), "", $servalue );
            $servalue = str_replace(chr(10), chr(13).chr(10), $servalue);
            $data[$fieldname] = unserialize($servalue);

          } else if ($metaspec["fields"][$field]["type"]=="image") {
            eval ('global $field_'.$fieldname.';');
            eval ('$value = $field_'.$fieldname.';');
            $data[$fieldname] = $value;

          } else if ($metaspec["fields"][$field]["type"]=="diskfile") {
            eval ('global $field_'.$fieldname.';');
            eval ('$value = $field_'.$fieldname.';');
            $data[$fieldname] = $value;

          } else if ($metaspec["fields"][$field]["type"]=="html") {
            eval ('global $field_'.$fieldname.';');
            eval ('$value = $field_'.$fieldname.';');
            $data[$fieldname] = $value;
 
          } else if ($metaspec["fields"][$field]["type"]=="date") {
            if ($metaspec["fields"][$field]["specs"]["edit"] == "html") {
              if (!$GLOBALS["field_year_".$fieldname] || !$GLOBALS["field_month_".$fieldname] || !$GLOBALS["field_day_".$fieldname]) {
                $data[$fieldname] = "";
              } else {
                $data[$fieldname] = N_BuildDate($GLOBALS["field_year_".$fieldname], $GLOBALS["field_month_".$fieldname], $GLOBALS["field_day_".$fieldname]);
              }
            } else {
              eval ('global $field_date_'.$fieldname.';');
              eval ('$date = $field_date_'.$fieldname.';');
              if (!($date)) {
                $data[$fieldname] = "";
              } else {
                $data[$fieldname] = JSCAL_Decode ($date) + 12*3600; // simulate builddate function
              }
            }
          } else if ($metaspec["fields"][$field]["type"]=="time") {
            eval ('global $field_hour_'.$fieldname.';');
            eval ('$hour = $field_hour_'.$fieldname.';');
            eval ('global $field_minute_'.$fieldname.';');
            eval ('$minute = $field_minute_'.$fieldname.';');
            $data[$fieldname] = N_BuildTime ($hour, $minute);

          } else if ($metaspec["fields"][$field]["type"]=="datetime") {
            if ($metaspec["fields"][$field]["specs"]["edit"] == "html") {
              if (!$GLOBALS["field_year_".$fieldname] || !$GLOBALS["field_month_".$fieldname] || !$GLOBALS["field_day_".$fieldname]) {
                $data[$fieldname] = "";
              } else {
                $data[$fieldname] = N_BuildDateTime($GLOBALS["field_year_".$fieldname], $GLOBALS["field_month_".$fieldname], $GLOBALS["field_day_".$fieldname], $GLOBALS["field_hour_".$fieldname], $GLOBALS["field_minute_".$fieldname], 0);
              }
            } else {
              eval ('global $field_date_'.$fieldname.';');
              eval ('$date = $field_date_'.$fieldname.';');
              $data[$fieldname] = JSCAL_Decode ($date);
            }

          } else if ($metaspec["fields"][$field]["type"]=="bigfile") { 
            if (strpos (N_UTF2HTML($_POST["filename_".$fieldname]), "'")) {
              // LF 20071227: Moved UTF-decoding from "as late as possible" to "as early as possible".  
              // Reason: This solves a problem with filenames containing a-greve (which has a "space"  
              // in its multibyte UTF representation).  
              $value = str_replace(chr(160), chr(32), stripcslashes(N_UTF2HTML($_POST["filename_".$fieldname])));
              $value = str_replace(chr(13).chr(10), chr(10), $value);
              $value = str_replace(chr(13), "", $value );             
              $files[$fieldname]["name"] = str_replace(chr(10), chr(13).chr(10), $value);
              if (strpos (" ".$files[$fieldname]["name"], "\\")) {
                while (strpos (" ".$files[$fieldname]["name"], "\\")) {
                  $files[$fieldname]["name"] = N_KeepAfter ($files[$fieldname]["name"], "\\");
                }
              } else if (strpos (" ".$files[$fieldname]["name"], "/")) {
                while (strpos (" ".$files[$fieldname]["name"], "/")) {
                  $files[$fieldname]["name"] = N_KeepAfter ($files[$fieldname]["name"], "/");
                }
              }
            } else {
              $value = str_replace(chr(160), chr(32), stripcslashes(N_UTF2HTML($_FILES["field_".$fieldname]["name"])));
              $value = str_replace(chr(13).chr(10), chr(10), $value);
              $value = str_replace(chr(13), "", $value );
              $files[$fieldname]["name"] = str_replace(chr(10), chr(13).chr(10), $value);
            }
            $files[$fieldname]["tmpfilename"] = $_FILES["field_".$fieldname]["tmp_name"];
            $files[$fieldname]["bigfile"] = "yes";
          } else if ($metaspec["fields"][$field]["type"]=="file") { 
            if (strpos (N_UTF2HTML($_POST["filename_".$fieldname]), "'")) {
              $value = str_replace(chr(160), chr(32), stripcslashes(N_UTF2HTML($_POST["filename_".$fieldname])));
              $value = str_replace(chr(13).chr(10), chr(10), $value);
              $value = str_replace(chr(13), "", $value );             
              $files[$fieldname]["name"] = str_replace(chr(10), chr(13).chr(10), $value);
              if (strpos (" ".$files[$fieldname]["name"], "\\")) {
                while (strpos (" ".$files[$fieldname]["name"], "\\")) {
                  $files[$fieldname]["name"] = N_KeepAfter ($files[$fieldname]["name"], "\\");
                }
              } else if (strpos (" ".$files[$fieldname]["name"], "/")) {
                while (strpos (" ".$files[$fieldname]["name"], "/")) {
                  $files[$fieldname]["name"] = N_KeepAfter ($files[$fieldname]["name"], "/");
                }
              }
            } else {
              $value = str_replace(chr(160), chr(32), stripcslashes(N_UTF2HTML($_FILES["field_".$fieldname]["name"])));
              $value = str_replace(chr(13).chr(10), chr(10), $value);
              $value = str_replace(chr(13), "", $value );
              $files[$fieldname]["name"] = str_replace(chr(10), chr(13).chr(10), $value);
            }
            $files[$fieldname]["content"] = N_ReadFile ($_FILES["field_".$fieldname]["tmp_name"]);
            $files[$fieldname]["tmpfilename"] = $_FILES["field_".$fieldname]["tmp_name"];

          } else if ($metaspec["fields"][$field]["type"] == "strml" || $metaspec["fields"][$field]["type"] == "txtml") {
            if (array_key_exists("field_$fieldname", $_REQUEST)) { // The field decided to behave as a normal text field
              $value = N_UTF2HTML($_REQUEST["field_$fieldname"]);
              $value = str_replace(chr(160), chr(32), stripcslashes($value));
              $value = str_replace(chr(13).chr(10), chr(10), $value);
              $value = str_replace(chr(13), "", $value );
              $value = str_replace(chr(10), chr(13).chr(10), $value);
              $data[$fieldname] = $value;
            } else {
              global $myconfig;
              $langs = $myconfig[IMS_SuperGroupName()]["ml"]["languages"];
              if (!$langs) $langs = array("nl", "en");
              $langvalues = array();
              $morelanguages = false;
              foreach ($langs as $nr => $lang) {
                $value = N_UTF2HTML($_REQUEST["field_{$lang}_{$fieldname}"]);
                $value = str_replace(chr(160), chr(32), stripcslashes($value));
                $value = str_replace(chr(13).chr(10), chr(10), $value);
                $value = str_replace(chr(13), "", $value);
                $value = str_replace(chr(10), chr(13).chr(10), $value);
                $langvalues[$lang] = $value;
                if ($value && $nr >= 1) $morelanguages = true;
              }
              if ($morelanguages) { // Only save as ML string if more than just the first (default) languages has been used
                $data[$fieldname] = FORMS_ML_Encode($langvalues);
              } else {
                $data[$fieldname] = $langvalues[$langs[0]];
              }
            }
          } else {
            eval ('global $field_'.$fieldname.';');
            eval ('$value = $field_'.$fieldname.';');
            $value = str_replace(chr(160), chr(32), stripcslashes($value));
            $value = str_replace(chr(13).chr(10), chr(10), $value);
            $value = str_replace(chr(13), "", $value );
            $value = str_replace(chr(10), chr(13).chr(10), $value);
            $data[$fieldname] = $value;

            //sbr (verplichte string velden, waar het cijfer 0 is ingevuld zijn gevuld)
            if ($value." " == " ") { 
              eval ('global $other_field_'.$fieldname.';');
              eval ('$value = $other_field_'.$fieldname.';');
              $value = str_replace(chr(160), chr(32), stripcslashes($value));
              $value = str_replace(chr(13).chr(10), chr(10), $value);
              $value = str_replace(chr(13), "", $value );
              $value = str_replace(chr(10), chr(13).chr(10), $value);
              $data[$fieldname] = $value;
            }
          }
        }
      } // while (list ($field,)=each($fields)) {
    } // for ($multiloop=1; $multiloop<=$multiloopmax; $multiloop++) { // thb, OK PRESSED (READ)

    // Validate fields
    for ($multiloop=1; $multiloop<=$multiloopmax; $multiloop++) { // thb
      reset ($fields);

      while (list ($field,)=each($fields)) {

        if ($multiloop > 1) {
           $fieldname = $field."__".$multiloop;
        } else {
           $fieldname = $field;
        }
      
        if (IMS_TagCount ($fullspec["formtemplate"], $field) >= $multiloop) {
          $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
          $fieldtitle = $allfields[$field]["title"];
          if (!$fieldtitle) $fieldtitle = $metaspec["fields"][$field]["title"]; 
          if (!$fieldtitle) $fieldtitle = $allfields[str_replace ("meta_","",$field)]["title"];
          if (!$fieldtitle) $fieldtitle = $field;
          $type = $metaspec["fields"][$field]["type"];
          if ($metaspec["fields"][$field]["required"]) {
            // DVB: add filename check for required bigfile or file field
            if ($data[$fieldname].$files[$fieldname]["name"]." " == " ") {//sbr verplichte string velden  met cijfer 0 als inhoud
              if ($myconfig[IMS_SupergroupName()]["requiredfieldbyname"] != "no")
                FORMS_CollectError($fieldname, $fieldtitle, ML("verplicht veld is niet ingevuld", "required field is empty"), true);
              else
                FORMS_CollectError("", "", ML("Een of meer verplichte velden zijn niet ingevuld", "One or more required fields are empty"), true);
              if ($myconfig[IMS_SuperGroupName()]["showerrorsinform"] != "yes") FORMS_HandleErrors($data); // old behaviour is to immediately stop execution without validating the other fields
            }
          }
          if ($metaspec["fields"][$field]["validationcode"]) {
            $thereloaddata = $data; // in case the validation code calls FORMS_ShowError / FORMS_HandleErrors (instead of setting $error)
            $error = FORMS_Validate ($data[$fieldname], $data, $metaspec["fields"][$field]["validationcode"]);
            if ($error) {
              FORMS_CollectError($fieldname, $fieldtitle, $error, true);
              if ($myconfig[IMS_SuperGroupName()]["showerrorsinform"] != "yes") FORMS_HandleErrors($data); // old behaviour is to immediately stop execution without validating the other fields
            }
          }
        }
      } // while (list ($field,)=each($fields)) {
    } // for ($multiloop=1; $multiloop<=99; $multiloop++)
    if ($fullspec["showerrors"]["donothandlevalidationerrors"] != "yes" || (stripos($fullspec["postcode"], "FORMS_HandleErrors") === false) && stripos($fullspec["postcode"], "FORMS_HasErrors") === false) {
      FORMS_HandleErrors($data);
    }

    global $myconfig;
    if($myconfig[IMS_SuperGroupName()]["formfieldmaxrepeat"]) {
      $multiloopmax = $myconfig[IMS_SuperGroupName()]["formfieldmaxrepeat"];
    } else {
      $multiloopmax = 10;
    }
    for ($multiloop=1; $multiloop<=$multiloopmax; $multiloop++) { // thb SHOW

    reset ($fields);
    while (list ($field,)=each($fields)) {      

      if ($multiloop > 1) {
         $fieldname = $field."__".$multiloop;
      } else {
         $fieldname = $field;
      }

      if (IMS_TagCount ($fullspec["formtemplate"], $field) >= $multiloop) {

        if ($metaspec["fields"][$field]["type"]=="auto") { // CODE_READ

          $data[$fieldname] = FORMS_ShowValueTEXT ($value, $metaspec["fields"][$field], $data, $fullspec["autoobject"]);

        }
      }
    }

    } // for ($multiloop=1; $multiloop<=99; $multiloop++)


    uuse ("ims");
    uuse ("soap");
    $thereloaddata = $data;
    $portal = $fullspec["portal"]; 
    eval ($postcode);
    FORMS_HandleErrors();
    N_Redirect ($gotook);
  } else {
    N_Redirect ($gotocancel);
  }
}

function FORMS_Validate ($input, $data, $code) 
{
  $error = "";
  eval ($code); // No need for N_Eval because it's in a function and followed by a return
  return $error;
}

function FORMS_ExpandTree (&$tree, $list, $current_id="", $hide_id="") 
{
  global $treelevel;
  reset($list);
  while (list ($object_id)=each($list)) {
    if ($object_id!=$hide_id) {
      $object = TREE_AccessObject ($tree, $object_id);
      $item = "";
      for ($i=0; $i<$treelevel; $i++) $item .= "&nbsp;&gt;&nbsp;";
      $item .= $object["shorttitle"];
      if ($object_id==$current_id) {
        $result .= '<option value="'.$object_id.'" selected>'.$item.'</option>';

      } else {
        $result .= '<option value="'.$object_id.'">'.$item.'</option>';
      }
      if ($object["children"]) {
        $treelevel++;
        $result .= FORMS_ExpandTree ($tree, $object["children"], $current_id, $hide_id);
        $treelevel--;
      }
    }
  }
  return $result;
}

function FORMS_GenerateSuperForm ($fullspec)
{
  N_Debug ("FORMS_GenerateSuperForm (...)");

  global $data, $input;
  if (!$fullspec["gotook"]) $fullspec["gotook"] = N_MyFullURL();
  if (!$fullspec["gotocancel"]) $fullspec["gotocancel"] = N_MyFullURL();
  global $thegoto;
  if ($thegoto) {
    $fullspec["gotook"] = $fullspec["gotocancel"] = $thegoto;
  }

  $formtemplate = $fullspec["formtemplate"];
  $metaspec = $fullspec["metaspec"];
  $input = $fullspec["input"];
  $precode = $fullspec["precode"];

  // thb 2007-12-10: allow custom url to be passed to the action part of the form
  // use the "formactionurl" field of the form array
  $formactionurl = $fullspec["formactionurl"];
  if(!$formactionurl) $formactionurl = "/nkit/form.php";

  uuse ("ims");
  uuse ("soap");
  $content = "";
  global $iampopup;
  $iampopup = $fullspec["popup"];
  global $reloaddata;
  eval ($precode);
  $fullspec["portal"] = $portal;
  if ($reloaddata) {
    $alldata = SHIELD_Decode ($reloaddata);
    foreach ($alldata as $key => $value) {
      $data[$key] = $value;
    }
  }
  if (!$content && !$formtemplate) return "";
  if ($content) {
    $formtemplate = '<font face="arial" size=2>'.$content . '</font>' . $formtemplate;
  }
  $formtemplate = IMS_CleanupTags ($formtemplate);

  $fullspec["formtemplate"] = $formtemplate;
  $encspec = SHIELD_Encode ($fullspec);

  if (IMS_TagExists ($formtemplate, "ok")) {
    $fullspec["foundok"] = true;
  }
  if (is_array ($metaspec["fields"])) for ($i=0; $i<10; $i++) { // allow 10 levels of cloning
    foreach ($metaspec["fields"] as $name => $specs) {
      if ($specs["type"]=="clone") {
        $oldtitle = $metaspec["fields"][$name]["title"];
        $allfields = MB_Load ("ims_fields", IMS_SuperGroupName());
        $tmp_required = $specs["required"];
        $metaspec["fields"][$name] = $allfields[$specs["source"]];
        $metaspec["fields"][$name]["required"] = $tmp_required;
        if ($oldtitle) $metaspec["fields"][$name]["title"] = $oldtitle;
      }
    }
  }
  $metaspec["fields"] = FORMS_EnhanceAllFieldspecs($metaspec["fields"]);

  $thefieldlist = $metaspec["fields"];
  if (is_array($thefieldlist)) reset($thefieldlist);
  if (is_array($thefieldlist)) while (list($thefield)=each($thefieldlist)) {
    if ($thefieldlist[$thefield]["type"]=="bigfile") $fullspec["files"] = true;
    if ($thefieldlist[$thefield]["type"]=="file") $fullspec["files"] = true;
  }

  $form = "";

  global $errordata;
  if ($errordata) {
    $errors = SHIELD_Decode($errordata);
    $buttons = "";
    if ($errors["allowretry"] === "no") {
      if ($iampopup) {
        $buttons = '<form><input type="button" value="'.ML("Sluiten", "Close").'" onClick="window.close()"></form>';
      } else {
        $prevurl = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "reloaddata"), "errordata");
        $buttons = '<form><input type="button" value="'.ML("Terug", "Back").'" onClick="location.href = \''.$prevurl.'\'"></form>';
      }
    }
    $errormessage = FORMS_ShowCollectedErrors($errors, $buttons, ($errors["allowretry"] !== "no"), $iampopup, $fullspec);
    if ($errors["allowretry"] == "no") return $errormessage;

    if (IMS_TagExists($formtemplate, "errors")) {
      $formtemplate = IMS_TagReplace($formtemplate, "errors", $errormessage);
    } else {
      $form .= $errormessage; // put error message on top of form
    }
  } else {
    $formtemplate = IMS_TagReplace($formtemplate, "errors", "");
  }

  if ($fullspec["files"]) {
    $form .= '<form enctype="multipart/form-data" method="post" action="'.$formactionurl.'" accept-charset="utf-8">';
//    $form .= '<input type="hidden" name="MAX_FILE_SIZE" value="200000000">'; // does not seem to work for IE :-(
  } else {
    $form .= '<form method="post" action="'.$formactionurl.'" accept-charset="utf-8">';
  }
  $form .= '<input type="hidden" name="prevurl" value="'.N_MyVeryFullURL().'">';
  $form .= '<input type="hidden" name="encspec" value="'.$encspec.'">';

  global $newcase, $sourceid, $targetid, $mousebutton;
  $form .= '<input type="hidden" name="newcase" value="'.$newcase.'">';
  $form .= '<input type="hidden" name="sourceid" value="'.$sourceid.'">';
  $form .= '<input type="hidden" name="targetid" value="'.$targetid.'">';
  $form .= '<input type="hidden" name="mousebutton" value="'.$mousebutton.'">';

  $form .= '<input type="hidden" name="command" value="handlesuperform">'; 

  if ($myconfig[IMS_SuperGroupName()]["dontdisableokbutton"] != "yes" && $fullspec["multipleokbuttons"] != "yes" && $myconfig[IMS_SuperGroupName()]["autocompletefields"] != "yes") {
    // If you use multiple OK buttons, you might want to know on the server which button was pressed, but form.submit() doesnt tell -> so dont use javascript
    /* This feature is not compatible with autocomplete, because form.submit() bypasses all onsubmit events.
     * Onsubmit events can not be called programmatically, so the "disableokbutton" can not help this.
     * But onsubmit events are essential for autocomplete; without it, users can submit (old / empty) internal 
     * values that do not match the currently visible value.
     */
    $form .= '
      <script language="javascript">
      var gedrukt = 0;
      function knopStop (form,element) {
        if ( ! gedrukt ) {
           form.elements[element].disabled=true;
           gedrukt = 1;
           form.submit();
        }
      }
      </script>';
  }
  if ($fullspec["multipleokbuttons"] == "yes") {
    while (IMS_TagExists ($formtemplate, "ok")) {
      $txt = str_replace (chr(10)," ",str_replace (chr(13),"",IMS_TagParams ($formtemplate, "ok")));
      if (!$txt) $txt = ML("OK","OK");    
      $formtemplate = IMS_TagReplace ($formtemplate, "ok", '<input type="submit" name="ok" value="'.$txt.'" alt="'.$txt.'" title="'.$txt.'">');
    }
  } else {
    if (IMS_TagExists ($formtemplate, "ok")) { 
      $txt = str_replace (chr(10)," ",str_replace (chr(13),"",IMS_TagParams ($formtemplate, "ok")));
      if (!$txt) $txt = ML("OK","OK");    
      global $myconfig;
      if ($myconfig[IMS_SuperGroupName()]["dontdisableokbutton"] != "yes" && $fullspec["multipleokbuttons"] != "yes") {
         $formtemplate = IMS_TagReplace ($formtemplate, "ok", '<input class="ims_skin_button" type="submit" name="ok" value="'.$txt.'" alt="'.$txt.'" title="'.$txt.'" onclick="knopStop(this.form,\'ok\');">');
      } else {
        $formtemplate = IMS_TagReplace ($formtemplate, "ok", '<input class="ims_skin_button" type="submit" name="ok" value="'.$txt.'" alt="'.$txt.'" title="'.$txt.'">');
      }
    }
  }

  if (IMS_TagExists ($formtemplate, "cancel")) {
    $txt = str_replace (chr(10)," ",str_replace (chr(13),"",IMS_TagParams ($formtemplate, "cancel")));
    if (!$txt) $txt = ML("Annuleren","Cancel");
    // KOEN: avoid bug in IE with nested forms
    $theurl = N_MyFullURL();
    global $command, $encspec;
    if (strpos ($theurl, "nkit/form.php") && 
        $command == "showsuperform" &&
        $encspec) {
      $formtemplate = IMS_TagReplace ($formtemplate, "cancel", '<input class="ims_skin_button" type="button" onclick="window.close()" name="cancel" value="'.$txt.'" alt="'.$txt.'" title="'.$txt.'">');
    } else {
      $formtemplate = IMS_TagReplace ($formtemplate, "cancel", '<input class="ims_skin_button" type="submit" name="cancel" value="'.$txt.'" alt="'.$txt.'" title="'.$txt.'">');
    }
  }
  if (IMS_TagExists ($formtemplate, "reset")) {
    // LF20100407: get rid of reloaddata and/or errordata when resetting a form
    $txt = str_replace (chr(10)," ",str_replace (chr(13),"",IMS_TagParams ($formtemplate, "reset")));
    if (!$txt) $txt = ML("Herstellen","Reset");
    if ($_GET["reloaddata"] || $_GET["errordata"]) {
      $location = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "reloaddata"), "errordata");
      $onclick = "location.replace('$location'); return false;";
      $formtemplate = IMS_TagReplace ($formtemplate, "reset", '<input class="ims_skin_button" type="reset" onclick="'.htmlspecialchars($onclick).'" name="reset" value="'.$txt.'" alt="'.$txt.'" title="'.$txt.'">');
    } else {
      $formtemplate = IMS_TagReplace ($formtemplate, "reset", '<input class="ims_skin_button" type="reset" name="reset" value="'.$txt.'" alt="'.$txt.'" title="'.$txt.'">');
    }
  }
  if (IMS_TagExists ($formtemplate, "back")) {
    $txt = str_replace (chr(10)," ",str_replace (chr(13),"",IMS_TagParams ($formtemplate, "back")));
    if (!$txt) $txt = '&lt; '.ML("Vorige","Previous");
    $formtemplate = IMS_TagReplace ($formtemplate, "back", '<input class="ims_skin_button" type="button" onclick="history.back()"  value="'.$txt.'" alt="'.$txt.'" title="'.$txt.'">');
  }
  if (IMS_TagExists ($formtemplate, "next")) {
    $txt = str_replace (chr(10)," ",str_replace (chr(13),"",IMS_TagParams ($formtemplate, "next")));
    if (!$txt) $txt = ML("Volgende","Next").' &gt;';
    $formtemplate = IMS_TagReplace ($formtemplate, "next", '<input class="ims_skin_button" type="submit" name="ok" value="'.ML("Volgende","Next").' &gt;" alt="'.ML("Volgende","Next").'" title="'.ML("Volgende","Next").'">');
  }

  $fields = $metaspec["fields"]; 

  global $myconfig;
  if($myconfig[IMS_SuperGroupName()]["formfieldmaxrepeat"]) {
    $multiloopmax = $myconfig[IMS_SuperGroupName()]["formfieldmaxrepeat"];
  } else {
    $multiloopmax = 10;
  }
  for ($multiloop=1; $multiloop<=$multiloopmax; $multiloop++) { // thb SHOW HTML
  $foundsomething = false;

  if (is_array($fields)) reset ($fields);
  if (is_array($fields)) while (list ($field,)=each($fields)) {
    if ($multiloop > 1) {
       $fieldname = $field."__".$multiloop;
    } else {
       $fieldname = $field;
    }

    $doit = false; 
//    if (strpos (" ".$formtemplate, "[[[$field:]]]")) $doit = true;
    if (IMS_TagExists ($formtemplate, $field)) $doit = true;

    if (strpos (" ".$formtemplate, "((($field:)))")) $doit = true;
    if (strpos (" ".$formtemplate, "{{{".$field.":}}}")) $doit = true;

    if ($doit) $foundsomething = true;

    if ($doit) {

    if ($data [$field]===null) {
      $value = $metaspec["fields"][$field]["default"];
    } else {
      $value = $data [$fieldname];
    }

/*
    // We make sure a RPC iframe is generated if it MIGHT be needed
    // The assumption here is that [[[ and ((( both do RPC iframe or both don't do RPC iframe
    // There are 3 cases to consider:
    // (1) [[[ is not used and ((( MIGHT be used, we let $formtemplate handle the RPC iframe
    // (2) [[[ is used and ((( is not used, we let [[[ handle the RPC iframe
    // (3) [[[ is used and ((( is used, we let [[[ handle the RPC iframe
    if (!IMS_TagExists($formtemplate, $field)) { // field is NOT used in [[[ mode
      global $init_DHTML_PrepRPC;
      $init_DHTML_PrepRPC_backup = $init_DHTML_PrepRPC;
      $fieldvalue = FORMS_ShowValueHTML ($value, $metaspec["fields"][$field], $data, $fullspec["autoobject"]);
      if ($init_DHTML_PrepRPC_backup != $init_DHTML_PrepRPC) { // true if DHTML_PrepRPC has been called for the first time
         $init_DHTML_PrepRPC = false;
         $formtemplate = $formtemplate.DHTML_PrepRPC();
         $fieldvalue = FORMS_ShowValueHTML ($value, $metaspec["fields"][$field], $data, $fullspec["autoobject"]); // this time without RPC iframe
      }
    } else { // field is used in [[[ mode
      if ($formtemplate != N_str_replace_once ("((($field:)))", "", $formtemplate)) { // AND field is used in ((( mode
        global $init_DHTML_PrepRPC;
        $init_DHTML_PrepRPC_backup = $init_DHTML_PrepRPC;
        $init_DHTML_PrepRPC = true;
        $fieldvalue = FORMS_ShowValueHTML ($value, $metaspec["fields"][$field], $data, $fullspec["autoobject"]); 
        $init_DHTML_PrepRPC = $init_DHTML_PrepRPC_backup;
      }
    }
*/

    if ($formtemplate != N_str_replace_once ("((($field:)))", "", $formtemplate)) { // only determine if it is used later on (predict future)
      $fieldvalue = FORMS_ShowValueHTML ($value, $metaspec["fields"][$field], $data, $fullspec["autoobject"]); 
    }

    if (!$fields[$field]["title"]) {
      $allfields = MB_Load ("ims_fields", IMS_SuperGroupName());
      if ($allfields[$fieldname]["title"]) {
        $fieldtitletext = $allfields[$fieldname]["title"] ;
      } else {
        $fieldtitletext = $fieldname ;
      }
    } else {
      $fieldtitletext = $fields[$field]["title"] ;
    }

    if (IMS_TagExists($formtemplate, $field)) {
      // LF20090205: if a form uses only ((($field))) and not [[[$field]]], then dont do all the stuff 
      // below. This prevents side-effects like SHIELD_ForceLogon, which is needed for [[[$mdfield]]] 
      // but undesirable for ((($mdfield))).

      $style = $myconfig[IMS_SupergroupName()]["textareastyle"];// to adjust style of textarea JH 2010-08-16

      if ($metaspec["fields"][$field]["type"]=="code") {  // CODE_EDIT
        if (!is_array ($value)) {
          $content = '<input name="field_'.$fieldname.'" value="'.N_XML2HTML ($value).'" size="30">';
        }
        $flexparams = IMS_TagParams ($formtemplate, $field);

        //qqq eval ($metaspec["fields"][$field]["edit"]);

        $content = N_Eval ($metaspec["fields"][$field]["edit"], get_defined_vars(), "content");

        $fieldcode = $content; 
      } else if ($metaspec["fields"][$field]["type"]=="taxo") {
        $content = $fieldcode = FORMS_TAXO_Edit ($fieldname, $value, $metaspec["fields"][$field]); 
      } else if ($metaspec["fields"][$field]["type"]=="fk") {
        $thespecs = $metaspec["fields"][$field];
        $colspecs = array();
        $allthefields = MB_Load ("ims_fields", IMS_SuperGroupName());
        for ($ii=1; $ii<=7; $ii++) { 
          // TODO taal in indexexpressie?
          if ($thespecs["specs"]["fk"]["field$ii"]) {
            if (strpos (" ".$thespecs["specs"]["fk"]["table"], "ims_") && strpos (" ".$thespecs["specs"]["fk"]["table"], "_objects")) { // CMS and DMS
              $expression = '$object["meta_'.$thespecs["specs"]["fk"]["field$ii"].'"].$object["parameters"]["published"]["'.$thespecs["specs"]["fk"]["field$ii"].'"]';
            } else if (strpos (" ".$thespecs["specs"]["fk"]["table"], "process_") && strpos (" ".$thespecs["specs"]["fk"]["table"], "_cases_")) { // BPMS
              $expression = 'FORMS_ShowValue ($object["data"]["'.$thespecs["specs"]["fk"]["field$ii"].'"], "'.$thespecs["specs"]["fk"]["field$ii"].'")'; 
            } else { // raw tables
              $expression = '$object["'.$thespecs["specs"]["fk"]["field$ii"].'"]';
            } 
            $colspecs[$allthefields[$thespecs["specs"]["fk"]["field$ii"]]["title"]] = $expression;
          }
        } 
        $content = $fieldcode = FORMS_FK_Edit ($thespecs["specs"]["fk"]["table"], $fieldname, $value, $colspecs);
      } else if ($metaspec["fields"][$field]["type"]=="multilist") {
        $sourcefield = $metaspec["fields"][$field]["source"];
        $sourcefieldspecs = $metaspec["fields"][$sourcefield];
        if (!$sourcefieldspecs) $sourcefieldspecs = MB_Fetch("ims_fields", IMS_SuperGroupName(), $sourcefield);
        if ($sourcefieldspecs["type"] == "list") {
          $sourcefieldspecs["show"] = "visible";
          $sourcefieldspecs["method"] = "multi"; // shouldnt be necessary, but just in case
          if (count($sourcefieldspecs["values"])>MAX_LIST_SIZE && $myconfig[IMS_SuperGroupName()]["autocompletefields"] == "yes") {
            $fieldcode = FORMS_LIST_Edit ($fieldname, $value, $sourcefieldspecs, $fieldtitletext, true);
          } else {
            $fieldcode = FORMS_MULTI_Edit ($fieldname, $value, $sourcefieldspecs);
          }
        } elseif ($sourcefieldspecs["type"] == "fk") {
          $thespecs = $sourcefieldspecs;
          $colspecs = array();
          $allthefields = MB_Load ("ims_fields", IMS_SuperGroupName());
          for ($ii=1; $ii<=7; $ii++) { 
            // TODO taal in indexexpressie?
            if ($thespecs["specs"]["fk"]["field$ii"]) {
              if (strpos (" ".$thespecs["specs"]["fk"]["table"], "ims_") && strpos (" ".$thespecs["specs"]["fk"]["table"], "_objects")) { // CMS and DMS
                $expression = '$object["meta_'.$thespecs["specs"]["fk"]["field$ii"].'"].$object["parameters"]["published"]["'.$thespecs["specs"]["fk"]["field$ii"].'"]';
              } else if (strpos (" ".$thespecs["specs"]["fk"]["table"], "process_") && strpos (" ".$thespecs["specs"]["fk"]["table"], "_cases_")) { // BPMS
                $expression = 'FORMS_ShowValue ($object["data"]["'.$thespecs["specs"]["fk"]["field$ii"].'"], "'.$thespecs["specs"]["fk"]["field$ii"].'")'; 
              } else { // raw tables
                $expression = '$object["'.$thespecs["specs"]["fk"]["field$ii"].'"]';
              } 
              $colspecs[$allthefields[$thespecs["specs"]["fk"]["field$ii"]]["title"]] = $expression;
            }
          } 
          $content = $fieldcode = FORMS_FK_Edit ($thespecs["specs"]["fk"]["table"], $fieldname, $value, $colspecs, $autofields="", $extrapostcode="", $selectspec=array(), $slowselectspec=array(), $multi = true);
        } else {
          $fieldcode = "SOURCE TYPE NOT ALLOWED '".$sourcefieldspecs["type"]."'";
        }
      } else if ($metaspec["fields"][$field]["type"]=="listemb") {
        $thespecs = $metaspec["fields"][$field]["specs"]["listemb"];
        $allthefields = MB_Load ("ims_fields", IMS_SuperGroupName());
        $theheads = array();
        $thefields = array();
        for ($i=1; $i<=7; $i++) { 
          if ($thespecs["field$i"]) {
            array_push ($thefields, $thespecs["field$i"]);
            array_push ($theheads, $allthefields[$thespecs["field$i"]]["title"]);
          }
        }
        $mdspecs = array (
          "title"=>$metaspec["fields"][$field]["title"],
          "form"=>$thespecs["form"],
          "table"=>$thespecs["table"],
          "fk_field"=>$thespecs["fkfield"],
          "heads"=>$theheads, 
          "fields"=>$thefields,
          "maxrows"=>$thespecs["maxrows"],
          "sort"=>$thespecs["field1"]
        );
        $content = $fieldcode = FORMS_RMD_Edit ($fieldname, $value, $mdspecs);
      } else if ($metaspec["fields"][$field]["type"]=="hyperlink") {
        $fieldcode = FORMS_HYP_Edit ($fieldname, $value);
      } else if ($metaspec["fields"][$field]["type"]=="composite") {
        // $specs["fieldspec"] is not needed when the fields in $specs["fields"] are stored in ims_fields  
        $fieldcode = FORMS_Composite_Edit ($fieldname, $value, $metaspec["fields"][$field]["fields"], $metaspec["fields"][$field]["fieldspec"]);  
      } else if ($metaspec["fields"][$field]["type"]=="image") {
        $fieldcode = FORMS_IMG_Edit ($fieldname, $value);
      } else if ($metaspec["fields"][$field]["type"]=="diskfile") {
        $fieldcode = FORMS_Diskfile_Edit ($fieldname, $value);
      } else if ($metaspec["fields"][$field]["type"]=="html") {
        $fieldcode = FORMS_HTML_Edit ($fieldname, $value);
      } else if ($metaspec["fields"][$field]["type"]=="auto") {
        uuse ("dhtml");
        $value = FORMS_ShowValue ($value, $metaspec["fields"][$field], $data, $fullspec["autoobject"]); 
        T_Start ("ims", array("extratop"=>1, "extrabottom"=>1, "noheader"=>"yes", "extra-table-props"=>"width=113", "nobr"=>"yes")); 
        echo DHTML_DynamicObject (N_HtmlEntities ($value)."&nbsp;", "dyn_".str_replace ("meta_", "", $fieldname));
        $fieldcode = TS_End();
      } else if ($metaspec["fields"][$field]["type"]=="string") { 
        $fieldcode = '<input id="dwf_'.$fieldname.'" name="field_'.$fieldname.'" value="'.N_HtmlEntities($value).'" size="30" title="'.$fieldtitletext.'" alt="'.$fieldtitletext.'">';
      } else if ($metaspec["fields"][$field]["type"]=="strml") {
        $fieldcode = FORMS_STRML_Edit($fieldname, $value, $fieldtitletext, $metaspec["fields"][$field]["specs"]);
      } else if ($metaspec["fields"][$field]["type"]=="bigstring") {
        $fieldcode = '<input id="dwf_'.$fieldname.'" name="field_'.$fieldname.'" value="'.N_HtmlEntities($value).'" size="60" title="'.$fieldtitletext.'" alt="'.$fieldtitletext.'">';
      } else if ($metaspec["fields"][$field]["type"]=="smallstring") {
        $fieldcode = '<input id="dwf_'.$fieldname.'" name="field_'.$fieldname.'" value="'.N_HtmlEntities($value).'" size="15" title="'.$fieldtitletext.'" alt="'.$fieldtitletext.'">';
      } else if ($metaspec["fields"][$field]["type"]=="password") {
        $fieldcode = '<input id="dwf_'.$fieldname.'" type="password" name="field_'.$fieldname.'" value="'.$value.'" size="15" title="'.$fieldtitletext.'" alt="'.$fieldtitletext.'">';
      } else if ($metaspec["fields"][$field]["type"]=="smalltext") {
        $fieldcode = '<textarea id="dwf_'.$fieldname.'" name="field_'.$fieldname.'" rows="3" cols="23" title="'.$fieldtitletext.'"' . ($style?' style="' . $style . '"':"") . '>';

        $fieldcode .= str_replace ("[", "&#91;", N_HtmlEntities($value));
        $fieldcode .= '</textarea>';
      } else if ($metaspec["fields"][$field]["type"]=="text") {
        $fieldcode = '<textarea id="dwf_'.$fieldname.'" name="field_'.$fieldname.'" rows="4" cols="46" title="'.$fieldtitletext.'"' . ($style?' style="' . $style . '"':"") . '>';
        $fieldcode .= str_replace ("[", "&#91;", N_HtmlEntities($value));
        $fieldcode .= '</textarea>';
      } else if ($metaspec["fields"][$field]["type"]=="txtml") {
        $fieldcode = FORMS_TXTML_Edit($fieldname, $value, $fieldtitletext, $metaspec["fields"][$field]["specs"]);
      } else if ($metaspec["fields"][$field]["type"]=="bigtext") {
        $fieldcode = '<textarea id="dwf_'.$fieldname.'" name="field_'.$fieldname.'" rows="12" cols="46" title="'.$fieldtitletext.'"' . ($style?' style="' . $style . '"':"") . '>';

        $fieldcode .= str_replace ("[", "&#91;", N_HtmlEntities($value));
        $fieldcode .= '</textarea>';
      } else if ($metaspec["fields"][$field]["type"]=="verywidetext") {
        $fieldcode = '<textarea name="field_'.$fieldname.'" wrap=off rows=5 cols=110 style="font-size:12px; font-family: courier new, courier;" title="'.$fieldtitletext.'">';
        $fieldcode .= str_replace ("[", "&#91;", N_HtmlEntities($value));
        $fieldcode .= '</textarea>';
      } else if ($metaspec["fields"][$field]["type"]=="verybigtext") {
        $fieldcode = '<textarea id="dwf_'.$fieldname.'" name="field_'.$fieldname.'" wrap=off rows=15 cols=110 style="font-size:12px; font-family: courier new, courier;" title="'.$fieldtitletext.'">';
        $fieldcode .= str_replace ("[", "&#91;", N_HtmlEntities($value));
        $fieldcode .= '</textarea>';
      } else if ($metaspec["fields"][$field]["type"]=="bigfile") { 
        $fieldcode = '<input type="hidden" name="filename_'.$fieldname.'" id="filename_'.$fieldname.'">';
        $fieldcode .= '<input onchange="javascript: document.getElementById(\'filename_'.$fieldname.'\').value=document.getElementById(\'field_'.$fieldname.'\').value" name="field_'.$fieldname.'" id="field_'.$fieldname.'" type="file" size="15" title="'.$fieldtitletext.'" alt="'.$fieldtitletext.'">'; 
//      $fieldcode = '<input type="hidden" name="filename_'.$fieldname.'">';
//      $fieldcode .= '<input onchange="javascript:forms[0].filename_'.$fieldname.'.value=forms[0].field_'.$fieldname.'.value" name="field_'.$fieldname.'" type="file" size="45" title="'.$fieldtitletext.'" alt="'.$fieldtitletext.'">';
      } else if ($metaspec["fields"][$field]["type"]=="file") { 
        $fieldcode = '<input type="hidden" name="filename_'.$fieldname.'" id="filename_'.$fieldname.'">'; 
        $wdth = $myconfig[IMS_SupergroupName()]["uploadfieldwidth"];
        if (!$wdth)
           $wdth = 15;
        $fieldcode .= '<input onchange="javascript: document.getElementById(\'filename_'.$fieldname.'\').value=document.getElementById(\'field_'.$fieldname.'\').value" name="field_'.$fieldname.'" id="field_'.$fieldname.'" type="file" size="' . $wdth . '" title="'.$fieldtitletext.'" alt="'.$fieldtitletext.'">'; 
//      $fieldcode = '<input type="hidden" name="filename_'.$fieldname.'">';
//      $fieldcode .= '<input onchange="javascript:forms[0].filename_'.$fieldname.'.value=forms[0].field_'.$fieldname.'.value" name="field_'.$fieldname.'" type="file" size="15" title="'.$fieldtitletext.'" alt="'.$fieldtitletext.'">'; 
      } else if ($metaspec["fields"][$field]["type"]=="tree") { 
        $fieldcode  = '<select name="field_'.$fieldname.'" title="'.$fieldtitletext.'">';
        $expr = $metaspec["fields"][$field]["tree"];
        eval ('$tree = '.$expr.';');
        if ($metaspec["fields"][$field]["list"]) {
           eval ($metaspec["fields"][$field]["list"]); // fill $list with array of keys to show
           $fieldcode .= FORMS_ExpandTree ($tree, $list, $value, $metaspec["fields"][$field]["hide"]);
        } else {
           $fieldcode .= FORMS_ExpandTree ($tree, TREE_AllRootObjects ($tree), $value, $metaspec["fields"][$field]["hide"]);
        }
        $fieldcode .= '</select>';
      } else if ($metaspec["fields"][$field]["type"]=="list") {
        if ($metaspec["fields"][$field]["method"]=="multi") {
          if (count($metaspec["fields"][$field]["values"])>MAX_LIST_SIZE && $myconfig[IMS_SuperGroupName()]["autocompletefields"] == "yes") {
            $fieldcode = FORMS_LIST_Edit ($fieldname, $value, $metaspec["fields"][$field], $fieldtitletext, true);
          } else {
            $fieldcode = FORMS_MULTI_Edit ($fieldname, $value, $metaspec["fields"][$field]);
          }
        } else if ($metaspec["fields"][$field]["method"]=="other") {
          $fieldcode  = '<select id="dwf_'.$fieldname.'" name="field_'.$fieldname.'" title="'.$fieldtitletext.'">';
          $values = $metaspec["fields"][$field]["values"];
          if (!is_array($values)) $values=array();
          reset ($values); 
          if ($metaspec["fields"][$field]["sort"]) ksort ($values);
          $found = false;
          reset ($values);
          //GVA $truevalue=="dummy" to $truevalue==="dummy" no comparing strings and integers
          while (list ($listvalue, $truevalue)=each($values)) {
            if ($truevalue==="dummy") {
              $truevalue = $listvalue;
              N_Log ("errors", "FORMS_GenerateSuperForm: dummy alert, obsolete listvalue replacement ($listvalue)");
            }          
            if ($truevalue==$value) {
              $found = true;
              $fieldcode .= '<option value="'.$truevalue.'" selected>'.FORMS_ML_Filter($listvalue).'</option>';
            } else {
              $fieldcode .= '<option value="'.$truevalue.'">'.FORMS_ML_Filter($listvalue).'</option>';
            }          
          }
          if (!$found) { // other value
            $fieldcode .= '<option value="" selected>'.ML("Overig:","Other:").'</option>';
            $fieldcode .= '<input name="other_field_'.$fieldname.'" value="'.N_HtmlEntities($value).'" size="20">';
          } else {
            $fieldcode .= '<option value="">'.ML("Overig:","Other:").'</option>';
            $fieldcode .= '<input name="other_field_'.$fieldname.'" value="" size="20">';
          }
          $fieldcode .= '</select>';
        } else { // single choice 
          $values = $metaspec["fields"][$field]["values"];
          if ($metaspec["fields"][$field]["sort"]) ksort ($values);
          if (!is_array($values)) $values=array();

          $customfunc = "";
          if (substr($metaspec["fields"][$field]["method"], 0, 6)=="custom") {
            global $myconfig;
            $custommethod = substr($metaspec["fields"][$field]["method"], 6);
            $customfunc = $myconfig[IMS_SuperGroupName()]["customlistmethod"][$custommethod]["editfunc"];
          }
          if ($customfunc && is_callable($customfunc)) {
            $flexparams = IMS_TagParams ($formtemplate, $field);
            $fieldcode = $customfunc($values, $value, $fieldname, $field, $fieldtitletext, $flexparams);
          } else {
            if (count($values)>MAX_LIST_SIZE) { 
              $fieldcode = FORMS_LIST_Edit ($fieldname, $value, $metaspec["fields"][$field], $fieldtitletext);
            } else { 
              if ($metaspec["fields"][$field]["method"]=="radiover") {
                reset ($values); 
                $tmp = "";
                while (list ($listvalue, $truevalue)=each($values)) {
                  if ($truevalue==="dummy") {
                    $truevalue = $listvalue;
                    N_Log ("errors", "FORMS_GenerateSuperForm: dummy alert, obsolete listvalue replacement ($listvalue)");
                  }          
                  if ($truevalue==$value) {
                    $tmp .= '<input type="radio" name="field_'.$fieldname.'" value="'.$truevalue.'" checked> '.FORMS_ML_Filter($listvalue).'<br>';
                  } else {
                    $tmp .= '<input type="radio" name="field_'.$fieldname.'" value="'.$truevalue.'"> '.FORMS_ML_Filter($listvalue).'<br>';
                  }
                }
                $fieldcode = $tmp; 
              } else if ($metaspec["fields"][$field]["method"]=="radiohor") {
                reset ($values); 
                $tmp = "";
                while (list ($listvalue, $truevalue)=each($values)) {
                  if ($truevalue==="dummy") {
                    $truevalue = $listvalue;
                    N_Log ("errors", "FORMS_GenerateSuperForm: dummy alert, obsolete listvalue replacement ($listvalue)");

                  }          
                  if ($truevalue==$value) {
                    $tmp .= '<input type="radio" name="field_'.$fieldname.'" value="'.$truevalue.'" checked> '.FORMS_ML_Filter($listvalue).' ';
                  } else {
                    $tmp .= '<input type="radio" name="field_'.$fieldname.'" value="'.$truevalue.'"> '.FORMS_ML_Filter($listvalue).' ';
                  }
                }
                $fieldcode = $tmp; 
              } else {
                reset ($values); 
                $fieldcode = '<select id="dwf_'.$fieldname.'" name="field_'.$fieldname.'" title="'.$fieldtitletext.'">';
                while (list ($listvalue, $truevalue)=each($values)) {
                  if ($truevalue==="dummy") {
                    $truevalue = $listvalue;
                    N_Log ("errors", "FORMS_GenerateSuperForm: dummy alert, obsolete listvalue replacement ($listvalue)");
                  }          
                  if ($truevalue==$value) {
                    $fieldcode .= '<option value="'.$truevalue.'" selected>'.FORMS_ML_Filter($listvalue).'</option>';
                  } else {
                    $fieldcode .= '<option value="'.$truevalue.'">'.FORMS_ML_Filter($listvalue).'</option>';
                  }
                }
                $fieldcode .= '</select>'; 
              }
            }
          }
        }
      } else if ($metaspec["fields"][$field]["type"]=="yesno") {
        if ($value) {
          $fieldcode = '<input type="checkbox" name="field_'.$fieldname.'" value="true" checked title="'.$fieldtitletext.'" alt="'.$fieldtitletext.'">';
        } else {
          $fieldcode = '<input type="checkbox" name="field_'.$fieldname.'" value="true" title="'.$fieldtitletext.'" alt="'.$fieldtitletext.'">';
        }
      } else if ($metaspec["fields"][$field]["type"]=="date") {
        if (!$value && $metaspec["fields"][$field]["auto"]) $value = time();
        if ($metaspec["fields"][$field]["specs"]["edit"] == "html") {
          $fieldcode = FORMS_HTMLDate_Edit($fieldname, $value, $metaspec["fields"][$field]["specs"]);
        } else {
          $fieldcode = JSCAL_CreateDate ('field_date_'.$fieldname, $value);
        }
      } else if ($metaspec["fields"][$field]["type"]=="time") {
        if (!$value) $value=time();
        $fieldcode  = '<select name="field_hour_'.$fieldname.'" title="'.ML("uur","hour").'">';
        for ($i=0; $i<=23; $i++) {
          if ($i==N_Time2Hour ($value)) {
            $fieldcode .= '<option value="'.$i.'" selected>'. sprintf("%02d", $i) .'</option>';
          } else {
            $fieldcode .= '<option value="'.$i.'">'. sprintf("%02d", $i) .'</option>';
          }
        }
        $fieldcode .= '</select>&nbsp;:&nbsp;';
        $fieldcode .= '<select name="field_minute_'.$fieldname.'" title="'.ML("minuut","minute").'">';
        for ($i=0; $i<=59; $i++) {
          if ($i==N_Time2Minute ($value)) {
            $fieldcode .= '<option value="'.$i.'" selected>'. sprintf("%02d", $i) .'</option>';
          } else {
            $fieldcode .= '<option value="'.$i.'">'. sprintf("%02d", $i) .'</option>';
          }
        }
        $fieldcode .= '</select>';
      } else if ($metaspec["fields"][$field]["type"]=="datetime") {
        if (!$value && $metaspec["fields"][$field]["auto"]) $value = time();
        if ($metaspec["fields"][$field]["specs"]["edit"] == "html") {
          $fieldcode = FORMS_HTMLDate_Edit($fieldname, $value, $metaspec["fields"][$field]["specs"], true);
        } else {
          $fieldcode = JSCAL_CreateDateTime ('field_date_'.$fieldname, $value);
        }

      } else {
        $fieldcode = "UNKNOWN TYPE '".$metaspec["fields"][$field]["type"]."'";
      } 
      $field = str_replace ("/", "\/", $field); 

//    $formtemplate = N_str_replace_once ("[[[$field:]]]", $fieldcode, $formtemplate);
      $formtemplate = IMS_TagReplace ($formtemplate, $field, $fieldcode);
    }

    $formtemplate = N_str_replace_once ("((($field:)))", $fieldvalue, $formtemplate);

    global $errordata;
    if ($errordata) {
      $errors = SHIELD_Decode($errordata);
      foreach ($errors["errors"] as $specs) if ($specs["field"]) $errorfields[$specs["field"]] = "x";
      $beforefieldtitle = $fullspec["showerrors"]["beforefieldtitle"];
      if (!$beforefieldtitle) $beforefieldtitle = '<font color="red">';
      $afterfieldtitle = $fullspec["showerrors"]["afterfieldtitle"];
      if (!$afterfieldtitle) $afterfieldtitle = '</font>';
    }

    if ($metaspec["fields"][$field]["title"]) {
      $new = FORMS_ML_Filter($metaspec["fields"][$field]["title"]);
      $new = "<label for=\"dwf_$field\">$new</label>";
      if ($errorfields[$field]) $new = $beforefieldtitle . $new . $afterfieldtitle;
      $formtemplate = N_str_replace_once ("{{{".$field.":}}}", $new, $formtemplate);
    } else {
      $fieldobject = MB_Ref ("ims_fields", IMS_SuperGroupName());
      if ($fieldobject[$field]["title"]) {
      $new = "<label for=\"dwf_$field\">$new</label>";
      if ($errorfields[$field]) $new = $beforefieldtitle . $new . $afterfieldtitle;
      $formtemplate = N_str_replace_once ("{{{".$field.":}}}", $new, $formtemplate);
      } 
    }

    } // if ($doit)
  }

  if (!$foundsomething) $multiloop=999;
  } // for ($multiloop=1; $multiloop<=99; $multiloop++)



  $form .= $formtemplate;
  $form .= '<input type="hidden" name="enforceutf8" value="&#307;"></form>';
  return $form;
}

// "collect" an error. Nothing will happen until FORMS_HandleErrors is called. This allows
// us to collect multiple (validation) errors and present them all at once to the user.
// If you use this function in "postcode" (instead of FORMS_ShowError), you must also call FORMS_HandleErrors.
function FORMS_CollectError($fieldname, $fieldtitle, $error, $allowretry="yes", $errortitle="") {
  global $form_collected_errors;
  if ($allowretry === "no") $form_collected_errors["allowretry"] = "no";
  $form_collected_errors["errors"][] = array("field" => $fieldname, "fieldtitle" => $fieldtitle, "error" => $error, "errortitle" => $errortitle);
}

function FORMS_HandleErrors($data = array()) {
  global $form_collected_errors, $myconfig;

  if ($form_collected_errors) {
    global $iampopup, $iaminline, $prevurl, $thereloaddata, $myconfig, $encspec;

    $fullspec = SHIELD_Decode($encspec, true);
    $showinform = ($fullspec["gotook"]    // Check that we are called inside a form (FORMS_GenerateSuperForm ensures that $fullspec["gotook"] is set)
                   && $prevurl            // and that we will be able to redirect back to that form
                   && ($iaminline         // Show errors in form for forms created with FORMS_GenerateInlineForm
                       || ($myconfig[IMS_SuperGroupName()]["showerrorsinform"] == "yes"))); // All other forms (popups, but also CMS / BPMS forms), it depends on setting

    $allowretry = $form_collected_errors["allowretry"];
    if ($data) $thereloaddata = $data;
    // if (!$prevurl) $allowretry = "no"; // Disabled: just let FORMS_ShowError use history.back() and hope it all works out...

    // Add missing fieldtitles
    foreach ($form_collected_errors["errors"] as $i => $error) {
      if ($error["field"] && $error["field"] != "postcode" && !$error["fieldtitle"]) {
        if ($fullspec["metaspec"]["fields"][$error["field"]]["title"]) $form_collected_errors["errors"][$i]["fieldtitle"] = $fullspec["metaspec"]["fields"][$error["field"]]["title"];
      }
    }

    if ($myconfig[IMS_SuperGroupName()]["formslogging"] == "yes") {
      global $encspec;
      if ($encspec) { $es = SHIELD_Decode($encspec, true); }
      $shortmsg = "Validation error: field = {$form_collected_errors["errors"][0]["field"]}, error = {$form_collected_errors["errors"][0]["error"]}";
      $longmsg = "Validation error:<br/>";
      foreach ($form_collected_errors["errors"] as $error) {
        $longmsg .= "field = {$error["field"]}<br/>" .
                    "error = {$error["error"]}<br/>" .
                    "fieldtitle = {$error["fieldtitle"]}<br/>";
        if ($error["errortitle"]) {
                    "error title = {$error["errortitle"]}<br/>";
        }
        if (!$showinform) break;
      }
      $longmsg .= "allowretry = $allowretry<br/>" .
                  "form title = " . $es["title"] . "<br/>" .
                  "url = " . N_MyFullUrl() . "<br/>" .
                  "prevurl = $prevurl<br/>" .
                  "user = " . SHIELD_CurrentUser(IMS_SuperGroupName()) . "<br/>";
      N_Log("forms", $shortmsg, $longmsg);
    }

    if (!$showinform) {
      // Use old show errors method, which will only show one (the first) message
      foreach ($form_collected_errors["errors"] as $i => $error) break;
      $title = $error["errortitle"];
      if (!$title) $title = ML("Foutmelding", "Error");
      $errormessage = $error["error"];
      if ($error["fieldtitle"]) $errormessage = $error["fieldtitle"] . ": " . $errormessage;
      FORMS_ShowError_Old($title, $errormessage, $allowretry);
    }

    // Let FORMS_GenerateSuperForm show the error
    $enc = SHIELD_Encode ($thereloaddata);
    if (!$prevurl) $prevurl = N_MyFullUrl();
    $enc2 = SHIELD_Encode($form_collected_errors);
    $prevurl = N_AlterURL(N_AlterUrl($prevurl, "reloaddata", $enc), "errordata", $enc2);
    N_Redirect($prevurl);
   }
}

function FORMS_ShowError_Old ($title="____________________DEBUG____________________", $error="", $allowretry="yes")
{
  global $iampopup, $thereloaddata, $myconfig;

  echo "<html>";
  echo "<head>";

  uuse("skins");// JG - CORE-27 nog bespreken met Liesbeth
  if ( function_exists ("SKIN_CSS") ) 
    echo SKIN_css();

  if( $myconfig[IMS_SuperGroupName()]["loadjquery"] == "auto" ) {
    echo DHTML_LoadJquery();
  }
  echo "<title>".$title."</title>";
  if (!$iampopup) {
    echo "<body>";
    echo '<br><br><br><br><br><DIV align="center"><CENTER><P align="center">';
    echo "<table bgcolor=#f0f2ff cellspacing=0 cellpadding=15 border=1 id=\"MeasureMe\"><tr><td><center>";
  } else {
    echo "<body bgcolor=#f0f2ff>";
    echo "<table cellspacing=0 cellpadding=15 border=0 id=\"MeasureMe\"><tr><td><center>";
  }
  echo "<font color=ff0000 face=\"arial, helvetica\" size=4><b>$title</b></a></font><br><br>";
  echo "<font face=\"arial, helvetica\" size=2><b>$error</b></a></font><br><br><br>";
  if ($allowretry==="no") {
    if ($iampopup) {
      echo '<form><input type="button" value="'.ML("Sluiten", "Close").'" onClick="window.close()">&nbsp;&nbsp;&nbsp;</form>';
    } else {
      global $prevurl;
      if ($prevurl) {
        // Go back without reloading the data
        echo '<form><input type="button" value="'.ML("Terug", "Back").'" onClick="location.href = \''.$prevurl.'\'">&nbsp;&nbsp;&nbsp;</form>';
      } else {
        echo '<form><input type="button" value="'.ML("Sluiten", "Close").'" onClick="window.close()">&nbsp;&nbsp;&nbsp;</form>';
      }
    }
  } else {
    if ($iampopup) {
      global $prevurl;
      if ($prevurl) {
        $enc = SHIELD_Encode ($thereloaddata);
        $prevurl = N_AlterURL ($prevurl, "reloaddata", $enc);
        echo '<form><input type="button" value="'.ML("Terug", "Back").'" onClick="location.href = \''.$prevurl.'\'">&nbsp;&nbsp;&nbsp;</form>';
      } else {
         echo '<form><input type="button" value="'.ML("Terug", "Back").'" onClick="history.back()">&nbsp;&nbsp;&nbsp;</form>';
      } 
    } else { 
      global $prevurl;
      if ($prevurl) {
        $enc = SHIELD_Encode ($thereloaddata);
        $prevurl = N_AlterURL ($prevurl, "reloaddata", $enc);
        echo '<form><input type="button" value="'.ML("Terug", "Back").'" onClick="location.href = \''.$prevurl.'\'">&nbsp;&nbsp;&nbsp;</form>';
      } else {
        echo '<form><input type="button" value="'.ML("Terug", "Back").'" onClick="history.back()">&nbsp;&nbsp;&nbsp;</form>';
      }
    }
  }
  echo "</td></tr></table>";
  echo "</center>";
  if ($iampopup) {
    if ( isset( $_REQUEST["encspec"] ) ) // JG - for fancybox - also don't resize show_errors() if dontresize is set 
    {
      $decoded = SHIELD_decode( $_REQUEST['encspec'], true );
      if ( !$decoded['dontresize'] )
        echo DHTML_EmbedJavaScript (DHTML_PerfectSize ());
    } else {
      echo DHTML_EmbedJavaScript (DHTML_PerfectSize ());
    }
  }
  if (!$iampopup) {
    echo '</p></center></div>';
  }
  echo "</body>";
  echo "</html>";
  N_Exit();
  die("");
}

function FORMS_ShowError ($title="____________________DEBUG____________________", $error="", $allowretry="yes")
{
  global $form_collected_errors;
  if ($allowretry === "no") $form_collected_errors["allowretry"] = "no";
  if ($title == "____________________DEBUG____________________" || strtolower($title) == "error" || strtolower($title) == "fout" || strtolower($title) == "foutmelding") {
    $title = "";
  }
  FORMS_CollectError("postcode", "", $error, $allowretry, $title);

  FORMS_HandleErrors();
}

function FORMS_HasErrors()
{
  global $form_collected_errors;
  if ($form_collected_errors) {
    return $form_collected_errors;
  } else {
    return false;
  }
}

function FORMS_ShowCollectedErrors($errordata, $buttons, $aboveform, $iampopup, $fullspec) {
  if ($fullspec["showerrors"]["showcollectederrorsfn"]) {
    FLEX_LoadSupportFunctions(IMS_SuperGroupName());
    if (is_callable($fullspec["showerrors"]["showcollectederrorsfn"])) return $fullspec["showerrors"]["showcollectederrorsfn"]($errordata, $buttons, $aboveform, $iampopup, $fullspec);
  }

  // If there is one error, show it like this (everything centered):
  //             <<<ERRORTITLE>>>
  //    <<<fieldtitle>>>: <<<error>>>
  // If there is more than one error, show it like this (everything left aligned):
  //    ERROR
  //    <<<errortitle>>>: <<<fieldtitle>>>: <<<error>>>
  //    <<<errortitle>>>: <<<fieldtitle>>>: <<<error>>>
  foreach ($errordata["errors"] as $error) {
    if (count($errordata["errors"]) > 1 && $error["errortitle"]) $errormessage .= "<b><font color=\"ff0000\">{$error["errortitle"]}:</font></b> ";
    if ($error["fieldtitle"]) $errormessage .= "<b><font color=\"ff0000\">{$error["fieldtitle"]}:</font></b> ";
    $errormessage .= "<b>{$error["error"]}</b><br>";
    if (count($errordata["errors"]) == 1 && $error["errortitle"]) $title = $error["errortitle"];
  }
  if (!$title) $title = ML("Foutmelding", "Error");
  $errormessage = '<font color="ff0000" size="4"><b>'.$title.'</b></font><br>' . $errormessage;
  if ($buttons) $errormessage = $errormessage . "<br/>" . $buttons;

  if (count($errordata["errors"]) == 1 && !$aboveform && $iampopup) {
    $errormessage = '<div style="text-align: center;"><font face="arial, helvetica" size="2">'.$errormessage.'</font></div>';
  } else {
    $errormessage = '<div style="text-align: left;"><font face="arial, helvetica" size="2">'.$errormessage.'</font></div>';
  }

  return $errormessage;

}

function FORMS_ShowSuperForm()
{
  uuse ("skins");
  global $debugforms;
  global $data, $input;
  global $encspec;
  global $myconfig;
  $input = $fullspec["input"];

  $fullspec = SHIELD_Decode ($encspec);
  IMS_CaptureHtmlHeaders(); // Start capturing headers before we do anything that might involve custom code / fields
  $generatedform = FORMS_GenerateSuperForm ($fullspec);
  if ($generatedform) {
    ob_start();
    if ( $myconfig[IMS_SuperGroupName()]["forms_use_doctype"]=="yes" )
      echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    echo "<html>";
    echo "<head>";

    if ( $myconfig[IMS_SuperGroupName()]["forms_use_doctype"]=="yes" && N_IE() )
      echo "<style type='text/css'>center > table{ text-align:left;}</style>";

    if( $myconfig[IMS_SuperGroupName()]["loadjquery"] == "auto" ) {
      echo DHTML_LoadJquery();
    }
    echo SKIN_CSS ();

    $obj = &SHIELD_CurrentUserObject ();
    echo "<title>".$fullspec["title"]."</title>";
    echo "</head>"."\n";

    echo IMS_MergeHtmlHeaders(ob_get_clean()); // Merge headers as soon as the HTML <head> section is complete

    echo "<body bgcolor=#f0f2ff>";
    echo "<table cellspacing=0 cellpadding=15 border=0 id=\"MeasureMe\"><tr><td>";
    echo $generatedform;
    echo "</td></tr></table>";
    if(!$fullspec["dontresize"])
      echo DHTML_EmbedJavaScript (DHTML_PerfectSize ());
//    echo "<script language=\"JavaScript1.2\">document.forms[0].elements[2].focus();</script>";
    if( !$fullspec['dontfocus'] )
    {
      echo '<script language="JavaScript1.2">';
      echo "if (document.forms[0] && document.forms[0].elements) {";
      echo "  var els = document.forms[0].elements;";
      echo "  for (var i=0; i<els.length; i++) {";
      echo '    if (els[i].getAttribute("type") != "hidden" && !els[i].getAttribute("disabled")) {';
      echo '      if (els[i].className.indexOf("dontfocusmeandstoptrying") >= 0) break;';
      echo '      if (els[i].className.indexOf("dontfocusmebutkeeptrying") >= 0) continue;';
      echo "      els[i].focus();";
      echo "      break;";
      echo "    }";
      echo "  }";
      echo "}";
      echo "</script>";
    }

    echo "</body>";
    echo "</html>";

  } else { // no content so let's pretend the OK button has been pressed
    echo IMS_MergeHtmlHeaders(""); // Because N_Exit gives a warning if capture and merge dont match.
    if ($fullspec["gotook"]) $gotook = $fullspec["gotook"]; else $gotook="closeme&refreshparent";
    eval ($fullspec["postcode"]);
    N_Redirect ($gotook);
  }
}

function FORMS_URL ($fullspec, $popup=true) 
{
  global $debugforms;
  if (!$fullspec["gotook"]) $fullspec["gotook"] = "closeme&refreshparent";
  if (!$fullspec["gotocancel"]) $fullspec["gotocancel"] = "closeme";
  $fullspec["status"] = true; // always show status
  $fullspec["files"] = false;
  $fullspec["popup"] = true;
  $list = $fullspec["metaspec"]["fields"];
  if (is_array($list)) reset($list);
  if (is_array($list)) while (list($field)=each($list)) {
    if ($list[$field]["type"]=="bigfile") $fullspec["status"] = true;
    if ($list[$field]["type"]=="bigfile") $fullspec["files"] = true;
    if ($list[$field]["type"]=="file") $fullspec["status"] = true;
    if ($list[$field]["type"]=="file") $fullspec["files"] = true;
  }
  $encspec = SHIELD_Encode ($fullspec); 
  if (!$popup) return "/nkit/form.php?command=showsuperform&encspec=$encspec";
  return DHTML_PopupURL (N_CurrentProtocol().getenv("HTTP_HOST")."/nkit/form.php?command=showsuperform&encspec=$encspec", 1, true);
}

function FORMS_EnhanceFieldspec($specs) {
  // This function may be called multiple times and may receive its own output as input. So it should be idempotent.
  if (strpos($specs["type"], "strml") !== false) {
    $autolevel = N_KeepAfter($specs["type"], "strml");
    $size = N_KeepBefore($specs["type"], "strml");
    $specs["type"] = "strml";
    if ($autolevel) $specs["specs"]["autolevel"] = $autolevel;
    if ($size == "big") $specs["specs"]["cols"] = 60;
    if ($size == "small") $specs["specs"]["cols"] = 15;
  }

  if (strpos($specs["type"], "txtml") !== false) {
    $autolevel = N_KeepAfter($specs["type"], "txtml");
    $size = N_KeepBefore($specs["type"], "txtml");
    $specs["type"] = "txtml";
    if ($autolevel) $specs["specs"]["autolevel"] = $autolevel;
    if ($size == "big") {
      $specs["specs"]["cols"] = 46;
      $specs["specs"]["rows"] = 12;
    }
    if ($size == "small") {
      $specs["specs"]["cols"] = 23;
      $specs["specs"]["rows"] = 3;
    }
    if ($size == "verywide") {
      $specs["specs"]["cols"] = 110;
      $specs["specs"]["rows"] = 5;
    }
    if ($size == "verybig") {
      $specs["specs"]["cols"] = 110;
      $specs["specs"]["rows"] = 5;
    }
  }

  if (function_exists('FORMS_EnhanceFieldspec_Extra')) $specs = FORMS_EnhanceFieldspec_Extra($specs);
    // If you use this function, it should be made permanently available!
    // This function may be called multiple times and may receive its own output as new input. So it should be idempotent.
    // So please make sure that FORMS_EnhanceFieldspec_Extra($a) == FORMS_EnhanceFieldspec_Extra(FORMS_EnhanceFieldspec_Extra($a)) for all possible values of $a

  return $specs;

}

function FORMS_EnhanceAllFieldspecs($allspecs) {
  foreach ($allspecs as $fieldname => $specs) {
    $allspecs[$fieldname] = FORMS_EnhanceFieldspec($specs);
  }
  return $allspecs;
}

function FORMS_MULTI_View ($fieldname, $value, $specs)
{
  if ($specs["show"]=="visible") {
    $values = $specs["values"];
    if (!is_array($values)) $values=array();
    if ($specs["sort"]) ksort ($values);
    $ctr=0;
    foreach ($values as $show => $internal) {
      if (strpos (" ;".$value.";", ";".$internal.";")) {
        if ($showvalue) {
          $showvalue.="<br>";
        }
        $showvalue .= FORMS_ML_Filter($show);
      }
    }
  } else {
    $values = $specs["values"];
    if (!is_array($values)) $values=array();
    if ($specs["sort"]) ksort ($values);
    $ctr=0;
    foreach ($values as $show => $internal) {
      if (strpos (" ;".$value.";", ";".$internal.";")) {
        if ($showvalue) {
          $showvalue.="<br>";
        }
        $showvalue .= $internal;
      }
    }
  }
  $defaultlabel = "&nbsp;";
  foreach ($values as $visible => $internal) {
    if (!$internal) $defaultlabel = $visible;
  }
  if (!$showvalue) $showvalue = $defaultlabel;
  return $showvalue;
}

function FORMS_MULTI_Edit ($fieldname, $value, $specs)
{
  uuse ("dhtml");

  $form["title"] = ML("Kies", "Select");
  $form["input"]["tmpkey"] = N_GUID();
  $form["input"]["specs"] = $specs;
  $form["input"]["dyn"] = "dyn_".$fieldname;
  $form["input"]["field"] = "field_".$fieldname;
  $form["input"]["value"] = $value;
  $form["formtemplate"] = "<table>";
  $values = $specs["values"];   
  if (!is_array($values)) $values=array();
  if ($specs["sort"]) ksort ($values);
  $ctr=0;
  foreach ($values as $show => $internal) {
    $show = FORMS_ML_Filter($show);
    $ctr++;
    if (!$internal) continue; // must happen AFTER increasing the counter!
      // Ignore the empty value ("Kies...", "<geen>" er whatever.) Selecting the empty value in the popup wont 
      // work (it cannot be added to the list of selected items, and selecting it doesnt deselect other items).
      // Allowing developers / admins to have an empty value in multiple select lists is useful, because:
      // - its corresponding visible label will be shown in the parent form to show that no items have been selected
      // - to allow a developer/admin to clone a single select list that has a (desirable) empty value
    $form["metaspec"]["fields"]["f".$ctr]["type"] = "yesno";    
    $form["formtemplate"] .= '<tr><td><font face="arial" size=2><b>'.$show.':</b></font></td><td>[[[f'.$ctr.']]]</td></tr>';
  }
  $form["formtemplate"] .= "<tr><td colspan=2>&nbsp</td></tr><tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr></table>";
  $form["precode"] = '
    if ($tmp = TMP_LoadObject ($input["tmpkey"])) {
      $data = $tmp;
    } else {
      $values = $input["specs"]["values"];
      if (!is_array($values)) $values=array();
      if ($input["specs"]["sort"]) ksort ($values);
      $ctr=0;
      foreach ($values as $show => $internal) {
        $ctr++;
        if (strpos (" ;".$input["value"].";", ";".$internal.";")) {
         $data["f".$ctr] = true;
        } else {
          $data["f".$ctr] = false;
        }
      }      
    }
  ';
  $form["postcode"] = '
    $values = $input["specs"]["values"];
    if (!is_array($values)) $values=array();
    if ($input["specs"]["sort"]) ksort ($values);
    $ctr=0;
    $value = "";
    foreach ($values as $show => $internal) {
      $ctr++;
      if ($data["f".$ctr]) {
        if ($value) {
          $value .= ";".$internal;
        } else {
          $value = $internal;
        }
      }
    }         
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ($input["dyn"], FORMS_MULTI_View ($input["field"], $value, $input["specs"])));
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ($input["field"], $value));
    TMP_SaveObject ($input["tmpkey"], $data);
    $gotook = "closeme";
  ';
  $url = FORMS_URL ($form);
  T_Start ("ims", array("extratop"=>1, "extrabottom"=>1, "noheader"=>"yes", "extra-table-props"=>"width=113", "nobr"=>"yes"));
  echo DHTML_DynamicObject (FORMS_MULTI_View ($fieldname, $value, $specs)."&nbsp;", "dyn_".$fieldname);
  $dhtmlobject = TS_End();
  return DHTML_InvisiTable ('<font face="arial" size="2">', "</font>",
    $dhtmlobject,
    '<input type=hidden name="field_'.$fieldname.'" value="'.N_HtmlEntities ($value).'">&nbsp;<a title="'.ML("Kies","Select").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>',
    '&nbsp;<a title="'.ML("Kies","Select").'" class="ims_navigation" href="'.$url.'">'.ML("Kies","Select").'</a>'
  );
} 

function FORMS_FK_Edit ($table, $fieldname, $value, $specs, $autofields="", $extrapostcode="", $selectspec=array(), $slowselectspec=array(), $multi = false, $whereinspec = array() ) 
{
  N_Debug ("FORMS_FK_Edit ($table, $fieldname, $value, ...)");

  // NOTE: $multi = true works ONLY with autocomplete

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
  $form["input"]["dyn"] = "dyn_".$fieldname;
  $form["input"]["field"] = "field_".$fieldname;
  $form["input"]["fieldauto"] = "fieldauto_".$fieldname;
  $form["input"]["table"] = $table;
  $form["input"]["showfield"] = $showfield;
  $form["input"]["extrapostcode"] = $extrapostcode;
  $form["input"]["select"] = $selectspec;
  $form["input"]["slowselect"] = $slowselectspec;
  $form["input"]["wherein"] = $whereinspec;

  global $myconfig;
  $autocomplete = ($myconfig[IMS_SuperGroupName()]["autocompletefields"] == "yes" &&  MB_MUL_Engine($table) == "MYSQL"); // autocomplete for FK fields uses MySQL-only queries such as "leftfilter"
  if ($autocomplete) $form["input"]["autocomplete"] = "yes";
  if ($multi && !$autocomplete && $myconfig[IMS_SuperGroupName()]["autocompletefields"] == "yes") return "AUTOCOMPLETE REQUIRED BUT NOT POSSIBLE FOR NON-MYSQL TABLE '{$table}'";
  if ($multi && $myconfig[IMS_SuperGroupName()]["autocompletefields"] != "yes") return "AUTOCOMPLETE REQUIRED BUT NOT ENABLED";
  if (is_array ($autofields)) {
    foreach ($autofields as $dummy => $field) {
      $fieldspecs = MB_Fetch ("ims_fields", IMS_SuperGroupName(), $field);
      $extracode .= '
        $data["'.$fieldname.'"] = $value;
        $remembervalue = $value;
        '.$fieldspecs["calc"].'
        if ($popuplevel == 1) {
          echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_'.$field.'", $value));
        } else {
          echo DHTML_EmbedJavaScript (DHTML_Parent_Parent_SetDynamicObject ("dyn_'.$field.'", $value));
        }
        $value = $remembervalue;
      ';
    }
  }
  $form["input"]["extracode"] = $extracode;
  $form["metaspec"]["fields"]["filter"]["type"] = "string";
  // thb 2007-09-06 zoeken replaced by ok to allow browser to send return key to ok button
  //    <tr><td colspan=2><center>[[[OK:'.ML("Zoeken","Search").']]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
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


  $form["precode"] = '
    $result = MB_TurboMultiQuery ($input["table"], array (
      "slice" => "count"
    ));
    if ($result <= MAX_LIST_SIZE || $input["autocomplete"] == "yes") { // If I had wanted to search, I would have used the autocomplete input field already

    uuse ("dhtml");
    $form["title"] = ML("Kies", "Choose");
    $form["formtemplate"] = \'
      <table>
        <tr><td colspan=2>
    \';
    $atable = array (
      "input" => $input,
      "name" => "testhypertable",
      "style" => "ims",
      "maxlen" => MAX_LIST_SIZE, 
      "filter" => $data["filter"],
      "table" => $input["table"],      
      "tablespecs" => array (
        "sort_default_col" => 2, "sort_default_dir" => "u", 
        "sort_2"=>"auto", "sort_3"=>"auto", "sort_4"=>"auto", "sort_5"=>"auto",
        "sort_6"=>"auto", "sort_7"=>"auto", "sort_8"=>"auto", "sort_9"=>"auto"
      ),
      "select" => $input["select"],
      "slowselect" => $input["slowselect"],
      "wherein" => $input["wherein"],
      "tableheads" => array (""),
      "sort" => array (""),
      "content" => array (\'
        if ($input) {
          reset ($input["specs"]);
          list ($dummy, $expr) = each($input["specs"]);
          $object = $record = MB_Load ($input["table"], $key);
          eval (\\\'$calcvalue = \\\'.$expr.\\\';\\\');
          $subform = array();
          $subform["input"] = $input;
          $subform["input"]["value"] = $key;
          $subform["input"]["calcvalue"] = $calcvalue;
          $subform["postcode"] = \\\'
            uuse ("dhtml");
            uuse ("tables");
            if ($input["autocomplete"] == "yes") {
              echo DHTML_EmbedJavaScript ("opener.opener.$(\"#".$input["fieldauto"]."\").data(\"autocomplete\")._select(\"{$input["value"]}\", \"{$input["calcvalue"]}\");");
            } else {
              echo DHTML_EmbedJavaScript (DHTML_Parent_Parent_SetValue ($input["field"], html_entity_decode($input["value"])));     
              echo DHTML_EmbedJavaScript (DHTML_Parent_Parent_SetDynamicObject ($input["dyn"], N_HtmlEntities(html_entity_decode($input["calcvalue"])))); 
            }
            $popuplevel = 2;
            $key = $input["value"]; // provide extrapostcode with the same context as in the other eval
            if ($input["extrapostcode"]) eval ($input["extrapostcode"]);
            echo DHTML_EmbedJavaScript (DHTML_Parent_Parent_PerfectSize ());
            echo DHTML_EmbedJavaScript (DHTML_CloseParent ());
            $gotook="closeme";
            $value = $input["value"];
          \\\'.$input["extracode"];
          $urltitle = ML ("Kies", "Choose");
          $url = FORMS_URL ($subform);
          echo \\\'<a title="\\\'.$urltitle .\\\'" href="\\\'.$url.\\\'"><img border=0 src="/ufc/rapid/openims/icon_arrow_next.gif"></a>\\\';
        }
      \')
    );

    foreach ($input["specs"] as $title => $expr) {
      array_push ($atable["tableheads"], $title);
      array_push ($atable["sort"], $expr);
      array_push ($atable["content"], \'echo "<a class=\"ims_navigation\" title=\"$urltitle\" href=\"$url\">".(\'.$expr.\')."</a>";\');
    }   
    $form["formtemplate"] .= TABLES_Auto ($atable);
    $form["formtemplate"] .= \'
        </td></tr>
        <tr><td colspan=2>&nbsp;</td></tr>
        <tr><td colspan=2><center>[[[CANCEL]]]</center></td></tr>
      </table>
    \';
    N_Redirect (FORMS_URL ($form));

    }
  ';
  $form["postcode"] = '
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
    $result = MB_TurboMultiQuery ($input["table"], array (
      "filter" => array ($exp, $data["filter"]),
      "slice" => "count",
      "select" => $input["select"],
      "slowselect" => $input["slowselect"],
      "wherein" => $input["wherein"]
    ));
    if ($result==1) {

      $result = MB_TurboMultiQuery ($input["table"], array (
        "filter" => array ($exp, $data["filter"]),
        "select" => $input["select"],
        "slowselect" => $input["slowselect"],
        "wherein" => $input["wherein"]
      ));
      reset ($result);
      list ($dummy, $key) = each ($result);
      reset ($input["specs"]);
      list ($dummy, $expr) = each($input["specs"]);
      $object = $record = MB_Load ($input["table"], $key);
      eval (\'$calcvalue = \'.$expr.\';\');
      
      if ($input["autocomplete"] == "yes") {
        echo DHTML_EmbedJavaScript ("opener.$(\"#".$input["fieldauto"]."\").data(\"autocomplete\")._select(\"$key\", \"{$calcvalue}\");");
      } else {
        echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ($input["field"], html_entity_decode($key)));     
        echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ($input["dyn"], N_HtmlEntities(html_entity_decode($calcvalue)))); 
      }
      $popuplevel = 1;
      if ($input["extrapostcode"]) eval ($input["extrapostcode"]);
      $value = $key;
      if ($input["extracode"]) eval ($input["extracode"]);
      N_Redirect ("closeme");

    } else {

    $atable = array (
      "input" => $input,
      "name" => "testhypertable",
      "style" => "ims",
      "maxlen" => MAX_LIST_SIZE,
      "filter" => $data["filter"],
      "table" => $input["table"],      
      "tablespecs" => array (
        "sort_default_col" => 2, "sort_default_dir" => "u", 
        "sort_2"=>"auto", "sort_3"=>"auto", "sort_4"=>"auto", "sort_5"=>"auto",
        "sort_6"=>"auto", "sort_7"=>"auto", "sort_8"=>"auto", "sort_9"=>"auto"
      ),
      "tableheads" => array (""),
      "sort" => array (""),
      "select" => $input["select"],
      "slowselect" => $input["slowselect"],
      "wherein" => $input["wherein"],
      "content" => array (\'
        if ($input) {
          reset ($input["specs"]);
          list ($dummy, $expr) = each($input["specs"]);
          $object = $record = MB_Load ($input["table"], $key);
          eval (\\\'$calcvalue = \\\'.$expr.\\\';\\\');
          $subform = array();
          $subform["input"] = $input;
          $subform["input"]["value"] = $key;
          $subform["input"]["calcvalue"] = $calcvalue;
          $subform["postcode"] = \\\'
            uuse ("dhtml");
            uuse ("tables");
            if ($input["autocomplete"] == "yes") {
              echo DHTML_EmbedJavaScript ("opener.opener.$(\"#".$input["fieldauto"]."\").data(\"autocomplete\")._select(\"{$input["value"]}\", \"{$input["calcvalue"]}\");");
            } else {
              echo DHTML_EmbedJavaScript (DHTML_Parent_Parent_SetValue ($input["field"], html_entity_decode($input["value"])));     
              echo DHTML_EmbedJavaScript (DHTML_Parent_Parent_SetDynamicObject ($input["dyn"], N_HtmlEntities(html_entity_decode($input["calcvalue"])))); 
            }
            $popuplevel = 2;
            $key = $input["value"]; // provide extrapostcode with the same context as in the other eval
            if ($input["extrapostcode"]) eval ($input["extrapostcode"]);
            echo DHTML_EmbedJavaScript (DHTML_Parent_Parent_PerfectSize ());
            echo DHTML_EmbedJavaScript (DHTML_CloseParent ());
            $gotook="closeme";
            $value = $input["value"];
          \\\'.$input["extracode"];
          $urltitle = ML ("Kies", "Choose");
          $url = FORMS_URL ($subform);
          echo \\\'<a title="\\\'.$urltitle .\\\'" href="\\\'.$url.\\\'"><img border=0 src="/ufc/rapid/openims/icon_arrow_next.gif"></a>\\\';
        }
      \')
    );
    foreach ($input["specs"] as $title => $expr) {
      array_push ($atable["tableheads"], $title);
      array_push ($atable["sort"], $expr);
      array_push ($atable["content"], \'echo "<a class=\"ims_navigation\" title=\"$urltitle\" href=\"$url\">".(\'.$expr.\')."</a>";\');
    }   
    $form["formtemplate"] .= TABLES_Auto ($atable);
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
  T_Start ("ims", array("extratop"=>1, "extrabottom"=>1, "noheader"=>"yes", "extra-table-props"=>"width=113", "nobr"=>"yes"));
  echo DHTML_DynamicObject ($calcvalue."&nbsp;", "dyn_".$fieldname);
  $dhtmlobject = TS_End();
  global $myconfig;
  if ($autocomplete) {
    DHTML_RequireAll();
    $dhtmlobject = "<input id='fieldauto_{$fieldname}' name='fieldauto_{$fieldname}' value='".N_HtmlEntities(html_entity_decode($calcvalue))."' class=\"dontfocusmeandstoptrying\" alt=\"".N_HtmlEntities($fieldtitletext)."\" />";
    //$dhtmlobject .= '<a href="#" onclick="alert($(\''.DHTML_EncodeJsString("input[name='field_{$fieldname}']").'\').val()); return false;">debug</a>';
    
    uuse("dyna");
    $dynacode = '

      $input = $serverinput;

      // Load siteconfig (not default with dyna.php)
      global $myconfig;
      if (file_exists (getenv("DOCUMENT_ROOT") . "/config/{$input["sgn"]}/siteconfig.php")) {
        include (getenv("DOCUMENT_ROOT") . "/config/{$input["sgn"]}/siteconfig.php");
      }

      // dvb & lf 10-09-2012 enable lowlevel functions for dynamic FK_EDIT fields
      global $disableflex;
      $disableflex = "no";
      uuse ("flex");
      FLEX_LoadAllLowLevelFunctions ();

      foreach ($input["specs"] as $title => $expr) {
        if (!$firstexpr) $firstexpr = $expr;
        if ($exp) {
          $exp .= " . " . $expr;
        } else {
          $exp = $expr;
        }
      }

      $qitems = trim(N_UTF2HTML(stripslashes($_REQUEST["items"])));
      if ($qitems) { // retrieve items by internal values (needed to initialise multi-select version)
        $qitems = explode(";", $qitems);
        $result = array();
        // Just check that the records exist
        foreach ($qitems as $key) {
          if (MB_Load($input["table"], $key)) $result[$key] = $key;
        }
        $limit = count($result); // no limit
      } else {
        $q = trim(strtolower(SEARCH_RemoveAccents(N_UTF2HTML(stripslashes($_REQUEST["term"])))));
        if (!strlen($q)) $q = ""; // leave "0" alone, initialize the other falsy values
        $limit = $input["maxresults"];
        if ($_REQUEST["limit"] && $_REQUEST["limit"] < $serverinput["maxresults"]) $limit = $_REQUEST["limit"];
        $items = array();
        
        if ($q . "" != "") {
          $words = explode (" ", MB_Text2Words ($q, true));
          
          $qspecs = array(
            "leftfilter" => array ($firstexpr, $words[0]),
            "filter" => array ($exp, $q),
            "slice" => array(1, $limit + 1),
            "sort" => $firstexpr,
            "select" => $input["select"],
            "slowselect" => $input["slowselect"],
            "wherein" => $input["wherein"]
          );

          // First find results that start with the first word of the query (and have the remainder of the query anywhere)
          if (MB_MUL_Engine($input["table"]) == "MYSQL") {
            $result = MB_TurboMultiQuery ($input["table"], $qspecs);
         
            // Then supplement with results containing the query anywhere
            if (count($result) < $limit) {
              unset($qspecs["leftfilter"]);
              $result2 = MB_TurboMultiQuery ($input["table"], $qspecs);
              // Since this query will return duplicate results with the previous query,
              // the slice should not be smaller than $limit + 1.
              foreach ($result2 as $key2 => $val2) // array_merge has feature where numeric keys are renumbered 
                if (!$result[$key2])
                  $result[$key2] = $val2;
            }
          } else { // virtual tabel?
            FLEX_LoadSupportFunctions(IMS_SuperGroupName()); // This doesnt actually work with dyna.php
            unset($qspecs["leftfilter"]);
            $result = MB_MultiQuery ($input["table"], $qspecs);
          }
        } else { // geen zoekterm
          $qspecs = array(
            "slice" => array(1, $limit + 1),
            "sort" => $firstexpr,
            "select" => $input["select"],
            "slowselect" => $input["slowselect"],
            "wherein" => $input["wherein"]
          );
          $result = MB_TurboMultiQuery ($input["table"], $qspecs);
        }
      }
      foreach ($result as $key => $dummy) {
        $record = MB_Load($input["table"], $key);
        $object = $record = MB_Load ($input["table"], $key);
        $item = array();
        eval (\'$calcvalue = \'.$firstexpr.\';\');
        $item["label"] = FORMS_ML_Filter($calcvalue);
        $item["value"] = $key;
        if (count($input["specs"]) > 1) {
          foreach ($input["specs"] as $title => $expr) {
            eval (\'$partcalcvalue = \'.$expr.\';\');
            $item["columns"][] = FORMS_ML_Filter($partcalcvalue);
          }
        }
        $items[] = $item;
      }
      if (!$items) $items[] = array("value" => "", "unselectable" => true, "class" => "noresults", "label" => "(" . ML("geen resultaten", "no results") . ")");
      if ((count($items) > $limit)) {
        $items = array_slice($items, 0, $limit);
        $items[] = array("value" => "", "unselectable" => true, "class" => "moreresults", "label" => "(" . ML("meer dan %1 resultaten, verfijn uw zoekvraag", "more than %1 resultaten, please refine your query", $limit) . ")");
      }

      echo "[";
      $first = true;
      foreach ($items as $item) {
        if (!$first) echo ","; 
        if (!$item["label"]) $item["label"] = "?";
        $value = DHTML_EncodeJsString(html_entity_decode($item["value"]), false, true);
        echo "{\"value\":\"{$value}\"";
        foreach ($item as $thetitle => $thevalue) {
          if ($thetitle == "value" || $thetitle == "columns") continue;
          $thevalue = DHTML_EncodeJsString(html_entity_decode($thevalue), false, true);
          $thetitle = DHTML_EncodeJsString(html_entity_decode($thetitle), false, true);
          echo ",\"{$thetitle}\":\"{$thevalue}\"";
        }
        if ($item["columns"]) {
          echo ",\"columns\":[";
          $firstcol = true;
          foreach ($item["columns"] as $thevalue) {
            if (!$firstcol) echo ","; 
            if ($firstcol && !$thevalue) $thevalue = "?";
            $thevalue = DHTML_EncodeJsString(html_entity_decode($thevalue), false, true);
            echo "\"{$thevalue}\"";
            $firstcol = false;
          }
          echo "]";
        }
        echo "}";
        $first = false;
      }
      echo "]";
   
    ';
    $dynainput = $form["input"];
    $dynainput["sgn"] = IMS_SuperGroupName();
    $dynainput["maxresults"] = ( $myconfig[IMS_SuperGroupName()]["autocomplete"]["maxresults"] ? $myconfig[IMS_SuperGroupName()]["autocomplete"]["maxresults"] : 10 );
    $sid = DYNA_CreateAPI ($dynacode, $dynainput);
    $source = '"/nkit/dyna.php?sid='.$sid.'"';
    SHIELD_FlushEncoded();
    
    if (count($specs) > 1) {
      $columns = array();
      foreach ($specs as $thetitle => $dummy) {
        $columns[] = '"' . DHTML_EncodeJsString(FORMS_ML_Filter($thetitle)) . '"';
      }
    }

    $js = '
      $(function() {
        var fieldname = "'.$fieldname.'";
        var source = '.$source.';
        var defaultlabel = "'. DHTML_EncodeJsString(html_entity_decode("")).'";
        var defaultvalue = "'. DHTML_EncodeJsString(html_entity_decode("")).'";
        var maxresults = ' . $dynainput["maxresults"] . ';
        var columns = ' . ($columns ? '[' . implode(",", $columns) . ']' : 'false') . ';
        var multi = ' . ($multi ? "true" : "false") . ';
        JQK_AutocompleteList(fieldname, source, defaultlabel, defaultvalue, maxresults, columns, multi);
      });
    ';

    // LF201107: Changed hover color to SugarCRM's cornflower blue by request from EvK. As a consequence, background needed to revert to white instead of yellow.
    $style = ' 
      <style>
        .ui-autocomplete-loading#fieldauto_'.$fieldname.' { background: white url("/openims/libs/jquery/ui-1.8.13/css/openims/images/ui-anim_basic_16x16.gif") right center no-repeat; }
        #ul_'.$fieldname.' {
          white-space:nowrap;
          background: #fff; /* Old: #fdffce */
        }
        #ul_'.$fieldname.' .moreresults {
          color: #666;
          font-style: italic;
        }
        #ul_'.$fieldname.' .noresults {
          color: #c00;
          font-style: italic;
        }
        #ul_'.$fieldname.' .ui-state-hover {
          border: 1px dotted #999999; 
          background: #94c1e8; 
          font-weight: normal; 
          color: #212121;
        }
        #ul_selected_'.$fieldname.' li {
          border: 1px solid #AAA;
          background-color: #fff;
          padding: 1px 4px 1px 4px;
          white-space: nowrap;
        }
      </style>
    ';

    if ( $scrollbar_height = $myconfig[IMS_supergroupname()]["autocomplete"]["scrollbar_height"] )
    {
    $style .= "<style type='text/css'>
  .ui-autocomplete {
    max-height: ".$scrollbar_height.";
    overflow-y: auto;
    /* prevent horizontal scrollbar */
    overflow-x: hidden;
  }</style>";
  }

    $dhtmlobject .= '<input type=hidden id="field_'.$fieldname.'" name="field_'.$fieldname.'" value="'.N_HtmlEntities (html_entity_decode($value)).'">';
    if ($myconfig[IMS_SuperGroupName()]["autocompletefieldspopupbutton"] == "no") {
      $popupbutton1 = $popupbutton2 = "";
    } else {
      $popupbutton1 = '&nbsp;<a title="'.ML("Kies","Select").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>';
      $popupbutton2 = '&nbsp;<a title="'.ML("Kies","Select").'" class="ims_navigation" href="'.$url.'">'.ML("Kies","Select").'</a>';
    }

    $multiselectedobject = "";
    if ($multi) {
      $multiselectedobject = "<div style='padding: 3px 0 0 0; font-size:12px; font-family:arial;' id='divdyn_selected_".$fieldname."'></div><div style='clear:both'></div>"; // object should be empty, will be initialized by javascript
    }
    return $style . $multiselectedobject . DHTML_InvisiTable ('<font face="arial" size="2">', "</font>",
      $dhtmlobject,
      $popupbutton1,
      $popupbutton2
    ) . DHTML_EmbedJavaScript($js);
    
  } else {  
  
    return DHTML_InvisiTable ('<font face="arial" size="2">', "</font>",
      $dhtmlobject,
      '<input type=hidden name="field_'.$fieldname.'" value="'.N_HtmlEntities ($value).'">&nbsp;<a title="'.ML("Kies","Select").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>',
      '&nbsp;<a title="'.ML("Kies","Select").'" class="ims_navigation" href="'.$url.'">'.ML("Kies","Select").'</a>'
    );
  }
}

function FORMS_LIST_Edit ($fieldname, $value, $specs, $fieldtitletext = "", $multi = false) 
{
  // LF: Note about html_entity_decode:
  // IHMO the field SHOULD work with HTML stuff in $specs["values"], such as "Bed & Breakfast" etc.
  // It didn't work in the past, but many problems (Bed & Breakfast) got solved by the browser, whereas
  // other problems were solved by using escaped input ("&lt;none&gt;"). This is risky because error
  // correction in browsers depend on the DOCTYPE and can also work differently on static content
  // than on dynamic content.
  // To make both types of input work, I have added N_HtmlEntities everywhere where values are echo'd,
  // or where a value is given to javascript and used as parameter to innerHTML (DHTML_SetDynamicObject). 
  // To prevent double escaping of already escaped input, I did html_entity_decode *before* each N_HtmlEntities.  

  // NOTE: $multi = true works ONLY with autocomplete

  if (!$fieldtitletext) $fieldtitletext = $specs["title"];
  global $myconfig;
  foreach ($specs["values"] as $visual => $internal) {
    if ($firstvisual == "") { $firstvisual = $visual; $firstvalue = $internal;}
    if ($value == $internal) {
      $showvalue = FORMS_ML_Filter($visual);
    }
  }
  if (!$showvalue && $firstvisual && $myconfig[IMS_SuperGroupName()]["autocompletefields"] != "yes") {
    $value = $firstvalue;
    $showvalue = $firstvisual;  
  }
  $form = array();
  $form["title"] = $specs["title"];
  $form["input"]["specs"] = $specs;
  $form["input"]["fieldname"] = $fieldname;
  $form["input"]["value"] = $value;
  $form["input"]["values"] = $specs["values"];

  if ($myconfig[IMS_SuperGroupName()]["autocompletefields"] == "yes") $form["input"]["autocomplete"] = "yes";
  $form["metaspec"]["fields"]["filter"]["type"] = "string";
  $form["formtemplate"] = '
    <table>
      <tr><td><font face="arial" size=2><b>'.ML("Zoeken naar","Search for").':</b></font></td><td>[[[filter]]]</td></tr>
      <tr><td colspan=2>&nbsp;</td></tr>
      <tr><td colspan=2><font face="arial" size=2><center>'.ML("Laat het zoekveld leeg voor alle opties", "Leave searchfield empty for all options").'</center></font></td></tr>
      <tr><td colspan=2><center>[[[OK:'.ML("Zoeken","Search").']]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
    </table>
  ';
  $form["precode"] = '
    if ($input["autocomplete"] == "yes") { // skip this form, go directly to results
      N_Redirect(N_AlterUrl(N_MyFullUrl(), "command", "handlesuperform"));
    }
  ';
  $form["postcode"] = '
    $values = array();
    foreach ($input["values"] as $visual => $internal) {
      if (!$data["filter"] || strpos(strtoupper(" ".$visual), strtoupper($data["filter"]))) {
        $values[FORMS_ML_Filter($visual)]=$internal;
      }
    }
    if (!$values) FORMS_ShowError ("Foutmelding", "Niets gevonden", true);        
    if (count($values)==1) { 
      foreach ($values as $visual => $internal) {
        if ($input["autocomplete"] == "yes") {
          // Use the _select method provided in jquerykit.js; will work both for single and multi select.
          echo DHTML_EmbedJavaScript ("opener.$(\"#fieldauto_".$input["fieldname"]."\").data(\"autocomplete\")._select(\"{$internal}\", \"{$visual}\");");
        } else {
          echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_".$input["fieldname"], html_entity_decode($internal)));
          echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_".$input["fieldname"], N_HtmlEntities(html_entity_decode($visual))));
        }
        N_Redirect ("closeme");
      }
    } else {
      if ($input["specs"]["sort"]) ksort ($values);
      T_Start ("ims");
      if ($input["specs"]["title"]) {
        echo $input["specs"]["title"];
      } else {
        echo ML("Keuze", "Choice");
      }
      T_NewRow();    
      foreach ($values as $visual => $internal) {
        if ($input["autocomplete"] == "yes" && !$internal) continue; // Skip the empty value ("Kies...", "<geen>" or whatever), because emptying/deleting input is already possible through other means. Additionally, with multiple select, the value would actually get *added* to the item list, which is wrong.
        $form = array();
        $form["input"] = $input;
        $form["input"]["visual"] = $visual;
        $form["input"]["internal"] = $internal;
        $form["postcode"] = \'
          if ($input["autocomplete"] == "yes") {
            // Use the _select method provided in jquerykit.js; will work both for single and multi select.
            echo DHTML_EmbedJavaScript ("opener.opener.$(\"#fieldauto_".$input["fieldname"]."\").data(\"autocomplete\")._select(\"{$input["internal"]}\", \"{$input["visual"]}\");");
          } else {
            echo DHTML_EmbedJavaScript (DHTML_Parent_Parent_SetValue ("field_".$input["fieldname"], html_entity_decode($input["internal"])));
            echo DHTML_EmbedJavaScript (DHTML_Parent_Parent_SetDynamicObject ("dyn_".$input["fieldname"], N_HtmlEntities(html_entity_decode($input["visual"]))));
          }
          echo DHTML_EmbedJavaScript (DHTML_CloseParent ());
          $gotook="closeme";
        \';
        $url = FORMS_URL ($form);
        echo "<a class=\"ims_navigation\" href=\"$url\">".N_HtmlEntities(html_entity_decode($visual))."</a>";
        T_NewRow();
      }
      $form = array();
      $form["formtemplate"] = TS_End()."<br>[[[cancel]]]";    
      $form["title"] = $input["specs"]["title"];
      N_Redirect (FORMS_URL ($form));
    }
  ';
  $url = FORMS_URL ($form);
  T_Start ("ims", array("extratop"=>1, "extrabottom"=>1, "noheader"=>"yes", "extra-table-props"=>"width=113", "nobr"=>"yes"));
  echo DHTML_DynamicObject (N_HtmlEntities(html_entity_decode($showvalue))."&nbsp;", "dyn_".$fieldname);
  $dhtmlobject = TS_End();
  global $myconfig;
  if ($myconfig[IMS_SuperGroupName()]["autocompletefields"] == "yes") {
    DHTML_RequireAll();
    // Encode, but prevent double encoding, using both html_entity_decode and N_HtmlEntities
    $dhtmlobject = "<input id='fieldauto_{$fieldname}' name='fieldauto_{$fieldname}' value='".N_HtmlEntities(html_entity_decode($showvalue))."' class=\"dontfocusmeandstoptrying\" alt=\"".N_HtmlEntities($fieldtitletext)."\" />";
    // dontfocusmeandstoptrying: if this field is the FIRST visible field on the form, no field will receive focus.
    //$dhtmlobject .= '<a href="#" onclick="alert($(\''.DHTML_EncodeJsString("input[name='field_{$fieldname}']").'\').val()); return false;">debug</a>';

    $style = ' 
      <style>
        .ui-autocomplete-loading#fieldauto_'.$fieldname.' { background: white url("/openims/libs/jquery/ui-1.8.13/css/openims/images/ui-anim_basic_16x16.gif") right center no-repeat; }
        #ul_'.$fieldname.' {
          white-space:nowrap;
          background: #fff;
        }
        #ul_'.$fieldname.' .moreresults {
          color: #666;
          font-style: italic;
        }
        #ul_'.$fieldname.' .noresults {
          color: #c00;
          font-style: italic;
        }
        #ul_'.$fieldname.' .ui-state-hover {
          border: 1px dotted #999999; 
          background: #94c1e8; 
          font-weight: normal; 
          color: #212121;
        }
        #ul_selected_'.$fieldname.' li {
          border: 1px solid #AAA;
          background-color: #fff;
          padding: 1px 4px 1px 4px;
          white-space: nowrap;
        }
      </style>
    ';
    
    $values = $specs["values"];
    if ($specs["sort"]) ksort($values);

    // Defaultlabel / defaultvalue: if a list item has an empty value, use its label as the defaultlabel.
    // Otherwise, use an empty string as default label. This is different from the non-autocomplete version,
    // which uses the first list item as default
    foreach ($values as $visible => $internal) {
      if (!$internal) {
        $defaultlabel = $visible;
        $defaultvalue = "";
      }
    }
    if (!$defaultlabel) {
      //$defaultlabel = $firstvisual; // $firstvisual, $firstvalue determined earlier
      //$defaultvalue = $firstvalue;
      $defaultlabel = $defaultvalue = "";
    }
    global $myconfig;
    $maxresults = $myconfig[IMS_supergroupname()]["autocomplete"]["maxresults"] ? $myconfig[IMS_supergroupname()]["autocomplete"]["maxresults"] : 10;
    if ( $scrollbar_height = $myconfig[IMS_supergroupname()]["autocomplete"]["scrollbar_height"] )
    {
    $style .= "<style type='text/css'>
  .ui-autocomplete {
    max-height: ".$scrollbar_height.";
    overflow-y: auto;
    /* prevent horizontal scrollbar */
    overflow-x: hidden;
  }</style>";
  }

    uuse("dyna");
    $dynacode = '
      $items = $items1 = $items2 = array();
      $broke = false;

      $qitems = trim(N_UTF2HTML(stripslashes($_REQUEST["items"])));
      if ($qitems) { // retrieve items by internal values (needed to initialise multi-select version
        $qitems = explode(";", $qitems);
        foreach ($serverinput["values"] as $visible => $internal) {
          if (in_array($internal, $qitems)) {
            $items[] = array("internal" => $internal, "visible" => FORMS_ML_Filter($visible));
          }
        }
      } else { // standard search
        $q = trim(strtolower(SEARCH_RemoveAccents(N_UTF2HTML(stripslashes($_REQUEST["term"])))));   
        if (!strlen($q)) $q = ""; // leave "0" alone, initialize the other falsy values   
        $limit = $serverinput["maxresults"];
        if ($_REQUEST["limit"] && $_REQUEST["limit"] < $serverinput["maxresults"]) $limit = $_REQUEST["limit"];
        foreach ($serverinput["values"] as $visible => $internal) {
          if (!$internal) continue;
          $check  = trim(strtolower(SEARCH_RemoveAccents(FORMS_ML_Filter($visible))));
          if ($q === "" || QRY_SlowFilter($check, $q)) {
            $item = array("internal" => $internal, "visible" => FORMS_ML_Filter($visible));
            if ($q === "" || substr($check, 0, strlen($q)) == $q) {
              $items1[] = $item;
            } else {
              $items2[] = $item;
            }
            if (count($items1) > $limit) { $broke = true; break; }
          }
        }
        $items = array_merge($items1, $items2);
        if (!$items) $items[] = array("internal" => "", "unselectable" => true, "class" => "noresults", "visible" => "(" . ML("geen resultaten", "no results") . ")");
        if ((count($items) > $limit) || $broke) {
          $items = array_slice($items, 0, $limit);
          $items[] = array("internal" => "", "unselectable" => true, "class" => "moreresults", "visible" => "(" . ML("meer dan %1 resultaten, verfijn uw zoekvraag", "more than %1 resultaten, please refine your query", $limit) . ")");
        }
      }  
      echo "[";
      $first = true;
      foreach ($items as $item) {
        if (!$first) echo ","; 
        $internal = DHTML_EncodeJsString(html_entity_decode($item["internal"]), false, true);
        $visible = DHTML_EncodeJsString(html_entity_decode($item["visible"]), false, true);
        $class = DHTML_EncodeJsString(html_entity_decode($item["class"]), false, true);

        echo "{\"value\":\"{$internal}\",\"label\":\"{$visible}\"";
        if ($class) echo ",\"class\":\"{$class}\"";
        if ($item["unselectable"]) echo ",\"unselectable\":true";
        echo "}";
        $first = false;
      }
      echo "]";
    ';
    $sid = DYNA_CreateAPI ($dynacode, array("values" => $values, "maxresults" => $maxresults));
    $source = '"/nkit/dyna.php?sid='.$sid.'"';
    SHIELD_FlushEncoded();
    
    $js = '
      $(function() {
        var fieldname = "'.$fieldname.'";
        var source = '.$source.';
        var defaultlabel = "'. DHTML_EncodeJsString(html_entity_decode(FORMS_ML_Filter($defaultlabel))).'";
        var defaultvalue = "'. DHTML_EncodeJsString(html_entity_decode($defaultvalue)).'";
        var maxresults = ' . $maxresults . ';
        var columns = false; // no columns
        var multi = ' . ($multi ? "true" : "false") . ';
        JQK_AutocompleteList(fieldname, source, defaultlabel, defaultvalue, maxresults, columns, multi);
      });
    ';

    $dhtmlobject .= '<input type=hidden id="field_'.$fieldname.'" name="field_'.$fieldname.'" value="'.N_HtmlEntities (html_entity_decode($value)).'">';
    if ($myconfig[IMS_SuperGroupName()]["autocompletefieldspopupbutton"] == "no") {
      $popupbutton1 = $popupbutton2 = "";
    } else {
      $popupbutton1 = '&nbsp;<a title="'.ML("Kies","Select").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>';
      $popupbutton2 = '&nbsp;<a title="'.ML("Kies","Select").'" class="ims_navigation" href="'.$url.'">'.ML("Kies","Select").'</a>';
    }

    $multiselectedobject = "";
    if ($multi) {
      $multiselectedobject = "<div style='padding: 3px 0 0 0; font-size:12px; font-family:arial;' id='divdyn_selected_".$fieldname."'></div><div style='clear:both'></div>"; // object should be empty, will be initialized by javascript
    }

    return $style . $multiselectedobject . DHTML_InvisiTable ('<font face="arial" size="2">', "</font>",
      $dhtmlobject,
      $popupbutton1,
      $popupbutton2
    ) . DHTML_EmbedJavaScript($js);
   
  } else {
    return DHTML_InvisiTable ('<font face="arial" size="2">', "</font>",
      $dhtmlobject,
      '<input type=hidden name="field_'.$fieldname.'" value="'.N_HtmlEntities (html_entity_decode($value)).'">&nbsp;<a title="'.ML("Kies","Select").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>',
      '&nbsp;<a title="'.ML("Kies","Select").'" class="ims_navigation" href="'.$url.'">'.ML("Kies","Select").'</a>'
    );
  }
}

function FORMS_FK_View ($table, $value, $expr)
{
  $key = $value;
  $object = $record = MB_Load ($table, $key);
  eval ('$calcvalue = '.$expr.';');
  return $calcvalue;
}

function FORMS_HYP_Edit ($fieldname, $value)
{
  $hyperspec = unserialize ($value);
  $form["title"] = ML("Hyperlink", "Hyperlink");
  $form["input"]["tmpkey"] = N_GUID();
  $form["input"]["fieldname"] = $fieldname;
  $form["input"]["url"] = $hyperspec["url"];
  $form["input"]["text"] = $hyperspec["text"];
  $form["input"]["title"] = $hyperspec["title"];
  $form["metaspec"]["fields"]["url"]["type"] = "string";
  $form["metaspec"]["fields"]["text"]["type"] = "string";
  $form["metaspec"]["fields"]["title"]["type"] = "string";
  $form["formtemplate"] = '
    <table>
      <tr><td><font face="arial" size=2><b>'.ML("URL","URL").':</b></font></td><td>[[[url]]]</td></tr>
      <tr><td><font face="arial" size=2><b>'.ML("Tekst","Text").':</b></font></td><td>[[[text]]]</td></tr>
      <tr><td><font face="arial" size=2><b>'.ML("Titel","Title").':</b></font></td><td>[[[title]]]</td></tr>
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
    </table>
  ';         
  $form["precode"] = '
    if ($tmp = TMP_LoadObject ($input["tmpkey"])) {
      $data["url"] = $tmp["url"];
      $data["text"] = $tmp["text"];
      $data["title"] = $tmp["title"];
    } else {
      $data["url"] = $input["url"];
      $data["text"] = $input["text"];
      $data["title"] = $input["title"];    
    }
  ';
  $form["postcode"] = '
    uuse ("dhtml");
    uuse ("tables");
    $content = "<a target=\"_blank\" title=\"".$data["title"]."\" href=\"".$data["url"]."\">".$data["text"]."</a>";
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_".$input["fieldname"], $content));
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_url_".$input["fieldname"], $data["url"]));
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_title_".$input["fieldname"], $data["title"]));
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_text_".$input["fieldname"], $data["text"]));
    TMP_SaveObject ($input["tmpkey"], array ("url" => $data["url"], "text"=>$data["text"], "title" => $data["title"]));
    $gotook="closeme";
  ';
  $url = FORMS_URL ($form);
  $content = "<a target=\"_blank\" title=\"".$hyperspec["title"]."\" href=\"".$hyperspec["url"]."\">".$hyperspec["text"]."</a>";


  return DHTML_InvisiTable ('<font face="arial" size="2">', "</font>", 
    DHTML_DynamicObject ($content."&nbsp;", "dyn_".$fieldname),
    '<input type=hidden name="field_url_'.$fieldname.'" value="'.N_HtmlEntities ($hyperspec["url"]).'">',
    '<input type=hidden name="field_text_'.$fieldname.'" value="'.N_HtmlEntities ($hyperspec["text"]).'">',
    '<input type=hidden name="field_title_'.$fieldname.'" value="'.N_HtmlEntities ($hyperspec["title"]).'">',
    '&nbsp;',
    '<a title="'.ML("Kies","Select").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/edit_small.gif"></a>',
    '&nbsp;',
    '<a title="'.ML("Kies","Select").'" class="ims_navigation" href="'.$url.'">'.ML("Wijzig","Edit").'</a>'
  );
}

function FORMS_IMG_Edit ($fieldname, $value)
{
  $form["title"] = ML("Plaatje", "Image");
  $form["input"]["fieldname"] = $fieldname;
  $form["metaspec"]["fields"]["image"]["type"] = "file";
  $form["formtemplate"] = '
    <table>
      <tr><td><font face="arial" size=2><b>'.ML("Bestand","File").':</b></font></td><td>[[[image]]]</td></tr>
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
    </table>
  ';         
  $form["postcode"] = '
    uuse ("dhtml");
    uuse ("tables");
    uuse ("assets");
    if ($files["image"]["name"]) { 
      $img = ASSETS_StoreImage (IMS_SuperGroupName(), $files["image"]["name"], $files["image"]["content"]);
    } else {
      $img = "/openims/blank.gif";
    }
    $content = ASSETS_ShowImageThumbnail ($img);
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_1".$input["fieldname"], $content));
    $view = DHTML_InvisiTable (\'<font face="arial" size="2">\', "</font>", 
      \'<a target="_blank" title="\'.ML("Bekijk","View").\'" href="\'.$img.\'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>\',
      \'&nbsp;\',
      \'<a target="_blank" title="\'.ML("Bekijk","View").\'" class="ims_navigation" href="\'.$img.\'">\'.ML("Bekijk","View").\'</a>\'
    ); 
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_2".$input["fieldname"], $view));
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_".$input["fieldname"], $img));
    $gotook="closeme";
  ';
  $url = FORMS_URL ($form);
  $show = $value;
  T_Start ("ims", array ("extratop"=>1, "extrabottom"=>1)); 
  echo DHTML_DynamicObject (ASSETS_ShowImageThumbnail ($value), "dyn_1".$fieldname);
  $dyn1 = TS_End(); 
  $view = DHTML_InvisiTable ('<font face="arial" size="2">', "</font>", 
    '<a target="_blank" title="'.ML("Bekijk","View").'" href="'.$show.'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>',
    '&nbsp;',
    '<a target="_blank" title="'.ML("Bekijk","View").'" class="ims_navigation" href="'.$show.'">'.ML("Bekijk","View").'</a>'
  ); 
  $dyn2 = DHTML_DynamicObject ($view, "dyn_2".$fieldname); 
  return DHTML_InvisiTable ('<font face="arial" size="2">', "</font>", 
    $dyn1,
    '<input type=hidden name="field_'.$fieldname.'" value="'.N_HtmlEntities ($value).'">',
    '&nbsp;',
    $dyn2,
    '&nbsp;',
    '<a title="'.ML("Wijzig","Edit").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/edit_small.gif"></a>',
    '&nbsp;',
    '<a title="'.ML("Wijzig","Edit").'" class="ims_navigation" href="'.$url.'">'.ML("Wijzig","Edit").'</a>'
  ); 
}

function FORMS_Diskfile_Edit ($fieldname, $value)
{

  FLEX_LoadSupportFunctions (IMS_SuperGroupName());
  if (function_exists ("FORMS_Diskfile_Edit_custom")) {
    return  FORMS_Diskfile_Edit_custom ($fieldname, $value);
  }

  $form["title"] = ML("Bestand", "File");
  $form["input"]["fieldname"] = $fieldname;
  $form["metaspec"]["fields"]["diskfile"]["type"] = "file";
  $form["formtemplate"] = '
    <table>
      <tr><td><font face="arial" size=2><b>'.ML("Bestand","File").':</b></font></td><td>[[[diskfile]]]</td></tr>
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
    </table>
  ';         
  $form["postcode"] = '
    uuse ("dhtml");
    uuse ("tables");
    uuse ("assets");
    if ($files["diskfile"]["name"]) { 
      $img = ASSETS_StoreImage (IMS_SuperGroupName(), $files["diskfile"]["name"], $files["diskfile"]["content"]);
      // being (mis)used here for storing any file on disk for field of type diskfile
    }
    $content = ASSETS_ShowDiskFileName ($img);
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_1".$input["fieldname"], $content));
    $view = DHTML_InvisiTable (\'<font face="arial" size="2">\', "</font>", 
      \'<a target="_blank" title="\'.ML("Bekijk","View").\'" href="\'.$img.\'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>\',
      \'&nbsp;\',
      \'<a target="_blank" title="\'.ML("Bekijk","View").\'" class="ims_navigation" href="\'.$img.\'">\'.ML("Bekijk","View").\'</a>\'
    ); 
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_2".$input["fieldname"], $view));
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_".$input["fieldname"], $img));
    $gotook="closeme";
  ';
  $url = FORMS_URL ($form);
  $show = $value;
  T_Start ("ims", ($value ? array ("extratop"=>1, "extrabottom"=>1) : array("extra-table-props" => "border='0'")));  
  echo DHTML_DynamicObject (ASSETS_ShowDiskFileName($value), "dyn_1".$fieldname);
  $dyn1 = TS_End(); 
  if($value!="")
  {
    $view = DHTML_InvisiTable ('<font face="arial" size="2">', "</font>", 
      '<a target="_blank" title="'.ML("Bekijk","View").'" href="'.$show.'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>',
      '&nbsp;',
      '<a target="_blank" title="'.ML("Bekijk","View").'" class="ims_navigation" href="'.$show.'">'.ML("Bekijk","View").'</a>'
    ); 
    $dyn2 = DHTML_DynamicObject ($view, "dyn_2".$fieldname); 
  }
  if($value=="")
    $editstring = ML("Voeg toe","Add");
  else
    $editstring = ML("Wijzig","Edit"); 
  
  return DHTML_InvisiTable ('<font face="arial" size="2">', "</font>", 
    $dyn1,
    '<input type=hidden name="field_'.$fieldname.'" value="'.N_HtmlEntities ($value).'">',
    '&nbsp;',
    $dyn2,
    '&nbsp;',
    '<a title="'.$editstring.'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/edit_small.gif"></a>',
    '&nbsp;',
    '<a title="'.$editstring.'" class="ims_navigation" href="'.$url.'">'.$editstring.'</a>'
  ); 
}

function FORMS_HTML_Edit ($fieldname, $value)
{
  $form["input"]["value"] = $value;
  $form["input"]["fieldname"] = $fieldname;
  $form["input"]["tmpkey"] = N_GUID();
  $form["postcode"] = '
    uuse ("assets");
    if ($tmp = TMP_LoadObject ($input["tmpkey"])) {
      ASSETS_ReEditHTML (IMS_SuperGroupName(), $tmp);
    } else {
      $newid = ASSETS_EditHTML (IMS_SuperGroupName(), $input["value"]);
      TMP_SaveObject ($input["tmpkey"], $newid);
      echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_".$input["fieldname"], $newid));
      $show = "/nkit/showhtml.php?sgn=".IMS_SuperGroupName()."&assetid=".$newid;
      $view = DHTML_InvisiTable (\'<font face="arial" size="2">\', "</font>", 
        \'<a target="_blank" title="\'.ML("Bekijk","View").\'" href="\'.$show.\'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>\',
        \'&nbsp;\',
        \'<a target="_blank" title="\'.ML("Bekijk","View").\'" class="ims_navigation" href="\'.$show.\'">\'.ML("Bekijk","View").\'</a>\'
      ); 
      echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_".$input["fieldname"], $view));
    }
    $gotook = "closeme";
  '; 
  $url = FORMS_URL ($form);
  $show = "/nkit/showhtml.php?sgn=".IMS_SuperGroupName()."&assetid=".$value;
  $view = DHTML_InvisiTable ('<font face="arial" size="2">', "</font>", 
    '<a target="_blank" title="'.ML("Bekijk","View").'" href="'.$show.'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>',
    '&nbsp;',
    '<a target="_blank" title="'.ML("Bekijk","View").'" class="ims_navigation" href="'.$show.'">'.ML("Bekijk","View").'</a>'
  ); 
  $dyn = DHTML_DynamicObject ($view, "dyn_".$fieldname); 
  return DHTML_InvisiTable ('<font face="arial" size="2">', "</font>", 
    '<input type=hidden name="field_'.$fieldname.'" value="'.N_HtmlEntities ($value).'">',
    $dyn,
    '&nbsp;',
    '<a title="'.ML("Wijzig","Edit").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/edit_small.gif"></a>',
    '&nbsp;',
    '<a title="'.ML("Wijzig","Edit").'" class="ims_navigation" href="'.$url.'">'.ML("Wijzig","Edit").'</a>'
  ); 
}

function FORMS_MD_View ($fieldname, $value, $specs)
{
  return ML("N/B","N/A");
}

function FORMS_MD_BuildTable ($fieldname, $value, $tmpkey, $specs)
{
  /* Very old master-detail table, in which the keys of the records in the "other" table are stored in a multi-value field.
   * Such a field will not be queryable. It's better to define foreign keys in the "other" table and use FORMS_RMD_BuildTable.
   */

  $specs["extratop"] = 1;
  $specs["extrabottom"] = 1;
  $specs["sort_bottomskip"] = 1;
  $specs["sort_default_col"] = 1;
  $specs["no_reload"] = "yes";
  $specs["sort"] = N_GUID();
  $specs["sortlinkcss"] = "ims_md_edit";
  for ($i=1; $i<=count ($specs["fields"]); $i++) {
    $specs["sort_$i"] = "auto";
  }
  T_Start("ims", $specs);
  foreach ($specs["heads"] as $dummy => $head) {
    echo $head;
    T_Next();
  }
  echo " ";
  T_NewRow();
  if (TMP_LoadObject ($tmpkey)) {
    $value = TMP_LoadObject ($tmpkey);
    $value = $value["data"];
  }
  foreach ($value as $key => $data) {
    foreach ($specs["fields"] as $dummy => $field) {
      echo FORMS_ShowValue ($data[$field], $field, $data);
      T_Next();
    }
    $form = array();
    $form["title"] = $specs["title"];
    $form["input"]["tmpkey"] = $tmpkey;
    $form["input"]["value"] = $value;
    $form["input"]["specs"] = $specs;
    $form["input"]["fieldname"] = $fieldname;
    $form["input"]["key"] = $key;
    $form["metaspec"]["fields"] = MB_Ref ("ims_fields", IMS_SuperGroupName());
    $form["formtemplate"] = N_ReadFile ("html::".IMS_SuperGroupName()."/objects/".$specs["form"]."/page.html");
    $form["precode"] = '
      if (TMP_LoadObject ($input["tmpkey"])) {
        $value = TMP_LoadObject ($input["tmpkey"]);
        $value = $value["data"];
      } else {
        $value = $input["value"];
      }
      $data = $value[$input["key"]];
    ';
    $form["postcode"] = '
      if (TMP_LoadObject ($input["tmpkey"])) {
        $value = TMP_LoadObject ($input["tmpkey"]);
        $value = $value["data"];
      } else {
        $value = $input["value"];
      }
      $value[$input["key"]] = $data;
      TMP_SaveObject ($input["tmpkey"], array ("data"=>$value));
      $html = FORMS_MD_BuildTable ($input["fieldname"], $input["value"], $input["tmpkey"], $input["specs"]);
      echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_".$input["fieldname"], $html));
      echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_".$input["fieldname"], base64_encode (serialize ($value))));
      $gotook = "closeme";
    ';
    $url = FORMS_URL ($form);
    echo '<a title="'.ML("Wijzig","Edit").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/edit_small.gif"></a>&nbsp;';

    $form = array();
    $form["input"]["tmpkey"] = $tmpkey;
    $form["input"]["value"] = $value;
    $form["input"]["specs"] = $specs;
    $form["input"]["fieldname"] = $fieldname;
    $form["input"]["key"] = $key;
    $form["postcode"] = '
      if (TMP_LoadObject ($input["tmpkey"])) {
        $value = TMP_LoadObject ($input["tmpkey"]);
        $value = $value["data"];
      } else {
        $value = $input["value"];
      }
      unset ($value[$input["key"]]);
      TMP_SaveObject ($input["tmpkey"], array ("data"=>$value));
      $html = FORMS_MD_BuildTable ($input["fieldname"], $input["value"], $input["tmpkey"], $input["specs"]);
      echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_".$input["fieldname"], $html));
      echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_".$input["fieldname"], base64_encode (serialize ($value))));
      $gotook = "closeme";
    ';
    $url = FORMS_URL ($form);
    echo '<a title="'.ML("Verwijder","Delete").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/delete_small.gif"></a>';
    T_NewRow();
  }
  $form = array();
  $form["title"] = $specs["title"];
  $form["input"]["tmpkey"] = $tmpkey;
  $form["input"]["value"] = $value;
  $form["input"]["specs"] = $specs;
  $form["input"]["fieldname"] = $fieldname;
  $form["metaspec"]["fields"] = MB_Ref ("ims_fields", IMS_SuperGroupName());
  $form["formtemplate"] = N_ReadFile ("html::".IMS_SuperGroupName()."/objects/".$specs["form"]."/page.html");
  $form["postcode"] = '
    if (TMP_LoadObject ($input["tmpkey"])) {
      $value = TMP_LoadObject ($input["tmpkey"]);
      $value = $value["data"];
    } else {
      $value = $input["value"];
    }
    $value [N_GUID()] = $data;
    TMP_SaveObject ($input["tmpkey"], array ("data"=>$value));
    $html = FORMS_MD_BuildTable ($input["fieldname"], $input["value"], $input["tmpkey"], $input["specs"]);
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_".$input["fieldname"], $html));
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_".$input["fieldname"], base64_encode (serialize ($value))));
    $gotook = "closeme";
  ';
  $url = FORMS_URL ($form);
  echo "<a href=\"$url\">".ML("Toevoegen","Add")."<a>";
  T_NewRow();
  $result = TS_End();
  return $result;
}

function FORMS_MD_Edit ($fieldname, $value, $specs)
{
  global $css_ims_md_edit_once;
  if (!$css_ims_md_edit_once) {
    $css = '
      <STYLE type="text/css"><!--
        A.ims_md_edit:link{color: #000000; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}
        A.ims_md_edit:visited{color: #000000; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}
        A.ims_md_edit:hover{color: #FF0000; text-decoration: underline; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}
      --></STYLE>
    ';
    $css_ims_md_edit_once = true;
  }
  $tmpkey = N_GUID();
  $dyn = DHTML_DynamicObject (FORMS_MD_BuildTable ($fieldname, $value, $tmpkey, $specs), "dyn_".$fieldname); 
  return $css.DHTML_InvisiTable ('<font face="arial" size="2">', "</font>", 
    '<input type=hidden name="field_'.$fieldname.'" value="'.base64_encode (serialize ($value)).'">',
    $dyn
  );
}

function FORMS_MD_Input ($fieldname)
{
  return unserialize (base64_decode ($_POST["field_$fieldname"]));
}


function FORMS_RMD_BuildTable ($fieldname, $thecase, $specs, $readonly = false, $dynamicurl = "") {
  // Relational master-detail table. The values are stored as foreign keys in the *other* table.

  // Use the old RMD field by default. 
  global $myconfig;
  if ($myconfig[IMS_SuperGroupName()]["useadvancedmdfield"] != "yes") return FORMS_RMD_BuildTable_Old($fieldname, $thecase, $specs, $readonly);

  // Difference with FORMS_RMD_BuildTable_Old is that this version uses true (dynamic) autotables, 
  // so that pagination and is possible.

  /* There are two nested dynamic objects:
   * - The outer dynamic object is all content generated by this function (FORMS_RMD_BuildTable). 
   *   This dynamic object is refreshed whenever you add / edit / delete a record.
   *   Since add / edit /delete are all interactive popups, the refreshing is achieved by using 
   *   DHTML_Parent_SetDynamicObject from the "postcode" of these form popups.
   * - The inner dynamic object is the dynamic autotable. This dynamic object
   *   is refreshed when you sort, paginate or filter the table.
   *   This refreshing is achieved by using an invisible iframe (DHTML_RPCUrl and DHTML_FormActionUrl)
   * As a consequence of this setup, whenever the user adds / edits / deletes a record, the "inner"
   * autotable returns to its default sort-by-column-one / show-page-one / do-not-filter state.
   */

  // $specs = specs for this MD field
  // $fspecs = specs for other fields (the fields used in the columns of the table)
  // $tspecs = specs for the *auto*table. This is different from FORMS_RMD_BuildTable_Old.

  uuse("dhtml");

  // Check if we were called dynamically (from postcode of an add / edit / delete popup). 
  // If so, we need to fake the url so that N_MyFullUrl will think that $prevurl is our actual url.
  // This is needed because TABLES_Auto relies on N_AlterUrl to create sorting/pagination links.
  if ($dynamicurl) {
    $myurlparts = N_ExplodeUrl($dynamicurl);

    global $fake_query_string; // Used by N_MyFullUrl
    $fake_query_string = "";
    $_GET = array();
    $_POST = array(); // needed because TABLES_Auto uses N_MyVeryFullUrl
    foreach ($myurlparts["query"] as $paramname => $paramvalue) {
      $GLOBALS[$paramname] = $paramvalue;
      $_GET[$paramname] = $paramvalue;
      if ($fake_query_string) $fake_query_string .= "&$paramname=".urlencode($paramvalue); else $fake_query_string = "$paramname=".urlencode($paramvalue);
    }
    $_SERVER["QUERY_STRING"] = $fake_query_string; // just in case (but does not change the outcome of getenv("QUERY_STRING");

    // fake the script name
    $_SERVER["REDIRECT_URL"] = $myurlparts["path"];
  }

  $specscopy = $specs;
  preg_match ('/_(([0-9a-f])*)$/i', $specs["table"], $matches);
  $theprocess = $matches[1];
  $rebuild = SHIELD_Encode (array ("fieldname"=>$fieldname, "thecase"=>$thecase, "specs"=>$specs, "readonly"=>$readonly, "url"=>($dynamicurl ? $dynamicurl : N_MyFullUrl())));

  $tspecs = array();
  $tspecs["tablespecs"]["sort_default_col"] = 1;
  $field = $specs["fields"][0];
  $fspecs = $allfields[$field];
  if ($fspecs["type"]=="date" || $fspecs["type"]=="datetime") {
    $tspecs["tablespecs"]["sort_default_dir"] = "d"; 
  } else {
    $tspecs["tablespecs"]["sort_default_dir"] = "u"; 
  }
  $tspecs["maxlen"] = $specs["maxrows"]; // qqq mol / repair tabellengte hier
  $tspecs["style"] = "ims";
  $tspecs["inplace"] = "yes";
  $tspecs["colfilter"] = "yes";
  $tspecs["nofilter"] = "yes"; // Dont show any filter form at all (because nested forms are not allowed in HTML)
  $tspecs["name"] = "md_$fieldname";
  $tspecs["sortlinkcss"] = "ims_rmd_edit"; 
  $tspecs["table"] = $specs["table"];
  $tspecs["select"] = array('$record["data"]["'.$specs["fk_field"].'"]' => $thecase);
  $tspecs["slowselect"] = array('SHIELD_RecordViewAllowed("'.IMS_SuperGroupName().'", "'.N_KeepAfter($specs["table"], "cases_").'", $key)' => true);
  $tspecs["tableheads"] = $specs["heads"];
  for ($i=1; $i<=count ($specs["heads"]); $i++) {
    $tspecs["tablespecs"]["sort_$i"] = "auto";
  }

  if (!$readonly && $specs["form"]=="#auto#") {
    $process = SHIELD_AccessProcess (IMS_SuperGroupName(), $theprocess);
    $useform = 0;
    for ($i=7; $i>=1; $i--) {
      if (SHIELD_ValidateAccess_List (IMS_SuperGroupName(), SHIELD_CurrentUser(), $process["usedataform"][$i])) $useform = $i;
    }
    if ($useform) $specs["form"] = $process["dataform$useform"];
  }
  foreach ($specs["fields"] as $dummy => $field) {
    $fspecs = $allfields[$field];
    if ($fspecs["type"] == "date" || $fspecs["type"] == "datetime" || $fspecs["type"] == "time") {
      $tspecs["sort"][] = '$record["data"]["'.$field.'"]';
      $tspecs["content"][] = 'echo ($record["data"]["'.$field.'"] ? FORMS_ShowValue($record["data"]["'.$field.'"], "'.$field.'", $record["data"]) : "&nbsp;");';
      $tspecs["colfilterexp"][] = 'FORMS_ShowValue($record["data"]["'.$field.'"], "'.$field.'", $record["data"])';
    } else {
      $tspecs["sort"][] = 'FORMS_ShowValue($record["data"]["'.$field.'"], "'.$field.'", $record["data"])';
      $tspecs["content"][] = 'echo ($sortvalue ? $sortvalue : "&nbsp;");';
      $tspecs["colfilterexp"][] = 'FORMS_ShowValue($record["data"]["'.$field.'"], "'.$field.'", $record["data"])';
    }
  }

  if (!$readonly) {
    $i = count($specs["fields"]) + 1;
    $tspecs["colfiltertype"][$i] = 'none';
    $tspecs["tableheads"][] = "&nbsp;";
    $tspecs["sort_$i"] = "none";
    $tmpcontent = "";
    if ($specs["form"] && $specs["form"]!="#auto#") {
      $tmpcontent .= '
        uuse("dhtml");
        $url = "/".IMS_Object2Site(IMS_SuperGroupName(), "'.$specs["form"].'")."/'.$specs["form"].'.php?theprocess='.$theprocess.'&thecase=$key&editonly=yes&autosize=yes&thegoto=closeme&rebuild='.$rebuild.'";
        $url = DHTML_PopupURL ($url);
        echo \'<a title="\'.ML("Details","Details").\'" href="\'.$url.\'"><img border=0 src="/ufc/rapid/openims/edit_small.gif"></a>&nbsp;\';
      ';
    }
    $tmpcontent .= '
      if (SHIELD_HasProcessRight(IMS_SuperGroupName(), N_KeepAfter("'.$specs["table"].'", "cases_"), "delete", $key)) {
        $form = array();
        $form["input"]["key"] = $key;
        $form["input"]["rebuild"] = "'.$rebuild.'";
        $form["formtemplate"] = \'
          <table width=100>
            <tr><td><font face="arial" color="#ff0000" size=2><b>\'.$title.\'</b></font></td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td><nobr><font face="arial" size=2><b>\'.ML("Weet u het zeker?","Are you sure?").\'</b></font></nobr></td></tr>
            <tr><td>&nbsp</td></tr>
            <tr><td ><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        \';
        $form["postcode"] = \'
          uuse("tmp");
          $rebuild = SHIELD_Decode($input["rebuild"]);
          MB_Delete ($rebuild["specs"]["table"], $input["key"]);
          MB_Flush();
          $dyn = FORMS_RMD_BuildTable ($rebuild["fieldname"], $rebuild["thecase"], $rebuild["specs"], $rebuild["readonly"], $rebuild["url"]);
          echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_".$rebuild["fieldname"], $dyn));
          echo DHTML_EmbedJavaScript (DHTML_Parent_PerfectSize ()); 
          $gotook = "closeme";
        \';
        $url = FORMS_URL ($form);
        echo \'<a title="\'.ML("Verwijder","Delete").\'" href="\'.$url.\'"><img border=0 src="/ufc/rapid/openims/delete_small.gif"></a>&nbsp;\';
      }

    ';
    $tspecs["content"][] = $tmpcontent;
  }

  uuse("tables");

  // Extra wrapper table for layout purposes. Looks good in default skin, but doesnt adapt to custom skins.
  // TODO: maybe create an "extrabottomrow" parameter in TABLES_Auto, and use that for the "Toevoegen" row.
  $content = '<table cellpadding="0" cellspacing="0" style="border-bottom: 1px solid #000; border-top: 1px solid #000;"><tr><td>';

  $content .= TABLES_Auto($tspecs);
  // A MD-table should never have normal hyperlinks in it, because they would refresh the form page, and data entered in the form would be lost.
  // So make sure that all non-javascript hyperlinks open in a new window (target="_blank"
  $content = preg_replace('/href="(?!javascript)([^"]+)"/', 'target="_blank" href="\1"', $content);
  $content = preg_replace('/href=(?!javascript)([^"]+)/', 'target="_blank" href=\1', $content);

  $content .= '</td></tr>';

  if (!$readonly && SHIELD_HasProcessRight (IMS_SuperGroupName(), N_KeepAfter ($specs["table"], "cases_"), "add")) {
    $content .= '<tr><td style="border-left: 1px solid #D0D0D0; border-right: 1px solid #D0D0D0; padding: 3px;"><font face="arial" size=2>';
    $url = "/".IMS_Object2Site (IMS_SuperGroupName(), $specs["form"])."/".$specs["form"].".php?new=yes&newcase=AUTOGUIDPARAM&prepfield=".$specs["fk_field"]."&prepdata=".$thecase."&theprocess=$theprocess&editonly=yes&autosize=yes&thegoto=closeme&rebuild=$rebuild";
    $url = DHTML_PopupURL ($url);
    $content .= "<a href=\"$url\">".ML("Toevoegen","Add")."<a>";
    $content .= '</font></td></tr>';
  }
  $content .= '</table>';

  return $content;
}

function FORMS_RMD_BuildTable_Old ($fieldname, $thecase, $specs, $readonly = false)
{
  N_Debug("FORMS_RMD_BuildTable called: fieldname = $fieldname, thecase = $thecase, readonly = $readonly");

  $tspecs["extratop"] = 1;
  if (!$readonly && SHIELD_HasProcessRight (IMS_SuperGroupName(), N_KeepAfter ($specs["table"], "cases_"), "add")) {
    $tspecs["sort_bottomskip"] = 1;
    $tspecs["extrabottom"] = 1;
  }
  $tspecs["sort_default_col"] = 1;

  $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
  $field = $specs["fields"][0];
  $fspecs = $allfields[$field];
  if ($fspecs["type"]=="date" || $fspecs["type"]=="datetime") {
    $tspecs["sort_default_dir"] = "d"; 
  } else {
    $tspecs["sort_default_dir"] = "u"; 
  }

  $tspecs["maxrows"] = $specs["maxrows"];
  $tspecs["no_reload"] = "yes";
  $tspecs["sort"] = N_GUID();
  $tspecs["sortlinkcss"] = "ims_rmd_edit";
  for ($i=1; $i<=count ($specs["heads"]); $i++) {
    $tspecs["sort_$i"] = "auto";
  }
  T_Start("ims", $tspecs);
  $ctr=0;
  foreach ($specs["heads"] as $dummy => $head) {
    echo $head;
    T_Next();
    $ctr++;
  }
  if (!$readonly) echo " ";
  T_NewRow();
//  if ($specs["sort"]) {
//    $keys = MB_TurboMultiQuery ($specs["table"], array (
//      "select" => array ('$record["data"]["'.$specs["fk_field"].'"]' => $thecase),
//      "value" => '$record["data"]',
//      "sort" => 'FORMS_ShowValue ($record["data"]["'.$specs["sort"].'"], "'.$specs["sort"].'", $record["data"])'
//    ));
//  } else {
    $keys = MB_TurboMultiQuery ($specs["table"], array (
      "select" => array ('$record["data"]["'.$specs["fk_field"].'"]' => $thecase),
      "value" => '$record["data"]'
    ));
//  }
  preg_match ('/_(([0-9a-f])*)$/i', $specs["table"], $matches);
  $theprocess = $matches[1];
  $rebuild = SHIELD_Encode (array ("fieldname"=>$fieldname, "thecase"=>$thecase, "specs"=>$specs));
  if (!$readonly && $specs["form"]=="#auto#") {
    $process = SHIELD_AccessProcess (IMS_SuperGroupName(), $theprocess);
    $useform = 0;
    for ($i=7; $i>=1; $i--)
    {
      if (SHIELD_ValidateAccess_List (IMS_SuperGroupName(), SHIELD_CurrentUser(), $process["usedataform"][$i])) $useform = $i;

    }
    if ($useform) $specs["form"] = $process["dataform$useform"];
  }
  foreach ($keys as $key => $data) {
    if (SHIELD_RecordViewAllowed (IMS_SuperGroupName(), N_KeepAfter ($specs["table"], "cases_"), $key)) {
      foreach ($specs["fields"] as $dummy => $field) {
        $text = FORMS_ShowValue ($data[$field], $field, $data);
        if (strlen($text)<25) {
          echo "<nobr>$text</nobr>";
        } else {
          echo str_replace (chr(13), "<br>", $text);
        }
        T_Next();
      }
      if (!$readonly && ($specs["form"] && $specs["form"]!="#auto#")) {
        $url = "/".IMS_Object2Site (IMS_SuperGroupName(), $specs["form"])."/".$specs["form"].".php?theprocess=$theprocess&thecase=$key&editonly=yes&autosize=yes&thegoto=closeme&rebuild=$rebuild";
        $url = DHTML_PopupURL ($url);
        echo '<a title="'.ML("Details","Details").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/edit_small.gif"></a>&nbsp;';
      } 
      if (!$readonly && SHIELD_HasProcessRight (IMS_SuperGroupName(), N_KeepAfter ($specs["table"], "cases_"), "delete", $key)) { 
        $form = array();
        $form["input"]["fieldname"] = $fieldname;
        $form["input"]["thecase"] = $thecase;
        $form["input"]["specs"] = $specs;
        $form["input"]["key"] = $key;
        $form["formtemplate"] = '
          <table width=100>
            <tr><td><font face="arial" color="#ff0000" size=2><b>'.$title.'</b></font></td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td></tr>
            <tr><td>&nbsp</td></tr>
            <tr><td ><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["postcode"] = '
          MB_Delete ($input["specs"]["table"], $input["key"]);
          MB_Flush();
          $dyn = FORMS_RMD_BuildTable ($input["fieldname"], $input["thecase"], $input["specs"]);
          echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_".$input["fieldname"], $dyn));
          echo DHTML_EmbedJavaScript (DHTML_Parent_PerfectSize ()); 
          $gotook = "closeme";
        ';
        $url = FORMS_URL ($form);
        echo '<a title="'.ML("Verwijder","Delete").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/delete_small.gif"></a>';
      }
      T_NewRow();
    }
  }
  if (!$readonly && SHIELD_HasProcessRight (IMS_SuperGroupName(), N_KeepAfter ($specs["table"], "cases_"), "add")) {
    $url = "/".IMS_Object2Site (IMS_SuperGroupName(), $specs["form"])."/".$specs["form"].".php?new=yes&newcase=AUTOGUIDPARAM&prepfield=".$specs["fk_field"]."&prepdata=".$thecase."&theprocess=$theprocess&editonly=yes&autosize=yes&thegoto=closeme&rebuild=$rebuild";
    $url = DHTML_PopupURL ($url);
    echo "<a href=\"$url\">".ML("Toevoegen","Add")."<a>";
  }
  T_NewRow();

  N_Debug("FORMS_RMD_BuildTable finished");

  return TS_End();

} 

function FORMS_RMD_Edit ($fieldname, $value, $specs)
{
  N_Debug("FORMS_RMD_Edit called");
  global $css_ims_rmd_edit_once;
  if (!$css_ims_rmd_edit_once) {
    $css = '
      <STYLE type="text/css"><!--
        A.ims_rmd_edit:link{color: #000000; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}
        A.ims_rmd_edit:visited{color: #000000; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}
        A.ims_rmd_edit:hover{color: #FF0000; text-decoration: underline; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}
      --></STYLE>
    ';
    $css_ims_rmd_edit_once = true;
  }

  global $thecase, $newcase, $encspec;
 
  if (!$thecase && $encspec) { // Determine DMS document ID when RMD is used as document property  
    $sd = SHIELD_Decode ($encspec);  
    if (is_array($sd["input"])) $thecase = $sd["input"]["id"];  
  }

  if ($thecase) {
    if (SHIELD_HasProcessRight (IMS_SuperGroupName(), N_KeepAfter ($specs["table"], "cases_"), "dataview")) {
      return $css.DHTML_DynamicObject (FORMS_RMD_BuildTable ($fieldname, $thecase, $specs), "dyn_".$fieldname);
    } else {
      return ML("U heeft niet voldoende rechten<br>om deze gegevens te bekijken.<br><br>","You have insufficient rights<br>to view this data.<br><br>");
    }
  } else if ($newcase) {
    if (SHIELD_HasProcessRight (IMS_SuperGroupName(), N_KeepAfter ($specs["table"], "cases_"), "dataview")) {
      return $css.DHTML_DynamicObject (FORMS_RMD_BuildTable ($fieldname, $newcase, $specs), "dyn_".$fieldname);
    } else {
      return ML("U heeft niet voldoende rechten<br>om deze gegevens te bekijken.<br><br>","You have insufficient rights<br>to view this data.<br><br>");
    }
  } else {
    return ML("Nog niet beschikbaar, komt beschikbaar<br>zodra dit formulier is opgeslagen (met OK)<br><br>","Not available yet, becomes available<br>as soon as this form has been saved (with OK)<br><br>");
  }
} 

function FORMS_RMD_View($value, $specs)
{
  global $css_ims_rmd_view_once;
  if (!$css_ims_rmd_view_once) {
    $css = '
      <STYLE type="text/css"><!--
        A.ims_rmd_edit:link{color: #000000; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}
        A.ims_rmd_edit:visited{color: #000000; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}
        A.ims_rmd_edit:hover{color: #FF0000; text-decoration: underline; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}
      --></STYLE>
    ';
    $css_ims_rmd_view_once = true;
  }

  global $thecase, $newcase;
  $fieldname = N_Guid(); // need to make something up, because FORMS_ShowValue usually doesnt know the fieldname

  if ($thecase) {
    return $css.DHTML_DynamicObject (FORMS_RMD_BuildTable ($fieldname, $thecase, $specs, $readonly = true), "dyn_".$fieldname);
  } else {
    return ML("N/B","N/A");
  }
}

function FORMS_MultiChoice_URL ($fieldname, $specs, $level, $prev)
{
  $form["title"] = $specs["title"][$level];
  $form["input"]["fieldname"] = $fieldname;
  $form["input"]["specs"] = $specs;
  $form["input"]["level"] = $level;
  $form["input"]["prev"] = $prev;
  $form["input"]["dyn"] = "dyn_".$fieldname;
  $form["input"]["field"] = "field_".$fieldname;
  if ($specs["levelcode"][$level]) {
    $form["precode"] = '
      $formtemplate = \'
          <table>
      \';
      $prev = $input["prev"];
      eval ($input["specs"]["levelcode"][$input["level"]]);
      T_Start ("ims");
      echo "&nbsp;";
      T_Next();
      echo $input["specs"]["title"][$input["level"]];
      T_NewRow();
      foreach ($list as $value => $show) {
        $url = FORMS_MultiChoice_URL ($input["fieldname"], $input["specs"], $input["level"]+1, $value);
        echo \'<a title="\'.$show.\'" href="\'.$url.\'"><img border=0 src="/ufc/rapid/openims/icon_arrow_next.gif"></a>\';
        T_Next();
        echo \'<a class="ims_navigation" href="\'.$url.\'">\'.$show.\'</a>\';
        T_NewRow();
      }
      $formtemplate .= \'
            <tr><td>\'.TS_End().\'</td></tr>
            <tr><td>&nbsp</td></tr>
      \';    
      if ($input["level"]) {
        $formtemplate .= \'
              <tr><td><center>[[[BACK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
        \';    
      } else {
        $formtemplate .= \'
              <tr><td><center>[[[CANCEL]]]</center></td></tr>
            </table>
        \';    
      }
    ';
  } else {
    $form["postcode"] = '
      $value = $input["prev"];
      $inputvalue = $value;
      eval ($input["specs"]["lookupcode"]);
      echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_".$input["fieldname"], $value."&nbsp;"));
      echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_".$input["fieldname"], $inputvalue));
      echo DHTML_EmbedJavaScript (DHTML_Parent_PerfectSize ());
      $gotook = "closeme";
    ';
  }
  if ($level==0) {
    return FORMS_URL ($form);
  } else {
    return FORMS_URL ($form, false);
  }
} 

function FORMS_MultiChoice_Edit ($fieldname, $value, $specs)
{
  $url = FORMS_MultiChoice_URL ($fieldname, $specs, 0, "");
  $inputvalue = $value;
  eval ($specs["lookupcode"]);
  T_Start ("ims", array("extratop"=>1, "extrabottom"=>1, "noheader"=>"yes", "extra-table-props"=>"width=113", "nobr"=>"yes"));
  echo DHTML_DynamicObject (N_HtmlEntities ($value)."&nbsp;", "dyn_".$fieldname);
  $dhtmlobject = TS_End();
  return DHTML_InvisiTable ('<font face="arial" size="2">', "</font>",
    $dhtmlobject,
    '<input type=hidden name="field_'.$fieldname.'" value="'.N_HtmlEntities ($inputvalue).'">&nbsp;<a title="'.ML("Kies","Select").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>',
    '&nbsp;<a title="'.ML("Kies","Select").'" class="ims_navigation" href="'.$url.'">'.ML("Kies","Select").'</a>'
  );
}

function FORMS_MultiChoice_View ($fieldname, $value, $specs)
{
  eval ($specs);
  return $value;
}

function FORMS_TAXO_View ($fieldname, $value, $specs)
{
  uuse ("taxo");
  $taxo = TAXO_Parse ($specs["specs"]["taxospecs"]);
  $found = TAXO_FindID ($taxo, $value);
  if ($found) {
    return N_XML2HTML ($found["specs"]["fullitem"]);
  } else {
    return "&lt;".ML ("geen","none")."&gt;";
  }
}

function FORMS_TAXO_URL ($fieldname, $taxo, $level, $prev="", $allownodes=false, $required=false, $sortleaves=false)
{
  $baseform["title"] = ML("Kies","Select");
  $baseform["input"]["fieldname"] = $fieldname;
  $baseform["input"]["taxo"] = $specs;
  $baseform["input"]["level"] = $level;
  $baseform["input"]["prev"] = $prev;
  $baseform["input"]["prevvalue"] = $prevvalue;
  $baseform["input"]["dyn"] = "dyn_".$fieldname;
  $baseform["input"]["field"] = "field_".$fieldname;
  $myform = $baseform;
  $myform["formtemplate"] = '<table>';
  //sbr 3-9-2008
  $sortarray = array();
  if ($sortleaves) $sortarray = array("sort"=>"x", "sort_default_col"=>1, "sort_1"=>"auto");
  T_Start ("ims",$sortarray);
  if ($prev) {
    echo ML("Kies","Select")." ($prev &gt; )";
  } else {
    echo ML("Kies","Select");
  }
  T_NewRow();

  foreach ($taxo as $key => $specs) {

    if ($allownodes) {
      $form = $baseform;
      $form["input"]["value"] = $key;
      $form["input"]["viewvalue"] = $specs["fullitem"];
      $form["postcode"] = '
        echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_".$input["fieldname"], $input["viewvalue"]."&nbsp;"));
        echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_".$input["fieldname"], $input["value"]));
        echo DHTML_EmbedJavaScript (DHTML_Parent_PerfectSize ());
        $gotook = "closeme";
      ';
      $choose_url = FORMS_URL ($form, false);
      echo DHTML_InvisiTable ('<font face="arial" size="2">', "</font>",
        $specs["item"],
        '&nbsp;',
        '<a title="'.ML("Kies", "Select").': '.$specs["fullitem"].'" class="ims_navigation" href="'.$choose_url.'"><img border=0 src="/ufc/rapid/openims/accept.gif" /></a>', 
        '&nbsp;',
        ($specs["children"] 
          ? '<a title="'.ML("Ga een niveau dieper", "Go down a level").'" href="' . FORMS_TAXO_URL ($fieldname, $specs["children"], $level+1, $specs["fullitem"], $allownodes, $required, $sortleaves) . '"><img border=0 src="/ufc/rapid/openims/nav_arrow_right.gif"></a>' 
          : '&nbsp;')
        );
    } else { 
      if ($specs["children"]) { // subtree (not choice)
        $url = FORMS_TAXO_URL ($fieldname, $specs["children"], $level+1, $specs["fullitem"], $allownodes, $required, $sortleaves);
        echo DHTML_InvisiTable ('<font face="arial" size="2">', "</font>",
          '<a title="'.$specs["fullitem"].'" class="ims_navigation" href="'.$url.'">'.$specs["item"].'</a>',
          '&nbsp;',
          '<a title="'.$specs["fullitem"].'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/icon_arrow_next.gif"></a>'
        );
      } else { // choice (not subtree)
        $form = $baseform;
        $form["input"]["value"] = $key;
        $form["input"]["viewvalue"] = $specs["fullitem"];

        $form["postcode"] = '
          echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_".$input["fieldname"], $input["viewvalue"]."&nbsp;"));
          echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_".$input["fieldname"], $input["value"]));
          echo DHTML_EmbedJavaScript (DHTML_Parent_PerfectSize ());
          $gotook = "closeme";
        ';
        $url = FORMS_URL ($form, false);
        echo '<a title="'.$specs["fullitem"].'" class="ims_navigation" href="'.$url.'">'.$specs["item"].'</a>';
      }
    }
    T_NewRow();
  }

  // DvG let user clear the taxo-field, except when field is required
  if (!$required) {
     $form = $baseform;
     $form["input"]["value"] = "";
     $form["input"]["viewvalue"] = "&lt;".ML("geen","none")."&gt;";
     $form["postcode"] = '
        echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_".$input["fieldname"], $input["viewvalue"]."&nbsp;"));
        echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_".$input["fieldname"], $input["value"]));
        echo DHTML_EmbedJavaScript (DHTML_Parent_PerfectSize ());
        $gotook = "closeme";
     ';
     $url = FORMS_URL ($form, false);
     echo '<a title="' .
          ML("Klik hier om het veld leeg te laten of leeg te maken.","Click here to clear this field") .
          '" class="ims_navigation" href="'.$url.'">&lt;'.ML("geen","none").'&gt;</a>';
     T_NewRow();
  }

  $myform["formtemplate"] .= '<tr><td>'.TS_End().'</td></tr>';
  $myform["formtemplate"] .= '<tr><td>&nbsp;</td></tr>';
  if ($level) {
    $myform["formtemplate"] .= '<tr><td><center>[[[BACK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr></table>';
  } else {
    $myform["formtemplate"] .= '<tr><td><center>[[[CANCEL]]]</center></td></tr></table>';
  }
  if ($level==0) {
    return FORMS_URL ($myform);
  } else {
    return FORMS_URL ($myform, false);
  }
}

function FORMS_TAXO_Edit ($fieldname, $value, $specs)
{
  uuse ("taxo");
  $taxo = TAXO_Parse ($specs["specs"]["taxospecs"]);
  $url = FORMS_TAXO_URL ($fieldname, $taxo, 0, "", $specs["specs"]["allownodes"], $specs["required"], $specs["specs"]["sortleaves"]); // LF - DvG
  $viewvalue = FORMS_TAXO_View ($fieldname, $value, $specs);
  T_Start ("ims", array("extratop"=>1, "extrabottom"=>1, "noheader"=>"yes", "extra-table-props"=>"width=113", "nobr"=>"yes"));
  echo DHTML_DynamicObject ($viewvalue."&nbsp;", "dyn_".$fieldname);
  $dhtmlobject = TS_End();
  return DHTML_InvisiTable ('<font face="arial" size="2">', "</font>",
    $dhtmlobject,
    '<input type=hidden name="field_'.$fieldname.'" value="'.N_HtmlEntities ($value).'">&nbsp;<a title="'.ML("Kies","Select").'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/icon_search.gif"></a>',
    '&nbsp;<a title="'.ML("Kies","Select").'" class="ims_navigation" href="'.$url.'">'.ML("Kies","Select").'</a>'
  );
}


function FORMS_Composite_Edit($fieldname, $value, $fields, $fieldspec = array(), $extrahtml = "") { 
  // Edit an object that is composed of several different $fields.
  // This looks a bit like FORMS_HYP_Edit (for hyperlinks), but is more general.
  // !!! The value of this field is an assocative array (beware, almost all other values are strings).
 
  $objectspec = $value;
  $fieldspec = N_Array_merge(MB_Load("ims_fields", IMS_SuperGroupName()), $fieldspec);
  $form["title"] = $fieldspec[$fieldname]["title"];
  $form["input"]["tmpkey"] = N_GUID();
  $form["input"]["fieldname"] = $fieldname;
  $form["input"]["fields"] = $fields;
  $form["formtemplate"] = $extrahtml . '<table>';
  foreach ($fields as $name) {
    $form["input"][$name] = $objectspec[$name];
    $form["formtemplate"] .= '<tr><td><font face="arial" size=2><b>{{{'.$name.'}}}:</b></font></td><td>[[['.$name.']]]</td></tr>';
  }
  $form["metaspec"]["fields"] = $fieldspec;
  $form["formtemplate"] .= '
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
    </table>
  ';         
  $form["precode"] = '
    $data = TMP_LoadObject ($input["tmpkey"]);
    if (!$data) foreach ($input["fields"] as $name) {
      $data[$name] = $input[$name];
    }
  ';
  $form["postcode"] = '
    uuse ("dhtml");
    uuse ("tables");
    $servalue = serialize($data);
    $firstfield = $input["fields"][0];
    $content = FORMS_ShowValue($data[$firstfield], $metaspec["fields"][$firstfield]);
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetDynamicObject ("dyn_{$input["fieldname"]}", $content));
    echo DHTML_EmbedJavaScript("opener.document.getElementById(\'dyn_a_{$input["fieldname"]}\').title = \'" . ML("Wijzig", "Edit") . "\';");
    echo DHTML_EmbedJavaScript (DHTML_Parent_SetValue ("field_".$input["fieldname"], $servalue));
    TMP_SaveObject($input["tmpkey"], $data);
    $gotook="closeme";
  ';
  $url = FORMS_URL ($form);
  $firstfield = $fields[0];
  $content = FORMS_ShowValue($objectspec[$firstfield], $fieldspec[$firstfield]);

  return DHTML_InvisiTable ('<font face="arial" size="2">', "</font>", 
    '<a id="dyn_a_'.$fieldname.'" title="'.($objectspec ? ML("Wijzig","Edit") : ML("Maak","Create")).'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/edit_small.gif"></a>',
    '&nbsp;',
    DHTML_DynamicObject ($content."&nbsp;", "dyn_".$fieldname),
    '<input type=hidden name="field_'.$fieldname.'" value="'.N_HtmlEntities(serialize($value)).'">'
  );
}

function FORMS_Composite_View($fieldname, $value, $fields, $fieldspec = array()) {
  $objectspec = $value;
  $fieldspec = N_Array_merge(MB_Load("ims_fields", IMS_SuperGroupName()), $fieldspec);
  if (!is_array($fields)) $fields = array($fields);
  $firstfield = $fields[0];
  if (!$firstfield && is_array($objectspec)) { reset($objectspec); $firstfield = key($objectspec); } // use the first one
  return FORMS_ShowValue($objectspec[$firstfield], $fieldspec[$firstfield]);
}

function FORMS_ML_View($value, $lang = "", $fallback = true) {
// View a multilingual text field, such as will be "created" when using FORMS_ML_Edit.
// However, it will not work if (from code) several strings are concatenated together.
// Therefore, you should almost always call FORMS_ML_Filter instead of FORMS_ML_View.
  $decoded = FORMS_ML_Decode($value);
  if (is_array($decoded)) {
    if (!$lang) $lang = ML_GetLanguage();
    if ($decoded[$lang] || $decoded[$lang] === "0") {
      return $decoded[$lang];
    } elseif ($fallback) {
      global $myconfig;
      $lang = $myconfig[IMS_SuperGroupName()]["ml"]["fallbacklanguage"][$lang];
      if ($lang && ($decoded[$lang] || $decoded[$lang] === "0")) return $decoded[$lang];
      foreach ($decoded as $text) {
        if ($text || $text === "0") return $text;
      }
      return "";
    } 
  } else { // Internal value is not multilingual
    if ($fallback) {
      return $value;
    } else { // Only return the value for one language: the first language in the configuration
      global $myconfig;
      if (!$lang) $lang = ML_GetLanguage();
      $langs = $myconfig[IMS_SuperGroupName()]["ml"]["languages"];
      if (!$langs) $langs = array("nl", "en");
      if ($lang == $langs[0]) {
        return $value;
      } else {
        return "";
      }
    }

  }

}

function FORMS_ML_Decode($value) {
  if (substr($value, 0, 5) == "#!ML!" && substr($value, -2) == "!#") {
    $result = array();
    $arr = explode("!", substr($value, 5, strlen($value) - 7));
    $count = count($arr);
    for ($i = 0; $i < $count; $i += 2) {
      $lang = $arr[$i];
      $text = urldecode($arr[$i+1]);
      $result[$lang] = $text;
    }
    return $result;
  } else {
    return false;
  }
}

function FORMS_ML_Encode($values) {
  $value = '#!ML!';
  foreach ($values as $lang => $langvalue) {
    $value .= $lang . '!' . urlencode($langvalue) . '!';  
    // urlencode or just str_replace on #, ! and % ???
    // urlencode/decode is much faster than str_replace, but will produce larger output for non-ascii characters
  }
  $value .= '#';
  return $value; 
}


function FORMS_ML_Concat(/* ... any number of string arguments ... */) {
  /* Concatenate a number of (possibly) multilingual strings, and create a new string (using fallback languages)
   *
   * E.g. FORMS_ML_Concat("#!ML!en!Letter!nl!Brief!#", " 09-5821")
   *      ==> "#!ML!en!Letter 09-5821!nl!Brief 09-5821!#"
   */
  $args = func_get_args();
  $langresult = array();
  // Get all languages used
  foreach ($args as $arg) {
    $decoded = FORMS_ML_Decode($arg);
    if ($decoded) foreach ($decoded as $lang => $text) $langs[$lang] = "x";
  }
  if (!$langs) return implode($args);

  $tmpresult = array();
  foreach ($args as $arg) {
    foreach ($langs as $lang => $dummy) {
      $tmpresult[$lang] .= FORMS_ML_View($arg, $lang);
    }
  }
  $result = FORMS_ML_Encode($tmpresult);
  return $result;

}

/* Autolevels for ML fields.
 * If an ML field has an autolevel specified, it will "by default" behave as a normal text field, but it will become
 * an ML field when a sufficiently high metadatalevel has been specified in the site configuration.
 *
 * Levels:
 * nummer achter "strml" / constante / bitwaarde / betekenis
 *  1 / ML_WIZARDS   /  1 / names of wizards
 *  2 / ML_FIELDS    /  2 / names of fields (DMS Properties, automatic forms, CMS/BPMS forms that use the {{{fieldname}}} convention
 *  3 / ML_WORKFLOWS /  4 / names of workflows, workflow steps, workflow stages, bpms tables, bpms process steps, bpms decisions, user groups
 *  4 / ML_DMSMETA   /  8 / standard metadata of DMS documents (name, description) 
 *  5 / ML_FOLDERS   / 16 / names of DMS folders, cases, casetypes
 *  6 / ML_CMSMETA   / 32 / CMS shorttitle / longtitle. Will only work if $myconfig["xxx_sites"]["ml"]["sitelanguages"][$site_id] is configured for the current site.
 * 31 / never        /    / use to create fields that work if they contain multilingual values, but that will *never* have a multilingual interface 
 * In the siteconfiguration, the bitwise OR of all the desired levels should be specified. Typical settings will be 1, 3, 7, 15, 31 etc.
 */


function FORMS_STRML_Edit($fieldname, $value, $title = "", $specs = array()) {
  if (!$title) $title = $fieldname;
  $cols = $specs["cols"];
  if (!$cols) $cols = 30;
  global $myconfig;

  // For ML_CMSMETA, check if the current site has any languages configured (and which ones), otherwise ignore
  if ($specs["autolevel"] == 6 && $myconfig[IMS_SuperGroupName()]["ml"]["metadatalevel"] & (1 << (6 - 1))) {
    $site_id = IMS_DetermineJustTheSite();
    $langs = $myconfig[IMS_SuperGroupName()]["ml"]["sitelanguages"][$site_id];
    if (!$langs) $specs["autolevel"] = 31; // make the field monolingual
  }

  if ($specs["autolevel"] && !($myconfig[IMS_SuperGroupName()]["ml"]["metadatalevel"] & (1 << ($specs["autolevel"] - 1)))) {
    $langvalue = FORMS_ML_Filter($value);
    $fieldcode = '<input id="dwf_'.$fieldname.'" name="field_'.$fieldname.'" value="'.N_HtmlEntities($langvalue).'" size="'.$cols.'" title="'.$title.'" alt="'.$title.'">';
  } else {
    if (!$langs) $langs = $myconfig[IMS_SuperGroupName()]["ml"]["languages"];
    if (!$langs) $langs = array("nl", "en");
    $fieldcode = '<table cellspacing=0 cellpadding=0 border=0>';
    if ($specs["horizontal"]) $fieldcode .= '<tr>';
    foreach ($langs as $lang) {
      $langvalue = FORMS_ML_Filter($value, $lang, false);
      if (!$specs["horizontal"]) $fieldcode .= '<tr>';
      $fieldcode .= '<td><input id="dwf_'.$lang.'_'.$fieldname.'" name="field_'.$lang.'_'.$fieldname.'" value="'.N_HtmlEntities($langvalue).'" size="'.$cols.'" title="'.$title.'" alt="'.$title.'"></td>';
      $fieldcode .= '<td>&nbsp;' . ML_LanguageIcon($lang);
      if ($specs["horizontal"]) $fieldcode .= '&nbsp;&nbsp;&nbsp;';
      $fieldcode .= '</td>';
      if (!$specs["horizontal"]) $fieldcode .= '</tr>';
    }
    if ($specs["horizontal"]) $fieldcode .= '</tr>';
    $fieldcode .= '</table>';
  }

  return $fieldcode;
}

function FORMS_TXTML_Edit($fieldname, $value, $title = "", $specs = array()) {
  if (!$title) $title = $fieldname;
  $cols = $specs["cols"];
  $rows = $specs["rows"];
  $style = $specs["style"];
  if (!$cols) $cols = 46;
  if (!$rows) $rows = 4;
  global $myconfig;
  if ($specs["autolevel"] && !($myconfig[IMS_SuperGroupName()]["ml"]["metadatalevel"] & (1 << ($specs["autolevel"] - 1)))) {
    $langvalue = FORMS_ML_Filter($value);
    $fieldcode .= '<textarea id="dwf_'.$lang.'_'.$fieldname.'" name="field_'.$fieldname.'" '.($cols > 80 ? 'wrap="off" ' : '') . 'rows="'.$rows.'" cols="'.$cols.'" title="'.$title.'" '.($style ? 'style="'.htmlspecialchars($style).'"' : '') . '>';
    $fieldcode .= str_replace ("[", "&#91;", N_HtmlEntities($langvalue));
    $fieldcode .= '</textarea>';
  } else {
    if ($specs["languages"]) {
      $langs = $specs["languages"];
    } else {
      $langs = $myconfig[IMS_SuperGroupName()]["ml"]["languages"];
    }
    if (!$langs) $langs = array("nl", "en");
    $fieldcode = '<table cellspacing=0 cellpadding=0 border=0>';
    foreach ($langs as $lang) {
      $langvalue = FORMS_ML_Filter($value, $lang, false);
      $fieldcode .= '<tr><td>';
      $fieldcode .= '<textarea id="dwf_'.$lang.'_'.$fieldname.'" name="field_'.$lang.'_'.$fieldname.'" '.($cols > 80 ? 'wrap="off" ' : '') . 'rows="'.$rows.'" cols="'.$cols.'" title="'.$title.'" '.($style ? 'style="'.htmlspecialchars($style).'"' : '') . '>';
      $fieldcode .= str_replace ("[", "&#91;", N_HtmlEntities($langvalue));
      $fieldcode .= '</textarea></td>';
      $fieldcode .= '<td>&nbsp;' . ML_LanguageIcon($lang) . '</td>';
      $fieldcode .= '</tr>';
    }
    $fieldcode .= '</table>';
  }

  return $fieldcode;
}

function FORMS_ML_Filter($content, $lang="", $fallback=true) {
// If you disable the $fallback option, you will receive an empty string when the value for $lang is empty (instead of the fallback $lang)
// Fallback should be disabled when showing *all* languages side by side, and should be enabled in all other situations.

  // A simple, not multilingual string
  if (!strpos(" $content", "#")) {
    if ($fallback) {
      return $content;
    } else { // Let FORMS_ML_View figure out whether to return the original string or the empty string
      return FORMS_ML_View($content, $lang, $fallback); 
    }
  }
  // Note: some non-multilingual strings (that contain #) will fall through and be "handled" by the preg_replace below.
  
  if (is_numeric($lang)) { // Important: when this function is a callback to ob_start, the second parameter is some kind of number.
    $lang = ""; 
    $fallback = true;
  }

  // One single multilingual string
  if (substr($content, 0, 5) == "#!ML!" && substr($content, -2) == "!#" && strpos(substr($content, 1, strlen($content)-2), "#") === false) {
    return FORMS_ML_View($content, $lang, $fallback); 
  } else {

    // Prevent multilingual substition in *values* of HTML fields, by replacing the # of multilingual strings with numeric entities, 
    // so that the "real" replacement function does not match anymore.
    // Reason: values of HTML fields contain stuff that will be submitted back to the server, and they probably contain some kind of *internal* value
    // in which the raw multilingual string should be left intact.
    // Since it is HTML, the numeric entity will be converted back to # by the browser, so the submit will be correct.
    $content = preg_replace('/value="([^"]*)#!ML!([^#]*)!#([^"]*)"/', 'value="$1&#'.'35;!ML!$2!&#'.'35;$3"', $content);

    if (!$fallback) {
      // determine the fallback language
      global $myconfig;
      $langs = $myconfig[IMS_SuperGroupName()]["ml"]["languages"];
      if (!$langs) $langs = array("nl", "en");
      $falllang = $myconfig[IMS_SuperGroupName()]["ml"]["fallbacklanguage"][$lang];
      if (!$falllang) $falllang = $langs[0];

      // determine what the result would be in the fallback language
      if ($falllang && $falllang != $lang) {
        $compare = preg_replace_callback(
          '/#!ML![^#]*!#/',
          create_function(
              '$matches',
              'return FORMS_ML_View($matches[0], "'.addslashes($falllang).', false");'
          ),
          $content);
      }
    }

    // Now look for all multilingual strings and substitute them with the string for the correct language
    $content = preg_replace_callback(
        '/#!ML![^#]*!#/',
        create_function(
            '$matches',
            'return FORMS_ML_View($matches[0], "'.addslashes($lang).'");'
        ),
        $content);

    if (!$fallback && ($compare == $content)) $content = "";

    return $content;

  }
}

function FORMS_ML_Filter_Internal($content) {
  // Return content for all languages, separated by spaces. For use in full text indexing (and nothing else).

  if (!strpos(" $content", "#")) return $content;
  
  $oldcontent = "";
  while ($oldcontent != $content) {
    $oldcontent = $content;
    $content = preg_replace_callback(
        '/#!ML!([^!#]+)!([^!#]*)!([^#]*)#/',
        create_function(
            // single quotes are essential here,
            // or alternative escape all $ as \$
            '$matches',
            'return urldecode($matches[2]) . " #!ML!".$matches[3]."#";'
        ),
        $content
    );
  }

  $content = str_replace('#!ML!#', '', $content);

  return $content;
}

function FORMS_HTMLDate_Edit($fieldname, $value, $specs, $time = false) {
  $dayfieldcode  = '<select name="field_day_'.$fieldname.'" title="'.ML("dag","day").'"><option value="">'.ML("DD", "DD").'</option>';
  for ($i=1; $i<=31; $i++) {
    if ($value && ($i==N_Date2Day ($value))) {
      $dayfieldcode .= '<option value="'.$i.'" selected>'. sprintf("%02d", $i) .'</option>';
    } else {
      $dayfieldcode .= '<option value="'.$i.'">'. sprintf("%02d", $i) .'</option>';
    }
  }
  $dayfieldcode .= '</select>';
  $monthfieldcode  = '<select name="field_month_'.$fieldname.'" title="'.ML("maand","month").'"><option value="">'.ML("MM", "MM").'</option>';
  for ($i=1; $i<=12; $i++) {
    if ($value && ($i==N_Date2Month ($value))) {
      $monthfieldcode .= '<option value="'.$i.'" selected>'. sprintf("%02d", $i) .'</option>';
    } else {
      $monthfieldcode .= '<option value="'.$i.'">'. sprintf("%02d", $i) .'</option>';
    }
  }
  $monthfieldcode .= '</select>';
  $yearfieldcode  = '<select name="field_year_'.$fieldname.'" title="'.ML("jaar","year").'"><option value="">'.ML("JJJJ", "YYYY").'</option>';
  $minyear = $specs["minyear"];
  if (!$minyear) $minyear = 1900;
  if (substr($minyear, 0, 8) == "current+") $minyear = N_Year() + substr($minyear, 8);
  if (substr($minyear, 0, 8) == "current-") $minyear = N_Year() - substr($minyear, 8);
  $maxyear = $specs["maxyear"];
  if (!$maxyear) $maxyear = N_Year();
  if (substr($maxyear, 0, 8) == "current+") $maxyear = N_Year() + substr($maxyear, 8);
  if (substr($maxyear, 0, 8) == "current-") $maxyear = N_Year() - substr($maxyear, 8);
  if ($maxyear < $minyear) $maxyear ^= $minyear ^= $maxyear ^= intval($minyear);

  for ($i=$minyear; $i<=$maxyear; $i++) {
    if ($value && ($i==N_Date2Year ($value))) {
      $yearfieldcode .= '<option value="'.$i.'" selected>'. sprintf("%04d", $i) .'</option>';
    } else {
      $yearfieldcode .= '<option value="'.$i.'">'. sprintf("%04d", $i) .'</option>';
    }
  }
  $yearfieldcode .= '</select>';
  if (ML_GetLanguage() == "en") {
    $fieldcode = $monthfieldcode . "&nbsp;&nbsp;" . $dayfieldcode . "&nbsp;&nbsp;" . $yearfieldcode;
  } else {
    $fieldcode = $dayfieldcode . "&nbsp;&nbsp;" . $monthfieldcode . "&nbsp;&nbsp;" . $yearfieldcode;
  }

  if ($time) {
    $hourfieldcode  = '<select name="field_hour_'.$fieldname.'" title="'.ML("uur","hour").'"><option value="">'.ML("HH", "HH").'</option>';
    for ($i=0; $i<=23; $i++) {
      if ($value && ($i==N_Time2Hour ($value))) {
        $hourfieldcode .= '<option value="'.$i.'" selected>'. sprintf("%02d", $i) .'</option>';
      } else {
        $hourfieldcode .= '<option value="'.$i.'">'. sprintf("%02d", $i) .'</option>';
      }
    }
    $hourfieldcode .= '</select>';
    $minfieldcode  = '<select name="field_minute_'.$fieldname.'" title="'.ML("minuut","minute").'"><option value="">'.ML("mm", "mm").'</option>';
    for ($i=0; $i<=59; $i++) {
      if ($value && ($i==N_Time2Minute($value))) {
        $minfieldcode .= '<option value="'.$i.'" selected>'. sprintf("%02d", $i) .'</option>';
      } else {
        $minfieldcode .= '<option value="'.$i.'">'. sprintf("%02d", $i) .'</option>';
      }
    }
    $minfieldcode .= '</select>';
    $fieldcode = $fieldcode . "&nbsp;&nbsp;&nbsp;&nbsp;" . $hourfieldcode . " : " . $minfieldcode;
  }

  return $fieldcode;
}

?>