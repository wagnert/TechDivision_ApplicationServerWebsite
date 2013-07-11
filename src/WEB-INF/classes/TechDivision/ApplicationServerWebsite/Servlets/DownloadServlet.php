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
use TechDivision\ServletContainer\Servlets\HttpServlet;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Interfaces\ServletResponse;

class DownloadServlet extends HttpServlet implements Servlet {

    /**
     * defines base download uri
     */
    const BASE_DOWNLOAD_URI = '/site/dl/';

    /**
     * @var mirror urls
     */
    protected $mirrors = array(
        'appserver-0.4.6beta_amd64.deb'
            => array(
                0 => 'https://dl.dropboxusercontent.com/s/otbx3nssfp9nmo0/appserver-0.4.6beta_amd64.deb'
            ),
        'ApplicationServer-0.4.6beta.pkg'
            => array(
                0 => 'https://dl.dropboxusercontent.com/s/9cf8282dh8ffw1d/ApplicationServer-0.4.6beta.pkg'
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
     * @param ServletRequest $req
     * @param ServletResponse $res
     */
    public function doGet(ServletRequest $req, ServletResponse $res)
    {
        /** @var \TechDivision\ServletContainer\Http\HttpServletRequest $req */
        /** @var \TechDivision\ServletContainer\Http\HttpServletResponse $res */

        // define downloadFilename
        $this->downloadFilename = str_replace(self::BASE_DOWNLOAD_URI, '', $req->getRequestUri());
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
            $res->setContent(sprintf('No mirror defined for "%s"', $this->downloadFilename));
        }

    }
}