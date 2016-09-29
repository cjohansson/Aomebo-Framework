<?php
/**
 *
 */

/**
 *
 */
namespace Dependencies\Bootstrap
{

    /**
     *
     */
    class Dependency extends \Aomebo\Associatives\Dependency
    {

        /**
         * @var array
         */
        protected $_subdependencies = array('jQuery');

        /**
         * @var array
         */
        protected $_options = array(
            'inline.html' => array(
                'mode' => parent::MODE_INLINE,
            ),
        );

    }
}
