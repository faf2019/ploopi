<?php
/*
	Copyright (c) 2002-2007 Netlor
	Copyright (c) 2007-2008 Ovensia
	Contributors hold Copyright (c) to their code submissions.

	This file is part of Ploopi.

	Ploopi is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	Ploopi is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Ploopi; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?

function ploopi_resizeimage($imagefile, $coef = 0, $wmax = 0, $hmax = 0, $format = '', $nbcolor = 0, $filename = '')
{
  //$c = new ploopi_cache($imagefile, 8640000, _PLOOPI_PATHDATA._PLOOPI_SEP.'cache'._PLOOPI_SEP);
  //if (!$c->start())
  //{
  
  $filename_array = explode('.',$imagefile);
  $extension = strtolower($filename_array[sizeof($filename_array)-1]);

  switch($extension)
  {
    case 'jpg':
    case 'jpeg':
      $imgsrc = ImageCreateFromJPEG($imagefile);
    break;

    case 'png':
      $imgsrc = ImageCreateFromPng($imagefile);
    break;

    case 'gif':
      $imgsrc = imagecreatefromgif($imagefile);
    break;

    default:
      return(0);
    break;
  }

  $w = imagesx($imgsrc);
  $h = imagesy($imgsrc);

  if (!$coef) // no coef defined
  {
    if ($wmax) $coef = $w/$wmax;
    if ($hmax && $h/$hmax > $coef) $coef = $h/$hmax;
  }

  $wdest = $w/$coef;
  $hdest = $h/$coef;

  $imgdest = imagecreatetruecolor ($wdest, $hdest);

  imagecopyresampled($imgdest, $imgsrc, 0, 0, 0, 0, $wdest, $hdest, $w, $h);

  if ($nbcolor) imagetruecolortopalette($imgdest, true, $nbcolor);

  if($format != '')
  {
  	$extension = $format;
	$imagefile = substr($imagefile,0,strlen($imagefile) - strlen(ploopi_file_getextension($imagefile)) + 1);
  }

  if($filename == '')
  {
	header("Content-Type: image/$extension");
	header("Content-Disposition: inline; filename=$imagefile");

	switch($extension)
	{
		case 'jpg':
		case 'jpeg':
		imagejpeg($imgdest);
		break;

		case 'png':
		imagepng($imgdest);
		break;

		case 'gif':
		imagepng($imgdest);
		break;

		default:
		return(0);
		break;
	}
  }
  else
  {
	switch($extension)
	{
		case 'jpg':
		case 'jpeg':
		imagejpeg($imgdest,$filename);
		break;

		case 'png':
		imagepng($imgdest,$filename);
		break;

		case 'gif':
		imagepng($imgdest,$filename);
		break;

		default:
		return(0);
		break;
	}
  }

  //$c->end();
  //}

return(1);
}


?>
