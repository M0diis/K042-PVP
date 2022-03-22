<?php

namespace App\Controller;


//use Services_OpenStreetMap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
//    private Services_OpenStreetMap $osm;

    public function __construct()
    {
//        $this->osm = new Services_OpenStreetMap();
    }

    #[Route('/map', name: 'map')]
    public function index(): Response
    {
        return $this->render('map/index.html.twig', [
            'controller_name' => 'MapController',
        ]);
    }
}
