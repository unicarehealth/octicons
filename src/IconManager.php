<?php declare(strict_types=1);
/**
 * 
 */

namespace Uch\Wac\Vis;

use Exception;
use Throwable;

class IconManager
{
	private _metadataLoaded = false;
	private _metadata = [];
	
	private _cssLoaded = false;
	private _css = '';
		
	function __construct()
	{
		
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
				$fileTextVal = file_get_contents('/../lib/octicons.css');
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
				$fileTextVal = file_get_contents('/../lib/data.json');
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