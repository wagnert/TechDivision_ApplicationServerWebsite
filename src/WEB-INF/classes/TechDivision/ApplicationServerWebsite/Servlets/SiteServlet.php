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

use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Interfaces\ServletResponse;
use Symfony\Component\Yaml\Parser;
use TechDivision\ApplicationServerWebsite\Utilities\I18n;
use TechDivision\ServletContainer\Servlets\DefaultServlet;


class SiteServlet extends DefaultServlet {

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
     * @param ServletConfig $config
     * @throws ServletException;
     * @return mixed
     * @todo Implement init() method
     */
    public function init(ServletConfig $config)
    {

        // IMPORTANT: call parent method
        parent::init($config);

        // initialize composer + mustache autoloader
        require $this->getServletConfig()->getWebappPath() . '/vendor/autoload.php';

        // init template engine
        $this->mustache = new \Mustache_Engine(array(
            'cache' => $this->getRootDir('cache/mustache'),
            'loader' => new \Mustache_Loader_FilesystemLoader($this->getRootDir('static/template')),
        ));

        // init translator engine
        $this->i18n = new I18n('de_DE');

        // init parser for template yaml data.
        $this->yaml = new Parser();
    }

    /**
     * Returns webapp folder
     *
     * TODO: should be retrieved by application object.
     * @return string
     */
    public function getWebappFolder()
    {
        return '/site';
    }

    /**
     * Returns webapp root dir with path extended.
     *
     * TODO: should be retrieved by application object.
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
     * @param ServletRequest $req
     * @param ServletResponse $res
     */
    public function doGet(ServletRequest $req, ServletResponse $res)
    {
        // check if root path is call without ending slash
        $internalData = array(
            'BaseUrl' => $this->getWebappFolder() . DS
        );

        // init language
        // TODO: should be given by e.g. $req->getLocale()
        $locale = 'de_DE';

        $this->i18n->setLocale($locale);

        // grab page to render
        $page = trim(str_replace($this->getWebappFolder(), '', $req->getRequestUri()), '/');
        // if noting left take default page
        if (!$page) {
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

        $globalData = $this->yaml->parse(
            // load global data
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

        $data = array_merge($internalData, $globalData, $pageData);

        // render given template with parsed data
        $res->setContent(
            $this->mustache->render($template, $data)
        );

     }

}