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



// JH sept 2010
// parallelle controle
// onder switch 'multiapprove' 
// voor bouwfonds

uuse ("ims");

function MULTIAPPROVE_Mail($type, $key, $obj, $sgn)
{
  uuse("ims");
  IMS_SetSupergroupName($sgn);

  global $myconfig;
  $domain = IMS_Object2Domain ($sgn, $key);

  $utable = "shield_" . $sgn . "_users";
  if ($myconfig[$sgn]["multiapprove_mailsenderiscurrentuser"] == "yes" and $type != "rappel")
    $me = SHIELD_CurrentUser();
  else
    $me = $obj["allocto"];
  $meobj = MB_Ref($utable, $me);
  $mename = $meobj["name"];
  if (!$mename)
    $mename = $me;
  if (!$mename)
    $mename = "mailer@osict.com";
  $meemail = $meobj["email"];

  $users = MB_Query($utable);
  MB_MultiLoad($utable, $users);

  foreach ($users as $usr => $dummy)
  {
    $user = MB_Ref($utable, $usr);// controle dat gebruiker nog bestaat en mail heeft
    if ($user["email"])
    {
      if ($obj["multiapprove"][$usr] == "x")
      {
         $to = $user["email"];
         $subj = ($type == "rappel" ? ML("Herinnering: ", "Reminder: ") : "") . ML("Distributie notificatie \"", "Distribution notification \"") . $obj["shorttitle"] . "\"";
         $fn = $obj["filename"];
         $dmsurl = "http://" . $domain . "/ufc/url/" . $sgn . "/" . $key . "/" . $fn;
         // am: bouwfonds wenst 1 url. Als de 2e url terug moet komen dan een schakeloptie.
         // $url = "http://" . $domain . FILES_DocPreviewUrl($sgn, $key);
         $title = $obj["shorttitle"];
              
         foreach ($obj["history"] as $hkey => $hval)
           if ($hval["type"] == "option")
             $hkeymem = $hkey;
         $comment = $obj["history"][$hkeymem]["comment"];
 
         $body = "";
         $body .= ML("Van: ", "From: ") . $mename . "\r\n";
         $body .= "Document: " . $title . "\r\n";
         $body .= ML("Distributiedatum: ", "Distribution date: ") . N_VisualDate($obj["multiapprove"]["date"]) . "\r\n";
         $body .= ML("Verloopdatum: ", "Enddate: ") . N_VisualDate($obj["multiapprove"]["enddate"]) . "\r\n";
         $body .= ML("Link naar het DMS: ", "Link to the DMS: ") . $dmsurl . "\r\n";
         // am: zie comment 1
         //$body .= "Link: " . $url . "\r\n"; 
         
         if ($type != "rappel")
           $body .= ML("Opmerking: ", "Comment: ") . $comment . "\r\n"; 

          
         if ($type == "rappel")
         {
           $body .= "\r\n"; 
           $body .= ML("Dit is een herinnering.", "This is a reminder.") . "\r\n";
         }
       
        N_SendMail($meemail, $to, $subj, $body);
      }
    }
  }
}

function MULTIAPPROVE_SaveDistributionlist($listname, $distribution)
{
  $me = SHIELD_CurrentUser();
  $sgn = IMS_SupergroupName();
  $utable = "shield_" . $sgn . "_users";
  $uobj = &MB_Ref($utable, $me);
  $uobj["multiapprove"][$listname] = $distribution;
}

function MULTIAPPROVE_ShowDistributionlists()
{
  echo "<br>";
  T_Start("ims", array ("noheader"=>true ) );
  $utable = "shield_" . IMS_SupergroupName() . "_users";
  $me = SHIELD_CurrentUser();
  $count = 0;
  $dlists = array();
  $uobj = MB_Ref($utable, $me);
  foreach ($uobj["multiapprove"] as $listname => $distr)
  {
    if ($listname and $distr)
    {
      ++$count;
      $dlists[$count] = $listname; 
    }
  }

  if ($count > 0)
  {
    asort($dlists);
    foreach ($dlists as $nr => $listname)
    {
      $form = array();
      $form["input"]["del"] = $listname;
      $form["postcode"] = '
        $utable = "shield_" . IMS_SupergroupName() . "_users";
        $me = SHIELD_CurrentUser();
        $uobj = &MB_Ref($utable, $me);
        unset($uobj["multiapprove"][$input["del"]]);
      ';

      $delurl = FORMS_URL($form);
      echo '<a title="'.ML("Verwijder distributielijst","Delete distribution list").'" href="'.$delurl.'"><img border="0" src="/ufc/rapid/openims/delete_small.gif"></a>';
      echo '&nbsp;';

      $url = MULTIAPPROVE_ShowUsers($listname);
      echo "<a class=\"ims_navigation\" title=\"".ML("Toon distributielijst","Show distribution list")."\" href=\"$url\">" . $listname . "</a><br>";
    }
  }
  else
  {
    echo ML("Geen distributielijsten", "No distribution lists");
  }

  TE_End();
  echo "<br>";
}

function MULTIAPPROVE_ShowUsers($listname)
{
  uuse("forms");
  $utable = "shield_" . IMS_SupergroupName() . "_users";
  $me = SHIELD_CurrentUser();
  $uobj = MB_Ref($utable, $me);
  $distr = $uobj["multiapprove"][$listname];
  
  $form = array();
  $form["title"] = ML("Gebruikers op de lijst","Users on the list");

  $form["formtemplate"] = '<table><tr><td><center><b>' . $listname . '</b></center><td><tr><tr><td>&nbsp;</td></tr>';
  $uslist = explode(";" , $distr);
  foreach ($uslist as $nr => $usr)
  {
    if ($usr)
    {
      $user = MB_Ref($utable, $usr);
      $username = $user["name"];
      if ($username)
        $form["formtemplate"] .= '<tr><td><font face="arial" size=2>' . $username . '</font></td></tr>';
    }
  }
  $form["formtemplate"] .= '<tr><td>&nbsp;</td></tr>
                            <tr><td><center>[[[ok]]]</center></td></tr>
                            </table>';

  return FORMS_URL($form);
}

function MULTIAPPROVE_ShowChoices($wf)
{
   $chc_arr = Array();
   $chc_arr = MULTIAPPROVE_Choices();
   $str = "";
   if ($chc_arr)
   {
     foreach ($chc_arr as $url => $chce)
       $str .= '&nbsp;<a class="ims_navigation" href="'.$url.'">'.$chce.'</a><br>';
     if ($wf) {
       $str .= "<hr>";
     }
   }
   return $str;
}

function MULTIAPPROVE_Choices()
{
  global $currentobject;
  if ($currentobject)
  {
    $objma = MB_Ref("ims_" . IMS_SupergroupName() . "_objects", $currentobject); 
    if ($objma and $objma["multiapprove"] and $objma["multiapprove"]["date"])
    {
      //controleer begintijd , eindtijd, of stadium klopt of gebruiker op lijst staat en of nog moet kiezen
      $now = time();
      $now = $now - ($now % (24 * 3600)); // hele dagen van maken
      $me = SHIELD_CurrentUser();

      $wf = $objma["workflow"];
      $stage = $objma["stage"];
      $sgn = IMS_SupergroupName();
      $wft = "shield_" . $sgn . "_workflows";
      $wfobj = MB_Ref($wft, $wf);

      $choices = $wfobj["multiapprove"][$stage]["choices"];
      $chces = array();
      $tmp = explode(";", $choices);
      foreach ($tmp as $choice)
        if ($choice)
          $chces[$choice] = $choice;

      if (($now >= $objma["multiapprove"]["date"]) and ((!$objma["multiapprove"]["enddate"]) or ($now <= $objma["multiapprove"]["enddate"])))
      {
        if (count($chces) > 0 and $objma["multiapprove"][$me] == "x")
        {
 
          $arr = Array();
          uuse("forms");

          foreach ($chces as $chce)
          {
            $form = array();

            $form["input"]["wf"] = $wf;
            $form["input"]["stage"] = $stage;
            $form["input"]["choice"] = $chce;
            $form["input"]["currentobject"] = $currentobject;
            $form["input"]["now"] = $now;
            $form["metaspec"]["fields"]["comment"]["type"] = "text";
            $form["formtemplate"] = '
               <table widht="100%">
               <tr><td><font face="arial" size=2><b>Toelichting:</b></font></td><td>[[[comment]]]</td></tr>
               <tr><td>&nbsp;</td></tr>
               <tr><td>[[[OK]]]</td><td>[[[CANCEL]]]</td></tr>
               </table>
            ';
            $form["postcode"] = '
              $sgn = IMS_SupergroupName();
              $obj = &MB_Ref("ims_" . $sgn . "_objects", $input["currentobject"]); 

              $me = SHIELD_CurrentUser();
              $now = time();
              $now = $now - ($now % (24 * 3600)); // hele dagen van maken

              if (($now != $input["now"]) or ($obj["workflow"] != $input["wf"]) or ($obj["stage"] != $input["stage"]) or($obj["multiapprove"][$me] != "x"))
              {
                FORMS_ShowError(ML("Fout", "Error"), ML("Data zijn veranderd", "Data have changed"));
              }
              else
              {
                $obj["multiapprove"][$me] = $input["choice"];
                $obj["multiapprove"]["time"][$me] = time();
                $obj["multiapprove"]["comment"][$me] = $data["comment"];
                foreach ($obj["history"] as $hkey => $hval)
                  if ($hval["type"] == "option")
                    $hkeymem = $hkey;
                $obj["history"][$hkeymem]["multiapprove"][$me] = $input["choice"];
                $obj["history"][$hkeymem]["multiapprovetime"][$me] = $obj["multiapprove"]["time"][$me];
                $obj["history"][$hkeymem]["multiapprovecomment"][$me] = $data["comment"];
              }
            ';

            $url = FORMS_URL($form);
            $arr[$url] = $chce;
          }

          return $arr;
        }
      }
    }
  }
}

function MULTIAPPROVE_BatchJobs($sgn)
{
  $table = "ims_" . $sgn . "_objects";
  $utable = "shield_" . $sgn . "_users";
  $wftable = "shield_" . $sgn . "_workflows";

  $now = time();
  $now = $now - ($now % (24 * 3600));

  // eerst de update-mail, einde looptijd
  $specs["select"] = array('$record["published"] == "yes" or $record["preview"] == "yes"' => true, '$record["objecttype"]' => "document",
                           '$record["multiapprove"]["enddate"] > 0' => true);
  $specs["range"] = array('$record["multiapprove"]["enddate"]', 1, $now - 1);

  $keys = MB_TurboMultiQuery($table, $specs);
  MB_MultiLoad($table, $keys);

  foreach ($keys as $key => $dummy)
  {
    $obj = &MB_Ref($table, $key);
    $alloc = $obj["allocto"];
    if ($alloc)
    {
      $uobj = MB_Ref($utable, $alloc);
      $eml = $uobj["email"];
      if ($eml)
      {
        $wf = $obj["workflow"];
        $wfobj = MB_Ref($wftable, $wf);
        $stage = $obj["stage"];
        $instage = $wfobj["multiapprove"][$stage]["choices"];
        if ($instage)
        {
          MULTIAPPROVE_UpdateMail($eml, $key, $obj, $sgn);
          unset($obj["multiapprove"]);
        }
      }   
    }
  }
  MB_Flush();


 // dan de rappel-mail, einde rappel
  $specs["select"] = array('$record["published"] == "yes" or $record["preview"] == "yes"' => true, '$record["objecttype"]' => "document",
                           '$record["multiapprove"]["remind"] > 0' => true);
  $specs["range"] = array('$record["multiapprove"]["remind"]', 1, $now - 1  + 24 * 3600);

  $keys = MB_TurboMultiQuery($table, $specs);
  MB_MultiLoad($table, $keys);

  foreach ($keys as $key => $dummy)
  {
    $obj = &MB_Ref($table, $key);
    $wf = $obj["workflow"];
    $wfobj = MB_Ref($wftable, $wf);
    $stage = $obj["stage"];
    $instage = $wfobj["multiapprove"][$stage]["choices"];
    if ($instage)
    {
      MULTIAPPROVE_Mail("rappel", $key, $obj, $sgn);
      unset($obj["multiapprove"]["remind"]);
    }   
  }  
  MB_Flush();
}

function MULTIAPPROVE_UpdateMail($eml, $key, $obj, $sgn)
{
  uuse("ims");
  IMS_SetSupergroupName($sgn);

  $utable = "shield_" . $sgn . "_users";

  global $myconfig;
  $domain = IMS_Object2Domain ($sgn, $key);

  $subj = "Update document distributie \"" . $obj["shorttitle"] . "\"";
  $url = "http://" . $domain . FILES_DocPreviewUrl($sgn, $key);
  $title = $obj["shorttitle"];
  $fn = $obj["filename"];
  $dmsurl = "http://" . $domain . "/ufc/url/" . $sgn . "/" . $key . "/" . $fn;

  $body = "";
  $body .= "Document: " . $title . "\r\n";
  $body .= ML("Distributiedatum: ", "Distribution date: ") . N_VisualDate($obj["multiapprove"]["date"]) . "\r\n";
  $body .= ML("Verloopdatum: ", "Enddate :") . N_VisualDate($obj["multiapprove"]["enddate"]) . "\r\n"; 
  $body .= ML("Link naar het DMS: ", "Link to the DMS: ") . $dmsurl . "\r\n";
  //am: zie comment multi_Mail 
// $body .= "Link: " . $url . "\r\n";

  $body .= "\r\n";
  foreach ($obj["multiapprove"] as $makey => $maval)
  {
    $uobj = MB_Ref($utable, $makey);
    $uname = $uobj["name"];
    if ($maval and $uname)
    {
      $body .= $uname;
      $body .= ($maval == "x" ? ML(" heeft geen keuze gemaakt.", " has made no choice.". "\r\n") : 
                                ML(" heeft de keuze \"", " has made the choice \"") . $maval . ML("\" gemaakt.", "\".")) . "\r\n";
    }
  }

  N_SendMail($eml, $eml, $subj, $body);
}

function MULTIAPPROVE_Status()
{
  $now = time();
  $now = $now - ($now % (24 * 3600));
  global $currentobject;
  global $myconfig;
  uuse("shield");
  $sgn = IMS_SupergroupName();
  if ($currentobject and SHIELD_HasObjectRight($sgn, $currentobject, "view"))
  {
    $obj = MB_Ref("ims_" . $sgn . "_objects", $currentobject);
    if(($myconfig[IMS_SuperGroupName()]["multiapprovekeepstatus"]=="yes") && !$obj["multiapprove"]) {   
      $hist = end($obj["history"]);
      $obj["multiapprove"] = $hist["multiapprove"];
    }
    if ($obj["multiapprove"])
     {
      $showform = false;
      if (($now >= $obj["multiapprove"]["date"]) and ((!$obj["multiapprove"]["enddate"]) or ($obj["multiapprove"]["enddate"] >= $now))) $showform = true;
      if($myconfig[IMS_SuperGroupName()]["multiapprovekeepstatus"]=="yes") $showform = true;
      if($showform)
      {
        $stage = $obj["stage"];
        $wf = $obj["workflow"];
        $wfobj = MB_Ref("shield_" . $sgn . "_workflows", $wf);
        if ($wfobj["multiapprove"][$stage]["choices"])
        {
          uuse("forms");
          $form = array();
          $form["title"] = ML("Status overzicht", "Status review");
          $form["formtemplate"] .= '<font face="arial" size=2><b>' . ML("Goedkeuringsstatus", "Approval status") . '</b><br>
                                    <nobr>' . ML("Naam document", "Document name") .  ': ' . $obj["shorttitle"] . '</nobr><br>' .
                                   '<nobr>' . ML("Huidige datum", "Today") . ': ' . N_VisualDate($now, 0, 1) . '</nobr><br>' .
                                   '<nobr>' . ML("Einddatum", "Enddate") . ': ' . N_VisualDate($obj["multiapprove"]["enddate"], 0 ,1) . '</nobr><br><br><br></font>';
          T_Start("ims");
   
          echo '<nobr>' . ML("Toegewezen aan", "Allocated to") . '&nbsp;&nbsp;</nobr>';
          T_Next();
          echo ML("Datum", "Date") . '&nbsp;&nbsp;';
          T_Next();
          echo ML("Keuze", "Choice");
          T_NewRow(); 
          echo '&nbsp;';
          T_NewRow();
          
          foreach ($obj["multiapprove"] as $makey => $maval) 
          {
            $mausr = MB_Ref("shield_" . $sgn . "_users", $makey);
            $maname = $mausr["name"];
            $maeml = $mausr["email"];
            if ($maname and $maval)
            {
              if ($maeml)
                $maurl = '<a href="mailto:' . $maeml . '">' . $maname . '</a>';
              else
                $maurl = $maname;
            $tm = $obj["multiapprove"]["time"][$makey]; 
            if (!$tm)
              $tm = $hist["multiapprovetime"][$makey]; //gv Goedkeuringsstatus had no proved date
              
              echo '<nobr>' . $maurl . '&nbsp;&nbsp;</nobr>';
              T_Next();
              echo '<nobr>' . ($maval != "x" ? N_VisualDate($tm, 1, 1) : "-") . '&nbsp;&nbsp;</nobr>';
              T_Next();
              echo '<nobr>' . ($maval == "x" ? "-" : $maval) . '&nbsp;&nbsp;</nobr>';
              T_NewRow();
             }   
          }
          
           
          $str = TS_End();
          $form["formtemplate"] .= $str;
          $form["formtemplate"] .= '<br><br><center>[[[ok]]]</center>';
         
          $url = FORMS_URL($form);
          return '<a class="ims_navigation" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/copy_small.gif"> ' . 
                 ML("Status overzicht", "Status review") . '</a><br>';
        }
      }
    }
  }
}


?>