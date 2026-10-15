<?php

/**
 * Options for the themeswitcher plugin
 */

$meta['themes'] = ['string'];
$meta['default'] = ['string', '_pattern' => '/^[a-z0-9_-]*$/'];
$meta['dark'] = ['string', '_pattern' => '/^[a-z0-9_-]*$/'];
$meta['switcher'] = ['multichoice', '_choices' => ['cycle', 'menu']];
$meta['menu'] = ['multichoice', '_choices' => ['site', 'user', 'page', 'none']];
$meta['examples'] = ['onoff'];
