<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/v1/submit/latlng", name="location_submit", methods={"POST"})
     *
     * Available POST parameters:
     * - lat
     * - lng
     *
     * @return Response
     */
    public function index(): Response
    {
        $lat = $_POST['lat'];
        $lng = $_POST['lng'];

        if(!empty($lat) || !empty($lng))
        {
            return new Response(400);
        }

        return new Response(200);
    }
}
