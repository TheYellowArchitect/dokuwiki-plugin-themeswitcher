<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use dokuwiki\plugin\themeswitcher\MenuItem;

/**
 * Theme Switcher: applies the reader's theme before the page is drawn, adds the switcher
 * to a menu and includes the example themes in the stylesheet
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author  TheYellowArchitect <hello@theyellowarchitect.com>
 */
class action_plugin_themeswitcher extends ActionPlugin
{
    /** @inheritdoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'addHeadScript');
        $controller->register_hook('MENU_ITEMS_ASSEMBLY', 'AFTER', $this, 'addMenuItem');
        $controller->register_hook('CSS_STYLES_INCLUDED', 'BEFORE', $this, 'addExampleStyles');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'ignoreAction');
    }

    /**
     * Add a tiny script to <head> that puts the reader's theme on <html data-wiki-theme="...">
     *
     * It runs before the page is drawn, so readers never see the default theme flash first.
     * It also hands the settings to script.js as JSINFO.themeswitcher.
     *
     * @param Event $event TPL_METAHEADER_OUTPUT
     */
    public function addHeadScript(Event $event)
    {
        /** @var helper_plugin_themeswitcher $helper */
        $helper = $this->loadHelper('themeswitcher');
        $config = $helper->getClientConfig();
        if (!$config) return;

        $json = json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $event->data['script'][] = [
            '_data' => '(function(c){'
                . '(window.JSINFO=window.JSINFO||{}).themeswitcher=c;'
                . 'var t=null;try{t=localStorage.getItem(c.key)}catch(e){}'
                . 'if(!c.themes.some(function(x){return x[0]===t})){'
                . 't=c.dark&&window.matchMedia&&matchMedia("(prefers-color-scheme: dark)").matches?c.dark:c.def}'
                . 'document.documentElement.setAttribute("data-wiki-theme",t)'
                . '})(' . $json . ');',
        ];
    }

    /**
     * Add the switcher to the configured menu (site, user or page tools)
     *
     * @param Event $event MENU_ITEMS_ASSEMBLY
     */
    public function addMenuItem(Event $event)
    {
        if ($event->data['view'] !== $this->getConf('menu')) return;
        /** @var helper_plugin_themeswitcher $helper */
        $helper = $this->loadHelper('themeswitcher');
        if (count($helper->getThemes()) < 2) return;

        try {
            $item = new MenuItem($this->getLang('theme'));
        } catch (\RuntimeException $e) {
            return; // disabled through the "disableactions" setting
        }

        // in the page tools, keep "Back to top" last
        $items = &$event->data['items'];
        $last = end($items);
        if ($last && $last->getType() === 'top') {
            array_splice($items, count($items) - 1, 0, [$item]);
        } else {
            $items[] = $item;
        }
    }

    /**
     * Add the example themes to the screen stylesheet when they are enabled
     *
     * Their relative image paths are resolved against the examples folder.
     *
     * @param Event $event CSS_STYLES_INCLUDED
     */
    public function addExampleStyles(Event $event)
    {
        if ($event->data['mediatype'] !== 'screen' || !$this->getConf('examples')) return;
        $event->data['files'][__DIR__ . '/examples/examples.less'] = DOKU_BASE . 'lib/plugins/themeswitcher/examples/';
    }

    /**
     * Treat do=themeswitcher as a normal page view
     *
     * That action only reaches the server from the mobile tools dropdown without JavaScript,
     * where there is nothing to switch.
     *
     * @param Event $event ACTION_ACT_PREPROCESS
     */
    public function ignoreAction(Event $event)
    {
        if ($event->data === 'themeswitcher') $event->data = 'show';
    }
}
