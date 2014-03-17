<?php

/**
 * TechDivision\ApplicationServerWebsite\Utilities\I18n
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category   Application
 * @package    TechDivision_ApplicationServerWebsite
 * @subpackage Utilities
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/techdivision/TechDivision_ApplicationServerWebsite
 */

namespace TechDivision\ApplicationServerWebsite\Utilities;

use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;

/**
 * Utility class with several .
 *
 * @category   Application
 * @package    TechDivision_ApplicationServerWebsite
 * @subpackage Utilities
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/techdivision/TechDivision_ApplicationServerWebsite
 */
class I18n extends Translator
{

    /**
     * Holds translation domain temp.
     *
     * @var string
     */
    protected $domain;

    /**
     * Constructor.
     *
     * @param string $locale The locale
     * 
     * @return void
     */
    public function __construct($locale)
    {
        parent::__construct($locale);
        $this->addLoader('xliff-file', new XliffFileLoader());
    }

    /**
     * Translates all values of an array
     *
     * @param array  &$data  The values to be translated 
     * @param string $domain The translation domain
     * 
     * @return void
     */
    public function translateData(array &$data = array(), $domain = 'messages')
    {
        // set translation domain
        $this->domain = $domain;
        // translate all values from array recursively
        array_walk_recursive($data, array($this, '__'));
    }

    /**
     * Translation callback function.
     *
     * @param string &$text The text to translate
     * @param string $key   The translation key
     * 
     * @return void
     */
    public function __(&$text, $key)
    {
        $text = $this->trans($text, array(), $this->domain);
    }
}
