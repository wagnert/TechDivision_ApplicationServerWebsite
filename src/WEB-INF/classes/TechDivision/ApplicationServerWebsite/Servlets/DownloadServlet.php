<?php

/**
 * TechDivision\ApplicationServerWebsite\Servlets\AbstractServlet
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
 * @subpackage Servlets
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/techdivision/TechDivision_ApplicationServerWebsite
 */

namespace TechDivision\ApplicationServerWebsite\Servlets;

use TechDivision\Servlet\ServletConfig;
use TechDivision\Servlet\Http\HttpServletRequest;
use TechDivision\Servlet\Http\HttpServletResponse;
use TechDivision\ServletEngine\Http\Servlet;

/**
 * This abstrac base servlet that provides template rendering
 * functionality.
 *
 * @category   Application
 * @package    TechDivision_ApplicationServerWebsite
 * @subpackage Servlets
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       https://github.com/techdivision/TechDivision_ApplicationServerWebsite
 */
class DownloadServlet extends Servlet
{

    /**
     * Defines the base download URI.
     * 
     * @var string
     */
    const BASE_DOWNLOAD_URI = '/dl/';

    /**
     * Array containing the download mirrors.
     * 
     * @var array
     */
    protected $mirrors = array(
        'Techtalk072013'
            => array(
                0 => '${webapp.asset.techtalk.url}'
            ),
        'API'
            => array(
                0 => '${webapp.app.api.url}'
            ),
        'Admin'
            => array(
                0 => '${webapp.app.admin.url}'
            ),
        'Example'
            => array(
                0 => '${webapp.app.exmaple.url}'
            ),
        'Site'
            => array(
                0 => '${webapp.app.site.url}'
            ),
        'Magento1810'
            => array(
                0 => '${webapp.app.magento_1810.url}'
            ),
    );

    /**
     * The download filename.
     *
     * @var string
     */
    protected $downloadFilename = null;

    /**
     * Returns the requested mirror URI.
     *
     * @return string The requested mirror URI
     */
    public function getMirrorUrl()
    {
        if (array_key_exists($this->downloadFilename, $this->mirrors)) {
            return $this->mirrors[$this->downloadFilename][0];
        }
    }

    /**
     * Handles a GET request.
     * 
     * @param \TechDivision\Servlet\Http\HttpServletRequest  $servletRequest  The request instance
     * @param \TechDivision\Servlet\Http\HttpServletResponse $servletResponse The response instance
     * 
     * @return void
     */
    public function doGet(HttpServletRequest $servletRequest, HttpServletResponse $servletResponse)
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
<<<<<<< HEAD
                    array(
                        "Status"                 => "HTTP/1.1 302 OK",
                        "Date"                   => gmdate('D, d M Y H:i:s \G\M\T', time()),
                        "Last-Modified"          => gmdate('D, d M Y H:i:s \G\M\T', time()),
                        "Expires"                => gmdate('D, d M Y H:i:s \G\M\T', time() - 3600),
                        "Server"                 => "Apache/4.3.29 (Unix) PHP/5.4.10",
                        "Content-Language"       => "de",
                        "Location"               => $mirrorUrl,
                        "Connection"             => "close",
                    )
                );
=======
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
>>>>>>> 8419796d042c3cd52635a605ca2fea59db779886
            $res->setContent(PHP_EOL);
        } else {
            $res->setContent(sprintf('No mirror defined for download "%s"', $this->downloadFilename));
        }
    }
}
