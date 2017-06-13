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
	private $_metadataLoaded = false;
	private $_metadata = [];

	private $_cssLoaded = false;
	private $_css = '';

	function __construct() {}

	/**
	 * Overload/magic method to allow named icons to be requested as a property.
	 * @throws Exception if $name is invalid
	 */
	public function __get(string $iconName) : Icon
	{
		return $this->getIcon($iconName);
	}

	/**
	 * Gets the named Icon instance.
	 * @throws Exception if $name is invalid
	 */
	public function getIcon(string $name) : Icon
	{
		$element = $this->getSVGElement($name);
		$iconMetadata = $this->getMetadata()->{$name};
		return new Icon($element, $iconMetadata);
	}

	/**
	 * Gets SVG element markup.
	 * @throws Exception if $name is invalid
	 */
	public function toSVG(string $name, array $options = []) : string
	{
		return $this->getIcon($name)->toSVG($options);
	}

	/**
	 * Gets SVG element markup, assuming use of the SVG sprite.
	 * Note that the SVG sprite-sheet would need to be copied from the NPM build version.
	 * @throws Exception if $name is invalid
	 */
	public function toSVGUse(string $name, array $options = []) : string
	{
		$icon = $this->getIcon($name);
		return $icon->toSVGUse($options);
	}

	/**
	 * Gets markup for a hidden sprite-sheet for icons that have already been loaded via toSVG() or toSVGUse().
	 * We could know which icons have been used if the only way to get them was toSVGUse() in this class, but
	 * the magic method allows icons to be passed back individually, and then we don't know how they were used
	 * (inline or use).
	 *
	 * E.g.
	 * <svg xmlns="http://www.w3.org/2000/svg" style="width:0;height:0;visibility:hidden;">
	 *		<symbol viewBox="0 0 16 16" id="alert">
	 *			<path fill-rule="evenodd" d="M8.865 ..."/>
	 *		</symbol>
	 *		<symbol viewBox="0 0 10 16" id="arrow-down">
	 *			<path fill-rule="evenodd" d="M7 ..."/>
	 *		</symbol>
	 *	</svg>
	 */
	public function getSVGSpritesheet() : string
	{
		/*

		*/

		$ml = '';
		$metadata = $this->getMetadata();
		foreach (get_object_vars($metadata) as $name => $iconMetadata)
		{
			//$iconMetadata = $metadata->{$name};
			//if (!property_exists($iconMetadata, 'svgElement')) continue;

			if (!property_exists($iconMetadata, 'svgElement')) continue;

			$ml .= $this->getIcon($name)->toSVGSymbol();
		}

		// $ml = '';
		// foreach ($this->_usedNames as $name)
		// {
			// $ml .= $this->getIcon($name)->toSVGSymbol();
		// }

		if ($ml != '')
		{
			$ml = '<svg style="width:0;height:0;visibility:hidden;">' . $ml . '</svg>';
		}

		return $ml;
	}

	/**
	 * Gets the SVG element.
	 * Loads and caches it for repeated use.
	 * @throws Exception if $name is invalid
	 */
	public function getSVGElement(string $name) : SimpleXMLElement
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
			$iconMetadata->svgElement->addAttribute('symbol', $name);
		}

		return $iconMetadata->svgElement;
	}

	/**
	 * Gets required CSS as a string to be written in the page.
	 * @param bool $wrapInStyleElement Optional parameter allowing the css to be wrapped in an HTML style element.
	 * @throws Exception on error
	 */
	public function getCSS(bool $wrapInStyleElement = false) : string
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
	protected function getMetadata() : stdClass
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