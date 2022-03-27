<?php

namespace App\Controller;

use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    private PDO $pdo;

    public function __construct(ParameterBagInterface $params)
    {
        $dsn = "pgsql:host=".$params->get('database_host').";port=5432;dbname=".$params->get('database_name').";";

        $this->pdo = new PDO($dsn, $params->get('database_user'), $params->get('database_password'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    /**
     * @Route("/map/pan/{lat}/{long}", name="map_pan", methods={"GET", "HEAD"})
     */
    public function previewPanTo(Request $request): Response
    {
        $lat = $request->attributes->get('lat');
        $lng = $request->attributes->get('lng');

        return $this->render('map/index.html.twig', [
            'locations' =>  $this->getLocations(),
            'lat' => $lat,
            'lng' => $lng,
            'zoom' => 18
        ]);
    }

    /**
     * @Route("/map", name="map")
     * @Route("/zemelapis", name="zemelapis")
     */
    public function index(): Response
    {
        return $this->render('map/index.html.twig', [
            'locations' => $this->getLocations(),
            'lat' => 54.911824,
            'lng' => 23.923679,
            'zoom' => 13
        ]);
    }

    private function getLocations(): array
    {
        return $this->pdo->query("SELECT location_id, ST_X(latlong) AS lat, ST_Y(latlong) AS lng, created_at FROM \"Locations\"")->fetchAll();
    }
}
