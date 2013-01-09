<?php

class Default_Form_User extends Default_Form_Base
{
    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator('FormElements')
            ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'zend_form'))
            ->addDecorator('Form');

        $this->setAttrib('id', 'user-form');

        $this->addElements(
            array(
                new Zend_Form_Element_Select('country', array(
                        'required' => true,
                        'label' => 'Choose your country',
                        'multiOptions' => $this->getCountriesArray(),
                        'filters' => array('StringTrim'),
                        'decorators' => $this->decorators['text_big'],
                        'class' => 'fancy_input fl',
                        'validators' => array()
                    )
                ),
                new Zend_Form_Element_Text('city', array(
                        'required' => true,
                        'label' => 'Enter your city',
                        'filters' => array('StringTrim'),
                        'decorators' => $this->decorators['text_big'],
                        'class' => 'fancy_input fl default-value',
                        'data-default-value' => 'Choose your city',
                        'validators' => array()
                    )
                ),
                new Zend_Form_Element_Text('zipcode', array(
                        'required' => true,
                        'label' => 'Enter zip code',
                        'filters' => array('StringTrim'),
                        'decorators' => $this->decorators['text_big'],
                        'class' => 'fancy_input fl default-value',
                        'data-default-value' => 'Enter zip code',
                        'validators' => array()
                    )
                ),
                new Zend_Form_Element_Select('state', array(
                        'required' => true,
                        'label' => 'Choose state',
                        'filters' => array('StringTrim'),
                        'decorators' => $this->decorators['text_big'],
                        'multiOptions' => $this->getStates(),
                        'class' => 'fancy_input fl default-value',
                        'validators' => array()
                    )
                ),
            )
        );
    }


}

