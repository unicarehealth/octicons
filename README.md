# Octicons: Port for PHP (including ZF3 view helper)

> SVG icon management.

## Usage

#### PHP
Instantiate an icon manager and use it to render icon markup.

```
//Get an icon manager:
$iconManager = new Uch\Wac\Vis\IconManager();
//e.g. 1: Write out 'gear' icon markup:
echo $iconManager->gear->toSVG();
//e.g. 2: Write out 'law' icon markup :
echo $iconManager->law->toSVG(['width' => 32, 'title' => 'Weigh up this option', 'class' => 'custom-css-class']);
```

i.e. $iconManager->{icon-name}->toSVG(options);

To include the very small CSS in your page, add it to a style element (or $iconManager->getCss(true) includes a style wrapper:
```
<style type="text/css">
echo $iconManager->getCss();
</style>
```

#### ZF3 (Zend Framework)
1) Add the view helper reference to your module config (aliased here as 'icons'):
```
'view_helpers' => [
		'invokables' => [
				\Uch\Wac\Vis\Zf\IconViewHelper::class => \Uch\Wac\Vis\Zf\IconViewHelper::class
			],
			'aliases' => [
				'icons' => \Uch\Wac\Vis\Zf\IconViewHelper::class
			]
		];
```

2) Include the following in your layout.phtml (before writing headStyle to the page):
```
$this->headStyle()->prependStyle($this->icons()->getCss());
```

3) Use as follows within any of your .phtml files:
echo $this->icons()->{name}->toSVG(options);

```
// e.g. 1
echo $this->icons()->search->toSVG(['width' => 32]);
// e.g. 2
echo $this->icons()->search->toSVG(['width' => 32, 'id' => 'someButton', 'class' => 'float-right']);
```


