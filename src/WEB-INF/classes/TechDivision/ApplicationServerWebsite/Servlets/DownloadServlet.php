<?php

/**
 * TechDivision\ApplicationServerWebsite\Servlets\SiteServlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ApplicationServerWebsite\Servlets;

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
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;

class DownloadServlet extends DefaultServlet {

    /**
     * defines base download uri
     */
    const BASE_DOWNLOAD_URI = '/dl/';

    /**
     * @var mirror urls
     */
    protected $mirrors = array(
        'Linux'
            => array(
                0 => '${webapp.dl.url.linux}'
            ),
        'MacOS'
            => array(
                0 => '${webapp.dl.url.macos}'
            ),
    );

    /**
     * The download filename
     *
     * @var string
     */
    protected $downloadFilename = null;

    /**
     * Get mirror by request uri
     *
     * @param ServletRequest $req
     * @return string
     */
    public function getMirrorUrl()
    {
        if (array_key_exists($this->downloadFilename, $this->mirrors)) {
            return $this->mirrors[$this->downloadFilename][0];
        }
    }

    /**
     * @param Request $req
     * @param Response $res
     */
    public function doGet(Request $req, Response $res)
    {
        /** @var \TechDivision\ServletContainer\Http\HttpRequest $req */
        /** @var \TechDivision\ServletContainer\Http\HttpResponse $res */

        // define downloadFilename
        $this->downloadFilename = basename($req->getUri());

        // check mirror for filename
        $mirrorUrl = $this->getMirrorUrl();
        // if mirror exists send redirect headers
        if ($mirrorUrl) {
            $res->setHeaders(
                    array(
                        "status"                 => "HTTP/1.1 302 OK",
                        "Date"                   => gmdate('D, d M Y H:i:s \G\M\T', time()),
                        "Last-Modified"          => gmdate('D, d M Y H:i:s \G\M\T', time()),
                        "Expires"                => gmdate('D, d M Y H:i:s \G\M\T', time() - 3600),
                        "Server"                 => "Apache/4.3.29 (Unix) PHP/5.4.10",
                        "Content-Language"       => "de",
                        "Location"               => $mirrorUrl,
                        "Connection"             => "close",
                    )
                );
            $res->setContent(PHP_EOL);
        } else {
            $res->setContent(sprintf('No mirror defined for OS "%s"', $this->downloadFilename));
        }

    }
}