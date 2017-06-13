# Octicons: Port for PHP (including ZF3 view helper)

> SVG icon management.

## Usage

#### PHP
Instantiate an icon manager and use it to render icon markup.

```php
//Get an icon manager:
$iconManager = new Uch\Wac\Vis\IconManager();
//e.g. 1: Write out 'gear' icon markup:
echo $iconManager->gear->toSVG();
//e.g. 2: Write out 'law' icon markup :
echo $iconManager->law->toSVG(['width' => 32, 'title' => 'Weigh up this option', 'class' => 'custom-css-class']);
```

i.e. $iconManager->{icon-name}->toSVG(options);

You may also want to include the tiny CSS in your page as follows:
```php
echo '<style type="text/css">' . $iconManager->getCss() . '</style>';

//Or simply:
echo $iconManager->getCss(true);

```

#### ZF3 (Zend Framework)
1) Add the view helper reference to your module config (aliased here as 'icons'):
```php
'view_helpers' => [
		'invokables' => [\Uch\Wac\Vis\Zf\IconViewHelper::class => \Uch\Wac\Vis\Zf\IconViewHelper::class],
		'aliases' => [icons' => \Uch\Wac\Vis\Zf\IconViewHelper::class]
		];
```

2) Include the following in your layout.phtml (before writing headStyle to the page):
```php
$this->headStyle()->prependStyle($this->icons()->getCss());
```

3) Inline or with spritesheet - use as follows within any of your .phtml files:

Inline:
echo $this->icons()->{name}->toSVG(options);


Referring to spritesheet definitions:
echo $this->icons()->{name}->toSVGUse(options);


```php
// e.g. 1
echo $this->icons()->search->toSVG(['width' => 32]);	//Or use toSVGUse(...)
// e.g. 2
echo $this->icons()->search->toSVG(['width' => 32, 'id' => 'someButton', 'class' => 'float-right']);

//And if you have used toSVGUse(), you will need to include this before closing the body element:
echo $this->icons()->getSVGSpritesheet();

```
