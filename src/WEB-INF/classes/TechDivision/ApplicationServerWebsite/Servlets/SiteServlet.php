<?php

namespace TechDivision\ApplicationServerWebsite\Servlets;

require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/vendor/autoload.php';
\Mustache_Autoloader::register();

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
use TechDivision\ServletContainer\Servlets\HttpServlet;


class SiteServlet extends HttpServlet implements Servlet {

    /**
     * define default template to use.
     *
     * @var string
     */
    const DEFAULT_TEMPALTE = 'index';

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
     * constructor
     *
     * @return void
     */
    public function __construct()
    {
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
     * Returns webapp root dir with path extended.
     *
     * @param string $path
     * @return string
     */
    protected function getRootDir($path = null)
    {
        $rootDir = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) ;
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
        error_log($req->getRequestUri());

        // init language
        // TODO: should be given by e.g. $req->getLocale()
        $locale = 'de_DE';

        $this->i18n->setLocale($locale);

        // grap template path to render
        $template = trim(str_replace('/site', '', $req->getRequestUri()), '/');
        // if noting left take default template
        if (!$template) {
            $template = self::DEFAULT_TEMPALTE;
        }

        $globalData = $this->yaml->parse(
            // load global data
            file_get_contents($this->getRootDir('static' . DS . 'data' . DS . 'global.yml'))
        );
        $templateData = $this->yaml->parse(
            // load specific data
            file_get_contents($this->getRootDir('static' . DS . 'data' . DS . $template . '.yml'))
        );

        // add global translations
        $this->i18n->addResource('xliff-file',
            $this->getRootDir('locales' . DS . $locale . DS . 'global.xliff'),
            $locale, 'global'
        );
        // add template view translations
        $this->i18n->addResource('xliff-file',
            $this->getRootDir('locales' . DS . $locale . DS . $template . '.xliff'),
            $locale, $template
        );

        $this->i18n->translateData($globalData, 'global');
        $this->i18n->translateData($templateData, $template);

        $data = array_merge($globalData, $templateData);

        // render given template with parsed data
        $res->setContent(
            $this->mustache->render($template, $data)
        );

     }

}