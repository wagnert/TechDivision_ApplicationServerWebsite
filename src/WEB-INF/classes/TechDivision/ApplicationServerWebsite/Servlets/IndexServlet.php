<?php

/**
 * TechDivision\ApplicationServerWebsite\Servlets\IndexServlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ApplicationServerWebsite\Servlets;

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;

/**
 * This servlet renders static content using the default
 * template engine.
 *
 * @package     TechDivision\ApplicationServerWebsite
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class IndexServlet extends AbstractServlet {

    /**
     * @see \TechDivision\ServletContainer\Servlets\HttpServlet::doGet()
     */
    public function doGet(Request $req, Response $res)
    {   
        $res->setContent($this->processTemplate($req));
    }
}