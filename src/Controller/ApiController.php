<?php

namespace App\Controller;

use PDO;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private PDO $pdo;

    public function __construct(ParameterBagInterface $params)
    {
        $dsn = "pgsql:host=".$params->get('database_host').";port=5432;dbname=".$params->get('database_name').";";

        $this->pdo = new PDO($dsn, $params->get('database_user'), $params->get('database_password'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    /**
     * @Route("/api/v1/submit/latlng", name="location_submit", methods={"POST"})
     *
     * Available POST parameters:
     * - lat
     * - lng
     *
     * @return Response
     */
    public function index(LoggerInterface $logger): Response
    {
        $lat = $_POST['lat'];
        $lng = $_POST['lng'];

        $logger->info("Received latlng: $lat, $lng");

        if(empty($lat) || empty($lng))
        {
            return new Response(400);
        }

        $stmt = $this->pdo->prepare("INSERT INTO \"LocationTest\"(latlong) VALUES (POINT(:lat, :lng))");

        $stmt->bindParam(':lat', $lat);
        $stmt->bindParam(':lng', $lng);

        $stmt->execute();

        return new Response(200);
    }
}
