<?php

use dokuwiki\Extension\Plugin;

/**
 * Theme Switcher: reads the theme settings
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author  TheYellowArchitect <hello@theyellowarchitect.com>
 */
class helper_plugin_themeswitcher extends Plugin
{
    /**
     * The configured themes, in order
     *
     * Parses the "themes" setting: `id|Label` entries separated by commas or new lines.
     * The label is optional; entries with an invalid id are skipped.
     *
     * @return string[] theme id => label
     */
    public function getThemes()
    {
        $themes = [];
        foreach (preg_split('/[,\n]+/', (string)$this->getConf('themes')) as $entry) {
            $parts = explode('|', $entry, 2);
            $id = strtolower(trim($parts[0]));
            if (!preg_match('/^[a-z0-9_-]+$/', $id)) continue;
            $label = trim($parts[1] ?? '');
            $themes[$id] = $label === '' ? ucfirst($id) : $label;
        }
        return $themes;
    }

    /**
     * Everything the browser needs: the themes, the default, the dark-mode default and the switcher style
     *
     * @return array|null null when fewer than two themes are configured
     */
    public function getClientConfig()
    {
        $themes = $this->getThemes();
        if (count($themes) < 2) return null;

        $default = $this->getConf('default');
        if (!isset($themes[$default])) $default = array_key_first($themes);
        $dark = $this->getConf('dark');
        if (!isset($themes[$dark])) $dark = '';

        $list = [];
        foreach ($themes as $id => $label) $list[] = [$id, $label];

        return [
            'key' => 'themeswitcher:' . DOKU_BASE, // one saved choice per wiki, even with several wikis on one domain
            'themes' => $list,
            'def' => $default,
            'dark' => $dark,
            'switcher' => $this->getConf('switcher') === 'menu' ? 'menu' : 'cycle',
        ];
    }
}
