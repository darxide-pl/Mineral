
# Mineral plugin for CakePHP3

## Output minification plugin 
With Mineral You can: 
1. Minify all of HTML output to "one line" form * 
2. Minify only selected elements/blocks/pages/templates
3. Cleaning output from: inline css, inline style blocks, inline scripts blocks
4. Use events for custom output processing

*depending on the options selected in the minifier - by default plugin will omit blocks of script

Webstites, which use this plugin: 
https://www.ecrf.biz.pl/

## Requirements 
1. CakePHP 3+
2. PHP 7.0+

## Installation

1. Create folder in `/src/plugins/` called `Mineral` and download repo there. 
2. In `/config/bootstrap.php` load this plugin: `Plugin::load('Mineral', ['bootstrap' => FALSE, 'routes' => FALSE]);
`
3. Load MineralHelper Plugin in `/src/View/AppView.php` (go to section basic usage)

# 1. Minifying entire output
## Basic usage
First scenario: we wanna minify every output in our app. 
Load MineralHelper inside `/src/View/AppView.php` 
```php
<?php
namespace App\View;
use Cake\View\View;

class AppView extends View
{
    public function initialize() {

        $this->loadHelper('Mineral.Mineral');
    }
}
```
... and thats all :) 
Helper uses cake's `afterLayout()` event, so output processing will be called automatically. Now, all HTML has inline form and we can go home, but... there's a lot of scenarios and options we can use. 

## Cutting out inline CSS
 I think, every front developer should place styles inside css files, but `<div style="color:red">it's not always like that</div>.` 
With this option we cut out inline css and force devs to keep code clean.
By default this option is disabled - bearing in mind `wywiwyg` editors - which often use inline css, and that option will limit functionality of ckeditor (for example).
```php 
<?php
namespace App\View;
use Cake\View\View;

class AppView extends View
{
    public function initialize() {

        $this->loadHelper('Mineral.Mineral', [
            'css' => TRUE
        ]);
    }
}
```
## Cutting out style tags
Cutting out `<style></style>` tags from output with content
```php
<?php

namespace App\View;
use Cake\View\View;

class AppView extends View
{
    public function initialize() {

        $this->loadHelper('Mineral.Mineral', [
            'style' => TRUE
        ]);
    }
}
```
## Cutting out blocks of scripts 
All of scripts should be placed in .js files, so we should have option to cut out everything which appears inside `<script></script>` tags: 
```php 
<?php

namespace App\View;
use Cake\View\View;

class AppView extends View
{
    public function initialize() {

        $this->loadHelper('Mineral.Mineral', [
            'script' => TRUE
        ]);
    }
}
```

# 2 Callbacks
## beforePruning 
We have option to manipulating output before Mineral will do that.  
```php 
<?php

namespace App\View;
use Cake\View\View;

class AppView extends View
{
    public function initialize() {

        $this->loadHelper('Mineral.Mineral', [
            'beforePruning' => function($content) {
                return str_replace('NSA', '<div class="hidden">NSA</div>', $content);
            }
        ]);
    }
}
```

## afterPruning 
Similar option, but with callback we'll get `$content` which was cleaned

```php 
<?php

namespace App\View;
use Cake\View\View;

class AppView extends View
{
    public function initialize() {

        $this->loadHelper('Mineral.Mineral', [
            'afterPruning' => function($content) {
                return str_replace('NSA', '<div class="hidden">NSA</div>', $content);
            }
        ]);
    }
}
```
## Config 
Of course we can mix all of parameters: 
```php
<?php

namespace App\View;
use Cake\View\View;

class AppView extends View
{
    public function initialize() {

        $this->loadHelper('Mineral.Mineral', [
            'css' => TRUE, 
            'script' => TRUE,
        ]);
    }
}
```
# 3. Minifying parts of views

At the outset, I should point out that the config declared in `AppView`, will be default config for all other manipulations, but we can override config by calling `override($config)` somewhere in our app (helper, template, element).
Config will be overriden key by key, so if You wanna override `css` option (looking at the previous example), You should overwrite that option this way: 
```php 
<?php $this->Mineral->override([
    'css' => FALSE,
    /// 'script' option is still true
]) ?>
```

## Disabling automatic minifying
By default, Mineral uses cake's `afterLayout()` event to manipulating output. If we wanna minify only parts of views, we should disable that behaviour, by calling `MineralHelper::disable()` method: 

```php 
<?php<div

namespace App\View;
use Cake\View\View;

class AppView extends View
{
    public function initialize() {

        $this->loadHelper('Mineral.Mineral', [
            'css' => TRUE
        ]);

        $this->Mineral->disable();
    }
}
```
We can enable minifier back, by using `enable()` method.

## Methods
Inside templates You can use bunch of methods to minify parts of views.  

1. process 

`string process(string $content, array $options)` 

Handy method to minify content

`$content` - block, element or just any string 

`$options` array of options the same as described in sections `1. Minifying entire output` and `2. Callbacks` 

```php 
// minifying element
<?= $this->Mineral->process($this->Element('footer')) ?>

// minifying block 
<?= $this->Mineral->process($this->fetch('content'), [
    'css' => TRUE
]) ?>

// minifying any string
<?= $this->Mineral->process('<div>
foo
</div>') ?>
```

2. minify

`string minify(string $content)`

minify method offers standard minification (HTML to "one line" form)
```php 
// minifying element
<?= $this->Mineral->minify($this->Element('footer')) ?>

// minifying block 
<?= $this->Mineral->minify($this->fetch('content')) ?>

// minify any string
<?= $this->Mineral->minify('<div>
foo
</div>') ?>
```
3. inlineCssPruning

`string inlineCssPruning(string $content)`

Remove `<div style="color:red">inline css</div>` from string 

```php 
// minifying element
<?= $this->Mineral->inlineCssPruning($this->Element('footer')) ?>

// minifying block 
<?= $this->Mineral->inlineCssPruning($this->fetch('content')) ?>

// minify any string
<?= $this->Mineral->inlineCssPruning('<div style="color:red">foo</div>') ?>
```

4. inlineStylePruning 

`string inlineStylePruning(string $content)` 

Remove style tags from output

```php 
// minifying element
<?= $this->Mineral->inlineStylePruning($this->Element('footer')) ?>

// minifying block 
<?= $this->Mineral->inlineStylePruning($this->fetch('content')) ?>

// minify any string
<?= $this->Mineral->inlineStylePruning('<style> * {color:red}</style>') ?>
```
5. inlineScriptPruning

`string inlineScriptPruning(string $content)`

Remove script blocks from output 

```php 
// minifying element
<?= $this->Mineral->inlineScriptPruning($this->Element('footer')) ?>

// minifying block 
<?= $this->Mineral->inlineScriptPruning($this->fetch('content')) ?>

// minify any string
<?= $this->Mineral->inlineScriptPruning('<script>alert("hello")</scrpt>') ?>
```

# 4 Minifying specific pages 
Now, we know every methods in this plugin. We can use them for example to minifying only specific pages. 

Let's say that we have `$page` entity in view, which comes from controller, and we wanna minify this page only if that page has set `$page->minify` property to true. 

We can write custom method in app view, which takes `$page` entity as parameter and decides to enable or disable minification. 

In `/src/View/AppView.php` 

```php 
<?php

namespace App\View;
use Cake\View\View;

class AppView extends View
{
    public function initialize() {

        $this->loadHelper('Mineral.Mineral', [
            'css' => TRUE,
            'script' => TRUE, 
            'style' => TRUE
        ]);

        $this->Mineral->disable();
    }

    // our custom method
    public function minifyMe($page) {
        
        if($page->minify) {
            return $this->Mineral->enable();
        }

        return $this->Mineral->disable();
    }
}
```
And in your template You can call Your method: 

```html
<head>
<?php $this->minifyMe($page) ?>
</head>
```
