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
        $data=array('','','',1);
        switch ($state) {
            case DOKU_LEXER_ENTER: // a pattern set by addEntryPattern()
                ++$this->_id;
                $this->_count = 0;
                $this->_level = $this->_find_current_level($handler);
                $this->_section_ids = array();
                //$data[0] .= '<div class="clearer"></div>';
                $data[0] .= '<ul class="kc-summary" id="kc-summary'.$this->_id.'">';
                $this->_start_item($match,$data);
                break;
            case DOKU_LEXER_MATCHED: // a pattern set by addPattern()
                $data[0] .= '</li>';
                //$data[0] .= '<div class="clearer"></div>';
                $this->_start_item($match,$data);
                break;
            case DOKU_LEXER_SPECIAL: // a pattern set by addSpecialPattern()
                break;
            case DOKU_LEXER_UNMATCHED: // ordinary text encountered within the plugin's syntax mode which doesn't match any pattern
                $this->_linkify(htmlentities($match),$data);
                break;
            case DOKU_LEXER_EXIT: // a pattern set by addExitPattern()
                $data[0] .= '</li>';
                $data[0] .= '</ul>';
                $data[0] .= '<div class="clearer"></div>';
                break;
        }
        return $data;
    }
 
    function render($mode, &$renderer, &$data) {
        $return=false;
        if($data[1] && ($mode == 'xhtml' || $mode == 'metadata')){
          $renderer->toc_additem($data[1],$data[2],$data[3]);
          $return=true;
        }
        if($mode == 'xhtml'){
          $renderer->doc .= $data[0];
          $return=true;
        }
        return $return;
    }

    // HACK: find current level by finding most
    // recent 'section_open' in $handler->calls array.
    function _find_current_level(&$handler) {
        $level=1;
        for($i=count($handler->calls)-1; $i>=0; $i--) {
          if($handler->calls[$i][0] == 'section_open') {
            $level=$handler->calls[$i][1][0]+1;
            break;
          }
        }
        return $level;
    }

    function _start_item($match, &$data) {
        $pattern = '/[^*]*\*([^[]*)\[([^]]*)\][ \t]*\{([^}]*)\}/';
        $height = $this->getConf('height');
        global $ID;
        $ns = getNS($ID);
        preg_match($pattern, $match, $matches);
        list(,$title,$page,$image) = $matches;
        $this->_page = '';
        if (trim($page)) {
          resolve_pageid($ns,$page,$exists);
          $this->_page = wl($page);
        }
        if (trim($image)) {
          resolve_mediaid($ns,$image,$exists);
          $image_size = @getimagesize(mediaFN($image));
          if($image_size[1]) {
            $width = (int)round($image_size[0]*$height/$image_size[1]);
          } else {
            $width = $image_size[0];
          }
        } else {
          $image = '';
        }
        $sid = sectionID($title,$this->_section_ids);
        if ($this->_count % 2) {
          $side = 'right';
        } else {
          $side = 'left';
        }
        $data[0] .= '<li class="'.$side.'"><a name="'.$sid.'"></a>';
        $data[0] .= '<h'.strval($this->_level).'>';
        $this->_linkify(htmlentities($title), $data);
        $data[0] .= '</h'.strval($this->_level).'>';
        if ($image) {
          $this->_linkify('<img src="'.ml($image,array('w'=>$width,'h'=>$height)).'"/>', $data);
        }
        $data[1] = $sid;
        $data[2] = $title;
        $data[3] = $this->_level;
        ++$this->_count;
    }

    function _linkify($text,&$data) {
      if ($this->_page) {
        $data[0] .= '<a href="'.$this->_page.'">'.$text.'</a>';
      } else {
        $data[0] .= $text;
      }
    }
}
