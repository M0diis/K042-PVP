<?php

namespace App\Controller;

use PDO;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/api/v1/test", name="test", methods={"GET", "HEAD"})
     */
    public function testAPI(): Response
    {
        return new Response('API is working.', 200);
    }

    /**
     * @Route("/api/v1/location/all", name="get_all")
     */
    public function getAllLocations(): Response
    {
        $sql = "SELECT ST_X(latlong) AS lat, ST_Y(latlong) AS lng, created_at FROM \"Locations\"";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->json($result);
    }

    /**
     * @Route("/api/v1/getimage/{id}.png", name="getimage", methods={"GET", "HEAD"})
     *
     * @param Request $request
     * @return Response
     */
    public function getImage(Request $request): Response
    {
        $id = $request->attributes->get('id');

        $stmt = $this->pdo->prepare("SELECT encode(data, 'base64') as base64 FROM \"Images\" WHERE image_id = :id");

        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $base64 = $stmt->fetch(PDO::FETCH_ASSOC)['base64'];

        if (!$base64)
        {
            return new Response(json_encode(['error' => 'Image not found']), 404);
        }

        $image = base64_decode($base64);
        $image = base64_decode($image);

        $response = new Response($image);
        $response->headers->set('Content-Type', 'image/png');

        return $response;
    }

    /**
     * @Route("/api/v1/get/location/{id}", name="getloc", methods={"GET", "HEAD"})
     *
     * @param int $id The id of the location to get
     *
     * @return Response
     */
    public function getLocation(Request $request): Response
    {
        $id = $request->attributes->get('id');

        $stmt = $this->pdo->prepare('SELECT * FROM "Locations" WHERE location_id = :id');
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result)
        {
            return new Response(json_encode($result), 200);
        }

        return new Response(json_encode(['error' => 'Location not found']), 404);
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
    public function submitLatLng(): Response
    {
        $apiKey = $_POST['api-key'] ?? null;

        if ($apiKey !== $this->params->get('api-key'))
        {
            return new Response('Invalid API key.', Response::HTTP_UNAUTHORIZED);
        }

        $lat = $_POST['lat'];
        $lng = $_POST['lng'];

        $this->logger->info("Received latlng: $lat, $lng");

        if(empty($lat) || empty($lng))
        {
            return new Response(400);
        }

        $stmt = $this->pdo->prepare("INSERT INTO \"Locations\"(latlong, email, status) VALUES (ST_POINT(:lat, :lng, 4326), null, 'Laukia patvirtinimo')");

        $stmt->bindParam(':lat', $lat);
        $stmt->bindParam(':lng', $lng);

        $stmt->execute();

        return new Response(200);
    }
}
