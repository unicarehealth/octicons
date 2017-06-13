<?php declare(strict_types=1);
/**
 * Individual renderable icon.
 * Component of primer/octicons port for PHP.
 */

namespace Uch\Wac\Vis;

use SimpleXMLElement;
use stdClass;

class Icon
{
	private $_nsPrefix = 's';
	private $_nsUri = 'http://www.w3.org/2000/svg';

	private $_options = [];
	private $_element = null;

	function __construct(SimpleXMLElement $element, stdClass $metadata)
	{
		$this->_element = $element;

		if (!property_exists($metadata, 'options'))
		{
			//Add default html element attributes and cache with metadata:
			$metadata->options = [
									'version' => '1.1',
									'width' => $this->_element['width'],
									'height' => $this->_element['height'],
									'viewBox' => (string)$this->_element['viewBox'],
									'class' => "octicon octicon-" . $this->_element['symbol'],
									'aria-hidden' => 'true'
								];
		}
		$this->_options = $metadata->options;
	}

	/**
	 * Gets SVG element markup.
	 */
	public function toSVG(array $options = []) : string
	{
		$pathElement = $this->_element->xpath('//' . $this->_nsPrefix . ':path')[0];

		$xmlString = $pathElement->asXML();
		$xmlString = preg_replace('/^.+\n/', '', $xmlString);	//Remove XML declaration line

		return '<svg ' . $this->getHtmlAttributes($options) . '>' . $xmlString . '</svg>';
	}

	/**
	 * Gets SVG element markup, assuming use of the SVG sprite.
	 * Note that the SVG sprite-sheet would need to be copied from the NPM build version.
	 */
	public function toSVGUse(array $options = []) : string
	{
		return '<svg ' . $this->getHtmlAttributes($options) . '><use xlink:href="#' . $this->_element['symbol'] . '"/></svg>';
	}
	
	/**
	 * Gets element markup for an SVG symbol to be used in a spritesheet.
	 * E.g. 		
	 *	<symbol viewBox="0 0 16 16" id="alert">
	 *		<path fill-rule="evenodd" d="M8.865 ..."/>
	 *	</symbol>
	 */
	public function toSVGSymbol() : string
	{			
		$pathElement = $this->_element->xpath('//' . $this->_nsPrefix . ':path')[0];

		$xmlString = $pathElement->asXML();
		$xmlString = preg_replace('/^.+\n/', '', $xmlString);	//Remove XML declaration line

		return '<symbol viewBox="' . $this->_options['viewBox'] . '" id="' . $this->_element['symbol'] . '">' . $xmlString . '</symbol>';
	}
	
	/**
	 * Gets HTML element attributes as a string.
	 */
	protected function getHtmlAttributes(array $options = []) : string
	{
		//Merging options may lose important defaults, so these are fixed below:
		$htmlAttributes = array_merge($this->_options, $options);

		//Parse options:
		if (isset($options['class']) && gettype($options['class']) == 'string')
		{
			$htmlAttributes['class'] = $this->_options['class'] . rtrim(' ' . $options['class']);
		}

		$widthSet = isset($options['width']) && gettype($options['width']) == 'integer' && $options['width'] > 0;
		$heightSet = isset($options['height']) && gettype($options['height']) == 'integer' && $options['height'] > 0;
		if ($widthSet || $heightSet)
		{
			//intval() parses the number until 'px':
			$spriteWidth = (float)intval($this->_options['width']);
			$spriteHeight = (float)intval($this->_options['height']);

			$newWidth = ($widthSet) ? $options["width"] : intval($options["height"] * $spriteWidth / $spriteHeight);
			$newHeight = ($heightSet) ? $options["height"] : intval($options["width"] * $spriteHeight / $spriteWidth);

			$htmlAttributes['width'] = sprintf('%dpx', $newWidth);
			$htmlAttributes['height'] = sprintf('%dpx', $newHeight);
		}

		if (isset($options['aria-label']) && gettype($options['aria-label']) == 'string')
		{
			$htmlAttributes['role'] = 'img';
			unset($htmlAttributes['aria-hidden']); //un-hide the icon
		}

		$cleanHtmlAttributes = [];
		foreach($htmlAttributes as $key => $value)
		{
			if (gettype($value) != 'string') continue;
			$cleanHtmlAttributes[] = $key .'="'. htmlspecialchars($value) .'"';
		}

		return implode(' ', $cleanHtmlAttributes);
	}
}