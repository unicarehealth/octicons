# GitHub Octicons - port for PHP (including ZF3 view helper)

[![npm version](https://img.shields.io/npm/v/octicons.svg)](https://www.npmjs.org/package/octicons)
[![Build Status](https://travis-ci.org/primer/octicons.svg?branch=master)](https://travis-ci.org/primer/octicons)

> SVG icon management.

## Usage

PHP:
You can use this by instantiating an instance of the icon manager and rendering icon markup as follows:

//Get icon manager:
$iconManager = new Uch\Wac\Vis\IconManager();
//e.g. 1: Write out 'gear' icon markup:
echo $iconManager->gear->toSVG();
//e.g. 2: Write out 'law' icon markup :
echo $iconManager->law->toSVG(['width' => 32, 'title' => 'Weigh up this option', 'class' => 'custom-css-class']);

i.e. $iconManager->{name}->toSVG(options);


ZF3:
1) Add the view helper reference to your module config:

e.g. 'view_helpers' => [
		'invokables' => [
				\Uch\Wac\Vis\Zf\IconViewHelper::class => \Uch\Wac\Vis\Zf\IconViewHelper::class
			],
			'aliases' => [
				'icons' => \Uch\Wac\Vis\Zf\IconViewHelper::class
			]
		];
			
2) include the following in your layout.phtml (before writing headStyle to the page):

$this->headStyle()->prependStyle($this->icons()->getCss());

3) use as follows within any of your .phtml files:

echo $this->icons()->{name}->toSVG(options);

e.g. 1: echo $this->icons()->search->toSVG(['width' => 32]);
e.g. 2: echo $this->icons()->search->toSVG(['width' => 32, 'id' => 'someButton', 'class' => 'float-right']);



