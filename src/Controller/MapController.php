<?php

namespace App\Controller;

use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
     * @Route("/map", name="map")
     */
    public function index(): Response
    {
        $locations = $this->pdo->query("SELECT latlong[0] as lat, latlong[1] as long, created_at FROM \"LocationTest\"")->fetchAll();

        return $this->render('map/index.html.twig', [
            'locations' => $locations,
        ]);
    }
}
