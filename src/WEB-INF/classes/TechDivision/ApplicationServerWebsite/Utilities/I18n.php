<?php

namespace TechDivision\ApplicationServerWebsite\Utilities;

/**
 * TechDivision\ApplicationServerWebsite\Utilities\I18n
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

/**
 * @package     TechDivision
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <jz@techdivision.com>
 */

use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;

class I18n extends Translator
{

    /**
     * holds translation domain temp.
     *
     * @var string
     */
    protected $domain;

    /**
     * Constructor.
     *
     * @param string $locale The locale
     *
     * @api
     */
    public function __construct($locale)
    {
        parent::__construct($locale);
        $this->addLoader('xliff-file', new XliffFileLoader());
    }

    /**
     * Translates all values of an array
     *
     * @param array $data
     * @param string $domain
     */
    public function translateData(array &$data = array(), $domain = 'messages')
    {
        // set translation domain
        $this->domain = $domain;
        // translate all values from array recursively
        array_walk_recursive($data, array($this, '__'));
    }

    /**
     * translation function
     *
     * @param $text
     * @param $key
     */
    public function __(&$text, $key)
    {
        $text = $this->trans($text, array(), $this->domain);
    }

}