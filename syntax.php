<?php

use dokuwiki\Utf8\PhpString;
use dokuwiki\Utf8\Sort;

/**
 * DokuWiki Plugin autoindex (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */
class syntax_plugin_autoindex extends \dokuwiki\Extension\SyntaxPlugin
{
    /** @inheritDoc */
    public function getType()
    {
        return 'substition';
    }

    /** @inheritDoc */
    public function getSort()
    {
        return 155;
    }

    /** @inheritDoc */
    public function getPType()
    {
        return 'block';
    }

    /** @inheritDoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('{{autoindex}}', $mode, 'plugin_autoindex');
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $data = array();

        return $data;
    }

    /** @inheritDoc */
    public function render($format, Doku_Renderer $renderer, $data)
    {
        global $INFO;
        global $conf;

        if ($format === 'metadata') {
            return false;
        }

        $opts = [
            'listfiles' => true,
            'pagesonly' => true,
            'firsthead' => true,
            'depth' => 1,
        ];
        $data = [];
        $ns = ':' . cleanID(getNS($INFO['id']));
        $ns = utf8_encodeFN(str_replace(':', '/', $ns));

        search($data, $conf['datadir'], 'search_universal', $opts, $ns, 1, '');
        uasort($data, [$this, 'titleSort']);

        if ($format === 'xhtml') {
            $renderer->doc .= '<div class="plugin_autoindex">';
        }

        $last = '';
        foreach ($data as $page) {
            $first = PhpString::strtoupper(PhpString::substr($page['title'], 0, 1));
            if ($first !== $last) {
                if ($last !== '') {
                    $renderer->listu_close();
                    if ($format === 'xhtml') $renderer->doc .= '</div>';
                }

                $last = $first;
                if ($format === 'xhtml') $renderer->doc .= '<div>';
                $renderer->header($first, 2, 0);
                $renderer->listu_open();
            }

            $renderer->listitem_open(1);
            $renderer->listcontent_open();
            $renderer->internallink($page['id'], $page['title']);
            $renderer->listcontent_close();
            $renderer->listitem_close();
        }

        if ($last !== '') {
            $renderer->listu_close();
            if ($format === 'xhtml') $renderer->doc .= '</div>';
        }

        if ($format === 'xhtml') {
            $renderer->doc .= '</div>';
        }

        return true;
    }

    /**
     * Custom comparator for sorting by title
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public function titleSort($a, $b)
    {
        return Sort::strcmp($a['title'], $b['title']);
    }
}

