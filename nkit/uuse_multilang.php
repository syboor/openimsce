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



/*

"en" English
"nl" Dutch
"fr" French
"de" German
"it" Italian
"pt" Portuguese
"es" Spanish
"ja" Japanese
"zh" Chinese
"ko" Korean


Files:
DOC_ROOT/openims/multilang/multilang.ubd           core database, is upgraded when OpenIMS is upgraded        
DOC_ROOT/config/$sgn/multilang/multilang.ubd       custom database, extends and overrules core database
DOC_ROOT/tmp/mlcache/fr-$sgn.dat                   per language cache, somewhat like the FLEX cache

Format of the multilang core database:
array of 
  key => array(
    "orig" => array(
      "nl" => "een",     // as found in the source code (function call to ML)
      "en" => "one",     // as found in the source code 
    ),
    "trans" => array(
      "fr" => "un",      // extra language, stored in this database
      "de" => "eins",    // extra language, stored in this database
    ),
    "status" => array(   // status of translations
      "fr" => "autogenerated", 
      "de" => "ok",
    )
  )
)

The custom multilang database (one for each sitecollection) has the same structure. 
In the custom multilang database, "trans" can also contain "nl" or "en" entries; in the core database this is impossible.
The custom database may contain records not present in the core database, because of ML-calls in the custom code (maatwerk).

The cache contains the raw keys (concatenation of original nl and en, without md5) and the translations. 
The cache is per sitecollection and per language (because most requests will only need one sitecollection and one language).
The cache integrates custom and core translations. Original NL or EN strings are never stored in the cache. 



If you are dealing with FIELDS containing strings such as "#!ML!en!Letter!nl!Brief!#", use this function:

FORMS_ML_Filter($string) // This function will translate to the current language (ML_GetLanguage()).

*/

uuse ("shield");

function ML_Translate ($source, $dest, $string)
{  
  $key = DFC_Key("ML_Translate ($source, $dest, $string) v4");
  if (DFC_Exists($key)) return DFC_Read ($key);
  uuse ("soap");
  $trans = SOAP_Translate ($source, $dest, $string);
  if (is_string ($trans)) {
    $trans = trim($trans);
    if (strpos (" ABCDEFGHIJKLMNOPQRSTUVWXYZ", substr ($string, 0, 1))) {
      $trans = strtoupper(substr($trans,0,1)).substr($trans,1);
    }
    return DFC_Write ($key, $trans);
  } else {
    return DFC_Write ($key, $string, 1); // translation failed, try again in 1 hour
  }
}

function ML_SetLanguage ($language)
{
  global $ml_language;
  $old = $ml_language;
  if (!$language) $language="nl";
  $ml_language = $language;
  return $old;
}

function ML_UseSiteLanguage($sgn = "", $site_id = "")
{
  global $ml_site_language, $ml_site_language_site_id, $ml_prod_language, $myconfig;

  if (!$ml_site_language || ($site_id && $ml_site_language_site_id != $site_id)) {
    if (!$sgn) $sgn = IMS_SuperGroupName();
    if (!$site_id) $site_id = IMS_DetermineJustTheSite();
    $ml_site_language = $ml_prod_language = ML_GetLanguage();
    $ml_site_language_site_id = $site_id;
    if ($myconfig[$sgn]["ml"] && $myconfig[$sgn]["ml"]["sitelanguage"] && $myconfig[$sgn]["ml"]["sitelanguage"][$site_id]) {
      $ml_site_language = $myconfig[$sgn]["ml"]["sitelanguage"][$site_id];
    }
  }  
  ML_SetLanguage($ml_site_language);
}

function ML_UseProdLanguage()
{
  global $ml_prod_language;
  if ($ml_prod_language) ML_SetLanguage($ml_prod_language);
}


function ML_GetLanguage ()
{
  // The order of checking language preference is:
  //   domain language > session language > user language > supergroup language > machine language
  //
  // The "domain language" and "session language" are intended ***only*** for the (not recommended) "multiple languages within one site" model. 
  // Their configuration options should not be used for the "one site per language" model. 
  //
  // For the "one site per language" model, $myconfig[$sgn]["ml"]["sitelanguage"][$site_id] = $language is used to configure it.
  // The language thus configured is active only while rendering the CMS page, and not inside the coolbar or DMS interface. 
  // Uuse_ims.php will use ML_UseSiteLanguage and ML_UseProdLanguage to switch between "product language" and "site language"
  // when necessary.
  // If you use "one site per language", the languages available in the sites can be diffent from the languages available
  // in the product. 
  //
  global $ML_GetLanguage_depth;
  $ML_GetLanguage_depth++;
  if ($ML_GetLanguage_depth > 1) {
    $ML_GetLanguage_depth--;
    return "nl";
  } 
  global $ml_language, $myconfig;
  if (!$ml_language) {
    if ($myconfig[IMS_SuperGroupName()]["ml"]["sitelanguagedomain"]) {
      $site_id = IMS_DetermineJustTheSite();
      $langdomains = $myconfig[IMS_SuperGroupName()]["ml"]["sitelanguagedomain"][$site_id];
      if ($langdomains) {
        $domainlangs = array_flip($langdomains);
        $domain = $_SERVER["HTTP_HOST"];
        if ($domainlangs[$domain]) ML_SetLanguage($domainlangs[$domain]);
      }
    }
    if (!$ml_language && $_COOKIE["ims_lang"]) { // for anynomous visitors
      $site_id = IMS_DetermineJustTheSite();
      if (in_array($_COOKIE["ims_lang"], $myconfig[IMS_SuperGroupName()]["ml"]["sitelanguages"][$site_id])) {
        ML_SetLanguage ($_COOKIE["ims_lang"]);
      }
    }
    if (!$ml_language) {
      $obj = SHIELD_CurrentUserObject ();
      if ($obj && $obj["lang"]) {
        ML_SetLanguage ($obj["lang"]);
      } elseif ($myconfig[IMS_SuperGroupName()]["defaultlanguage"]) {
        ML_SetLanguage ($myconfig[IMS_SuperGroupName()]["defaultlanguage"]);
      } elseif ($myconfig["defaultlanguage"]) {
        ML_SetLanguage ($myconfig["defaultlanguage"]);
      } else {
        ML_SetLanguage ("nl");
      }
    }
  }
  $ML_GetLanguage_depth--;
  return $ml_language;
}

function ML_GetPageLanguage() {
  // Returns the language of the CMS page content (rather than the language the user wanted, which may not be available)

  global $ml_page_language; // will be set by IMS_GetObjectContent. So works in CMS components.
  if ($ml_page_language) return $ml_page_language;
  return ML_GetLanguage();
}


/* 
 * To be able to create a correct multilingual database, the first two arguments of ML should be deterministic, translatable text:
 * - deterministic: Do not use PHP variable substitution or concatenation.
 * - translatable: In a instruction for admins / programmers, do not include the names of function or variables, because they should not be translated.
 * - text: Do not include HTML fragments. Exception: HTML character entities are allowed, and will be translated to normal characters when the translator downloads the database.
 *         Do not use leading or trailing whitespace, because it will probably be lost in the translation.
 * Additional limitations for technical reasons are:
 * - no string concatenation. Each argument should be one single string.
 * - no backslash-escaped quotation marks inside the string arguments. Please work around this by using different outer quotes, or by using HTML-entities for the inner quotes (&quot; and &#39;)
 * - no backslash-escaped quotation marks around the string arguments (e.g. when calling ML from inside postcode or precode).
 *
 * ML accepts optional extra arguments, which can be used to achieve variable substition. The extra arguments are not subject to any of the limitations above.
 * Example: ML("Documenten %1 tot %2 van %3", "Documents %1 to %2 from %3", $from, $until, $total);
 *
 */

function ML ($dutch, $english)
{
  // TODO: (myconfig option) logging / failback scenario's, e.g. every call that tries to use the multilang database and doesnt find a match, include source file and line number

  $key = $dutch . "#" . $english;
  $lang = ML_GetLanguage();

  $data = ML_LoadLanguage($lang);

  if ($data[$key]) {
    $text = $data[$key];
  } elseif ($lang == "en") {
    $text = $english;
  } elseif ($lang == "nl") {
    $text = $dutch;
  } elseif ($lang == "test") {
    $text = "[$dutch#$english]";
  } else {
    global $myconfig;
    $fallback = $myconfig[IMS_SuperGroupName()]["ml"]["fallbacklanguage"][$lang];
    $fallbackdata = ML_LoadLanguage($fallback);
    if ($fallback && $fallback != $lang) {
      if ($fallbackdata[$key]) {
        $text = $fallbackdata[$key];
      } elseif ($fallback == "nl") {
        $text = $dutch;
      } elseif ($fallback == "en") {
        $text = $english;
      }
    }
    
    if (!$text) {
      // TODO fallback mechanism
      if ($dutch == $english){
        $text = $dutch;
      } else {
        $text = "!!![$dutch#$english]";
      }
    }
  }

  if (function_exists('ML_AlterResult')) {
    $text = ML_AlterResult($text, $lang, $dutch, $english);
  } else {
    // Deprecated settings. Do not use this settings. As soon as you import the ML_AlterResult function, they will stop working.
    global $myconfig;
    if ($myconfig["echtnederlands"]) {
      $internal_component = FLEX_LoadImportableComponent("support", "0a4e249dcedf355baff7e8d8216d774b");
      $internal_code = $internal_component["code"];
      eval ($internal_code);
      if (function_exists('ML_AlterResult')) $text = ML_AlterResult($text, $lang, $dutch, $english);
    }
  }

  // Substitute parameters in $text
  $args = func_get_args();
  if (count($args) >= 3) {
    for ($i = 1; $i <= (count($args) - 2); $i++) {
      $text = preg_replace("/%$i(?=($|[^0-9]))/", $args[$i+1], $text);
      // The lookahead assertion makes sure that %10 does not match when looking for %1
    }
  }

  return $text;


}

/* Return array of languages that the customer is allowed to modify from the admin-environment. */
function ML_ModifiableLanguages($internal = false) {
  global $myconfig;
  $result = array();

  if (is_array($myconfig[IMS_SuperGroupName()]["ml"]["languages"])) foreach ($myconfig[IMS_SuperGroupName()]["ml"]["languages"] as $lang) {
    if ($lang == "nl") {
      if (!$internal && $myconfig[IMS_SuperGroupName()]["ml"]["customize_nl"] == "yes") $result[] = $lang;
    } elseif ($lang == "en") {
      if (!$internal && $myconfig[IMS_SuperGroupName()]["ml"]["customize_en"] == "yes") $result[] = $lang;
    } else {
      $result[] = $lang;
    }
  }
  if (is_array($myconfig[IMS_SuperGroupName()]["ml"]["sitelanguage"])) foreach ($myconfig[IMS_SuperGroupName()]["ml"]["sitelanguage"] as $lang) {
    if ($lang == "nl") {
      if (!$internal && $myconfig[IMS_SuperGroupName()]["ml"]["customize_nl"] == "yes") $result[] = $lang;
    } elseif ($lang == "en") {
      if (!$internal && $myconfig[IMS_SuperGroupName()]["ml"]["customize_en"] == "yes") $result[] = $lang;
    } else {
      $result[] = $lang;
    }
  }
  return $result;
}

function ML_LanguageIcon($lang) {
  $description["nl"] = ML("Nederlands","Dutch");
  $description["en"] = ML("Engels","English");
  $description["de"] = ML("Duits","German");
  $description["fr"] = ML("Frans","French");
  if ($lang == "nl-be" || $lang == "fr-be") {
    // Als er geen standaard Nl of Fr actief is op het systeem, dan wordt nl-be en fr-be getoond met Nl / Fr vlag en de tekst "Nederlands" / "Frans".
    // Als er wel standaard Nl of Fr actief is, dan wordt het getoond als Vlaams resp Waals, met regionale vlag. 
    // Gebruik van regionale vlag ipv Nl / Fr vlag is ongebruikelijk en ik denk ook politiek provocerend, dat moeten we alleen doen als
    // we echt niet anders kunnen (omdat Nl / Fr reeds gebruikt wordt voor standaard Nl / Fr).
    global $myconfig;
    $languages = $myconfig[IMS_SuperGroupName()]["ml"]["languages"];
    if (in_array("nl", $languages)) {
      $description["nl-be"] = ML("Vlaams","Flemish");
      $imagefile["nl-be"] = "f0-nl-vl.gif";
    } else {
      $description["nl-be"] = ML("Nederlands","Dutch");
    }
    if (in_array("fr", $languages)) {
      $description["fr-be"] = ML("Waals","Wallon");
      $imagefile["fr-be"] = "f0-fr-wl.gif";
    } else {
      $description["fr-be"] = ML("Frans","French");
    }
  }

  // Special images (name of language != name of nation)
  $imagefile["en"] = "f0-gb.gif";

  if ($imagefile[$lang]) {
    $file = $imagefile[$lang];
  } else {
    $file = "f0-$lang.gif";
  }
  if (N_FileExists("html::/openims/$file")) {
    $img = '<img title="'.($description[$lang] ? N_HtmlEntities($description[$lang]) : strtoupper($lang)).'" border="0" width="21" height="14" src="/ufc/rapid/openims/'.$file.'" />';
  } else {
    $img = strtoupper($lang);
  }
  return $img;
}

function ML_LanguageSelect($goto = "", $site = "" , $coolbar_v2 = false ) {
  // Only use $site from within the coolbar
  global $myconfig;
  if ($site) $languages =  $myconfig[IMS_SuperGroupName()]["ml"]["sitelanguages"][$site];
  if (!$languages) $languages = $myconfig[IMS_SuperGroupName()]["ml"]["languages"];
  if (!$languages)
  {
    $languages = array("nl", "en"); // zodan heeft alleen 'nl' en 'en' plaatjes aangeleverd, daarom alleen nieuwe plaatjes tonen indien deze alleen talen worden gebruikt.
    $v2_images = true;
  } else
    $v2_images = false;
  
  if (!$goto) $goto = N_MyFullUrl();

  $description["nl"] = ML("Nederlands","Dutch");
  $description["en"] = ML("Engels","English");
  $description["de"] = ML("Duits","German");
  $description["fr"] = ML("Frans","French");

  $content .= '<table><tr>';

  $newrow = ceil(count($languages) / 2) - 1;
  foreach ($languages as $i => $lang) {
    $content .= '<td class="ims_td">';
    $url = "";
    if ($myconfig[IMS_SuperGroupName()]["ml"]["sitelanguagedomain"]) {
      $site_id = IMS_DetermineJustTheSite();
      $langdomains = $myconfig[IMS_SuperGroupName()]["ml"]["sitelanguagedomain"][$site_id];
      if ($langdomains && $langdomains[$lang]) {
        $domain = $langdomains[$lang];
        $url = N_MyFullUrl();
        $url = str_replace(N_CurrentProtocol().$_SERVER["HTTP_HOST"], N_CurrentProtocol().$domain, $url);
        if (IMS_Preview() && !strpos($url, "activate_preview")) $url = $url . (strpos($url, "?") ? "&" : "?") . "activate_preview=yes";
      }
    }
    if (!$url) $url = "/openims/action.php?genc=".SHIELD_Encode($goto)."&command=setlang&lang=".$lang;
    $img = ML_LanguageIcon($lang);
    $content .= '<a href="'.$url.'">'.$img.'</a>';

//    $coolbar_v2 .= '<li class="nl-nl"><a title="Nederlands" href="'.$url.'"><span>Nederlands</span></a></li>';//zoals aangeleverd door vormgever
//    $content_v2 .= '<li class="'.$lang.'-'.$lang.'"><a title="'.$description[$lang].'" href="'.$url.'"><span>'.$description[$lang].'</span></a></li>';

    if ( !$v2_images )
    {
      $content_v2 .= '<li class="dis_'.$lang.'-'.$lang.'"><a title="'.$description[$lang].'" href="'.$url.'">'.ML_LanguageIcon($lang).'</a></li>';
    }
    else 
    {
      $content_v2 .= '<li class="'.$lang.'-'.$lang.'">';
      $content_v2 .= '<a title="'.$description[$lang].'" href="'.$url.'"><span>';
      $content_v2 .= $description[$lang];
      $content_v2 .= '</span></a>';
      $content_v2 .= '</li>';
    }

//<a title="Engels (UK)" href="#"><span>Engels (UK)</span></a>
	if ($i == $newrow) 
		$content .= '</tr><tr>';
//    $content_v2 .='</ul><ul class="LangList">';
  }

  $content .= '</table>';

  if ( $coolbar_v2 ) 
    return $content_v2;
  return $content;
}

function ML_UpdateDatabase($internal = false) {
  /* Scan the OpenIMS and custom source code for ML calls, identify the function arguments, and add an entry to the multilang-database (if it doesn't exist yet) */

  /* If this function fails to identify the function arguments, this will be logged. See ML() for when / why identification may fail. */
  
  if ($internal) {
    $files = array_merge(
               glob(getenv("DOCUMENT_ROOT") . "/nkit/*.php"),
               glob(getenv("DOCUMENT_ROOT") . "/openims/*.php"),
               glob(getenv("DOCUMENT_ROOT") . "/openims/flex/modules/*/files/config/___sgn___/flex/*.ubd"),  /* components inside importable packages */
               glob(getenv("DOCUMENT_ROOT") . "/openims/flex/*.ubd")                                         /* importable components */
    );
  } else {
    $files = array_merge(
               glob(getenv("DOCUMENT_ROOT") . "/nkit/*.php"),
               glob(getenv("DOCUMENT_ROOT") . "/openims/*.php"),
               glob(getenv("DOCUMENT_ROOT") . "/openims/flex/modules/*/files/config/___sgn___/flex/*.ubd"),  /* components inside importable packages */
               glob(getenv("DOCUMENT_ROOT") . "/openims/flex/*.ubd"),                                        /* importable components */
               glob(getenv("DOCUMENT_ROOT") . "/config/". IMS_SuperGroupName() . "/flex/*.ubd")              /* custom code */
    );
  }


  foreach ($files as $file) {
    N_Log("multilang", "(update database) processing file $file");

    //echo "filename: $file <br/>";
    $filecontent = N_ReadFile($file);
    if (substr($file, -4) == ".ubd") {
      $flexobject = unserialize($filecontent);
      if (!$flexobject) continue;
      $filecontent = "";
      foreach ($flexobject as $field => $specs) {
        if (substr($field, 0, 4) == "code") $filecontent .= $specs . "\n";
      }
    }  

    $matches = array();
    $offsets = array();
    $database = ML_LoadDatabase($internal);    

    // Find ML calls where the arguments are simple, double quoted strings
    if (preg_match_all ('/(^|[^a-zA-Z0-9_])ML(\s*)\(\\s*"([^"]*)"\s*,\s*"([^"]*)"\s*(\)|,)/ms', $filecontent, $matches, PREG_OFFSET_CAPTURE)) {
      if ($matches[0]) for ($i = 0; $i < count($matches[0]); $i++) {
        $offset = $matches[0][$i][1];
        $offsets[] = $offset;
        // Check if there are any unescaped $'s in the arguments, give error (non-deterministic arguments) if you find one!
        $nl = $matches[3][$i][0];
        $en = $matches[4][$i][0];
        if (preg_match('/(^|[^\\\\])\$/', $en) || preg_match('/(^|[^\\\\])\$/', $nl)) {
          //echo "$file position $offset: non-deterministic arguments: NL = " . htmlspecialchars($nl) . ", EN = " . htmlspecialchars($en) . " <br/>";
          N_Log("multilang", "(update database) $file position $offset: non-deterministic arguments: NL = " . htmlspecialchars($nl) . ", EN = " . htmlspecialchars($en));
        } else {
          $obj = &$database[md5($nl . "#" . $en)];
          //echo "$file position $offset: NL = " . htmlspecialchars($nl) . ", EN = " . htmlspecialchars($en) . " <br/>";
          if (substr($en, -1) == " ") N_Log("multilang", "(update database) $file position $offset: (warning) trailing whitespace in arguments: NL = " . htmlspecialchars($nl) . ", EN = " . htmlspecialchars($en));
          $obj["orig"]["en"] = $en; 
          $obj["orig"]["nl"] = $nl;
        }
      }
    }

    // Find ML calls where the arguments are simple, single quoted strings
    if (preg_match_all ("/(^|[^a-zA-Z0-9_])ML(\s*)\(\\s*'([^']*)'\s*,\s*'([^']*)'\s*(\)|,)/ms", $filecontent, $matches, PREG_OFFSET_CAPTURE)) {
      if ($matches[0]) for ($i = 0; $i < count($matches[0]); $i++) {
        $offset = $matches[0][$i][1];
        $offsets[] = $offset;
        $nl = $matches[3][$i][0];
        $en = $matches[4][$i][0];
        $obj = &$database[md5($nl . "#" . $en)];
        //echo "$file position $offset: NL = " . htmlspecialchars($nl) . ", EN = " . htmlspecialchars($en) . " <br/>";
        if (substr($en, -1) == " ") N_Log("multilang", "(update database) $file position $offset: (warning) trailing whitespace in arguments: NL = " . htmlspecialchars($nl) . ", EN = " . htmlspecialchars($en));
        $obj["orig"]["en"] = $en; 
        $obj["orig"]["nl"] = $nl;
      }
    }
    
    // Find *all* ML calls without any attempt to understand the arguments. 
    // If we had already found them before (because we understood the arguments), everything is OK; if not, report a problem.
    if (preg_match_all ('/(^|[^a-zA-Z0-9_])ML(\s*)\([^\)]*\)/ms', $filecontent, $matches, PREG_OFFSET_CAPTURE)) {
      if ($matches[0]) for ($i = 0; $i < count($matches[0]); $i++) {
        $offset = $matches[0][$i][1]; 
         if (!in_array($offset, $offsets)) {         
          //echo "$file position $offset: unable to identify arguments, partial match = " . htmlspecialchars($matches[0][$i][0]) . "<br/>";
          N_Log("multilang", "(update database) $file position $offset: unable to identify arguments, partial match = " . htmlspecialchars($matches[0][$i][0]));
        }
      }
    }

    ML_SaveDatabase($database, $internal);
    
  }
}

function ML_CleanDatabase($internal = false) {
  // Go through the ML database and delete all entries for which no custom translations or notes have been added
  $database = ML_LoadDatabase($internal);
  $keys = array_keys($database);
  foreach ($keys as $key) {
    $obj = $database[$key];
    $keep = false;
    if (is_array($obj["trans"])) foreach ($obj["trans"] as $lang => $text) {
      if ($text) $keep = true;
    }
    if (!$keep) unset($database[$key]);
  }
  ML_SaveDatabase($database, $internal);
}

function ML_LoadDatabase($internal = false) {
  if ($internal) {
    $file = getenv("DOCUMENT_ROOT")."/openims/multilang/multilang.ubd";
  } else {
    $file = getenv("DOCUMENT_ROOT")."/config/".IMS_SuperGroupName()."/multilang/multilang.ubd";
  }
  $database = unserialize(N_ReadFile($file));
  if (!is_array($database)) $database = array();
  return $database;
}

function ML_SaveDatabase($database, $internal = false) {
  if ($internal) {
    $file = getenv("DOCUMENT_ROOT")."/openims/multilang/multilang.ubd";
  } else {
    $file = getenv("DOCUMENT_ROOT")."/config/".IMS_SuperGroupName()."/multilang/multilang.ubd";
  }
  N_WriteFile($file, serialize($database));
}

function ML_ExportCSV($language, $internal = false, $all = false, $delim = ",", $encoding = "iso") {
  // Export the ML database (created with ML_UpdateDatabase) to CSV format. Return url to temporary file.
  // $all only applicable when $internal = false. If $all = true, export everything, if false, export only records where the target $language is missing from the internal database.

  $database = ML_LoadDatabase($internal);
  $database = N_SortBy($database, '$record["orig"]["nl"]');
  $internaldatabase = ML_LoadDatabase(true);

  // Usually you have the following columns: NL (sourcecode), EN (sourcecode), Target language (custom translation), Status, Key
  // Sometimes, you wants to overrule (rather than just fill in "missing" records) the target language from the core,
  // so you might need an extra column for the Target language (core translation)
  $extracol = false;
  if ($language != "nl" && $language != "en" && !$internal && $all) {
    // Check if the target language is present in the core (if not, no need to show a completely empty column...)
    foreach ($internaldatabase as $key => $internalobj) {
      if ($internalobj["trans"][$language]) {
        $extracol = true;
        break;
      }
    }
  }

  $row[] = "#NL (original, do not modify)";
  $row[] = "#EN (original, do not modify)";
  if ($extracol) $row[] = "#" . strtoupper($language) . " (original, do not modify)";
  if ($extracol || $language == "nl" || $language == "en") {
    $row[] = "#" . strtoupper($language) . " (custom)";
  } else {
    $row[] = "#" . strtoupper($language) . " (translation)";
  }
  $row[] = "#STATUS";
  $row[] = "#KEY (do not modify)";
  $csv = N_sputcsv($row, $delim);

  $orglanguages = array("nl", "en");
  if ($extracol) $orglanguages[] = $language;

  foreach ($database as $key => $obj) {
    if (!$obj) continue;
    $internalobj = $internaldatabase[$key];

    if (!$internal && !$all) {
      // Skip record if the internal database has a translation (and the custom database has not been modified using the $all setting)
      if ($internalobj["trans"][$language] && !$obj["trans"][$language]) continue;
    }
    $row = array();

    // NL and EN columns (original from source code)
    foreach (array("nl", "en") as $lang) {
      // html_entity_decode to get rid of ISO named character entities (&quot, &eacute), N_Html2Utf to get rid of non-ISO numeric entities (&#1234;)
      // Note that html_entity_decode leaves alone all entities which do not exist in the target character set.
      if ($encoding == "utf") {
        $row[] = N_Html2Utf(html_entity_decode($obj["orig"][$lang], ENT_COMPAT, 'ISO-8859-1'));
      } else {
        $row[] = N_Utf2Html(N_Html2Utf(html_entity_decode($obj["orig"][$lang], ENT_COMPAT, 'ISO-8859-1'))); 
      }
    }

    // Extra column with the core translation of the target language
    if ($extracol) {
      if ($encoding == "utf") {
        $row[] = N_Html2Utf(html_entity_decode($internalobj["trans"][$language], ENT_COMPAT, 'ISO-8859-1'));
      } else {
        $row[] = N_Utf2Html(N_Html2Utf(html_entity_decode($internalobj["trans"][$language], ENT_COMPAT, 'ISO-8859-1'))); 
      }
    }

    // Translation / modification column
    if ($encoding == "utf") {
      $row[] = N_Html2Utf(html_entity_decode($obj["trans"][$language], ENT_COMPAT, 'ISO-8859-1'));
    } else {
      $row[] = N_Utf2Html(N_Html2Utf(html_entity_decode($obj["trans"][$language], ENT_COMPAT, 'ISO-8859-1'))); 
    }

    // Status column
    $row[] = $obj["status"][$language];

    // Key column
    $row[] = $key;

    $csv .= N_sputcsv($row, $delim);
  }

  uuse("lib");
  return LIB_Doc2URL($csv, "multilang-" . $language . ".csv");
}

function ML_ImportCsv($file, $preview, $internal = false, $delim = ",", $encoding = "iso") {
  global $myconfig;
  $data = array();
  $handle = fopen($file, "r");

  // Not finished (obviously)

  if ($handle) {
    while (($row = fgetcsv($handle, 64000, $delim)) !== FALSE) {
      if ($encoding == "utf") {
        $data[] = N_Utf2Html($row);
      } else {
        $data[] = $row;
      }
    }

    fclose($handle);

    // Check that we have more than 1 column
    if (count($data[0]) == 1) $errors[] = ML("Geen scheidingsteken gevonden in regel 1", "No separator found in line 1");

    // Find out where the #KEY column is
    $extracol = 0;
    if ("KEY" == trim(N_KeepBefore(substr($data[0][5], 1), "("))) {
      $extracol = 1;
    } elseif ("KEY" == trim(N_KeepBefore(substr($data[0][4], 1), "("))) {
      ; // OK
    } else {
      $errors[] = ML("Geen %1 gevonden in kolom %2 of %3", "No %1 found in column %2 or %3", "#KEY", 5, 6);
    }
    if ($internal && $extracol) $errors[] = "Extra column not allowed for INTERNAL uploads";

    // Find out which language, check that that language is configured.
    $language = trim(N_KeepBefore(substr($data[0][2+$extracol], 1), "("));
    $found = false;
    foreach (ML_ModifiableLanguages() as $lang) {
      if (strtolower($language) == strtolower($lang)) $found = true;
    }
    if ($found) {
      if ($preview) echo "<b>" . ML("Taal gevonden", "Language found") . ": " . strtoupper($language) . "</b><br/>";
    } else {
      $errors[] = ML("Geen geconfigureerde taal gevonden in kolom %1", "No configured language found in column %1", 3+$extracol) . ($language ? ": $language" : $language);
    }
    if ($internal && ($language == "EN" || $language == "NL")) $errors[] = "NL or EN translations can not be uploaded internally";

    if (count($errors)) {
      if ($preview) {
        foreach ($errors as $error) {
          echo '<span style="color: red; font-weight: bold;">' . ML("Fout", "Error") . ": $error</span><br/>";
        }
      } else {
        FORMS_ShowError(ML("Fout", "Error"), implode("<br/>", $errors), "no");
      }
    }

    if ($preview) {
      echo "<b>Preview:</b>";
      echo '<div style="overflow: scroll; height: 300px; width: 800px">';
      T_Start("ims");
      for ($row = 0; $row < 10; $row++) {
        foreach ($data[$row] as $col) {
          echo N_HtmlEntities($col);
          if (!$col) echo "&nbsp;";
          T_Next();
        }
        T_NewRow();
      }
      foreach ($data[$row] as $col) { echo "..."; T_Next(); }
      T_NewRow();
      for ($row = count($data) - 10; $row < count($data); $row++) {
        foreach ($data[$row] as $col) {
          echo N_HtmlEntities($col);
          if (!$col) echo "&nbsp;";
          T_Next();
        }
        T_NewRow();
      }
      TE_End();
      echo '</div>';
    }

    // Import
    $database = ML_LoadDatabase($internal);
    for ($rownr = 1; $rownr < count($data); $rownr++) { // skip first row
      $row = $data[$rownr];
      $key = $row[4 + $extracol];
      $text = $row[2+$extracol];
      $status = $row[3+$extracol];
      if (!$key || !$database[$key]) {
        if ($preview) echo ML("Waarschuwing: de regel wordt overgeslagen omdat de sleutel niet bestaat", "Warning: the row will be skipped because the key does not exist") . ": " . implode(", ", $row) . "<br/>";
        continue;
      }
      if ($text) {
        $database[$key]["trans"][strtolower($language)] = $text;
      } else {
        unset($database[$key]["trans"][strtolower($language)]);
      }
      if ($status) {
        $database[$key]["status"][strtolower($language)] = $status;
      } else {
        unset($database[$key]["status"][strtolower($language)]);
      }
      //if ($preview) echo implode(" ", $row) . "<br/>";
    }
    if (!$preview) {
      ML_SaveDatabase($database, $internal);
      ML_RepairCache(IMS_SuperGroupName(), strtolower($language));
    }

  }

}

function ML_Import_osCommerce21 ()
{
  $path_en = "c:\\openims\\catalog\\oldlang\\english\\";
  $path_nl = "c:\\openims\\catalog\\oldlang\\dutch\\";
  $tree = N_Tree ($path_nl);
  foreach ($tree as $file_nl => $dummy) {
    if (strpos ($file_nl, ".php")) {
      $file_en = str_replace ("english admin", "admin", str_replace ("english catalog", "catalog", str_replace ("dutch", "english", $file_nl)));
      $nl = str_replace ('\\\'', '&escapedquote;', N_ReadFile ($file_nl));
      $en = str_replace ('\\\'', '&escapedquote;', N_ReadFile ($file_en));
      $regexp = "define[ ]*[(][ ]*[']([^']*)['][ ]*[,][ ]*[']([^']*)['][ ]*[)][ ]*[;]";
      for ($i=1; $i<250; $i++) if (N_RegExp ($en, $regexp, 1, $i)) {
        $total[$file_nl.N_RegExp ($en, $regexp, 1, $i)]["en"] = str_replace ('&escapedquote;', '\\\'', N_RegExp ($en, $regexp, 2, $i));
      }
      for ($i=1; $i<250; $i++) if (N_RegExp ($en, $regexp, 1, $i)) {
        $total[$file_nl.N_RegExp ($nl, $regexp, 1, $i)]["nl"] = str_replace ('&escapedquote;', '\\\'', N_RegExp ($nl, $regexp, 2, $i));
      }
    }
  }
  foreach ($total as $dummy => $strings) {
    if ($strings["nl"] && $strings["en"]) {
      $trans[$strings["en"]] = $strings["nl"];
    }
  } 
  return $trans;
}

function ML_Export_osCommerce22m2_File ($file_en, $trans)
{
  global $transcount, $notranscount, $notrans;
  $file_nl = str_replace ("english", "dutch", $file_en);
//  echo $file_en." ".$file_nl." ".strlen (N_ReadFile ($file_en))." ".strlen (N_ReadFile ($file_nl))."<br>";
  $nl = str_replace ('\\\'', '&escapedquote;', N_ReadFile ($file_nl));
  $en = str_replace ('\\\'', '&escapedquote;', N_ReadFile ($file_en));
  $regexp = "define[ ]*[(][ ]*[']([^']*)['][ ]*[,][ ]*[']([^']*)['][ ]*[)][ ]*[;]";
  for ($i=1; $i<250; $i++) if (N_RegExp ($nl, $regexp, 1, $i)) {
    $tag = N_RegExp ($nl, $regexp, 1, $i);
    $oldnl = str_replace ('&escapedquote;', '\\\'', N_RegExp ($nl, $regexp, 2, $i));
    $exp = "define[ ]*[(][ ]*[']".N_RegExp ($nl, $regexp, 1, $i)."['][ ]*[,][ ]*[']([^']*)['][ ]*[)][ ]*[;]";
    $olden = str_replace ('&escapedquote;', '\\\'',N_RegExp ($nl, $exp, 1, 1));
    if ($olden == $oldnl) { // has to be translated
      if ($trans[$olden]) {
        $newnl = $trans[$olden];
//        echo "TRANS Tag: $tag OldNL: $oldnl NewNL: $newnl<br>";
        $transcount++;
      } else {
//        echo "NO TRANS Tag: $tag OldNL: $oldnl<br>";
        $notranscount++;
        $notrans[$oldnl] = ""; 
      }
    }
  }

}

function ML_Export_osCommerce22m2 ($trans)
{
  global $transcount, $notranscount, $notrans;
  ML_Export_osCommerce22m2_File ("C:\\OpenIMS\\catalog\\admin\\includes\\languages\\english.php", $trans);
  ML_Export_osCommerce22m2_File ("C:\\OpenIMS\\catalog\\includes\\languages\\english.php", $trans);
  $path_nl = "C:\\OpenIMS\\catalog\\admin\\includes\\languages\\dutch";
  $path_en = "C:\\OpenIMS\\catalog\\admin\\includes\\languages\\english";
  $tree = N_Tree ($path_en);
  foreach ($tree as $file_en => $dummy) {
    if (strpos ($file_en, ".php")) {
       ML_Export_osCommerce22m2_File ($file_en, $trans);
    }
  }
  $path_nl = "C:\\OpenIMS\\catalog\\includes\\languages\\dutch";
  $path_en = "C:\\OpenIMS\\catalog\\includes\\languages\\english";
  $tree = N_Tree ($path_en);
  foreach ($tree as $file_en => $dummy) {
    if (strpos ($file_en, ".php")) {
       ML_Export_osCommerce22m2_File ($file_en, $trans);
    }
  }
  ksort ($notrans);
  foreach ($notrans as $oldnl => $dummy) {
    echo '$trans[\''.N_XML2HTML($oldnl).'\']=\'\';<br>';
    $amount++;
  }
  echo "Trans: $transcount Notrans: $notranscount Amount: $amount<br>";
}

function ML_LoadLanguage($lang) {  /* cached */
  if (!$lang) return;
  $sgn = IMS_SuperGroupName();
  global $ml_loadcache, $ml_loadcache_loaded, $ml_site_language;
  if (!$ml_loadcache_loaded[$sgn][$lang]) {
    N_Debug("ML_LoadLanguage($sgn, $lang) loading");
    global $myconfig;

    if (in_array($lang, ML_ModifiableLanguages())) {
      if (!N_FileExists("html::/tmp/mlcache/{$lang}-{$sgn}.dat")) ML_RepairCache($sgn, $lang);
      $d = N_ReadFile("html::/tmp/mlcache/{$lang}-{$sgn}.dat");
      if ($d) {
        $dat = unserialize ($d);
      } else {
        $dat = array();
      }
      $ml_loadcache[$sgn][$lang] = $dat;
      $ml_loadcache_loaded[$sgn][$lang] = "yes";
    } else {
      $ml_loadcache[$sgn][$lang] = array();
      $ml_loadcache_loaded[$sgn][$lang] = "yes";      
    }
  }
  return $ml_loadcache[$sgn][$lang];
}

function ML_RepairCache($sgn, $lang = "") {
  N_Debug("ML_RepairCache($sgn, $lang)");
  global $ML_RepairCache;
  global $myconfig;
  if ($ML_RepairCache[$sgn] != "busy") {
    $ML_RepairCache[$sgn] = "busy";
    if ($lang) {
      $langs = array($lang);
    } else {
      $langs = ML_ModifiableLanguages();
    }
    foreach ($langs as $lang) {
      $data = ML_LoadLanguage_Slow($sgn, $lang);
      N_WriteFile ("html::/tmp/mlcache/{$lang}-{$sgn}.dat", serialize($data));
    }
  }
}

function ML_LoadLanguage_Slow($sgn, $lang) { /* not cached */
  N_Debug("ML_LoadLanguage_Slow($sgn, $lang)");

  global $ml_loaddatabase_internal, $ml_loaddatabase_custom, $ml_loaddatabase_loaded;

  if (!$ml_loaddatabase_loaded[$sgn]) {
    IMS_SetSuperGroupName($sgn);
    $ml_loaddatabase_internal = ML_LoadDatabase(true);
    $ml_loaddatabase_custom[$sgn] = ML_LoadDatabase();
    $ml_loaddatabase_loaded[$sgn] = "yes";
  }

  $keys = array_keys(array_merge($ml_loaddatabase_internal, $ml_loaddatabase_custom));
  $data = array();
  foreach ($keys as $key) {
    if ($text = $ml_loaddatabase_custom[$sgn][$key]["trans"][$lang]) {
      $rawkey = $ml_loaddatabase_custom[$sgn][$key]["orig"]["nl"] . "#" . $ml_loaddatabase_custom[$sgn][$key]["orig"]["en"];
      $data[$rawkey] = $text;
    } elseif ($text = $ml_loaddatabase_internal[$key]["trans"][$lang]) {
      $rawkey = $ml_loaddatabase_internal[$key]["orig"]["nl"] . "#" . $ml_loaddatabase_internal[$key]["orig"]["en"];
      $data[$rawkey] = $text;
    }
  }
  
  return $data;
}

?>