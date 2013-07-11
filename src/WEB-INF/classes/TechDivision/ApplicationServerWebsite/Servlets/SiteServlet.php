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
use TechDivision\ServletContainer\Servlets\DefaultServlet;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Interfaces\ServletResponse;
use Symfony\Component\Yaml\Parser;

class SiteServlet extends DefaultServlet implements Servlet {

    /**
     * define default template to use.
     *
     * @var string
     */
    const DEFAULT_TEMPALTE = 'index';

    /**
     * holds mustache template engine
     *
     * @var \Mustache_Engine
     */
    protected $mustache = null;

    /**
     * holds yml parser instance
     *
     * @var Parser
     */
    protected $yaml = null;

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

        // init parser for yaml data.
        $this->yaml = new Parser();
    }

    public function __($text)
    {
        return 'Hallo';
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
        // init language
        // TODO: should be given by e.g. $req->getLocale()
        $language = 'de_de';

        // grap template to render
        $template = str_replace('/site', '', $req->getRequestUri());
        if (in_array($template, array('/', ''))) {
            $template = self::DEFAULT_TEMPALTE;
        }

        $data01 = $this->yaml->parse(
            // load global data
            file_get_contents($this->getRootDir('static/data/global.yml'))
        );
        $data02 = $this->yaml->parse(
            // load specific data
            file_get_contents($this->getRootDir('static/data/' . $language . '/' . $template . '.yml'))
        );

        // render given template with parsed data
        $res->setContent(
            $this->mustache->render('index', array_merge($data01, $data02))
        );

     }

}