<?php

namespace TechDivision\ApplicationServerWebsite\Servlets;

/**
 * TechDivision\ApplicationServerWebsite\Servlets\AbstractServlet
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

use TechDivision\ApplicationServerWebsite\Utilities\Template;
use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ApplicationServerWebsite\Utilities\I18n;
use TechDivision\ServletContainer\Servlets\HttpServlet;
use Symfony\Component\Yaml\Parser;

/**
 * This abstrac base servlet that provides template rendering
 * functionality.
 *
 * @package     TechDivision\ApplicationServerWebsite
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
abstract class AbstractServlet extends HttpServlet {

    /**
     * The key for the context param with the name of the settings file.
     *
     * @var string
     */
    const SETTINGS_FILE = 'settingsFile';
    
    /**
     * Define default template to use.
     *
     * @var string
     */
    const DEFAULT_TEMPALTE = 'default';
    const DEFAULT_PAGE = 'index';

    /**
     * Holds translator engine
     *
     * @var I18n
     */
    protected $i18n;

    /**
     * Holds mustache template engine.
     *
     * @var \Mustache_Engine
     */
    protected $mustache;

    /**
     * Holds yml parser instance.
     *
     * @var Parser
     */
    protected $yaml;

    /**
     * The default locale to use.
     *
     * @var string
     */
    protected $defaultLocale = 'en_US';

    /**
     * Array with the accepted languages.
     *
     * @var array
     */
    protected $locales = array('de-de' => 'de_DE', 'en-us' => 'en_US');

    /**
     * The application settings.
     *
     * @var array
     */
    protected $settings = array();

    /**
     * Initialise servlet class
     *
     * @param ServletConfig $config The servlets config object
     *
     * @return void
     */
    public function init(ServletConfig $config)
    {
        // initialize the servlet
        parent::init($config);

        // load the path to the properties file
        $settingsFile = $config->getServletManager()->getInitParameter(
            AbstractServlet::SETTINGS_FILE
        );
        
        // initialize the properties
        $this->settings = parse_ini_file(
            $this->getWebappPath() . DIRECTORY_SEPARATOR . $settingsFile
        );
    }
    
    /**
     * @see \TechDivision\ServletContainer\Servlets\HttpServlet::service()
     */
    public function service(Request $req, Response $res)
    {
        // initialize composer autoloader
        require $this->getServletConfig()->getWebappPath() . '/vendor/autoload.php';
        
        // serve the request
        parent::service($req, $res);
    }

    /**
     * Returns webapp root dir with path extended.
     *
     * @param string $path
     * @return string
     */
    protected function getRootDir($path = null)
    {
        $rootDir = $this->getServletConfig()->getWebappPath();
        if ($path) {
            $rootDir = $rootDir . DIRECTORY_SEPARATOR . $path;
        }
        return $rootDir;
    }
    
    /**
     * 
     * @param Request $req
     * @return string
     */
    protected function getBaseUrl(Request $req)
    {

        // initialize the base URL
        $baseUrl = '/';
        
        // if the application has NOT been called over a VHost configuration append application folder naem
        if (!$this->getServletConfig()->getApplication()->isVhostOf($req->getServerName())) {
            $baseUrl .= $this->getServletConfig()->getApplication()->getName() . '/';
        }
        
        return $baseUrl;
    }
    
    /**
     * Grab page to render.
     * 
     * @param Request $req The request with the path information
     * @return string The requested page name
     */
    protected function getPage(Request $req)
    {
        
        // try extract the page name from the URL
        $page = trim(
            str_replace(
                $this->getBaseUrl($req), 
                '', 
                $req->getPathInfo() . DIRECTORY_SEPARATOR
            ),
            '/'
        );
        
        // if no page name is available use the default one
        if (empty($page)) {
            $page = self::DEFAULT_PAGE;
        }
        
        // return the page name
        return $page;
    }
    
    /**
     * return the global data from the YAML file.
     * 
     * @return array The global data
     */
    protected function getGlobalData()
    {
        return $this->yaml->parse(
            file_get_contents(
                $this->getRootDir('data' . DIRECTORY_SEPARATOR . 'global.yml')
            )
        );
    }
    
    /**
     * Return the internal data.
     * 
     * @param Request $req The actual request to return the data from
     * @return array The internal data
     */
    protected function getInternalData(Request $req)
    {
        return array('BaseUrl' => $this->getBaseUrl($req));
    }
    
    /**
     * Return the page specific data.
     * 
     * @param Request $req The actual request to return the data from
     * @return array The page specific data
     */
    public function getPageData(Request $req)
    {
        return $this->yaml->parse(
            file_get_contents(
                $this->getRootDir('data' . DIRECTORY_SEPARATOR . $this->getPage($req) . '.yml')
            )
        );
    }

    /**
     * Process the template and return the HTML content as string.
     * 
     * @param Request $req The actual request to return the data from
     * @return string The HTML to render
     */
    public function processTemplate(Request $req)
    {

        // init parser for template yaml data.
        $this->yaml = new Parser();
        
        // init template engine
        $this->mustache = new \Mustache_Engine(
            array(
                'cache' => $this->getRootDir('cache/mustache'),
                'loader' => new \Mustache_Loader_FilesystemLoader(
                    $this->getRootDir('static/template')
                ),
                'partials_loader' => new \Mustache_Loader_FilesystemLoader(
                    $this->getRootDir('static/template/partials')
                )
            )
        );

        // init language
        $locale = $this->defaultLocale;
        list ($acceptLanguage) = explode(',', $req->getServerVar('HTTP_ACCEPT_LANGUAGE'));
        $acceptLanguage = strtolower($acceptLanguage);
        if (array_key_exists($acceptLanguage, $this->locales)) {
            $locale = $this->locales[$acceptLanguage];
        }

        // init translator engine
        $this->i18n = new I18n($locale);

        // grab page to render, if nothing left take default page
        $page = $this->getPage($req);
        
        // load the internal data
        $internalData = $this->getInternalData($req);

        // set default template
        $template = self::DEFAULT_TEMPALTE;
        // check if page specific template exists
        if (file_exists($this->getRootDir('static' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $page .'.mustache'))) {
            $template = $page;
        }

        // add global translations
        $this->i18n->addResource('xliff-file',
            $this->getRootDir('locales' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . 'global.xliff'),
            $locale, 'global'
        );
        // add template view translations
        $this->i18n->addResource('xliff-file',
            $this->getRootDir('locales' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $page . '.xliff'),
            $locale, $page
        );
        
        // translate data
        $globalData = $this->getGlobalData();
        $this->i18n->translateData($globalData, 'global');
        
        // check if page specific data exists
        $pageData = array();
        if (file_exists($this->getRootDir('data' . DIRECTORY_SEPARATOR . $page . '.yml'))) {
            
            // load page specific data
            $pageData = $this->getPageData($req);
            
            // translate data
            $this->i18n->translateData($pageData, $page);
        }

        // merge data arrays
        $data = array_merge($internalData, $globalData, $pageData);

        // render and return the content
        return $this->mustache->render($template, $data);
     }

    /**
     * Returns the application properties.
     *
     * @return array The application properties
     */
    protected function getSettings()
    {
        return $this->settings;
    }
    
    /**
     * Returns the setting with the passed key.
     * 
     * @param string $key The key of the property to return
     * @return string The requested setting
     */
    protected function getSetting($key)
    {
        if (array_key_exists($key, $settings = $this->getSettings())) {
            return $settings[$key];
        }
    }

    /**
     * Returns the applications root path.
     *
     * @return string The applications root path
     */
    protected function getWebappPath()
    {
        return $this->getServletConfig()->getWebappPath();
    }
}