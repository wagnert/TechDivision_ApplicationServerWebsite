<?php

namespace TechDivision\ApplicationServerWebsite\Servlets;

/**
 * TechDivision\ApplicationServerWebsite\Servlets\SiteServlet
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
use Symfony\Component\Yaml\Parser;
use TechDivision\ApplicationServerWebsite\Utilities\I18n;
use TechDivision\ServletContainer\Servlets\DefaultServlet;
use TechDivision\ServletContainer\Servlets\HttpServlet;


class SiteServlet extends HttpServlet {

    /**
     * define default template to use.
     *
     * @var string
     */
    const DEFAULT_TEMPALTE = 'default';
    const DEFAULT_PAGE = 'index';

    /**
     * holds translator engine
     *
     * @var I18n
     */
    protected $i18n;

    /**
     * holds mustache template engine
     *
     * @var \Mustache_Engine
     */
    protected $mustache;

    /**
     * holds yml parser instance
     *
     * @var Parser
     */
    protected $yaml;

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
            $rootDir = $rootDir . DS . $path;
        }
        return $rootDir;
    }

    /**
     * @param Request $req
     * @param Response $res
     */
    public function doGet(Request $req, Response $res)
    {

        error_log(__CLASS__ . ' HASH: ' . spl_object_hash($this));

        // initialize composer + mustache autoloader
        require $this->getServletConfig()->getWebappPath() . '/vendor/autoload.php';

        // init template engine
        $this->mustache = new \Mustache_Engine(array(
            'cache' => $this->getRootDir('cache/mustache'),
            'loader' => new \Mustache_Loader_FilesystemLoader($this->getRootDir('static/template')),
            'partials_loader' => new \Mustache_Loader_FilesystemLoader($this->getRootDir('static/template/partials')),
        ));

        // init translator engine
        $this->i18n = new I18n('de_DE');

        // init parser for template yaml data.
        $this->yaml = new Parser();

        // initialize the base URL
        $baseUrl = '/';

        // if the application has NOT been called over a VHost configuration append application folder naem
        if (!$this->getServletConfig()->getApplication()->isVhostOf($req->getServerName())) {
            $baseUrl .= $this->getServletConfig()->getApplication()->getName() . '/';
        }

        // check if root path is call without ending slash
        $internalData = array('BaseUrl' => $baseUrl);

        // init language
        // TODO: should be given by e.g. $req->getLocale()
        $locale = 'de_DE';

        // set locale for i18n
        $this->i18n->setLocale($locale);

        // grab page to render
        $page = trim(str_replace($baseUrl, '', $req->getPathInfo() .DS), '/');

        // if noting left take default page
        if ($page == '') {
            $page = self::DEFAULT_PAGE;
        }

        // set default template
        $template = self::DEFAULT_TEMPALTE;
        // check if page specific template exists
        if (file_exists($this->getRootDir('static' . DS . 'template' . DS . $page .'.mustache'))) {
            $template = $page;
        }

        // add global translations
        $this->i18n->addResource('xliff-file',
            $this->getRootDir('locales' . DS . $locale . DS . 'global.xliff'),
            $locale, 'global'
        );
        // add template view translations
        $this->i18n->addResource('xliff-file',
            $this->getRootDir('locales' . DS . $locale . DS . $page . '.xliff'),
            $locale, $page
        );

        // load global data
        $globalData = $this->yaml->parse(
            file_get_contents($this->getRootDir('data' . DS . 'global.yml'))
        );
        // translate data
        $this->i18n->translateData($globalData, 'global');

        $pageData = array();
        // check if page specific data exists
        if (file_exists($this->getRootDir('data' . DS . $page . '.yml'))) {
            $pageData = $this->yaml->parse(
                // load specific data
                file_get_contents($this->getRootDir('data' . DS . $page . '.yml'))
            );
            // translate data
            $this->i18n->translateData($pageData, $page);
        }

        // merge data arrays
        $data = array_merge($internalData, $globalData, $pageData);

        // render content
        $content = $this->mustache->render($template, $data);

        // render given template with parsed data
        $res->setContent($content);
     }

}