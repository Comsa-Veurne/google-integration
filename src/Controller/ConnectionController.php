<?php
/**
 * Created by PhpStorm.
 * User: cirykpopeye
 * Date: 2019-03-18
 * Time: 10:20
 */

namespace Comsa\GoogleIntegration\Controller;


use Comsa\GoogleIntegration\Connector;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ConnectionController extends Controller
{
    private $connector;

    public function connect()
    {
        $this->connector = new Connector(
            getenv('google_client_id'),
            getenv('google_secret'),
            $this->generateUrl('connection_url')
        );
    }
}