<?php declare(strict_types=1);

namespace Uch\Wac\Vis\Zf;

use Zend\View\Helper\AbstractHelper;
use Uch\Wac\Vis\IconManager;

class IconViewHelper extends AbstractHelper
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