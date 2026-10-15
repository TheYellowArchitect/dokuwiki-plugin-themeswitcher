/**
 * Theme Switcher: the switcher itself
 *
 * The reader's theme is already on <html data-wiki-theme="..."> at this point; a tiny
 * script in <head> (action.php) put it there before the page was drawn. This file turns
 * every switcher into a working control:
 *   - the menu item, which links to #themeswitcher
 *   - <button class="plugin_themeswitcher">, from ~~THEMESWITCHER~~
 *   - the "themeswitcher" entry of the mobile tools dropdown
 *
 * Other scripts can follow theme changes through the "themeswitcher:change" event on document.
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author  TheYellowArchitect <hello@theyellowarchitect.com>
 */
(function () {
    var cfg = window.JSINFO && window.JSINFO.themeswitcher;
    if (!cfg) return;

    var root = document.documentElement;
    var lang = (window.LANG && LANG.plugins && LANG.plugins.themeswitcher) || {};
    var TRIGGERS = 'a[href$="#themeswitcher"], button.plugin_themeswitcher';
    var menu = null;
    var opener = null;

    function text(key, value) {
        return (lang[key] || '%s').replace('%s', value);
    }

    function current() {
        return root.getAttribute('data-wiki-theme') || cfg.def;
    }

    function index(id) {
        for (var i = 0; i < cfg.themes.length; i++) {
            if (cfg.themes[i][0] === id) return i;
        }
        return -1;
    }

    /** Show a theme, remember it and update every switcher */
    function apply(id, save) {
        if (index(id) < 0) return;
        root.setAttribute('data-wiki-theme', id);
        if (save) {
            try {
                localStorage.setItem(cfg.key, id);
            } catch (e) {
                // storage blocked: the theme still changes, it just isn't remembered
            }
        }
        label();
        document.dispatchEvent(new CustomEvent('themeswitcher:change', {detail: {theme: id}}));
    }

    function next() {
        return cfg.themes[(Math.max(index(current()), 0) + 1) % cfg.themes.length][0];
    }

    /** Set the text and tooltip of every switcher */
    function label() {
        var i = Math.max(index(current()), 0);
        var shown = cfg.switcher === 'menu' ? text('theme', '') : text('current', cfg.themes[i][1]);
        var title = cfg.switcher === 'menu' ? text('choose', '') : text('next', cfg.themes[index(next())][1]);
        document.querySelectorAll(TRIGGERS).forEach(function (el) {
            // menu items with an icon keep their label in a <span>
            var target = el.querySelector('span') || el;
            target.textContent = shown;
            el.title = title;
            if (el.tagName === 'A') el.setAttribute('role', 'button');
            if (cfg.switcher === 'menu') el.setAttribute('aria-haspopup', 'true');
        });
    }

    // ---------------------------------------------------------------- menu mode

    function openMenu(trigger) {
        closeMenu();
        opener = trigger;
        menu = document.createElement('div');
        menu.className = 'plugin_themeswitcher_menu';
        menu.setAttribute('role', 'menu');
        menu.setAttribute('aria-label', text('choose', ''));
        cfg.themes.forEach(function (theme) {
            var item = document.createElement('button');
            item.type = 'button';
            item.setAttribute('role', 'menuitemradio');
            item.setAttribute('aria-checked', theme[0] === current() ? 'true' : 'false');
            item.setAttribute('data-theme', theme[0]);
            item.textContent = theme[1];
            menu.appendChild(item);
        });
        document.body.appendChild(menu);

        // below the switcher, right-aligned with it unless that would leave the screen
        var r = trigger.getBoundingClientRect();
        var left = r.right - menu.offsetWidth;
        if (left < 8) left = Math.max(8, r.left);
        menu.style.top = (r.bottom + window.scrollY + 4) + 'px';
        menu.style.left = (left + window.scrollX) + 'px';

        trigger.setAttribute('aria-expanded', 'true');
        (menu.querySelector('[aria-checked="true"]') || menu.firstChild).focus();
    }

    function closeMenu(refocus) {
        if (!menu) return;
        menu.remove();
        menu = null;
        if (opener) {
            opener.setAttribute('aria-expanded', 'false');
            if (refocus) opener.focus();
        }
        opener = null;
    }

    function moveFocus(step) {
        var items = Array.prototype.slice.call(menu.querySelectorAll('button'));
        var i = items.indexOf(document.activeElement);
        items[(i + step + items.length) % items.length].focus();
    }

    // ---------------------------------------------------------------- events

    document.addEventListener('click', function (e) {
        if (menu && menu.contains(e.target)) {
            var item = e.target.closest('button[data-theme]');
            if (item) {
                apply(item.getAttribute('data-theme'), true);
                closeMenu(true);
            }
            return;
        }
        var trigger = e.target.closest && e.target.closest(TRIGGERS);
        if (!trigger) {
            closeMenu(false);
            return;
        }
        e.preventDefault();
        if (cfg.switcher === 'menu') {
            if (menu && opener === trigger) closeMenu(true);
            else openMenu(trigger);
        } else {
            apply(next(), true);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (!menu) return;
        if (e.key === 'Escape') {
            e.preventDefault();
            closeMenu(true);
        } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            moveFocus(e.key === 'ArrowDown' ? 1 : -1);
        } else if (e.key === 'Tab') {
            closeMenu(false);
        }
    });

    // the mobile tools dropdown submits its form on change: choosing "Theme" cycles instead
    document.addEventListener('change', function (e) {
        var select = e.target;
        if (!select.matches || !select.matches('select.quickselect') || select.value !== 'themeswitcher') return;
        e.stopImmediatePropagation(); // capture phase: DokuWiki's submit handler never sees it
        select.value = '';
        apply(next(), true);
    }, true);

    // a choice made in another tab of the same wiki
    window.addEventListener('storage', function (e) {
        if (e.key === cfg.key && e.newValue) apply(e.newValue, false);
    });

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', label);
    else label();
})();
