<?php
/**
 * @package    DPFields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!key_exists('field', $displayData))
{
	return;
}

$field = $displayData['field'];
$value = $field->value;
if (!$value)
{
	return;
}

// Loading the language
JFactory::getLanguage()->load('com_dpfields', JPATH_ADMINISTRATOR . '/components/com_dpfields');

JHtml::_('jquery.framework');

$doc = JFactory::getDocument();

// Adding the javascript gallery library
$doc->addScript('media/com_dpfields/js/fotorama.min.js');
$doc->addStyleSheet('media/com_dpfields/css/fotorama.min.css');

$value = (array)$value;

$thumbWidth = $field->fieldparams->get('thumbnail_width', '64');
$maxImageWidth = $field->fieldparams->get('max_width', 0);
$maxImageHeight = $field->fieldparams->get('max_height', 0);

// Main container
$buffer = '<div class="fotorama" data-nav="thumbs" data-width="100%" ' . ($maxImageHeight ? 'data-height="' . $maxImageHeight . '"' : '') . '>';

foreach ($value as $path)
{
	// Only process valid paths
	if (!$path)
	{
		continue;
	}

	// The root folder
	$root = $field->fieldparams->get('directory', 'images');
	foreach (JFolder::files(JPATH_ROOT . '/' . $root . '/' . $path, '.', $field->fieldparams->get('recursive', '1'), true) as $file)
	{
		// Skip none image files
		if (!in_array(strtolower(JFile::getExt($file)), array(
				'jpg',
				'png',
				'bmp',
				'gif'
		)))
		{
			continue;
		}

		// Getting the properties of the image
		$properties = JImage::getImageFileProperties($file);

		// Relative path
		$localPath = str_replace(JPATH_ROOT . '/' . $root . '/', '', $file);
		$webImagePath = $root . '/' . $localPath;

		if (($maxImageWidth && $properties->width > $maxImageWidth) || ($maxImageHeight && $properties->height > $maxImageHeight))
		{
			$resizeWidth = $maxImageWidth ? $maxImageWidth : '';
			$resizeHeight = $maxImageHeight ? $maxImageHeight : '';
			if ($resizeWidth && $resizeHeight)
			{
				$resizeWidth .= 'x';
			}
			$resize = JPATH_CACHE . '/com_dpfields/gallery/' . $field->id . '/' . $resizeWidth . $resizeHeight . '/' . $localPath;
			if (!JFile::exists($resize))
			{
				// Creating the folder structure for the max sized image
				if (!JFolder::exists(dirname($resize)))
				{
					JFolder::create(dirname($resize));
				}
				try
				{
					// Creating the max sized image for the image
					$imgObject = new JImage($file);

					$imgObject = $imgObject->resize($properties->width > $maxImageWidth ? $maxImageWidth : 0,
							$properties->height > $maxImageHeight ? $maxImageHeight : 0, true, JImage::SCALE_INSIDE);

					$imgObject->toFile($resize);
				}
				catch (Exception $e)
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_DPFIELDS_TYPE_GALLERY_IMAGE_ERROR', $file, $e->getMessage()));
				}
			}
			if (JFile::exists($resize))
			{
				$webImagePath = JUri::base(true) . str_replace(JPATH_ROOT, '', $resize);
			}
		}

		// Thumbnail path for the image
		$thumb = JPATH_CACHE . '/com_dpfields/gallery/' . $field->id . '/' . $thumbWidth . '/' . $localPath;

		if (!JFile::exists($thumb))
		{
			try
			{
				// Creating the folder structure for the thumbnail
				if (!JFolder::exists(dirname($thumb)))
				{
					JFolder::create(dirname($thumb));
				}

				// Getting the properties of the image
				$properties = JImage::getImageFileProperties($file);
				if ($properties->width > $thumbWidth)
				{
					// Creating the thumbnail for the image
					$imgObject = new JImage($file);
					$imgObject->resize($thumbWidth, 0, false, JImage::SCALE_INSIDE);
					$imgObject->toFile($thumb);
				}
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_DPFIELDS_TYPE_GALLERY_IMAGE_ERROR', $file, $e->getMessage()));
			}
		}

		if (JFile::exists($thumb))
		{
			// Linking to the real image and loading only the thumbnail
			$buffer .= '<a href="' . $webImagePath . '"><img src="' . JUri::base(true) . str_replace(JPATH_ROOT, '', $thumb) . '" /></a>';
		}
		else
		{
			// Thumbnail doesn't exist, loading the full image
			$buffer .= '<img src="' . $webImagePath . '"/>';
		}
	}
}
$buffer .= '</div>';

echo $buffer;
