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



function MAINT_CollectGarbage ($supergroupname, $daystokeep=31, $deleterecord=false, $testmode=true , $list = false )
{
  uuse ("terra");
  if (!$supergroupname) {
    N_Log ("garbage_collector", "Could not start garbage collection: empty supergroupname");
    return;
  }
  IMS_SetSuperGroupName ($supergroupname);
  $specs["title"] = "COLLECT";
  
  if ( is_array( $list ) )
  {
    $specs["list"] = $list;
  }
  else
    $specs["tables"] = array ("ims_".$supergroupname."_objects");
	
  $specs["input"]["supergroupname"] = $supergroupname;
  $specs["input"]["deleteuntil"] = time()-(($daystokeep+0)*24*3600);// fout - bij 0 invuollen  wist het nog steeds 1 dag niet.
  $specs["input"]["deleterecord"] = $deleterecord;
  $specs["input"]["testmode"] = $testmode;
  $specs["input"]["user"] = SHIELD_CurrentUser();
  
  $specs["step_code"] = '
    if(is_array($object)) {
      if ($object["preview"]!="yes" && $object["published"]!="yes") {
        if (!$object["destroyed"] || $input["deleterecord"]) {
          $supergroupname = $input["supergroupname"];
          $deleteuntil = $input["deleteuntil"];
          $testmode = $input["testmode"];
          $deleterecord = $input["deleterecord"];
          $user = $input["user"];
          if($testmode) {$testmodestring = " testmode on";} else {$testmodestring="";}
          $latestdelete = 0;
          if(is_array($object["history"])) {
            foreach($object["history"] as $histkey=>$histitem) {
              if ($histitem["when"]>$latestdelete) $latestdelete = $histitem["when"];
            }
          }
          if($latestdelete<$deleteuntil) {
            N_Log ("garbage_collector", "Destroyed object $table $key user $user" . $testmodestring);
            $dir1 = "html::/".$supergroupname."/objects/$key/";
            $dir2 = "html::/".$supergroupname."/preview/objects/$key/";
            $dir3 = "html::/".$supergroupname."/objects/history/$key/";
            if(!$testmode) {
              if($deleterecord) {
                MB_Delete($table, $key);
              } else {
                $object["destroyed"]["time"] = time();
                $object["destroyed"]["user"] = $user;
              }

              MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(N_CleanPath ($dir1));
              rmdir (N_CleanPath ($dir1));
              MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(N_CleanPath ($dir2));
              rmdir (N_CleanPath ($dir2));
              MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(N_CleanPath ($dir3));
              rmdir (N_CleanPath ($dir3));
            }
          } else {
            if ($testmode) N_Log ("garbage_collector", "Keep object $table $key user $user" . $testmodestring);
          }
        } 
      }
    }
  ';
  
  if ( is_array( $list ) )
  {
    $specs['step_code'] = '
	$table = "ims_'.$supergroupname.'_objects";
	$key = $index;
	$object = &MB_Ref( $table , $key );
  ' . $specs['step_code'];
    //T_EO( $specs );
	//die();
	TERRA_Multi_List($specs);  
  } else 
    TERRA_Multi_Tables ($specs);
}

function MAINT_Server ($server) {
}

function MAINT_Status () {
}

function MAINT_SyncMetabase ($includehome=false) {

  echo "Transporting data from rack132 to rack107...<br>"; N_Flush();
  N_GetPage ("http://".N_ServerAddress ("rack107")."/nkit/gate.php?command=importdata&server=rack132");

  echo "Transporting data from rack133 to rack107...<br>"; N_Flush();
  N_GetPage ("http://".N_ServerAddress ("rack107")."/nkit/gate.php?command=importdata&server=rack133");

  if ($includehome) {
    echo "Transporting data from nicohome to rack107...<br>"; N_Flush();
    N_GetPage ("http://".N_ServerAddress ("rack107")."/nkit/gate.php?command=importdata&server=nicohome");
  }

  echo "Transporting data from rack107 to rack132...<br>"; N_Flush();
  N_GetPage ("http://".N_ServerAddress ("rack132")."/nkit/gate.php?command=importdata&server=rack107");

  echo "Transporting data from rack107 to rack133...<br>"; N_Flush();
  N_GetPage ("http://".N_ServerAddress ("rack133")."/nkit/gate.php?command=importdata&server=rack107");

  if ($includehome) {
    echo "Transporting data from rack107 to nicohome...<br>"; N_Flush();
    N_GetPage ("http://".N_ServerAddress ("nicohome")."/nkit/gate.php?command=importdata&server=rack107");
  }

  echo "Metabase synchronization completed";
}

?>