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

*** UNIVERSAL FULL TEXT SEARCH MANAGEMENT ***

FEATURES/REMARKS:
   * engine independent interface
   * multiple engine types and engines
   * all matching (including phrase matching) is case insensitive
   * basic support (using HTML Unicode like &#20001;) for Chinese, Japanese etc.
   * delayed background processing of update commands
   * most updates are processed in 0-2 minutes (real-time)
   * low level matabase is used for MySQL operations

ENGINES ($myconfig["ftengine"])
  WORDLIST BASED (UUSE_SEARCH)
    "ULTRA", default, textfile based
    "MYSQL", uses MySQL tables (MySQL any version)
  EXTERNAL ENGINE BASED (UUSE_S2)
    "S2_MYSQLFT", uses MySQL full text indexing (MySQL 4.1+)
    "S2_SPHINX", uses external sphinx search application
    "S2_SPHINX2", uses external sphinx search application, new version


CONSTRUCTION
  UUSE_SEARCH search functions                                       high level API for searching

  UUSE_SEARCH mid level maintenance functions                        pure (readable) text level
  UUSE_SEARCH high level maintenance functions                       document and web page level
  UUSE_SEARCH maintenance functions (obsolete)                       replaced by terra functions

  UUSE_SEARCH storage functions                                      store and retrieve complete text in index

  UUSE_SEARCH conversion functions                                   all kinds of conversions (also some in uuse_ims)
  UUSE_SEARCH filters                                                used by conversion

  UUSE_SEARCH wordlist management, wordlist engine interface layer   generic wordlist management layer
  UUSE_SEARCH wordlist management: disk engine                       disk based wordlist management
  UUSE_SEARCH wordlist management: mysql engine                      MySQL based wordlist management

  UUSE_SEARCH other functions                                            

  UUSE_S2 external indexing interface                                layer between UUSE_SEARCH and UUSE_S2
  UUSE_S2 external query interface                                   low level API for searching
  UUSE_S2 MySQL 4.1+ full text index interface                       connection to MySQL 4.1+ full text search engine

*/

uuse ("ims");
uuse ("s2");
uuse ("s3");

// ************************************************************************************************************************************************
// ********* SEARCH FUNCTIONS

define (SEARCH_MIN_WORDLEN, 1); // minimal word length for indexing and searching

function SEARCH_MatchContent ($query, $index, $key)
{
  $document = SEARCH_Load ($index, "documents_".SEARCH_Key2Name($key));
  return SEARCH_MatchString ($query, $document["fulltext"]);
}

function SEARCH_MatchMeta ($query, $index, $key)
{
  $document = SEARCH_Load ($index, "documents_".SEARCH_Key2Name($key));
  return SEARCH_MatchString ($query, $document["keywords"]);
}

function SEARCH_MatchString ($query, $string)
{
  $words = explode (" ", SEARCH_REMOVEACCENTS(strtolower (SEARCH_TEXT2WORDSQUERY($query))));
  $string = SEARCH_TEXT2WORDS ($string);
  $count = count ($words);
  $ok = true;
  for ($i=0; $i<$count; $i++) {
    if (trim($words[$i])) {
      if (!(strpos("  ".$string." ", " ".trim($words[$i])." "))) $ok = false; 
    }
  }
  return $ok;
}

function SEARCH_MatchAnyContent ($query, $index, $key)
{
  $document = SEARCH_Load ($index, "documents_".SEARCH_Key2Name($key));
  return SEARCH_MatchAnyString ($query, $document["fulltext"]);
}

function SEARCH_MatchAnyMeta ($query, $index, $key)
{
  $document = SEARCH_Load ($index, "documents_".SEARCH_Key2Name($key));
  return SEARCH_MatchAnyString ($query, $document["keywords"]);
}

function SEARCH_MatchAnyString ($query, $string)
{
  $words = explode (" ", SEARCH_REMOVEACCENTS(strtolower ($query)));
  $string = SEARCH_TEXT2WORDS ($string);
  $count = count ($words);
  $ok = false;
  for ($i=0; $i<$count; $i++) {
    if (trim($words[$i])) {
      if ((strpos("  ".$string." ", " ".trim($words[$i])." "))) $ok = true; 
    }
  }
  return $ok;
}

function SEARCH_Summary ($index, $query, $key, $mark = true)
{
  //N_log( "test_jg" , "SEARCH_Summary ($index, $query, $key)" );
  N_Debug("SEARCH_Summary ($index, $query, $key)");
  
  $query = SEARCH_REMOVEACCENTS(strtolower($query)); // Moved up; needs to happen before stripping all non-alnum stuff.

  $query = trim(preg_replace(array(
        "/xxx[a-z]+xxx\s*/", // LF201104: Remove keyword for case search; greatly increases chance of finding "entire" query in the document
        "/\s\-[^\s]+/",      // LF201104: Remove negative keywords (words that should not appear in the document)
        "/[^a-z0-9\*\"]/i",  // JG \* toegevoegd anders wordt * weggeknipt // LF201104: " added here but will be removed later
        "/[ ][ ]*/i"), 
    array(
        "",
        "",
        " ", 
        " "),
  $query)); 
  
  // LF201104: Split query into words, but if phrase search was used, keep the phrase together and do NOT highlight parts of the phrase.
  // Phrases are marked by quotes for both Sphinx2 and S2_MySQL (S2_MySQL uses +"...", but the + has already been stripped).
  if (strpos($query, '"') !== false) { // Searching by phrase
    // Search for pairs of quotes, remove the quotes while replacing any spaces inside the phrase with something else
    $querytmp = preg_replace_callback('/"([^"]*)"/', create_function('$matches', 'return str_replace(" ", "~", $matches[1]);'), $query);
    $wordstmp = explode(" ", $querytmp);
    $wordsStar = array();
    foreach ($wordstmp as $word) {
      $word = str_replace("~", " ", $word); // put spaces back in
      $word = str_replace('"', "", $word); // remove (unmatched) quotes
      if (trim($word)) $wordsStar[] = trim($word); 
    }
    // By keeping the phrase together as a single "word", highlighting of incomplete phrase "parts" is prevented.
    // This highlighting is wrong because it suggests that the highlighted parts are "why" a document was found by
    // the search engine, and it gives the impression that the search engine is ignoring / mixing the phrase.
    // Occasionally a phrase may (incorrectly) remain unmarked if it occurs in the document with more / different
    // whitespace than in the query. This is unlikely because if that is the case, we would not select that part 
    // of the document for the summary, so the phrase would have to be at the start of the document or near a
    // different search term that we managed to find. A fix for that would be to replace space with fancy 
    // whitespace-or-word-boundary regexp in SEARCH_MarkWordsInText.
  } else { // Searching by normal keywords only
    $wordsStar = explode(" ", $query);
  }
   
  $query = str_replace(array('*', '"'), '', $query); // Or maybe remove the entire word with * in it? Or replace * with a regexp and use preg_replace later on?
  
  // Note that * is removed from $query, but retained in $wordsStar because SEARCH_MarkWordsInText understands *.
  
  N_Debug("SEARCH_Summary: query = $query, words = " . implode(" /// ", $wordsStar));
  
  if ( isset( $GLOBALS["SEARCH_Summary_length"] ) )
    $spansize = $GLOBALS["SEARCH_Summary_length"];
  else
    $spansize = 120;

  // $words = explode (" ", SEARCH_REMOVEACCENTS(strtolower ($query))); // Not used anymore
  $count = count ($wordsStar);
  $document = SEARCH_Load ($index, "documents_".SEARCH_Key2Name($key));
  $fulltext = $document["fulltext"];

  // 20090708 KvD gooi HTML tags weg
  global $myconfig;
  if ($myconfig["searchsummarynohtmltags"] == "yes") {
    $fulltext = strip_tags($fulltext);
  } 
  ///
   $docutext = SEARCH_REMOVEACCENTS (strtolower ($fulltext));

  N_Debug("SEARCH_Summary: Looking for query $query in document text");
  if(trim($query)!=''){
    $pos = strpos (strtolower(" ".$docutext), strtolower ($query));
  }

  if ($pos) { // Try to find entire query (as a single phrase) in the text
    N_Debug("SEARCH_Summary: Found query $query at position $pos");
    $before = $spansize; 
    $after = $spansize;
    $start = $pos-$before;
    if ($start<0) { $start=0; $after=($spansize*2); }
    $oldstart = $start;
    if ($start) for ($i=20; $i>=1; $i--) if (substr ($docutext, $oldstart+$i, 1)==" ") $start = $oldstart + $i + 1;
    $end = $pos + $after;
    $oldend = $end;
    for ($i=20; $i>=1; $i--) if (substr ($docutext, $oldend+$i, 1)==" ") $end = $oldend + $i;
  } else {  // Try to find the first word of the query in the text
    // LF201104: This code was supposed to find the smallest word from the query in the text,
    // but didnt work because it never initialized $smallest.
    // I dont see why you would want the smallest word. I would rather start with the longest and keep trying.
    // But I am lazy so have decided to use the first word.
    $word = $wordsStar[0];
    if ($count > 1) N_Debug("SEARCH_Summary: Could not find query, looking for word {$word} in document text");
    if ($count > 1 && ($pos = strpos (" ".$docutext, $word))) { // Only try this if there are multiple words in the query
      N_Debug("SEARCH_Summary: Found word {$word} at position $pos");
      $before = $spansize;
      $after = $spansize;
      $start = $pos-$before;
      if ($start<0) { $start=0; $after=($spansize*2); }
      $oldstart = $start;
      if ($start) for ($i=20; $i>=1; $i--) if (substr ($docutext, $oldstart+$i, 1)==" ") $start = $oldstart + $i + 1;
      $end = $pos + $after;
      $oldend = $end;
      for ($i=20; $i>=1; $i--) if (substr ($docutext, $oldend+$i, 1)==" ") $end = $oldend + $i;
    } else {
      N_Debug("SEARCH_Summary: Giving up, showing start of document text");
      $start=0; $after=$spansize*2;
      $oldstart = $start;
      if ($start) for ($i=20; $i>=1; $i--) if (substr ($docutext, $oldstart+$i, 1)==" ") $start = $oldstart + $i + 1;
      $end = $pos + $after;
      $oldend = $end;
      for ($i=20; $i>=1; $i--) if (substr ($docutext, $oldend+$i, 1)==" ") $end = $oldend + $i;
    }
  }
  $sumary = " ".substr ($fulltext, $start, $end-$start)." ";
  $sumary = N_HtmlEntities($sumary);

  if ($mark) $sumary = SEARCH_MarkWordsInText($sumary, $wordsStar);
  $sumary = trim ($sumary);
  $sumary = preg_replace ("(\[\[\[(<[^<>]*>)*[^\[^\]]*(<[^<>]*>)*(:([^]]*))?\]\]\])", "...", $sumary);
  $sumary = IMS_Accents2Ascii($sumary );
  if ($start) {
    $sumary = "...".$sumary."...";
  } else {
    $sumary = $sumary."...";
  }
  
  if ( function_exists("SEARCH_CustomSummary") )
  {
    $sumary = SEARCH_CustomSummary( $sumary , $index, $query, $key );
  }
  return $sumary;
}

function SEARCH_MarkWordsInText($text, $words) {
  $count = count($words);
  for ($i=0; $i<$count; $i++) {
    if (trim($words[$i])) {
      if ( true || strpos( $words[$i] , "*" ) !== false ) 
      {
        $match = "/\b(" . preg_quote( $words[$i] ) . ")\b/i";
        $match = str_replace( "\*)" , "\w*)" , $match );
        $match = str_replace( "\b(\*" , "(\w*" , $match ); 
        $replace = "<b>" . "\\1" . "</b>"; 
        $text = preg_replace ($match, $replace , $text);   
      } else
        // Disabled. Does not work correctly when when the word appears at the beginning or end of text.
        // The wildcard version above works fine.
        $text = preg_replace ("/([^a-zA-Z])(".preg_quote($words[$i]).")([^a-zA-Z])/i", "\\1<b>\\2</b>\\3", $text);
    }
  }
  return $text;

}

function SEARCH ($index, $query, $from=1, $to=999999999) {
  N_Debug("SEARCH ($index, $query, $from, $to)");
  $search_specs = array();
  $search_specs["query"] = $query; 
  $search_specs["from"] = $from;
  $search_specs["to"] = $to;    
  $search_specs["sgn"] = N_KeepBefore($index, "#");
  $search_specs["index"] = $index;
  $search_specs["oldsearch"] = "yes";
  if (substr($index, -5) == "#bpms") {
    $search_specs["filterexpression"] = S3_BPMS_FilterExpression();
  } elseif ((strpos($index, "#publisheddocuments") !== false) || (strpos($index, "#previewdocuments") !== false)) {
    $search_specs["filterexpression"] = S3_DMS_FilterExpression();  //$expression; 
  } else {
    $search_specs["filterexpression"] = S3_CMS_FilterExpression();
  }
  return S3_SEARCH ($search_specs);
}

function SEARCH_GetGlobalFrom ()
{
  global $from;
  return $from;
}

function SEARCH_Native ($index, $query, $from=1, $to=999999999, $extraenginespecs = array()) 
{
  N_DEBUG ("SEARCH_Native ($index, $query, $from, $to)");
  global $myconfig;
  if ($myconfig["ftengine"]=="S2_MYSQLFT" || $myconfig["ftengine"]=="S2_SPHINX" || $myconfig["ftengine"]=="S2_SPHINX2") { 
    $list = S2_Query ($index, $query, $to, $extraenginespecs);

    $n = 0;
    foreach ($list as $id => $rating) 
    {
      $n++;
      $specs["rating"] = $rating;
      $result["result"][$id] = $specs;
      global $submode;
      if ($submode != "search") {
        if ($n>=$from && $n<=$to) {
          if ($n < 21 || (($to - $from) < 21) || (($n >= SEARCH_GetGlobalFrom ()-1) && ($n <= SEARCH_GetGlobalFrom ()+22))) {
            $result["result"][$id]["sumary"] = SEARCH_Summary ($index, $query, $id);
          }
        }
      }
    }
    $result["amount"] = count ($list);
    N_DEBUG ("SEARCH_Native ($index, $query) COMPLETED");
    return $result;
  }
  $result = array();
  
  $query = SEARCH_TEXT2WORDSQUERY($query);
  $words = explode (" ", SEARCH_REMOVEACCENTS(strtolower ($query)));

  $count = count ($words);
  $smallest = 0;
  $smallestsize = 9999999999;
  for ($i=0; $i<$count; $i++)
  {
    if (strlen ($words[$i]) < SEARCH_MIN_WORDLEN) {
      if (trim($words[$i])) {
        $result["ignore"][$words[$i]] = "x";
      }
    } else {

      $list[$i] = SEARCH_GetDocumentsOnWordList ($index, $words[$i]);
      $listsize[$i] = count ($list[$i]);
      if ($listsize[$i] < $smallestsize) {
        $smallest = $i;
        $smallestsize = $listsize[$i];
      }
    }
  }
  if (is_array ($list[$smallest])) {
    reset ($list[$smallest]);
    while (list($doc, $rank)=each($list[$smallest])) {
      $ok = true;
      for ($i=0; $i<$count; $i++){         
        if (strlen ($words[$i]) >= SEARCH_MIN_WORDLEN && $i!=$smallest) {
          if (!isset ($list[$i][$doc])){
            $ok = false;      
          } else {
            $rank += $list[$i][$doc];
          }
        }
      }
      if ($ok) {
        $result["result"][$doc]["rating"] = $rank;
        $result["amount"]+=1;
      }
    }
  }
  if (is_array($result["result"])) {
    arsort ($result["result"]);
    $n = 0;
    reset($result["result"]);
    while (list ($key) = each ($result["result"])) {
      $n++;
      if ($n>=$from && $n<=$to) {
        $result["result"][$key]["sumary"] = SEARCH_Summary ($index, $query, $key);
      }
    }
  }  
  N_DEBUG ("SEARCH_Native ($index, $query) COMPLETED");
  return $result;
}


// ************************************************************************************************************************************************
// ********* MID LEVEL INDEX MAINTENANCE FUNCTIONS

function SEARCH_ReplaceTextInIndex ($index, $key, $newfulltext, $newkeywords="") 
{
  N_Debug ("SEARCH_ReplaceTextInIndex ($index, $key, $newfulltext, $newkeywords)");

  $document = SEARCH_Load ($index, "documents_".SEARCH_Key2Name($key));
  $wordlist = SEARCH_TextToWordlist (SEARCH_TEXT2WORDS ($document["fulltext"]), SEARCH_TEXT2WORDS ($document["keywords"]));

  $fulltext = $document["fulltext"];
  $keywords = $document["keywords"];

  $list = array();
  $wordlistlen = count($wordlist);
  $wordlistcounter = 0;
  if (is_array($wordlist)) reset($wordlist);
  if (is_array($wordlist)) while (list($word)=each($wordlist)) {
    $rank = 1;
    $wordlistcounter++;
    if (strpos ("  ".$keywords." ", " ".$word." ")) $rank += 10000;
    // old code TTT $rank += round (5000 - ((4900 * strpos (" ".$fulltext, "".$word)) / (1+strlen ($fulltext))));
    $rank += round (5000 - ((4900 * $wordlistcounter) / (1 + $wordlistlen))); 
    $list[$word]["oldrank"] = $rank;
  }

  $wordlist = SEARCH_TextToWordlist (SEARCH_TEXT2WORDS ($newfulltext), SEARCH_TEXT2WORDS ($newkeywords));
  $fulltext = $newfulltext;
  $keywords = $newkeywords;

  $wordlistlen = count($wordlist);
  $wordlistcounter = 0;
  if (is_array($wordlist)) reset($wordlist);
  if (is_array($wordlist)) while (list($word)=each($wordlist)) {
    $rank = 1;
    $wordlistcounter++;
    if (strpos ("  ".$keywords." ", " ".$word." ")) $rank += 10000;
    // old code TTT $rank += round (5000 - ((4900 * strpos (" ".$fulltext, "".$word)) / (1+strlen ($fulltext))));
    $rank += round (5000 - ((4900 * $wordlistcounter) / (1 + $wordlistlen))); 
    $list[$word]["newrank"] = $rank;
  }
  foreach ($list as $word => $ranks) {
    if ($ranks["oldrank"]) {
       if ($ranks["newrank"]) {
         if (abs ($ranks["newrank"]-$ranks["oldrank"]) > 100) {
           SEARCH_RemoveDocumentFromWordlist ($index, $key, $word);
           SEARCH_AddDocumentToWordlist ($index, $key, $word, $ranks["newrank"]);      
         }
       } else {
         SEARCH_RemoveDocumentFromWordlist ($index, $key, $word);
       }
    } else {
      SEARCH_AddDocumentToWordlist ($index, $key, $word, $ranks["newrank"]);      
    }
  }
  $document = array();
  $document["fulltext"] = $newfulltext;
  $document["keywords"] = $newkeywords;
  SEARCH_Save ($index, "documents_".SEARCH_Key2Name($key), $document);
}

function SEARCH_AddTextToIndex ($index, $key, $fulltext, $keywords="", $date = false)  
{ 
  N_Debug ("SEARCH_AddTextToIndex ($index, $key, $fulltext, $keywords)");
  $keywords = FORMS_ML_Filter_Internal($keywords);

  global $myconfig;
  if ($myconfig["ftengine"]=="S2_MYSQLFT" || $myconfig["ftengine"]=="S2_SPHINX" || $myconfig["ftengine"]=="S2_SPHINX2") { 
    S2_AddReadableTextToIndex ($index, $key, $fulltext, $keywords, $date);
    return;
  }

  $replace = false;

  $keywords = SEARCH_TEXT2WORDS ($keywords);
  $fulltext = SEARCH_HTML2TEXT ($fulltext);

  if (!$fulltext && !$keywords) return;

  // add document to searchlist
  if (SEARCH_Exists ($index, "documents_$key")) {
    global $myconfig;
    if ($myconfig["ftengine"] == "MYSQL") { 
      SEARCH_Delete ($index, "documents_$key");
      SEARCH_AddTextToIndex ($index, $key, $fulltext, $keywords, $date);
    } else {
      SEARCH_ReplaceTextInIndex ($index, $key, $fulltext, $keywords);
    }
  } else {
    // add document to storage
    $document["fulltext"] = $fulltext;
    $document["keywords"] = $keywords;
    SEARCH_Save ($index, "documents_".SEARCH_Key2Name($key), $document);
  
    // add document to all relevant wordlists
    $fulltext = SEARCH_TEXT2WORDS ($fulltext);
    $wordlist = SEARCH_TextToWordlist ($fulltext, SEARCH_TEXT2WORDS ($keywords));

    $wordlistlen = count($wordlist);
    $wordlistcounter = 0;
    if (is_array($wordlist)) reset($wordlist);
    if (is_array($wordlist)) while (list($word)=each($wordlist)) {
      $rank = 1;
      $wordlistcounter++;
      if (strpos ("  ".$keywords." ", " ".$word." ")) $rank += 10000;
      // oude code TTT $rank += round (5000 - ((4900 * strpos (" ".$fulltext, "".$word)) / (1+strlen ($fulltext))));
      $rank += round (5000 - ((4900 * $wordlistcounter) / (1 + $wordlistlen))); 

      $toadd[$word] = $rank;
    }
    SEARCH_AddDocumentToWordlistMulti ($index, $key, $toadd);
  }
}

function SEARCH_RemoveFromIndex ($index, $key)
{
  N_Debug ("SEARCH_RemoveFromIndex ($index, $key)");

  global $myconfig;
  if ($myconfig["ftengine"]=="S2_MYSQLFT" || $myconfig["ftengine"]=="S2_SPHINX" || $myconfig["ftengine"]=="S2_SPHINX2") { 
    S2_RemoveFromIndex ($index, $key);
    return;
  }

  global $myconfig;
  if ($myconfig["ftengine"] == "MYSQL") {
    SEARCH_MYSQL_IndexCreate ($index);
    MB_MYSQL_Query ("DELETE FROM ftindex_".MB_MYSQL_Key2Name($index)." WHERE thekey='".mysql_escape_string($key)."';", 0, $myconfig["ftsqlconnection"]);
  } else {
    // load fulltext
    $document = SEARCH_Load ($index, "documents_".SEARCH_Key2Name($key));
    $wordlist = SEARCH_TextToWordlist (SEARCH_TEXT2WORDS ($document["fulltext"]), SEARCH_TEXT2WORDS ($document["keywords"]));
    if (is_array($wordlist)) {
      reset($wordlist);
      while (list($word)=each($wordlist)) {
        SEARCH_RemoveDocumentFromWordlist ($index, $key, $word);
      }
    }
  }
  SEARCH_Delete ($index, "documents_$key");
} 

// ************************************************************************************************************************************************
// ********* HIGH LEVEL INDEX MAINTENANCE FUNCTIONS

function SEARCH_AddHTMLToIndex ($index, $key, $html, $keywords="", $date=false)
{
  SEARCH_AddTextToIndex ($index, $key, SEARCH_HTML2TEXT($html), $keywords, $date);
}

function SEARCH_AddPreviewDocumentToDMSIndex ($sitecollection_id, $object_id) {
  global $myconfig;
  if($myconfig["backgroundfulltextindex"]!="no") {
    MB_Flush();
    N_AddModifyPreciseScedule ("", time()+10 , 'uuse("search"); SEARCH_Do_AddPreviewDocumentToDMSIndex ($input["sgn"], $input["key"]);', array("sgn"=>$sitecollection_id, "key"=>$object_id));
  } else {
    SEARCH_Do_AddPreviewDocumentToDMSIndex ($sitecollection_id, $object_id);
  }
}

function SEARCH_Do_AddPublishedDocumentToIndex ($index, $sitecollection_id, $object_id) 
{
  $object = IMS_AccessObject ($sitecollection_id, $object_id);
  $doc = FILES_TrueFileName ($sitecollection_id, $object_id, "published");
  $doctype = strrev(N_KeepBefore (strrev($doc), "."));

  global $myconfig;
  if ($myconfig[$sitecollection_id]["dmsindexmetadataonly"][$object["workflow"]] == "yes") {
     $text = "";
  } else {
     $text = IMS_Doc2Text (getenv("DOCUMENT_ROOT")."/$sitecollection_id/objects/$object_id/$doc", $doctype);
  }

  $keywords = $object["shorttitle"]." ".$object["longtitle"]." ";
  foreach ($object as $tag => $value) {
    if (substr ($tag, 0, 5)=="meta_") {
      $keywords .= $value." ";
      $field = substr ($tag, 5);
      IMS_SetSuperGroupName ($sitecollection_id);
      $keywords .= FORMS_ShowValue ($value, $field, $object, $object)." ";
    }
  } 
  if ($myconfig[$sitecollection_id]["casesearch"] == "yes") {
    $casekeyword = SEARCH_CaseKeywordForFolder($object["directory"]);
    $keywords .= $casekeyword . " ";
  }

  // Determine and loop over shortcuts
  IMS_UpdateShortcuts ($sitecollection_id, $object_id); // make certain they are up to date. TODO: find out if this is necessary.
  $list = MB_TurboSelectQuery ("ims_".$sitecollection_id."_objects", '$record["source"]', $object_id);
  foreach ($list as $shortcut_id => $dummy) {
    $shortcut = MB_Load("ims_".$sitecollection_id."_objects", $shortcut_id);

    // Shorttitle and longtitle. For permanents, title are always different from base object, but never edited by user. Skip those.
    if ($shortcut["versionshortcut"] != "yes" && $shortcut["shorttitle"] != $object["shorttitle"]) $keywords .= $shortcut["shorttitle"] . " ";
    if ($shortcut["versionshortcut"] != "yes" && $shortcut["longtitle"] != $object["longtitle"]) $keywords .= $shortcut["longtitle"] . " ";

    // Metadata: everything that is different from base object. (Only happens if shortcuts have independent metadata.)
    foreach ($shortcut as $tag => $value) {
      if (substr ($tag, 0, 5)=="meta_") {
        if ($shortcut[$tag] != $object[$tag]) {
          $keywords .= $value." ";
          $field = substr ($tag, 5);
          IMS_SetSuperGroupName ($sitecollection_id);
          $keywords .= FORMS_ShowValue ($value, $field, $shortcut, $shortcut)." ";
        }
      }
    } 

    // Case keyword
    if ($myconfig[$sitecollection_id]["casesearch"] == "yes") {
      $shortcutcasekeyword = SEARCH_CaseKeywordForFolder($shortcut["directory"]);
      if ($shortcutcasekeyword != $casekeyword) {
        $keywords .= $shortcutcasekeyword . " ";
      }
    }
  }

  $date = QRY_DMS_Changed_v1 ($object);
  SEARCH_AddTextToIndex ($index, $object_id, $text, $keywords, $date);
}

function SEARCH_Do_AddPreviewDocumentToIndex ($index, $sitecollection_id, $object_id) 
{
  $object = IMS_AccessObject ($sitecollection_id, $object_id);
  $doc = FILES_TrueFileName ($sitecollection_id, $object_id, "preview");
  $doctype = strrev(N_KeepBefore (strrev($doc), "."));

  global $myconfig;
  if ($myconfig[$sitecollection_id]["dmsindexmetadataonly"][$object["workflow"]] == "yes") {
     $text = "";
  } else {
     $text = IMS_Doc2Text (getenv("DOCUMENT_ROOT")."/$sitecollection_id/preview/objects/$object_id/$doc", $doctype);
  }

  $keywords = $object["shorttitle"]." ".$object["longtitle"]." ";
  foreach ($object as $tag => $value) {
    if (substr ($tag, 0, 5)=="meta_") {
      $keywords .= $value." ";
      $field = substr ($tag, 5);
      IMS_SetSuperGroupName ($sitecollection_id);
      $keywords .= FORMS_ShowValue ($value, $field, $object, $object)." ";
    }
  } 
  if ($myconfig[$sitecollection_id]["casesearch"] == "yes") {
    $casekeyword = SEARCH_CaseKeywordForFolder($object["directory"]);
    $keywords .= $casekeyword . " ";
  }

  // Determine and loop over shortcuts
  IMS_UpdateShortcuts ($sitecollection_id, $object_id); // make certain they are up to date. TODO: find out if this is necessary.
  $list = MB_TurboSelectQuery ("ims_".$sitecollection_id."_objects", '$record["source"]', $object_id);
  foreach ($list as $shortcut_id => $dummy) {
    $shortcut = MB_Load("ims_".$sitecollection_id."_objects", $shortcut_id);

    // Shorttitle and longtitle. For permanents, title are always different from base object, but never edited by user. Skip those.
    if ($shortcut["versionshortcut"] != "yes" && $shortcut["shorttitle"] != $object["shorttitle"]) $keywords .= $shortcut["shorttitle"] . " ";
    if ($shortcut["versionshortcut"] != "yes" && $shortcut["longtitle"] != $object["longtitle"]) $keywords .= $shortcut["longtitle"] . " ";

    // Metadata: everything that is different from base object. (Only happens if shortcuts have independent metadata.)
    foreach ($shortcut as $tag => $value) {
      if (substr ($tag, 0, 5)=="meta_") {
        if ($shortcut[$tag] != $object[$tag]) {
          $keywords .= $value." ";
          $field = substr ($tag, 5);
          IMS_SetSuperGroupName ($sitecollection_id);
          $keywords .= FORMS_ShowValue ($value, $field, $shortcut, $shortcut)." ";
        }
      }
    } 

    // Case keyword
    if ($myconfig[$sitecollection_id]["casesearch"] == "yes") {
      $shortcutcasekeyword = SEARCH_CaseKeywordForFolder($shortcut["directory"]);
      if ($shortcutcasekeyword != $casekeyword) {
        $keywords .= $shortcutcasekeyword . " ";
      }
    }
  }

  $date = QRY_DMS_Changed_v1 ($object);
  SEARCH_AddTextToIndex ($index, $object_id, $text, $keywords, $date);
}

function SEARCH_Do_AddPreviewDocumentToDMSIndex ($sitecollection_id, $object_id) 
{
  N_Debug ("SEARCH_AddPreviewDocumentToDMSIndex ($sitecollection_id, $object_id)");
  $index = $sitecollection_id."#previewdocuments";
  global $myconfig;

  if (is_array ($myconfig["maindmsindex"])) {

     if(SEARCH_WorkflowAllowsIndex($sitecollection_id, $object_id)) { // THB INDEX WFL

        if ($myconfig["maindmsindex"][$sitecollection_id]) {
           $object = IMS_AccessObject ($sitecollection_id, $object_id);
           $result = false;
           eval ('$result=('.$myconfig["maindmsindex"][$sitecollection_id].');');
           if ($result) {
              SEARCH_Do_AddPreviewDocumentToIndex ($index, $sitecollection_id, $object_id);
           } else {
              SEARCH_RemoveFromIndex ($index, $object_id);
           }
        } else {
           SEARCH_Do_AddPreviewDocumentToIndex ($index, $sitecollection_id, $object_id);
        }

     } else {
       SEARCH_RemoveFromIndex ($index, $object_id);
     }

  } else {
     if(SEARCH_WorkflowAllowsIndex($sitecollection_id, $object_id)) { // THB INDEX WFL
       SEARCH_Do_AddPreviewDocumentToIndex ($index, $sitecollection_id, $object_id);
     } else {
       SEARCH_RemoveFromIndex ($index, $object_id);
     }
  }

  if (is_array ($myconfig["extradmsindex"])) foreach ($myconfig["extradmsindex"] as $name => $specs) {
    if ($specs["sgn"]==$sitecollection_id) {
      $object = IMS_AccessObject ($sitecollection_id, $object_id);
      $result = false;
      eval ('$result=('.$specs["indexcondition"].');');
      $index = $sitecollection_id."#previewdocuments#extra#".$name;
      if ($result) {
        SEARCH_Do_AddPreviewDocumentToIndex ($index, $sitecollection_id, $object_id);
      } else {
        SEARCH_RemoveFromIndex ($index, $object_id);
      }
    }
  }
} // qqq

function SEARCH_AddDocumentToDMSIndex ($sitecollection_id, $object_id) {
  global $myconfig;
  if($myconfig["backgroundfulltextindex"]!="no") {
    MB_Flush();
    N_AddModifyPreciseScedule ("", time()+10 , 'uuse("search"); SEARCH_Do_AddDocumentToDMSIndex ($input["sgn"], $input["key"]);', array("sgn"=>$sitecollection_id, "key"=>$object_id));
  } else {
    SEARCH_Do_AddDocumentToDMSIndex ($sitecollection_id, $object_id);
  }
}

function SEARCH_Do_AddDocumentToDMSIndex ($sitecollection_id, $object_id) 
{
  N_Debug ("SEARCH_AddDocumentToDMSIndex ($sitecollection_id, $object_id)");
  $index = $sitecollection_id."#publisheddocuments";
  global $myconfig;

  if (is_array ($myconfig["maindmsindex"])) {

     if(SEARCH_WorkflowAllowsIndex($sitecollection_id, $object_id)) { // THB INDEX WFL

        if ($myconfig["maindmsindex"][$sitecollection_id]) {
           $object = IMS_AccessObject ($sitecollection_id, $object_id);
           $result = false;
           eval ('$result=('.$myconfig["maindmsindex"][$sitecollection_id].');');
           if ($result) {
              SEARCH_Do_AddPublishedDocumentToIndex ($index, $sitecollection_id, $object_id);
           } else {
              SEARCH_RemoveFromIndex ($index, $object_id);
           }
        } else {
           SEARCH_Do_AddPublishedDocumentToIndex ($index, $sitecollection_id, $object_id);
        }

     } else {
       SEARCH_RemoveFromIndex ($index, $object_id);
     }

  } else {
     if(SEARCH_WorkflowAllowsIndex($sitecollection_id, $object_id)) { // THB INDEX WFL
       SEARCH_Do_AddPublishedDocumentToIndex ($index, $sitecollection_id, $object_id);
     } else {
       SEARCH_RemoveFromIndex ($index, $object_id);
     }
  }

  if (is_array ($myconfig["extradmsindex"])) foreach ($myconfig["extradmsindex"] as $name => $specs) {
    if ($specs["sgn"]==$sitecollection_id) {
      $object = IMS_AccessObject ($sitecollection_id, $object_id);
      $result = false;
      eval ('$result=('.$specs["indexcondition"].');');
      $index = $sitecollection_id."#publisheddocuments#extra#".$name;
      if ($result) {
        SEARCH_Do_AddPublishedDocumentToIndex ($index, $sitecollection_id, $object_id);
      } else {
        SEARCH_RemoveFromIndex ($index, $object_id);
      }
    }
  }
}

function SEARCH_RemoveDocumentFromDMSIndex ($sitecollection_id, $object_id) {
  global $myconfig;
  if($myconfig["backgroundfulltextindex"]!="no") {
    MB_Flush();
    N_AddModifyPreciseScedule ("", time()+10 , 'uuse("search"); SEARCH_Do_RemoveDocumentFromDMSIndex ($input["sgn"], $input["key"]);', array("sgn"=>$sitecollection_id, "key"=>$object_id));
  } else {
    SEARCH_Do_RemoveDocumentFromDMSIndex ($sitecollection_id, $object_id);
  }
}

function SEARCH_Do_RemoveDocumentFromDMSIndex ($sitecollection_id, $object_id)
{
  N_Debug ("SEARCH_RemoveDocumentFromDMSIndex ($sitecollection_id, $object_id)");

  global $myconfig;
  if (is_array ($myconfig["extradmsindex"])) foreach ($myconfig["extradmsindex"] as $name => $specs) {
    if ($specs["sgn"]==$sitecollection_id) {
      $index = $sitecollection_id."#previewdocuments#extra#".$name;
      SEARCH_RemoveFromIndex ($index, $object_id);
      $index = $sitecollection_id."#publisheddocuments#extra#".$name;
      SEARCH_RemoveFromIndex ($index, $object_id);
    }
  }

  $index = $sitecollection_id."#publisheddocuments";  
  SEARCH_RemoveFromIndex ($index, $object_id);
  $index = $sitecollection_id."#previewdocuments";  
  SEARCH_RemoveFromIndex ($index, $object_id);
} // qqq

function SEARCH_AddPageToSiteIndex ($sitecollection_id, $site_id, $object_id) {

  global $myconfig;
  if($myconfig["backgroundfulltextindex"]!="no") {
    MB_Flush();
    N_AddModifyPreciseScedule ("", time()+10 , 'uuse("search"); SEARCH_Do_AddPageToSiteIndex ($input["sgn"], $input["site"], $input["key"]);', array("sgn" => $sitecollection_id, "site" => $site_id,"key" => $object_id));
  } else {
    SEARCH_Do_AddPageToSiteIndex ($sitecollection_id, $site_id, $object_id);
  }
}
// thb: voor toevoegen conditionele indexen. kan weg als die goed werken
function SEARCH_Do_AddPageToSiteIndex_OUD ($sitecollection_id, $site_id, $object_id)
{
  N_Debug ("SEARCH_AddPageToSiteIndex ($sitecollection_id, $site_id, $object_id)");

  global $myconfig;
  if ((SEARCH_WorkflowAllowsIndex($sitecollection_id, $object_id)) && (SEARCH_ObjectAllowsIndex($sitecollection_id, $object_id))) { // THB INDEX WFL - DVB OBJECT 
    $index = $sitecollection_id."#".$site_id;
    SEARCH_Do_Do_AddPageToSiteIndex ($sitecollection_id, $site_id, $object_id, $index);
  }
}

function SEARCH_Do_AddPageToSiteIndex ($sitecollection_id, $site_id, $object_id)
{
  N_Debug ("SEARCH_AddPageToSiteIndex ($sitecollection_id, $site_id, $object_id)");
  $index = $sitecollection_id."#".$site_id;
  global $myconfig;

  if (is_array ($myconfig["maincmsindex"])) {
    if ((SEARCH_WorkflowAllowsIndex($sitecollection_id, $object_id)) && (SEARCH_ObjectAllowsIndex($sitecollection_id, $object_id))) { // THB INDEX WFL - DVB OBJECT 
      if ($myconfig["maincmsindex"][$sitecollection_id]) {
        $object = IMS_AccessObject ($sitecollection_id, $object_id);
        $result = false;
        eval ('$result=('.$myconfig["maincmsindex"][$sitecollection_id].');');
        if ($result) {
          SEARCH_Do_Do_AddPageToSiteIndex ($sitecollection_id, $site_id, $object_id, $index);
        } else {
          SEARCH_RemovePageFromSiteIndex ($sitecollection_id, $site_id, $object_id);
        }
      } else {
         SEARCH_Do_Do_AddPageToSiteIndex ($sitecollection_id, $site_id, $object_id, $index);
      }
    } else {
      SEARCH_RemovePageFromSiteIndex ($sitecollection_id, $site_id, $object_id);
    }
  } else {
    if ((SEARCH_WorkflowAllowsIndex($sitecollection_id, $object_id)) && (SEARCH_ObjectAllowsIndex($sitecollection_id, $object_id))) { // THB INDEX WFL - DVB OBJECT 
      SEARCH_Do_Do_AddPageToSiteIndex ($sitecollection_id, $site_id, $object_id, $index);
    } else {
      SEARCH_RemovePageFromSiteIndex ($sitecollection_id, $site_id, $object_id);
    }
  }

  if (is_array ($myconfig["extracmsindex"])) foreach ($myconfig["extracmsindex"] as $name => $specs) {
    if ($specs["sgn"]==$sitecollection_id) {
      $object = IMS_AccessObject ($sitecollection_id, $object_id);
      $result = false;
      eval ('$result=('.$specs["indexcondition"].');');
      $index = $sitecollection_id."#" . $site_id . "#extra#".$name;

      if ($result) {
        SEARCH_Do_Do_AddPageToSiteIndex ($sitecollection_id, $site_id, $object_id, $index);
      } else {
        SEARCH_RemovePageFromSiteIndex ($sitecollection_id, $site_id, $object_id, $index);
      }
    }
  }
}

function SEARCH_Do_Do_AddPageToSiteIndex ($sitecollection_id, $site_id, $object_id, $index) {
  global $myconfig;

  $object = &IMS_AccessObject ($sitecollection_id, $object_id);
  $content = IMS_GetObjectContent ($sitecollection_id, $object_id, true);
  $keywords = $object["parameters"]["published"]["shorttitle"]." ".$object["parameters"]["published"]["longtitle"]." ".$object["parameters"]["published"]["keywords"]." ";

  if($myconfig[$sitecollection_id]["cms"]["indexmetadata"]) { 
    foreach($object["parameters"]["published"] as $key=>$value) { 
      if (substr($key, 0, 5)=="meta_") { 
        $keywords .= $value." "; 
        $field = substr ($key, 5); 
        IMS_SetSuperGroupName ($sitecollection_id); 
        $keywords .= FORMS_ShowValue ($value, $field, $object, $object)." "; 
      } 
    } 
  } 
  $date = QRY_CMS_Published_v1($sitecollection_id, $object);
  SEARCH_AddHTMLToIndex ($index, $object_id, $content, $keywords, $date);
}

function SEARCH_ObjectAllowsIndex($sitecollection_id, $object_id) {
   $object = IMS_AccessObject ($sitecollection_id, $object_id);
   if ($object["noindex"]) {
      return false;
   } else {
      return true;
   }
}

function SEARCH_RemovePageFromSiteIndex ($sitecollection_id, $site_id, $object_id, $index="") {

  global $myconfig;
  if($myconfig["backgroundfulltextindex"]!="no") {
    MB_Flush();
    N_AddModifyPreciseScedule ("", time()+10 , 
     'uuse("search"); SEARCH_Do_RemovePageFromSiteIndex ($input["sgn"], $input["site"], $input["key"], $input["index"]);',
     array("sgn" => $sitecollection_id, "site" => $site_id,"key" => $object_id, "index" => $index));
  } else {
    SEARCH_Do_RemovePageFromSiteIndex ($sitecollection_id, $site_id, $object_id, $index);
  }
}

function SEARCH_Do_RemovePageFromSiteIndex ($sitecollection_id, $site_id, $object_id, $index)
{
  N_Debug ("SEARCH_RemovePageFromSiteIndex ($sitecollection_id, $site_id, $object_id)");
  if(!$index) $index = $sitecollection_id."#".$site_id;
  SEARCH_RemoveFromIndex ($index, $object_id);
}

// ************************************************************************************************************************************************
// ********* STORAGE FUNCTIONS

function SEARCH_Load ($index, $key) 
{
  N_Debug ("SEARCH_Load ($index, $key)");
  global $myconfig;
  if ($myconfig["ftengine"]=="S2_MYSQLFT" || $myconfig["ftengine"]=="S2_SPHINX" || $myconfig["ftengine"]=="S2_SPHINX2") { 
    $key = str_replace ("documents_", "", $key);
    $tmp = S2_GetFromIndex ($index, $key);
    $result["fulltext"] = $tmp["readabletext"];
    $result["keywords"] = $tmp["readablekeywords"];
  } else {
    $result = unserialize (N_ReadFile (getenv("DOCUMENT_ROOT")."/searchindex/$index/$key.idxdat"));
  }
  return $result;
}

function SEARCH_Save ($index, $key, $object)
{
  N_Debug ("SEARCH_Save ($index, $key, ...)");
  global $myconfig;
  if ($myconfig["ftengine"]=="S2_MYSQLFT" || $myconfig["ftengine"]=="S2_SPHINX" || $myconfig["ftengine"]=="S2_SPHINX2") {
    return;
  }
  return N_WriteFile (getenv("DOCUMENT_ROOT")."/searchindex/$index/$key.idxdat", serialize($object));
}

function SEARCH_Delete ($index, $key)
{
  global $myconfig;
  if ($myconfig["ftengine"]=="S2_MYSQLFT" || $myconfig["ftengine"]=="S2_SPHINX" || $myconfig["ftengine"]=="S2_SPHINX2") { 
    return;
  }
  N_DeleteFile ("html::/searchindex/$index/$key.idxdat");
}

function SEARCH_Exists ($index, $key)
{
  global $myconfig;
  if ($myconfig["ftengine"]=="S2_MYSQLFT" || $myconfig["ftengine"]=="S2_SPHINX" || $myconfig["ftengine"]=="S2_SPHINX2") { 
    return S2_Exists ($index, $key);
  } else {
    return file_exists (getenv("DOCUMENT_ROOT")."/searchindex/$index/$key.idxdat");
  }
}

// ************************************************************************************************************************************************
// *** CONVERSION FUNCTIONS

function SEARCH_Any2Text ($any) 
{
  uuse ("marker");
  $any = MARKER_Filter ($any);
  $any = strtr ($any, chr(0).chr(255), "__");
  $any = SEARCH_REMOVEACCENTS ($any);

  $search = array  ("'[.]'i", "'[ ][ ]'i", "'[^a-zA-Z0-9 ]'i");          
  $replace = array ("",       " ",         "_");
  $any =  preg_replace ($search, $replace, $any);

  for ($i=0; $i<5; $i++) {
    $search = array  ("'[ ][a-zA-Z0-9][_]'i", "'[_][a-zA-Z0-9][ ]'i", "'[_]000[_]'i", "'[_][_]'i","'[_][ ][_]'i", "'[__]'i", "'[_][a-zA-Z0-9][_]'i", "'[_][a-zA-Z0-9][a-zA-Z0-9][_]'i", "'[_][a-zA-Z0-9][a-zA-Z0-9][_]'i");
    $replace = "_";
    $any =  preg_replace ($search, $replace, $any);
    $any =  preg_replace ("'([a-zA-Z])[0-9][_]'i", "\\1_", $any);    
    $any = str_replace ("__", "_", $any);
    $any = str_replace ("____", "_", $any);
    $any = str_replace ("________", "_", $any);
  }
  $any = str_replace ("_", " ", $any);

  for ($i=0; $i<5; $i++) {
    $any = str_replace ("  ", " ", $any);
    $any = str_replace ("     ", " ", $any);
    $any = str_replace ("         ", " ", $any);
  }

  return strtolower ($any);
}

function SEARCH_REMOVEACCENTS ($string) { 
  return strtr($string,
               chr(138).chr(140).chr(142).chr(154).chr(156).chr(158).chr(159).chr(165).chr(181).chr(192).chr(193).chr(194).chr(195).chr(196).chr(197).chr(198).chr(199).chr(200).chr(201).chr(202).chr(203).chr(204).chr(205).chr(206).chr(207).chr(208).chr(209).chr(210).chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218).chr(219).chr(220).chr(221).chr(223).chr(224).chr(225).chr(226).chr(227).chr(228).chr(229).chr(230).chr(231).chr(232).chr(233).chr(234).chr(235).chr(236).chr(237).chr(238).chr(239).chr(240).chr(241).chr(242).chr(243).chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251).chr(252).chr(253).chr(255),
               "SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy");
}

function SEARCH_HTML2TEXT ($html,$casesensitive=false) {
//for ($i=161; $i<255; $i++) {
//  $code = str_replace ("&", "", str_replace (";", "", htmlentities (chr($i))));
//  echo str_replace (" ", "&nbsp;", "                   ");
//  echo "\"'&($code|#$i);'i\",<br>";
//  echo "&nbsp;chr($i),<br>";
//}

  if (!$casesensitive) $i='i';
  $search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript
                   "'<style[^>]*?>.*?</style>'si",    // Strip out inline style
                   "'<!--.*?-->'si",                  // Strip out comment
                   "'<[\/\!]*?[^<>]*?>'si",           // Strip out html tags
                   "'([\r\n])[\s]+'",                 // Strip out white space
                   "'&(quot|#34);'i",                 // Replace html entities
                   "'&(amp|#38);'i",
                   "'&(lt|#60);'i",
                   "'&(gt|#62);'i",
                   "'&(nbsp|#32);'i",
                   "'&(euro|#128);'".$i,
                   "'&(iexcl|#161);'".$i,
                   "'&(cent|#162);'".$i,
                   "'&(pound|#163);'".$i,
                   "'&(curren|#164);'".$i,
                   "'&(yen|#165);'".$i,
                   "'&(brvbar|#166);'".$i,
                   "'&(sect|#167);'".$i,
                   "'&(uml|#168);'".$i,
                   "'&(copy|#169);'".$i,
                   "'&(ordf|#170);'".$i,
                   "'&(laquo|#171);'".$i,
                   "'&(not|#172);'".$i,
                   "'&(shy|#173);'".$i,
                   "'&(reg|#174);'".$i,
                   "'&(macr|#175);'".$i,
                   "'&(deg|#176);'".$i,
                   "'&(plusmn|#177);'".$i,
                   "'&(sup2|#178);'".$i,
                   "'&(sup3|#179);'".$i,
                   "'&(acute|#180);'".$i,
                   "'&(micro|#181);'".$i,
                   "'&(para|#182);'".$i,
                   "'&(middot|#183);'".$i,
                   "'&(cedil|#184);'".$i,
                   "'&(sup1|#185);'".$i,
                   "'&(ordm|#186);'".$i,
                   "'&(raquo|#187);'".$i,
                   "'&(frac14|#188);'".$i,
                   "'&(frac12|#189);'".$i,
                   "'&(frac34|#190);'".$i,
                   "'&(iquest|#191);'".$i,
                   "'&(Agrave|#192);'".$i,
                   "'&(Aacute|#193);'".$i,
                   "'&(Acirc|#194);'".$i,
                   "'&(Atilde|#195);'".$i,
                   "'&(Auml|#196);'".$i,
                   "'&(Aring|#197);'".$i,
                   "'&(AElig|#198);'".$i,
                   "'&(Ccedil|#199);'".$i,
                   "'&(Egrave|#200);'".$i,
                   "'&(Eacute|#201);'".$i,
                   "'&(Ecirc|#202);'".$i,
                   "'&(Euml|#203);'".$i,
                   "'&(Igrave|#204);'".$i,
                   "'&(Iacute|#205);'".$i,
                   "'&(Icirc|#206);'".$i,
                   "'&(Iuml|#207);'".$i,
                   "'&(ETH|#208);'".$i,
                   "'&(Ntilde|#209);'".$i,
                   "'&(Ograve|#210);'".$i,
                   "'&(Oacute|#211);'".$i,
                   "'&(Ocirc|#212);'".$i,
                   "'&(Otilde|#213);'".$i,
                   "'&(Ouml|#214);'".$i,
                   "'&(times|#215);'".$i,
                   "'&(Oslash|#216);'".$i,
                   "'&(Ugrave|#217);'".$i,
                   "'&(Uacute|#218);'".$i,
                   "'&(Ucirc|#219);'".$i,
                   "'&(Uuml|#220);'".$i,
                   "'&(Yacute|#221);'".$i,
                   "'&(THORN|#222);'".$i,
                   "'&(szlig|#223);'".$i,
                   "'&(agrave|#224);'".$i,
                   "'&(aacute|#225);'".$i,
                   "'&(acirc|#226);'".$i,
                   "'&(atilde|#227);'".$i,
                   "'&(auml|#228);'".$i,
                   "'&(aring|#229);'".$i,
                   "'&(aelig|#230);'".$i,
                   "'&(ccedil|#231);'".$i,
                   "'&(egrave|#232);'".$i,
                   "'&(eacute|#233);'".$i,
                   "'&(ecirc|#234);'".$i,
                   "'&(euml|#235);'".$i,
                   "'&(igrave|#236);'".$i,
                   "'&(iacute|#237);'".$i,
                   "'&(icirc|#238);'".$i,
                   "'&(iuml|#239);'".$i,
                   "'&(eth|#240);'".$i,
                   "'&(ntilde|#241);'".$i,
                   "'&(ograve|#242);'".$i,
                   "'&(oacute|#243);'".$i,
                   "'&(ocirc|#244);'".$i,
                   "'&(otilde|#245);'".$i,
                   "'&(ouml|#246);'".$i,
                   "'&(divide|#247);'".$i,
                   "'&(oslash|#248);'".$i,
                   "'&(ugrave|#249);'".$i,
                   "'&(uacute|#250);'".$i,
                   "'&(ucirc|#251);'".$i,
                   "'&(uuml|#252);'".$i,
                   "'&(yacute|#253);'".$i,


                   "'&(thorn|#254);'".$i,
                   "'&(yuml|#255);'".$i,
                   "'&#08211;'i",
                   "'&#08230;'i",
                   "'&#08216;'i",
                   "'&#08217;'i",
                   "'&#[0-9]{5};'i" // default replace by space
  );                   
 
  $replace = array (" ",
                    " ",
                    " ",
                    " ",
                    "\\1",
                    "\"",
                    "&",
                    "<",
                    ">",
                    " ",
                    chr(128),
                    chr(161),
                    chr(162),
                    chr(163),
                    chr(164),
                    chr(165),
                    chr(166),
                    chr(167),
                    chr(168),
                    chr(169),
                    chr(170),
                    chr(171),
                    chr(172),
                    chr(173),
                    chr(174),
                    chr(175),
                    chr(176),
                    chr(177),
                    chr(178),
                    chr(179),
                    chr(180),
                    chr(181),
                    chr(182),
                    chr(183),
                    chr(184),
                    chr(185),
                    chr(186),
                    chr(187),
                    chr(188),
                    chr(189),
                    chr(190),
                    chr(191),


                    chr(192),
                    chr(193),
                    chr(194),
                    chr(195),
                    chr(196),
                    chr(197),
                    chr(198),
                    chr(199),
                    chr(200),
                    chr(201),
                    chr(202),
                    chr(203),
                    chr(204),
                    chr(205),
                    chr(206),
                    chr(207),
                    chr(208),
                    chr(209),
                    chr(210),
                    chr(211),
                    chr(212),
                    chr(213),
                    chr(214),
                    chr(215),
                    chr(216),
                    chr(217),
                    chr(218),
                    chr(219),
                    chr(220),
                    chr(221),
                    chr(222),
                    chr(223),
                    chr(224),
                    chr(225),
                    chr(226),
                    chr(227),
                    chr(228),
                    chr(229),
                    chr(230),
                    chr(231),
                    chr(232),
                    chr(233),
                    chr(234),
                    chr(235),
                    chr(236),
                    chr(237),

                    chr(238),
                    chr(239),
                    chr(240),
                    chr(241),
                    chr(242),
                    chr(243),
                    chr(244),
                    chr(245),
                    chr(246),
                    chr(247),
                    chr(248),
                    chr(249),
                    chr(250),
                    chr(251),
                    chr(252),
                    chr(253),
                    chr(254),
                    chr(150),
                    chr(133),
                    chr(145),
                    chr(146),
                    " " // default replace by space

  );

  return preg_replace ($search, $replace, $html);
}

function SEARCH_Pdf2Text($pdfdata) {
  /* By default, this function is *not* used to index pdf documents; "pdftotext" is used.
   * If pdftotext is giving problems, you can try to call this function from the importable component IMS_Doc2Text_Extra.
   * source: http://community.livejournal.com/php/295413.html
   */


  // grab objects and then grab their contents (chunks)
  $allobjs = SEARCH_Pdf2Text_getDataArray($pdfdata, "obj", "endobj");
  foreach ($allobjs as $obj){
    $allfilters = SEARCH_Pdf2Text_getDataArray($obj, "<<", ">>");
    if (is_array($allfilters)) {
      $j++;
      $allchunks[$j]["filter"] = $allfilters[0];

      $alldata = SEARCH_Pdf2Text_getDataArray($obj, "stream\r\n", "endstream");
      if (is_array($alldata)){
        $allchunks[$j]["data"] = substr($alldata[0], strlen("stream\r\n"), strlen($alldata[0]) - strlen("stream\r\n") - strlen("endstream"));
      }
    }
  }

  // decode the chunks
  foreach ($allchunks as $chunk) {

    if ($chunk["data"]) {
      // look at the filter to find out which encoding has been used      
      if (strpos($chunk["filter"], "FlateDecode") !== false){
        $data = @gzuncompress($chunk["data"]);
        if (trim($data) != "") {
          $raw = SEARCH_Ps2Text($data);
          $raw = str_replace("-", "", $raw);
          $any = SEARCH_Any2Text($raw);
          if ($raw) {
            $prop = strlen($any) / strlen($raw);
            if ($prop > 0.6) $result .= $raw;
          }
        }
      }
    }
  }
  
  return $result;

}

function SEARCH_Ps2Text($psdata){
  $result = "";
  $alldata = SEARCH_Pdf2Text_getDataArray($psdata, "[", "]");
  if (is_array($alldata)) {
    foreach ($alldata as $pstext) {
      $alltext = SEARCH_Pdf2Text_getDataArray($pstext, "(", ")");
      if (is_array($alltext)) {
        foreach ($alltext as $text){
          $result .= substr($text, 1, strlen($text) - 2);
        }
      }
    }
  } else {
    // the data may just be in raw format (outside of [] tags)
    $alltext = SEARCH_Pdf2Text_getDataArray($psdata, "(", ")");
    if (is_array($alltext)) {
      foreach ($alltext as $text){
        $result .= substr($text, 1, strlen($text) - 2);
      }
    }
  }
  return $result;
}

function SEARCH_Pdf2Text_getDataArray($data, $start_word, $end_word){
  $start = 0;
  $end = 0;
  unset($result);
  
  while ($start!==false && $end!==false) {
    $start = strpos($data, $start_word, $end);
    if ($start !== false) {
      $end = strpos($data, $end_word, $start);
      if ($end !== false){
        // data is between start and end
        $result[] = substr($data, $start, $end - $start + strlen($end_word));
      }
    }
  }
  return $result;
}

function SEARCH_RTF2Text($rtf) {
  // Warning: this function is slow, about 2 seconds per MB of RTF. Do not call this function in the foreground!
  
  /* There are 3 ways to encode non-ascii characters:
      - 8-bit high-ascii characters in the current character set (usually ansi / iso-8859-1) are encoded 
        using \'hh where hh is a hexadecimal number.  I.e. \'f3 is the o-ecu character.
      - unicode characters are encoded using \uN where N is a decimal number
      - Word prefers to use special fonts rather than unicode.  I.e. a sigma is encoded as {\fN \'f3} 
        where N is an arbitrary number and \fN is defined somewhere in the font table as "Times New Roman Greek" 
        (or another string with "Greek" in it), even though \'f3 is o-ecu. Obviously, we are unable to support 
        such a braindead approach.
     Also, some ascii characters have a special meaning in RTF and need to be escaped by a backslash.
  */
   
  $stack = array(); 
    // The parser's stack.
  $state = array("destination" => "plain"); 
    // The current state of the parser. Currently the only thing we're interested in, is the output destination. 
    // Content for the "plain", "do", "footer" and "info" destinations tends to be meaningful and human readable,
    // content for other destinations does not.
  $readstate = "plain";
    // The current state of the tokenizer. Should not be saved on the stack!
    // plain: the default state. Anything encountered that is not "special", is "output" for the current destination.
    // controlword: reading a control word (something preceded by \)
    // controlparam: reading the (numeric) parameter of a control word. Not all control words have a parameter.
    // controldestination: reading a control word that is preceded by \*\. This means the control word indicates a new destination.

  $len = strlen($rtf);
  for ($pos = 0; $pos < $len; $pos++) {
    // if ($pos > 100000) break;
    
    $char = substr($rtf, $pos, 1);
    if ($readstate == "plain") {
      if ($char == "{") {
        $stack[] = $state; // Copy the current state to the stack.
      } elseif ($char == "}") {
        $state = array_pop($stack); // Retrieve old state from the stack
      } elseif ($char == "\\") {
        $readstate = "controlword";
        $controlword = "";
        $controlparam = "";
      } else {
        // I wanted to do an associate array lookup, but this turned out to be slow, so I hard-coded the condition.
        if ($state["destination"] == "plain" || $state["destination"] == "do" || $state["destination"] == "footnote" || $state["destination"] == "info") $output .= $char;
      }
    } elseif ($readstate == "controlword" || $readstate == "controldestination") {
      $ord = ord($char);
      if (($ord >= ord('a') && $ord <= ord('z')) || ($ord >= ord('A') && $ord <= ord('Z'))) {
        $controlword .= $char;
      } elseif ($controlword && ($char == "-" || ($ord >= ord('0') && $ord <= ord('9')))) {
        if ($readstate == "controldestination") {
          $readstate = "controldestinationparam";
        } else {
          $readstate = "controlwordparam";        
        }
        $controlparam = $char;
      } elseif ($char == "*" && substr($rtf, $pos + 1, 1) == "\\") {
        $pos++; // skip next char
        $readstate = "controldestination"; 
        $controlword = "";
      } elseif ($controlword == "" && $char == "'") {
        // \' is used for non-ascii characters, and the character is encoded in HEX.
        // THAT SUCKS, because for *all other* control words, the parameter is numeric,
        // and if you encounter an alphabetic character after a numeric character,
        // you are REQUIRED to act as if there was a *delimiter* before the alphabetic character. 
        // But not in this case!
        $hexsymbol = "";
        $nextchar = substr($rtf, $pos + 1, 1);     
          while ((ord($nextchar) >= ord('a') && ord($nextchar) <= ord('f')) 
               || (ord($nextchar) >= ord('A') && ord($nextchar) <= ord('F'))
               || (ord($nextchar) >= ord('0') && ord($nextchar) <= ord('9'))) {
          $hexsymbol .= $nextchar;
          $pos++;
          $nextchar = substr($rtf, $pos + 1, 1);       
        }
        $readstate = "plain";
        // assume the input is ISO-8859-1, just like our output should be
        if (hexdec($hexsymbol) > 32 && hexdec($hexsymbol) < 256) {
          $symbol = chr(hexdec($hexsymbol));
          if ($state["destination"] == "plain" || $state["destination"] == "do" || $state["destination"] == "footnote" || $state["destination"] == "info") $output .= $symbol;
        } else {
          $symbol = "";
        }
        // echo "HEXSYMBOL: $hexsymbol $symbol, surroundings: " . substr($rtf, $pos - 50, 100) . "<br/>";
      } elseif ($controlword == "") { 
        // backslash + a single non-alphabetic character is a control symbol, and does not need to be delimited
        $readstate = "plain";
        if ($char == "{" || $char == "}" || $char == "\\") {
          //$output .= $char;
          if ($state["destination"] == "plain" || $state["destination"] == "do" || $state["destination"] == "footnote" || $state["destination"] == "info") $output .= $char;
        } elseif ($char == "~") {
          // If case you ever want to produce HTML, \~ should is actually unbreakable space. 
          if ($state["destination"] == "plain" || $state["destination"] == "do" || $state["destination"] == "footnote" || $state["destination"] == "info") $output .= " ";
        } else {
          // In case I forgot something
          if ($state["destination"] == "plain" || $state["destination"] == "do" || $state["destination"] == "footnote" || $state["destination"] == "info") $output .= "SYMBOL($char)";
        }
      } else {
        if ($readstate == "controldestination" || $controlword == "footer" || $controlword == "fonttbl" || $controlword == "stylesheet" || $controlword == "colortbl" || $controlword == "pict" || $controlword == "info") {
          // Control words preceded by \*\ always trigger a destination change (and \*\ means:  ignore all content in this scope if you do not know this destination).
          // Additionally, some control words NOT preceded by \*\ trigger a destination change (footer, fonttbl) etc. All such destinations are in the RTF1.0 spec, any destinations invented later use \*\
          $state["destination"] = $controlword;
        }
        if ($controlword == "par") if ($state["destination"] == "plain" || $state["destination"] == "do" || $state["destination"] == "footnote" || $state["destination"] == "info") $output .= "\n";
        $readstate = "plain";
        if ($char != " ") $pos--; // go back (so that the character can be processed again)
        if ($state["destination"] == "info") {
          // The document's metadata has the destination "info". Because it is meaningful and human readable, we output it.
          // However, because it is metadata and not visible content, there is no whitespace included. 
          // But since every piece of metadata has a control word to indicate what kind of metadata it is (\author, \title etc.), 
          // if, for every control word we encounter, we output a space, all the metadata parts will be separated my whitespace.
          // (For some types of RTF-to-text conversion, you might want to include the info control words in the output to provide
          //  extra context. But for searching, that is useless, because the info control are identical for all documents.)
          $output .= " ";
        } 
      }
    } elseif ($readstate == "controlwordparam" || $readstate == "controldestinationparam") {
      $ord = ord($char);
      if ($ord >= ord('0') && $ord <= ord('9')) {
        $controlparam .= $char;
      } else {
        if ($readstate == "controldestinationparam" || $controlword == "footnote" || $controlword == "fonttbl" || $controlword == "stylesheet" || $controlword == "colortbl" || $controlword == "pict" || $controlword == "info") {
          $state["destination"] = $controlword;
        }      
        if ($controlword == "u") {
          $symbol = "&#". str_pad($controlparam, 5, "0", STR_PAD_LEFT) . ';';
          if ($state["destination"] == "plain" || $state["destination"] == "do" || $state["destination"] == "footnote" || $state["destination"] == "info") $output .= $symbol;
        }
        $readstate = "plain";
        if ($char != " ") $pos--; // go back (so that the character can be processed again)        
        // Dont do anything special for "info" controlwords that have parameters. If they have a parameter, they dont have any content. And the parameter is just a meaningless number like "time in seconds when the document was last saved" or "number of characters in this document".
      }
    }
    
  }

  return $output;
}

function SEARCH_CaseKeywordForFolder($folder_id) {
  $p = strpos ($folder_id, ")");
  if ($p) {
    $case_id = substr($folder_id, 1, $p-1); 
  } else {
    $case_id = "generic";
  }
  return 'xxx'.strtr(substr(md5($case_id), 0, 22), '0123456789x', 'ghijklmnopz').'xxx';
}

function SEARCH_TEXT2WORDS ($text)
{
  N_Debug ("SEARCH_TEXT2WORDS ($text)");

  $marker = "0947526043975403975"."985794385";

  $search = array (
                   "'[a-zA-Z0-9]{30}'ie",                                                      //NV long "word" killer to prevent preg from crashing
                   "'[&][#]([0-9]*)[;]'i",                                                     //NV indexing of Chinese, Korean, Japanese etc.
                   "'([_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)*)'ie",            //DB+NV indexing of emailadresses
                   "'((http:[/][/]|www.|ftp:[/][/])([a-z]|[A-Z]|[0-9]|[/.]|[~])*)'ie",         //DB indexing of url's
                   "'([1-9]{1}[0-9]{3}[a-zA-Z]{2})'ie",                                        //DB indexing of dutch postal codes
                   "'([^0-9])([1-9][0-9]?[0-9]?([.][0-9][0-9][0-9])+)([^0-9])'ie",             //DB indexing of numbers
                   "'([^0-9])([1-9][0-9]?[0-9]?([,][0-9][0-9][0-9])+)([^0-9])'ie",             //DB indexing of numbers
                   "'([.,][0-9]+)'ie",                                                         //DB indexing of numbers
                   "'([0-9]+[,.][0-9]+)'ie",                                                   //DB indexing of numbers
                   "'([0-9]+)'ie",                                                             //DB indexing of numbers  
                   "'[^a-zA-Z0-9_ ]'i",
                   "'[ ][ ][ ]*'i",                                                                
                   "'".$marker."([0-9]*)'i"                                                    //NV indexing of Chinese, Korean, Japanese etc.
                  );          
 
  $replace = array (
                    ' ',
                    $marker.'\\1'." ",
                    'SEARCH_FilterEmail ("\\1", "index")',
                    'SEARCH_FilterURL   ("\\1", "index")',
                    'SEARCH_FilterPostal("\\1", "index")',
                    '"\\1".SEARCH_FilterNumber("\\2", "dotbynothing", "index")."\\4"',
                    '"\\1".SEARCH_FilterNumber("\\2", "commabynothing", "index")."\\4"',
                    'SEARCH_FilterNumber("\\1", "dotandcommabynothing", "index")',
                    'SEARCH_FilterNumber("\\1", "dotandcommabyspace", "index")',
                    'SEARCH_FilterNumber("\\1", "addspaces", "index")',
                    " ",
                    " ",
                    "&#\\1;"
                   );
  return trim (preg_replace ($search, $replace, " ".strtolower(SEARCH_REMOVEACCENTS($text))." "));
}

//DB###

function SEARCH_TEXT2WORDSQUERY ($text) 
{
  N_Debug ("SEARCH_TEXT2WORDSQUERY ($text)");

  $marker = "0947526043975403975"."985794385";

  $search = array (
                   "'[&][#]([0-9]*)[;]'i",                                                     //NV indexing of Chinese, Korean, Japanese etc.
                   "'([_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)*)'ie",            //DB+NV indexing of emailadresses
                   "'((http:[/][/]|www.|ftp:[/][/])([a-z]|[A-Z]|[0-9]|[/.]|[~])*)'ie",         //DB indexing of url's
                   "'([1-9]{1}[0-9]{3}[a-zA-Z]{2})'ie",                                        //DB indexing of dutch postal codes
                   "'([^0-9])([1-9][0-9]?[0-9]?([.][0-9][0-9][0-9])+)([^0-9])'ie",             //DB indexing of numbers
                   "'([^0-9])([1-9][0-9]?[0-9]?([,][0-9][0-9][0-9])+)([^0-9])'ie",             //DB indexing of numbers
                   "'([.,][0-9]+)'ie",                                                         //DB indexing of numbers
                   "'([0-9]+[,.][0-9]+)'ie",                                                   //DB indexing of numbers
                   "'([0-9]+)'ie",                                                             //DB indexing of numbers  
                   "'[^a-zA-Z0-9_ ]'i",
                   "'[ ][ ][ ]*'i",                                                                
                   "'".$marker."([0-9]*)'i"                                                    //NV indexing of Chinese, Korean, Japanese etc.
                  );          
 
  $replace = array (
                    $marker.'\\1'." ",
                    "SEARCH_FilterEmail ('\\1','search')",
                    'SEARCH_FilterURL   ("\\1", "search")',
                    'SEARCH_FilterPostal("\\1", "search")',
                    '"\\1".SEARCH_FilterNumber("\\2", "dotbynothing", "search")."\\4"',
                    '"\\1".SEARCH_FilterNumber("\\2", "commabynothing", "search")."\\4"',
                    'SEARCH_FilterNumber("\\1", "dotandcommabynothing", "search")',
                    'SEARCH_FilterNumber("\\1", "dotandcommabyspace", "search")',
                    'SEARCH_FilterNumber("\\1", "addspaces", "search")',
                    " ",
                    " ",
                    "&#\\1;"
                   );

  return preg_replace ($search, $replace, strtolower(SEARCH_REMOVEACCENTS($text)));
}


//SBR
function SEARCH_TEXT2FILTERQUERY ($text)
{
  N_Debug ("SEARCH_TEXT2FILTERQUERY ($text)");

  $marker = "0947526043975403975"."985794385";

  $search = array (
                   "'[&][#]([0-9]*)[;]'i",                                                     //NV indexing of Chinese, Korean, Japanese etc.
                   "'([_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)*)'ie",            //DB+NV indexing of emailadresses
                   "'((http:[/][/]|www.|ftp:[/][/])([a-z]|[A-Z]|[0-9]|[/.]|[~])*)'ie",         //DB indexing of url's
                   "'([1-9]{1}[0-9]{3}[a-zA-Z]{2})'ie",                                        //DB indexing of dutch postal codes
                   "'[^a-zA-Z0-9_\. ]'i",                                                      //Allows filtering on text containing a .
                   "'[ ][ ][ ]*'i",
                   "'".$marker."([0-9]*)'i"                                                    //NV indexing of Chinese, Korean, Japanese etc.
                  );

  $replace = array (
                    $marker.'\\1'." ",
                    "SEARCH_FilterEmail ('\\1','search')",
                    'SEARCH_FilterURL   ("\\1", "search")',
                    'SEARCH_FilterPostal("\\1", "search")',
                    " ",
                    " ",
                    "&#\\1;"
                   );

  return preg_replace ($search, $replace, strtolower(SEARCH_REMOVEACCENTS($text)));
}


function SEARCH_TextToWordlist ($fulltext, $important="")
{
  N_Debug("SEARCH_TextToWordlist ($fulltext)");

  $tmplist = explode (" ", $fulltext);

  if(SEARCH_MIN_WORDLEN==1) {
    $result = array_count_values($tmplist);
  } else {
    reset($tmplist);
    while (list(,$tmp)=each($tmplist)) {
      if (strlen($tmp) >= SEARCH_MIN_WORDLEN) $result[$tmp]++;
    }
  }
  while (count ($result)>5000) {
    $result2 = array();
    foreach ($result as $word => $index) {
      if ($result[$word] > 1) {
        $result2[$word] = $result[$word]-1;        
      }
    }
    $result = $result2;
  }

  $keep = array_count_values(explode (" ", $important));
  foreach ($keep as $word => $index) {
    $result[$word] += $index;
  }

  return $result;
}

function SEARCH_Key2Name ($key)
{
  global $myconfig;
  N_Debug("SEARCH_Key2Name ($key)");

  if ($myconfig["ftengine"]=="S2_MYSQLFT" || $myconfig["ftengine"]=="S2_SPHINX" || $myconfig["ftengine"]=="S2_SPHINX2") return $key;

  if (!$key) return "_5f";


  $key= strtolower($key);
  $trans = array (
    " "=>"_20",
    "!"=>"_21",
    "\""=>"_22",
    "#"=>"_23",
    "$"=>"_24",
    "%"=>"_25",
    "&"=>"_26",
    "'"=>"_27",
    "("=>"_28",
    ")"=>"_29",
    "*"=>"_2a",
    "+"=>"_2b",
    ","=>"_2c",
    "-"=>"_2d",
    "."=>"_2e",
    "/"=>"_2f",
    ":"=>"_3a",
    ";"=>"_3b",
    "<"=>"_3c",
    "="=>"_3d",
    ">"=>"_3e",
    "?"=>"_3f",
    "@"=>"_40",
    "["=>"_5b",
    "\\"=>"_5c",
    "]"=>"_5d",
    "^"=>"_5e",
    "_"=>"_5f",
    "`"=>"_60",
    "{"=>"_7b",
    "|"=>"_7c",
    "}"=>"_7d",
    "~"=>"_7e");



  $key = strtr ($key, $trans);
  if (strlen ($key) > 180) $key = substr ($key, 0, 32);
  N_Debug($key);
  return $key;
}

function SEARCH_Hash ($key, $max) // 1..$max
{
  return 1 + (abs(N_Crc32($key)) % $max);
}


// ************************************************************************************************************************************************

// ********* INDEX MAINTENANCE FUNCTIONS (OBSOLETE, REPLACED BY TERRA FUNCTIONS)

function SEARCH_CreateDMSIndex ($sitecollection_id)
{
  $index = $sitecollection_id."#publisheddocuments";
  SEARCH_NukeIndex ($index, "please");
  $index = $sitecollection_id."#previewdocuments";
  SEARCH_NukeIndex ($index, "please");
  $docs = MB_Query ("ims_".$sitecollection_id."_objects", '
    $record["objecttype"]=="document" && 
    $record["published"]=="yes"
  ', '$record["shorttitle"]');
  foreach ($docs as $object_id => $name) {
    echo "Indexing $name (published, $object_id)<br>";
    N_Flush();
    SEARCH_Do_AddDocumentToDMSIndex ($sitecollection_id, $object_id);
  }
  $docs = MB_Query ("ims_".$sitecollection_id."_objects", '
    $record["objecttype"]=="document" && 
    ($record["preview"]=="yes" || $record["published"]=="yes")
  ', '$record["shorttitle"]');
  foreach ($docs as $object_id => $name) {
    echo "Indexing $name (preview, $object_id)<br>";
    N_Flush();
    SEARCH_Do_AddPreviewDocumentToDMSIndex ($sitecollection_id, $object_id);
  }
}

function SEARCH_CreateSiteIndex ($sitecollection_id, $site_id)
{
  $index = $sitecollection_id."#".$site_id;
  SEARCH_NukeIndex ($index, "please");
  $pages = MB_Query ("ims_".$sitecollection_id."_objects", '
    $record["objecttype"]=="webpage" && 
    $record["published"]=="yes" && 
    IMS_Object2Site ("'.$sitecollection_id.'", $key)=="'.$site_id.'"',
    '$record["parameters"]["published"]["shorttitle"]');
  foreach ($pages as $object_id => $name) {    
    N_Flush();
    echo "Indexing $name ($object_id)<br>";
    SEARCH_Do_AddPageToSiteIndex ($sitecollection_id, $site_id, $object_id);
  }
}

function SEARCH_Reindex($sitecollection="")
{
  if ($sitecollection) {
    $list[$sitecollection] = $sitecollection;
  } else {
    $list = MB_Query ("ims_sitecollections");  
  }
  reset($list);
  while (list($sitecollection_id)=each($list)) {
    echo "MAIL_Reindex ($sitecollection_id, ...);<br>";
    N_Flush();
    // multi archive
    global $myconfig; 
    if($myconfig["mail"]["multiarchive"]) { 
//      MAIL_Reindex ($sitecollection_id, "main"); // reindex old existing archive
      for($i=1;$i<=$myconfig["mail"]["accounts"];$i++) { 
        MAIL_Reindex ($sitecollection_id, $myconfig["mail"][$i]["archiveid"]); 
      } 
    } else { 
      MAIL_Reindex ($sitecollection_id, "main");
    }

    echo "BPMS_Reindex ($sitecollection_id);<br>";
    N_Flush();
    BPMS_Reindex ($sitecollection_id);

    echo "SEARCH_CreateDMSIndex ($sitecollection_id);<br>";
    N_Flush();
    SEARCH_CreateDMSIndex ($sitecollection_id);

    $sitelist = MB_Query ("ims_sites", '$record["sitecollection"]=="'.$sitecollection_id.'"');

    if (is_array ($sitelist)) reset($sitelist);
    if (is_array ($sitelist)) while (list ($site_id)=each($sitelist)) {
      echo "SEARCH_CreateSiteIndex ($sitecollection_id, $site_id);<br>";
      N_Flush();
      SEARCH_CreateSiteIndex ($sitecollection_id, $site_id);
    }
  }
  N_Log ("reindex", "SEARCH_Reindex ($sitecollection) COMPLETED ".N_CurrentServer ());
}


function SEARCH_SceduleReindex()
{
  N_QuickScedule ("nicohome", 'uuse("search");uuse("mail");SEARCH_Reindex();');
  N_QuickScedule ("rack132", 'uuse("search");uuse("mail");SEARCH_Reindex();');
  N_QuickScedule ("rack133", 'uuse("search");uuse("mail");SEARCH_Reindex();');
  N_QuickScedule ("rack107", 'uuse("search");uuse("mail");SEARCH_Reindex();');
}

function SEARCH_Test ()
{
  SEARCH_NukeIndex ("test", "please");
  for ($i=2; $i<=5; $i++)
  {
    $key = "http://www.tweakers.net/nieuws/$i";
    $content = N_FastGetPage ($key);
    SEARCH_AddHTMLToIndex ("test", $key, $content, "num$i");
  }
  SEARCH_AddHTMLToIndex ("test", "http://www.tweakers.net/nieuws/1", N_FastGetPage ("http://www.tweakers.net/nieuws/1"), "microsoft");
  SEARCH_AddHTMLToIndex ("test", "http://www.minfin.nl", N_FastGetPage ("http://www.minfin.nl"));
  SEARCH_AddHTMLToIndex ("test", "http://www.tweakers.net/nieuws/19", "oracle");
}

// ************************************************************************************************************************************************
// ********* FILTERS

function SEARCH_FilterURL($text, $mode)
{
  N_Debug("SEARCH_FilterURL($text, $mode)");
  switch ($mode) 
  {
     case "index":
        $replacement = strtr($text,":/@.&?=","       ");
        break;
     case "search":
        $replacement = strtr($text,":/@.&?=","       ");
        break;
  } 
  return $replacement;
}

function SEARCH_FilterEmail($text, $mode)
{

  N_Debug("SEARCH_FilterEmail($text, $mode)");
  switch ($mode) 
  {
     case "index":  
        $replacement = strtr($text,"@.","  ");
        break;
     case "search":
        $replacement = strtr($text,"@.","  ");
        break;
  } 
  return $replacement;
}

function SEARCH_FilterPostal($text, $mode)
{
  N_Debug("SEARCH_FilterPostal($text, $mode)");
  switch ($mode) 
  {
     case "index":  
        $replacement= substr($text,0,4) . " ". substr($text,4,2);
        break;
     case "search":
        $replacement= substr($text,0,4) . " ". substr($text,4,2);
        break;
  } 
  return $replacement;
}

function SEARCH_FilterNumber($text, $replacemode, $mode)
{
  N_Debug("SEARCH_FilterNumber($text, $replacemode, $mode)");

  switch ($replacemode)
  {
    case "dotbynothing":
        $text = str_replace(".","",$text);
        break;
    case "commabynothing":
        $text = str_replace(",","",$text);
        break;
    case "dotandcommabynothing":
        $text = str_replace(".","",$text);
        $text = str_replace(",","",$text);
        break;
    case "dotandcommabyspace":
        $text = str_replace("."," ",$text);
        $text = str_replace(","," ",$text);
        break;
    case "addspaces":
        break;
  }

  switch ($mode) 
  {
     case "index":  
        $replacement = " ".$text." ";
        break;
     case "search":
        $replacement = " ".$text." ";
        break;
  } 
  return $replacement;
}

// ************************************************************************************************************************************************
// ********* WORDLIST MANAGEMENT: ENGINE INTERFACE LAYER
//
// MYSQL: 182 secs DISK: 895 secs
//


function SEARCH_NukeIndex ($index, $please)
{
  global $myconfig;
  if ($myconfig["ftengine"] == "S2_MYSQLFT" || $myconfig["ftengine"] == "S2_SPHINX" || $myconfig["ftengine"]=="S2_SPHINX2") {
    S2_NukeIndex ($index, $please);
    return;
  }
  if ($myconfig["ftengine"] == "MYSQL") {
    SEARCH_MYSQL_NukeIndex ($index, $please);
  } else {
    SEARCH_DISK_NukeIndex ($index, $please);
  }
}

function SEARCH_AddDocumentToWordlistMulti ($index, $key, $wordlist)
{
  global $myconfig;
  if ($myconfig["ftengine"] == "MYSQL") { 
    SEARCH_MYSQL_AddDocumentToWordlistMulti ($index, $key, $wordlist);
  } else {
    foreach ($wordlist as $word => $rank) {
      SEARCH_AddDocumentToWordlist ($index, $key, $word, $rank);
    }
  }
}

function SEARCH_AddDocumentToWordlist ($index, $key, $word, $rank=1)
{
  global $myconfig;
  if ($myconfig["ftengine"] == "MYSQL") {
    SEARCH_MYSQL_AddDocumentToWordlist ($index, $key, $word, $rank);
  } else {
    SEARCH_DISK_AddDocumentToWordlist ($index, $key, $word, $rank);
  }
}

function SEARCH_RemoveDocumentFromWordlist ($index, $key, $word)
{
  global $myconfig;
  if ($myconfig["ftengine"] == "MYSQL") {
    SEARCH_MYSQL_RemoveDocumentFromWordlist ($index, $key, $word);
  } else {
    SEARCH_DISK_RemoveDocumentFromWordlist ($index, $key, $word);
  }
}

function SEARCH_GetDocumentsOnWordList ($index, $word)
{
  N_DEBUG ("SEARCH_GetDocumentsOnWordList($index, $word)");

  global $myconfig;
  if ($myconfig["ftengine"] == "MYSQL") {
    return SEARCH_MYSQL_GetDocumentsOnWordList ($index, $word);
  } else {
    return SEARCH_DISK_GetDocumentsOnWordList ($index, $word);
  }
}

// ************************************************************************************************************************************************
// ********* WORDLIST MANAGEMENT: DISK ENGINE

function SEARCH_DISK_NukeIndex ($index, $please)
{
  if ($please=="please") {
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(getenv("DOCUMENT_ROOT") . "/searchindex/$index/");
  }
}

function SEARCH_DISK_AddDocumentToWordlist ($index, $key, $word, $rank=1)
{
  N_SuperQuickAppendFile (getenv("DOCUMENT_ROOT")."/searchindex/$index/"."doculist2_".SEARCH_Key2Name ($word).".idxdat", "x".$key."x«".$rank."«");
}

function SEARCH_DISK_RemoveDocumentFromWordlist ($index, $key, $word)
{
  SEARCH_AddDocumentToWordlist ($index, $key, $word, "x");
}

function SEARCH_DISK_GetDocumentsOnWordList ($index, $word)
{  
  N_FlushAppendQueue ();
  $content = N_ReadFile (getenv("DOCUMENT_ROOT")."/searchindex/$index/"."doculist2_".SEARCH_Key2Name ($word).".idxdat");
  $e = explode ("«",$content);
  for ($i=0; $i<(count($e)-1)/2; $i++) {
    $rawkey = $e[$i*2];
    if ($rawkey) {
      $key = substr($e[$i*2],1,strlen($e[$i*2])-2);
      $rank = $e[$i*2+1];
      if ($rank=="x") {
        unset ($list[$key]);
      } else {
        $list[$key] = $rank;
      }
    }
  }
  return $list;
}

// ************************************************************************************************************************************************
// ********* WORDLIST MANAGEMENT: MYSQL ENGINE

function SEARCH_MYSQL_IndexCreate ($index)

{  
  global $search_mysql_indexcreate, $myconfig;
  MB_MYSQL_Connect ();
  if (!$search_mysql_indexcreate[$index]) {
    $search_mysql_indexcreate[$index]="yes";
    $statement = "
      (thekey varchar(128),
       theword varchar(128),
       therank INT,
       INDEX thekey_index (thekey),
       INDEX theword_index (theword),
       INDEX theword_therank_index (theword, therank),
       UNIQUE combined_index (thekey, theword))
    ";
    if ($myconfig["xmlmysql"]["tabletype"]) {
      MB_MYSQL_Query("CREATE TABLE ftindex_".MB_MYSQL_Key2Name_Remember($index)." $statement ENGINE=".$myconfig["xmlmysql"]["tabletype"].";", 1, $myconfig["ftsqlconnection"]);
    } else {
      MB_MYSQL_Query("CREATE TABLE ftindex_".MB_MYSQL_Key2Name_Remember($index)." $statement;", 1, $myconfig["ftsqlconnection"]);
    }
  }
}

function SEARCH_MYSQL_NukeIndex ($index, $please)
{
  global $myconfig;
  if ($please=="please") {
    MB_MYSQL_Connect ();
    MB_MYSQL_Query ("drop table ftindex_".MB_MYSQL_Key2Name($index).";", 1, $myconfig["ftsqlconnection"]);
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(getenv("DOCUMENT_ROOT") . "/searchindex/$index/");
  }
}

function SEARCH_MYSQL_AddDocumentToWordlistMulti ($index, $key, $wordlist)
{
  global $myconfig;
  if (is_array ($wordlist) && count($wordlist)) {
    SEARCH_MYSQL_IndexCreate ($index);
    MB_MYSQL_Query ("DELETE FROM ftindex_".MB_MYSQL_Key2Name($index)." WHERE thekey='".mysql_escape_string($key)."';", 0, $myconfig["ftsqlconnection"]);
    $sql .= "REPLACE INTO ftindex_".MB_MYSQL_Key2Name($index)."(thekey, theword, therank) VALUES ";
    foreach ($wordlist as $word => $rank) {
      $sql .= "('".mysql_escape_string($key)."', '".mysql_escape_string($word)."', $rank), ";
    }
    $sql = substr ($sql, 0, strlen($sql)-2); 
    $sql .= ";";
    MB_MYSQL_Query ($sql, 0, $myconfig["ftsqlconnection"]);
  }
}

function SEARCH_MYSQL_AddDocumentToWordlist ($index, $key, $word, $rank)
{
  global $myconfig;
  SEARCH_MYSQL_IndexCreate ($index);
//  MB_MYSQL_Query ("DELETE FROM ftindex_".MB_MYSQL_Key2Name($index)." WHERE thekey='".mysql_escape_string($key)."' AND theword='".mysql_escape_string($word)."';", 0, $myconfig["ftsqlconnection"]);
  MB_MYSQL_Query ("REPLACE INTO ftindex_".MB_MYSQL_Key2Name($index)."(thekey, theword, therank) VALUES ('".mysql_escape_string($key)."', '".mysql_escape_string($word)."', $rank);", 0, $myconfig["ftsqlconnection"]);
}

function SEARCH_MYSQL_RemoveDocumentFromWordlist ($index, $key, $word)
{
  global $myconfig;
  SEARCH_MYSQL_IndexCreate ($index);
  MB_MYSQL_Query ("DELETE FROM ftindex_".MB_MYSQL_Key2Name($index)." WHERE thekey='".mysql_escape_string($key)."' AND theword='".mysql_escape_string($word)."';", 0, $myconfig["ftsqlconnection"]);
}

function SEARCH_MYSQL_GetDocumentsOnWordList ($index, $word)
{  
  global $myconfig;
  SEARCH_MYSQL_IndexCreate ($index);
  $result = MB_MYSQL_Query ("select thekey, therank FROM ftindex_".MB_MYSQL_Key2Name($index)." WHERE theword = '".mysql_escape_string($word)."';", 0, $myconfig["ftsqlconnection"]);
  $list = array();
  while ($row = mysql_fetch_row ($result)) {
    $list[$row[0]] = $row[1];
  }
  return $list;
}

// ************************************************************************************************************************************************
// ********* OTHER FUNCTIONS

// get workflow for given object (with $key) and look if workflow allows indexing
function SEARCH_WorkflowAllowsIndex($sitecollection_id, $key) { // THB INDEX WFL
  if(!$sitecollection_id) {
      $ret=false;
  } else {
    $obj = MB_Ref("ims_" . $sitecollection_id . "_objects", $key);
    $wfl = $obj["workflow"];
    $wflobj = MB_Ref("shield_" . $sitecollection_id . "_workflows",$wfl);
    if(!$wflobj ["noindex"]) {
      return true;
    } else {
      return false;
    }
  }
}

function SEARCH_Mail($sitecollection_id, $query, $from=1, $to=999999999, $archiveselector, $usepermissions=true) { 
// $archiveselector can be "all" for all archives, or the archiveid of the archive to be searched
  global $myconfig; 
  if($myconfig["mail"]["multiarchive"]) {
    for($i=1;$i<=$myconfig["mail"]["accounts"];$i++) {
      if(($archiveselector==$myconfig["mail"][$i]["archiveid"]) || ($archiveselector=="all")) {
        if(!$usepermissions || ($usepermissions && (SHIELD_HasEMSArchiveRight($sitecollection_id, $myconfig["mail"][$i]["archiveid"], "read")))) {
          $archive = $myconfig["mail"][$i]["archiveid"];
          if($archive == "main") {
            $result1 = SEARCH ("mail_".$sitecollection_id."_$archive", $query, $from, $to);

          } else {
            $result1 = SEARCH ("mail_".$sitecollection_id."_" . $myconfig["mail"][$i]["archiveid"], $query, $from, $to);
          }

          foreach($result1["result"] as $temp => $resarray) { 
            $result1["result"][$temp]["archiveid"]=$myconfig["mail"][$i]["archiveid"];
            $result1["result"][$temp]["archivename"]=$myconfig["mail"][$i]["archivename"];
          }

          $result["result"] = N_array_merge($result["result"], $result1["result"]);
          $result["amount"] += $result1["amount"];
          $result["ignore"] = N_array_merge($result["ignore"], $result1["ignore"]);
        }
      }
    }
  } else {
    $archive = $archiveselector;
    $result = SEARCH ("mail_".$sitecollection_id."_".$archive, $query, $from, $to);
  }
  return $result;
} 

?>