<?php
  class Default_View_Helper_HeadLink extends Zend_View_Helper_HeadLink {
        public function toString($indent = null)
        {
            $indent = (null !== $indent)
                    ? $this->getWhitespace($indent)
                    : $this->getIndent();

            $items = array();
            
            $this->getContainer()->ksort();
            
            $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
            $merger = $bootstrap->hasResource('StaticMerger') 
                        ? $bootstrap->getResource('staticMerger') 
                        : false ;
                        
            if ($merger) {
                foreach ($this as $item) {
                    $items[] = $item->href;
                }
                return $indent .$merger->getHtml($items);
            } else {
                foreach ($this as $item) {
                    $items[] = $this->itemToString($item);
                }

                return $indent . implode($this->_escape($this->getSeparator()) . $indent, $items);
            }
            
        }
  }

