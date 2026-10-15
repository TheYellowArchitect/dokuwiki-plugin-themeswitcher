<?php

use dokuwiki\Extension\SyntaxPlugin;

/**
 * Theme Switcher: ~~THEMESWITCHER~~ puts the switcher into a page, e.g. the sidebar
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author  TheYellowArchitect <hello@theyellowarchitect.com>
 */
class syntax_plugin_themeswitcher extends SyntaxPlugin
{
    /** @inheritdoc */
    public function getType()
    {
        return 'substition';
    }

    /** @inheritdoc */
    public function getPType()
    {
        return 'normal';
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 300;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('~~THEMESWITCHER~~', $mode, 'plugin_themeswitcher');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        return [];
    }

    /** @inheritdoc */
    public function render($format, Doku_Renderer $renderer, $data)
    {
        if ($format !== 'xhtml') return false;
        $renderer->doc .= '<button type="button" class="plugin_themeswitcher">' . hsc($this->getLang('theme')) . '</button>';
        return true;
    }
}
