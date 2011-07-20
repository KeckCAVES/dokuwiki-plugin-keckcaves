<?php
/**
 * KeckCAVES Plugin
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_keckcaves_summary extends DokuWiki_Syntax_Plugin {

    var $_id = 0;
    var $_linked = false;

    function getType() { return 'baseonly'; }
    function getAllowedTypes() { return array('formatting', 'substition', 'disabled'); }
    function getPType() { return 'block'; }

    function getSort() { return 0; }
 
    function connectTo($mode) {
        $pattern = '\n {2,}\*[ \t]*[^[]*\[[^]]*\][ \t]*\{[^}]*\}';
        $this->Lexer->addEntryPattern('[ \t]*'.($pattern),$mode,'plugin_keckcaves_summary');
        $this->Lexer->addPattern($pattern,'plugin_keckcaves_summary');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('\n','plugin_keckcaves_summary');
    }
  
    function handle($match, $state, $pos, &$handler) {
        $data='';
        global $ID;
        $ns = getNS($ID);
        switch ($state) {
            case DOKU_LEXER_ENTER: // a pattern set by addEntryPattern()
                ++$this->_id;
                $data .= '<div class="clearer"></div>';
                $data .= '<dl class="kc-summary" id="kc-summary'.$this->_id.'">';
                $this->_start_item($match,$data);
                break;
            case DOKU_LEXER_MATCHED: // a pattern set by addPattern()
                $data .= '</dd>';
                $data .= '<div class="clearer"></div>';
                $this->_start_item($match,$data);
                break;
            case DOKU_LEXER_SPECIAL: // a pattern set by addSpecialPattern()
                break;
            case DOKU_LEXER_UNMATCHED: // ordinary text encountered within the plugin's syntax mode which doesn't match any pattern
                $this->_linkify($match,$data);
                break;
            case DOKU_LEXER_EXIT: // a pattern set by addExitPattern()
                $data .= '</dd>';
                $data .= '</dl>';
                $data .= '<div class="clearer"></div>';
                break;
        }
        return $data;
    }
 
    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml'){
            $renderer->doc .= $data;
            return true;
        }
        return false;
    }

    function _start_item($match, &$data) {
        $pattern = '/[^*]*\*([^[]*)\[([^]]*)\][ \t]*\{([^}]*)\}/';
        $height = 140;
        preg_match($pattern, $match, $matches);
        list(,$title,$page,$image) = $matches;
        resolve_pageid($ns,$page,$exists);
        resolve_pageid($ns,$image,$exists);
        $this->_page = wl($page);
        $image_size = @getimagesize(mediaFN($image));
        if($image_size[1]) {
          $width = (int)round($image_size[0]*$height/$image_size[1]);
        } else {
          $width = $image_size[0];
        }
        $data .= '<dt><a href="'.$this->_page.'">'.htmlentities($title).'</a></dt>';
        $data .= '<dd><a href="'.$this->_page.'"><img src="'.ml($image,array('w'=>$width,'h'=>$height)).'"/></a>';
    }

    function _linkify($text,&$data) {
        $data .= '<a href="'.$this->_page.'">'.htmlentities($text).'</a>';
    }
}
