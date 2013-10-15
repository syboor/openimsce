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



// 
  // Available variables are: $sitecollection_id, $site_id, $object_id, $template_id, $command and $siteinfo
  //

  if ($command=="meta") { // tell OpenIMS everything about this template

    // $extra = '<tr><td><font face="arial" size=2><b>Tem:</b></font></td><td>[[[tem]]]</td></tr>' . $extra;

    $metaspec = COLMIX_Metaspec();
    $formtemplate = COLMIX_Formtemplate($extra);
    $formdimensions = COLMIX_FormDimensions (10);

    // $metaspec["fields"]["tem"]["type"] = "string";

    $output["metaspec"] = $metaspec;
    $output["formtemplate"] = $formtemplate;
    $output["formdimensions"] = $formdimensions;

  } else if ($command=="new_object") { // generate data for new object
    
    $output = COLMIX_Default();

  } else if ($command=="generate_dynamic_page") { // generate dynamic page content (html format)

    $content = COLMIX_GetRawTemplate();
    $content = COLMIX_ProcessContentElements ($content);

    $verticalmenu .= IMS_Sitemap ($sitecollection_id, $site_id, $object_id);
    $content = str_replace ("[[[verticalmenu]]]", $verticalmenu, $content);

    $clickpath = IMS_CLickPath ($sitecollection_id, $site_id, $object_id, 'face="arial,helvetica" size="2"');
    $content = str_replace ("[[[clickpath]]]", $clickpath, $content);

    $content = COLMIX_InsertContent ($content);
    $output = $content;

  } else if ($command=="generate_static_page") { // generate static page (php file)

    $output = COLMIX_GenerateStaticPage();

  }
?>