<?php

namespace dokuwiki\plugin\themeswitcher\test;

use DokuWikiTest;

/**
 * Tests for reading the theme settings
 *
 * @group plugin_themeswitcher
 * @group plugins
 */
class ThemesTest extends DokuWikiTest
{
    protected $pluginsEnabled = ['themeswitcher'];

    /**
     * @return \helper_plugin_themeswitcher
     */
    protected function helperWith(array $settings)
    {
        global $conf;
        foreach ($settings as $key => $value) {
            $conf['plugin']['themeswitcher'][$key] = $value;
        }
        return plugin_load('helper', 'themeswitcher', true);
    }

    public function testParsesIdsAndLabels(): void
    {
        $helper = $this->helperWith(['themes' => " default|Default ,night| Night,\nSepia , bad id|Nope, |empty"]);
        $this->assertSame(['default' => 'Default', 'night' => 'Night', 'sepia' => 'Sepia'], $helper->getThemes());
    }

    public function testClientConfigFallsBackToTheFirstTheme(): void
    {
        $helper = $this->helperWith(['themes' => 'a|A, b|B', 'default' => 'missing', 'dark' => 'b']);
        $config = $helper->getClientConfig();
        $this->assertSame('a', $config['def']);
        $this->assertSame('b', $config['dark']);
        $this->assertSame([['a', 'A'], ['b', 'B']], $config['themes']);
    }

    public function testNoSwitcherWithFewerThanTwoThemes(): void
    {
        $helper = $this->helperWith(['themes' => 'only|Only']);
        $this->assertNull($helper->getClientConfig());
    }
}
