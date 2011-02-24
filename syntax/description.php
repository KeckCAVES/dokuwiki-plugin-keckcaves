<?php
/**
 * KeckCAVES Plugin
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_keckcaves_description extends DokuWiki_Syntax_Plugin {

    function getType() { return 'baseonly'; }
    function getAllowedTypes() { return array('formatting', 'substition', 'disabled', 'paragraphs'); }
    function getPType() { return 'stack'; } // Output paragraphs properly

    function getSort() { return 32; }
 
    function connectTo($mode) {
        $this->Lexer->addEntryPattern(
//            '[ \t]*-{2,}[ \t]*description[ \t]*-{2,}[ \t]*(?=\n)',
            '[ \t]*\*\*\*',
            $mode, 'plugin_keckcaves_description'
        );
    }

    function postConnect() {
        $this->Lexer->addExitPattern(
//            '[ \t]*-{2,}[ \t]*(?=\n)',
            '[ \t]*\*\*\*[ \t]*(?=\n)',
            'plugin_keckcaves_description'
        );
    }
  
    function handle($match, $state, $pos, &$handler) {
        $text='';
        switch ($state) {
            case DOKU_LEXER_ENTER: // a pattern set by addEntryPattern()
                return array($state,'');
            case DOKU_LEXER_MATCHED: // a pattern set by addPattern()
                return array($state,'');
            case DOKU_LEXER_EXIT: // a pattern set by addExitPattern()
                return array($state,'');
            case DOKU_LEXER_SPECIAL: // a pattern set by addSpecialPattern()
                return array($state,'');
            case DOKU_LEXER_UNMATCHED: // ordinary text encountered within the plugin's syntax mode which doesn't match any pattern
                return array($state,$match);
        }
        return array($state,'');
    }
 
    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml'){
            list($state,$text) = $data;
            switch ($state) {
                case DOKU_LEXER_ENTER:
                    $renderer->doc .= '<div class="kc-description">';
                    break;
                case DOKU_LEXER_UNMATCHED:
                    $renderer->doc .= $text;
                    break;
                case DOKU_LEXER_EXIT:
                    $renderer->doc .= '</div>';
                    break;
            }
            return true;
        }
        return false;
    }
}
