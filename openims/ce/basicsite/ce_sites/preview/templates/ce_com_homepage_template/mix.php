<?
/************************************ COPYRIGHT ************************************
 * OpenIMS CE                                                                      *
 * Copyright (c) 2001-2010 OpenSesame ICT.                                         *
 * De licentievoorwaarden voor deze software kunt u vinden in /openims/license.txt *
 * of op http://www.openims.com/openims/license.txt                                *
 *                                                                                 *
 ************************************ COPYRIGHT ************************************/

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