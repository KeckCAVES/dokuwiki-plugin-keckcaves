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
        $pattern = '/[^*]*\*([^[]*)\[([^]]*)\][ \t]*\{([^}]*)\}/';
        switch ($state) {
            case DOKU_LEXER_ENTER: // a pattern set by addEntryPattern()
                ++$this->_id;
                preg_match($pattern, $match, $matches);
                list(,$title,$page,$image) = $matches;
                resolve_pageid($ns,$page,$exists);
                resolve_pageid($ns,$image,$exists);
                $data .= '<div class="clearer"></div>';
                $data .= '<dl class="kc-summary" id="kc-summary'.$this->_id.'">';
                $this->_start_item($title,$page,$image,$data);
                break;
            case DOKU_LEXER_MATCHED: // a pattern set by addPattern()
                preg_match($pattern, $match, $matches);
                list(,$title,$page,$image) = $matches;
                resolve_pageid($ns,$page,$exists);
                resolve_pageid($ns,$image,$exists);
                if ($this->_linked) $data .= '</a>';
                $data .= '</dd>';
                $data .= '<div class="clearer"></div>';
                $this->_start_item($title,$page,$image,$data);
                break;
            case DOKU_LEXER_SPECIAL: // a pattern set by addSpecialPattern()
                break;
            case DOKU_LEXER_UNMATCHED: // ordinary text encountered within the plugin's syntax mode which doesn't match any pattern
                $data = htmlentities($match);
                break;
            case DOKU_LEXER_EXIT: // a pattern set by addExitPattern()
                if ($this->_linked) $data .= '</a>';
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

    function _start_item($title, $page, $image, &$data) {
        $height = 140;
        $image_size = @getimagesize(mediaFN($image));
        $width = (int)round($image_size[0]*$height/$image_size[1]);
        $this->_linked = (trim($page) != "");
        if ($this->_linked) {
            $data .= '<dt><a href="'.wl($page).'">'.htmlentities($title).'</dt>';
            $data .= '<dd><a href="'.wl($page).'"><img src="'.ml($image,array('w'=>$width,'h'=>$height)).'"/>';
        } else {
            $data .= '<dt>'.$title.'</dt>';
            $data .= '<dd><img src="'.ml($image).'"/>';
        }
    }
}
