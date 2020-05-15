<?php declare(strict_types=1);

namespace Uch\Wac\Vis;

use Laminas\View\Helper\AbstractHelper;

class ZfIconViewHelper extends AbstractHelper
{
	private $_iconManager = null;

	/** Constructor. */
	public function __construct()
	{
		$this->_iconManager = new IconManager();
	}

	/** Magic method used when instance is called as a function */
	public function __invoke() : IconManager
	{
		return $this->_iconManager;
	}
}