<?php declare(strict_types=1);
/**
 * 
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
		
	function __construct()
	{
		
	}
	
	/**
	 * @throws Exception if $name is invalid
	 */
	public function getMarkup(string $name, array $options = []) : string
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
		}
								
		$svgElement = $iconMetadata->svgElement;
				
		//Parse options:
		if (isset($options['class']) && gettype($options['class']) == 'string')
		{
			$svgElement->addAttribute('class', $options['class']);
		}
		
		if (isset($options['title']) && gettype($options['title']) == 'string')
		{
			$svgElement->addAttribute('title', $options['title']);
		}
				
		if (isset($options['width']) && gettype($options['width']) == 'integer' && $options['width'] > 0)
		{
			$svgElement['width'] = (string)$options['width'] . 'px';
		}
		
		if (isset($options['height']) && gettype($options['height']) == 'integer' && $options['height'] > 0)
		{
			$svgElement['height'] = (string)$options['height'] . 'px';
		}
				
		$xmlString = $svgElement->asXML();
		$xmlString = preg_replace('/^.+\n/', '', $xmlString);	//Remove XML declaration line
		
		return $xmlString;
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