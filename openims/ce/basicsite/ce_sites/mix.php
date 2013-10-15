<?
/************************************ COPYRIGHT ************************************
 * OpenIMS CE                                                                      *
 * Copyright (c) 2001-2010 OpenSesame ICT.                                         *
 * De licentievoorwaarden voor deze software kunt u vinden in /openims/license.txt *
 * of op http://www.openims.com/openims/license.txt                                *
 *                                                                                 *
 ************************************ COPYRIGHT ************************************/

// collection mix.php

function COLMIX_Metaspec()
{
  return IMSMIX_Metaspec();
}

function COLMIX_Formtemplate($extra="") 
{
  return IMSMIX_Formtemplate ($extra);
}
 
function COLMIX_FormDimensions ($extralines=0, $width=0)
{
  return IMSMIX_FormDimensions ($extralines, $width);
}

function COLMIX_Default()
{
  return IMSMIX_Default();
}

function COLMIX_GetRawTemplate()
{
  return IMSMIX_GetRawTemplate();
}

function COLMIX_ProcessContentElements ($content)
{
  return IMSMIX_ProcessContentElements ($content);
}

function COLMIX_InsertContent ($content)
{
  return IMSMIX_InsertContent ($content);
}

function COLMIX_GenerateStaticPage()
{
  return IMSMIX_GenerateStaticPage();
}

?>