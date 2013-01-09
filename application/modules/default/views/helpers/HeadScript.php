<?php
  class Default_View_Helper_HeadScript extends Zend_View_Helper_HeadScript {
     /**
     * Retrieve string representation
     *
     * @param  string|int $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        if ($this->view) {
            $useCdata = $this->view->doctype()->isXhtml() ? true : false;
        } else {
            $useCdata = $this->useCdata ? true : false;
        }
        $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd   = ($useCdata) ? '//]]>'       : '//-->';

        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $merger = $bootstrap->hasResource('StaticMerger') 
                    ? $bootstrap->getResource('staticMerger') 
                    : false ;
        
        $items = array();
        $items_to_merger = array();
        $this->getContainer()->ksort();
        
        foreach ($this as $item) {
            if (!$this->_isValid($item)) {
                continue;
            }
            
            if (!empty($item->source) || !$merger) {
                $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
            } else {
                $items_to_merger[] = $item->attributes['src'];
            }
        }
        
        $return = ($merger && count($items_to_merger) > 0) 
                        ? $merger->getHtml( $items_to_merger) 
                        : '';
                        
        $return .= implode($this->getSeparator(), $items);
        return $return;
    }
  }
