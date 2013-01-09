<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikolay
 * Date: 20.06.12
 * Time: 16:19
 * To change this template use File | Settings | File Templates.
 */

class Default_Form_Base extends Zend_Form
{
    protected $decorators = array(
        'text' => array(
            'viewHelper',
            array('Errors', array('tag' => 'div', 'class' => 'wrong')),
            array('Label', array(
                'class' => 'reg_input_title'
            )
            ),
            array(
                'decorator' => array('container' => 'HtmlTag'),
                'options' => array('tag' => 'div')
            ),
            array(
                'decorator' => array('delim1' => 'HtmlTag'),
                'options' => array('tag' => 'div', 'class' => 'clear_medium', 'placement' => Zend_Form_Decorator_Abstract::APPEND)
            ),
            array(
                'decorator' => array('delim2' => 'HtmlTag'),
                'options' => array('tag' => 'div', 'class' => 'clear_medium', 'placement' => Zend_Form_Decorator_Abstract::APPEND)
            ),
        ),
        'button' => array(
            'Errors',
            'viewHelper',
            array(
                'decorator' => array('container' => 'HtmlTag'),
                'options' => array('tag' => 'div', 'class' => 'reg_form_input')
            ),
            array(
                'decorator' => array('delim1' => 'HtmlTag'),
                'options' => array('tag' => 'div', 'class' => 'clear_medium', 'placement' => Zend_Form_Decorator_Abstract::PREPEND)
            ),
            array(
                'decorator' => array('delim2' => 'HtmlTag'),
                'options' => array('tag' => 'div', 'class' => 'clear_medium', 'placement' => Zend_Form_Decorator_Abstract::PREPEND)
            )
        ),
        'captcha' => array(
            array(
                'decorator' => array('clear1' => 'HtmlTag'),
                'options' => array(
                    'class' => 'clear',
                    'placement' => Zend_Form_Decorator_Abstract::PREPEND
                ),
            ),

            array(
                'decorator' => 'Label',
                'options' => array(
                    'class' => 'reg_input_title',
                    'placement' => Zend_Form_Decorator_Abstract::PREPEND
                ),
            ),

            array(
                'decorator' => 'Captcha',
                'options' => array(
                    'placement' => Zend_Form_Decorator_Abstract::APPEND
                ),
            ),
            array(
                'decorator' => array('container' => 'HtmlTag'),
                'options' => array(
                    'tag' => 'div'
                ),
            ),
            //array(
            //    'decorator' => array('clear2'=>'HtmlTag'),
            //    'options' => array(
            //        'class' => 'clear_medium',
            //        'placement'=> Zend_Form_Decorator_Abstract::PREPEND
            //    ),
            //),
            array('Errors', array('tag' => 'div', 'class' => '')),
            //'Captcha'
        ),
        'text_big' => array(
            array(
                'decorator' => array('delim1' => 'HtmlTag'),
                'options' => array('tag' => 'div', 'class' => 'clear_medium', 'placement' => Zend_Form_Decorator_Abstract::APPEND)
            ),
            'viewHelper',
            array('Errors', array('tag' => 'div', 'class' => 'wrong')),
            array('Label', array(
                'class' => 'reg_input_title'
            )
            ),
            array(
                'decorator' => array('container' => 'HtmlTag'),
                'options' => array('tag' => 'div')
            ),
            array(
                'decorator' => array('delim1' => 'HtmlTag'),
                'options' => array('tag' => 'div', 'class' => 'clear_medium', 'placement' => Zend_Form_Decorator_Abstract::APPEND)
            ),
            array(
                'decorator' => array('delim2' => 'HtmlTag'),
                'options' => array('tag' => 'div', 'class' => 'clear_medium', 'placement' => Zend_Form_Decorator_Abstract::APPEND)
            ),
        ),

    );

    protected function getCountriesArray()
    {
        $countries = Zend_Locale::getTranslationList('territory', null, 2);
        if (extension_loaded('intl')) {
            $intl = new Collator(Zend_Registry::get('locale'));
            $intl->asort($countries);
        } else {
            natsort($countries);
        }
        return [0 => ''] + $countries;
    }


    protected function getStates()
    {
        return array(
            '0' => "",
            'AL' => "Alabama",
            'AK' => "Alaska",
            'AZ' => "Arizona",
            'AR' => "Arkansas",
            'CA' => "California",
            'CO' => "Colorado",
            'CT' => "Connecticut",
            'DE' => "Delaware",
            'DC' => "District  Of Columbia",
            'FL' => "Florida",
            'GA' => "Georgia",
            'HI' => "Hawaii",
            'ID' => "Idaho",
            'IL' => "Illinois",
            'IN' => "Indiana",
            'IA' => "Iowa",
            'KS' => "Kansas",
            'KY' => "Kentucky",
            'LA' => "Louisiana",
            'ME' => "Maine",
            'MD' => "Maryland",
            'MA' => "Massachusetts",
            'MI' => "Michigan",
            'MN' => "Minnesota",
            'MS' => "Mississippi",
            'MO' => "Missouri",
            'MT' => "Montana",
            'NE' => "Nebraska",
            'NV' => "Nevada",
            'NH' => "New  Hampshire",
            'NJ' => "New Jersey",
            'NM' => "New Mexico",
            'NY' => "New  York",
            'NC' => "North Carolina",
            'ND' => "North Dakota",
            'OH' => "Ohio",
            'OK' => "Oklahoma",
            'OR' => "Oregon",
            'PA' => "Pennsylvania",
            'RI' => "Rhode  Island",
            'SC' => "South Carolina",
            'SD' => "South Dakota",
            'TN' => "Tennessee",
            'TX' => "Texas",
            'UT' => "Utah",
            'VT' => "Vermont",
            'VA' => "Virginia",
            'WA' => "Washington",
            'WV' => "West  Virginia",
            'WI' => "Wisconsin",
            'WY' => "Wyoming");
    }

    public function isValid($data)
    {
        $result = parent::isValid($data);

        if ($result) {
            return $result;
        }
        /**
         * @var Zend_Form_Element $element
         */
        foreach ($this->getElements() as $element) {
            if ($element->hasErrors()) {
                $decorator = $element->getDecorator('container');
                if ($decorator) {
                    $class = $decorator->getOption('class');
                    $decorator->setOption('class', $class . ' wrong');
                } else {
                    $class = $element->getAttrib('class');
                    $element->setAttrib('class', $class . ' wrong');
                }

            }
            ;
        }
        return false;
    }
}