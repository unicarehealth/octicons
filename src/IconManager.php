<?php declare(strict_types=1);
/**
 * Port of primer/octicons for PHP.
 */

namespace Uch\Wac\Vis;

use stdClass;
use SimpleXMLElement;
use Exception;
use Throwable;

class IconManager
{
	private $_nsPrefix = 's';
	private $_nsUri = 'http://www.w3.org/2000/svg';

	private $_metadataLoaded = false;
	private $_metadata = [];

	private $_cssLoaded = false;
	private $_css = '';

	function __construct() {}

	/**
	 * Gets SVG element markup.
	 * @throws Exception if $name is invalid
	 */
	public function toSVG(string $name, array $options = []) : string
	{
		$pathElement = $this->getSvgElement($name)->xpath('//' . $this->_nsPrefix . ':path')[0];

		$xmlString = $pathElement->asXML();
		$xmlString = preg_replace('/^.+\n/', '', $xmlString);	//Remove XML declaration line

		return '<svg ' . $this->getHtmlAttributes($name, $options) . '>' . $xmlString . '</svg>';
	}

	/**
	 * Gets SVG element markup, assuming use of the SVG sprite.
	 * Note that the SVG sprite-sheet would need to be copied from the NPM build version.
	 * @throws Exception if $name is invalid
	 */
	public function toSVGUse(string $name, array $options = []) : string
	{
		return '<svg ' . $this->getHtmlAttributes($name, $options) . '><use xlink:href="#' . $name . '"/></svg>';
	}

	/**
	 * Gets HTML element attributes as a string.
	 * @throws Exception if $name is invalid
	 */
	public function getHtmlAttributes(string $name, array $options = []) : string
	{
		$svgElement = $this->getSvgElement($name);
		$iconMetadata = $this->getMetadata()->{$name};

		if (!property_exists($iconMetadata, 'options'))
		{
			//Add default html element attributes and cache with metadata:
			$iconMetadata->options = [
										'version' => '1.1',
										'width' => $svgElement['width'],
										'height' => $svgElement['height'],
										'viewBox' => (string)$svgElement['viewBox'],
										'class' => "octicon octicon-" . $name,
										'aria-hidden' => 'true'
									];
		}

		//Merging options may lose important defaults, so these are fixed below:
		$htmlAttributes = array_merge($iconMetadata->options, $options);

		//Parse options:
		if (isset($options['class']) && gettype($options['class']) == 'string')
		{
			$htmlAttributes['class'] = $iconMetadata->options['class'] . rtrim(' ' . $options['class']);
		}

		$widthSet = isset($options['width']) && gettype($options['width']) == 'integer' && $options['width'] > 0;
		$heightSet = isset($options['height']) && gettype($options['height']) == 'integer' && $options['height'] > 0;
		if ($widthSet || $heightSet)
		{
			//intval() parses the number until 'px':
			$spriteWidth = (float)intval($iconMetadata->options['width']);
			$spriteHeight = (float)intval($iconMetadata->options['height']);

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

	/**
	 * Gets the SVG element.
	 * Loads and caches it for repeated use.
	 * @throws Exception if $name is invalid
	 */
	public function getSvgElement(string $name) : SimpleXMLElement
	{
		$metadata = $this->getMetadata();

		if (!property_exists($metadata, $name))
		{
			throw new Exception('Invalid icon name: ' . $name);
		}

		$iconMetadata = $metadata->{$name};
		if (!property_exists($iconMetadata, 'svgElement'))
		{
			//Load the XML file data and cache with metadata:
			$iconMetadata->svgElement = simplexml_load_file(__DIR__ . '/../lib/svg/' . $name . '.svg');	//stdClass simply allows a new property to be set
			$iconMetadata->svgElement->registerXPathNamespace($this->_nsPrefix, $this->_nsUri);
			$iconMetadata->svgElement->addAttribute('symbol', $name);
		}

		return $iconMetadata->svgElement;
	}

	/**
	 * Gets required CSS as a string to be written in the page.
	 * @param bool $wrapInStyleElement Optional parameter allowing the css to be wrapped in an HTML style element.
	 * @throws Exception on error
	 */
	public function getCss(bool $wrapInStyleElement = false) : string
	{
		if (!$this->_cssLoaded)
		{
			try
			{
				$fileTextVal = file_get_contents(__DIR__ . '/../lib/octicons.css');
				if ($fileTextVal === false)
				{
					throw new Exception('Unknown reason');
				}

				$this->_css = $fileTextVal;
				$this->_cssLoaded = true;
			}
			catch (Throwable $e)
			{
				throw new Exception('Unable to read CSS file: ' . $e->getMessage());
			}
		}

		$ml = ($wrapInStyleElement) ? '<style type="text/css">' . $this->_css . '</style>' : $this->_css;

		return $ml;
	}

	/**
	 * Gets required metadata as a stdClass with a property for each icon.
	 * @throws Exception on error
	 */
	public function getMetadata() : stdClass
	{
		if (!$this->_metadataLoaded)
		{
			try
			{
				$fileTextVal = file_get_contents(__DIR__ . '/../lib/data.json');
				if ($fileTextVal === false)
				{
					throw new Exception('Unknown reason');
				}

				$jd = @json_decode($fileTextVal, false);
				if (json_last_error() !== JSON_ERROR_NONE || $jd === null)
				{
					throw new Exception('Json file is malformed');
				}

				$this->_metadata = $jd;
				$this->_metadataLoaded = true;
			}
			catch (Throwable $e)
			{
				throw new Exception('Unable to read metadata file: ' . $e->getMessage());
			}
		}

		return $this->_metadata;
	}
}