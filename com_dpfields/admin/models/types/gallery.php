<?php
/**
 * @package    DPFields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpfields.models.types.base', JPATH_ADMINISTRATOR);

class DPFieldsTypeGallery extends DPFieldsTypeBase
{

	protected function postProcessDomNode ($field, DOMElement $fieldNode, JForm $form)
	{
		$fieldNode->setAttribute('type', 'gallery');
		$fieldNode->setAttribute('hide_default', 'true');

		return parent::postProcessDomNode($field, $fieldNode, $form);
	}
}
