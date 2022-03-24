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
    private ParameterBagInterface $params;
    private LoggerInterface $logger;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger)
    {
        $this->params = $params;
        $this->logger = $logger;

        $dsn = "pgsql:host=".$params->get('database_host').";port=5432;dbname=".$params->get('database_name').";";

        $this->pdo = new PDO($dsn, $params->get('database_user'), $params->get('database_password'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    /**
     * @Route("/api/v1/submit/latlng", name="location_submit", methods={"POST"})
     *
     * Available POST parameters:
     * - api_key: string
     * - lat: float
     * - lng: float
     *
     * @return Response
     */
    public function index(): Response
    {
        $apiKey = $_POST['api-key'] ?? null;

        if ($apiKey !== $this->params->get('api-key')) {
            return new Response('Invalid API key.', Response::HTTP_UNAUTHORIZED);
        }

        $lat = $_POST['lat'];
        $lng = $_POST['lng'];

        $this->logger->info("Received latlng: $lat, $lng");

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
