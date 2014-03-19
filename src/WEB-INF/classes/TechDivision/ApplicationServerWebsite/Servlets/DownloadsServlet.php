<?php

/**
 * TechDivision\ApplicationServerWebsite\Servlets\DownloadsServlet
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

use Github\Client;
use Github\HttpClient\CachedHttpClient;
use TechDivision\Servlet\Http\HttpServletRequest;
use TechDivision\Servlet\Http\HttpServletResponse;

/**
 * This servlet loads the GitHub data for the application server
 * releases, appends it to the template data.
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
class DownloadsServlet extends IndexServlet
{

    /**
     * The GitHub OAuth Token used for authentication.
     * 
     * @var string
     */
    const OAUTH_TOKEN = 'oauth.token';
    
    /**
     * The GitHub username to load the releases for.
     * 
     * @var string
     */
    const USERNAME = 'techdivision';
    
    /**
     * The GitHub repository to load the releases for.
     * 
     * @var string
     */
    const REPOSITORY = 'TechDivision_ApplicationServer';
    
    /**
     * Class name of the API service class to load tmp directory information.
     *
     * @var string
     */
    const SERVICE_CLASS = 'TechDivision\ApplicationServer\Api\ContainerService';

    /**
     * Returns the page data translated from the XLIFF files and
     * extended with the TechDivision_ApplicationServer releases
     * loaded from the GitHub API.
     * 
     * @param \TechDivision\Servlet\Http\HttpServletRequest $servletRequest The actual request instance
     *
     * @return array The requested page data
     * @see \TechDivision\ApplicationServerWebsite\Servlets\AbstractServlet::getPageData()
     */
    public function getPageData(HttpServletRequest $servletRequest)
    {
        
        // load parent page data
        $pageData = parent::getPageData($servletRequest);
        
        // load a API instance
        $initialContext = $servletRequest->getContext()->getInitialContext();
        $apiInstance = $initialContext->newInstance(DownloadsServlet::SERVICE_CLASS, array($initialContext));
        
        // initialize the path to the cache directory
        $cacheDir = $apiInstance->getTmpDir() . DIRECTORY_SEPARATOR . 'github-api-cache';
        
        // initialize the GitHub client
        $client = new Client(
            new CachedHttpClient(
                array('cache_dir' => $cacheDir)
            )
        );
        
        // login with a OAuth token
        $client->authenticate(
            $this->getSetting(
                DownloadsServlet::OAUTH_TOKEN
            ),
            null,
            Client::AUTH_URL_TOKEN
        );
        
        // load the releases from GitHub
        $releases = $client->api('repositories')
            ->releases()
            ->all(DownloadsServlet::USERNAME, DownloadsServlet::REPOSITORY);

        // merge the GitHub release information to the page data and return the result
        return array_merge(
            $pageData,
            array('Releases' => $releases)
        );
    }
}
