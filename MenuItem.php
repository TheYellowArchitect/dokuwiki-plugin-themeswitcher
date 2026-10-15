<?php

namespace dokuwiki\plugin\themeswitcher;

use dokuwiki\Menu\Item\AbstractItem;

/**
 * The theme switcher as an item of DokuWiki's menus
 *
 * It links to #themeswitcher; script.js turns that link into the switcher.
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author  TheYellowArchitect <hello@theyellowarchitect.com>
 */
class MenuItem extends AbstractItem
{
    protected $type = 'themeswitcher';
    protected $svg = __DIR__ . '/images/palette.svg';

    /**
     * @param string $label the item's label
     */
    public function __construct($label)
    {
        parent::__construct();
        $this->id = '#themeswitcher';
        $this->label = $label;
    }
}
