<?php
namespace DoctrineGui\InputFilter;

/**
 * @author Brendan <b.nash at southeaster dot com>
 *
 * @Contributors:
 *
 */

use Zend\InputFilter\InputFilter;

class JwtFilter extends InputFilter
{
    function __construct() {

        $this->add( array(
                'name'      => 'id',
                'required'  => false,
            )
        );

        $this->add( array(
                'name'      => 'client',
                'required'  => true,
            )
        );

        $this->add( array(
                'name'      => 'subject',
                'required'  => true,
            )
        );

        $this->add( array(
                'name'      => 'publicKey',
                'required'  => true,
            )
        );


    }

}