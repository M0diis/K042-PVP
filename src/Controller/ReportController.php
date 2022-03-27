<?php

namespace App\Controller;

use mysqli;
use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    private PDO $pdo;

    public function __construct(ParameterBagInterface $params)
    {
        $dsn = "pgsql:host=".$params->get('database_host').";port=5432;dbname=".$params->get('database_name').";";

        $this->pdo = new PDO($dsn, $params->get('database_user'), $params->get('database_password'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
    /**
     * @Route("/report/submit", name="report_to_main", methods={"GET", "HEAD"})
     * @Route("/pranesimas/patvirtinti", name="pranesimas_i_pradini", methods={"GET", "HEAD"})
     */
    public function redirectToGet() : Response
    {
        return $this->redirectToRoute('report');
    }

    /**
     * @Route("/report/submit", name="report_submit", methods={"POST"})
     * @Route("/pranesimas/patvirtinti", name="patvirtinti_pranesima", methods={"POST"})
     */
    public function report(Request $request) : Response
    {
        $email = $request->request->get('email');
        $lat = $request->request->get('lat');
        $long = $request->request->get('lng');

        if(empty($_FILES['image_file']['name']))
        {
            return $this->render('report/index.html.twig', [
                'errors' => [
                    'Prašome įkelti rastos kelio duobės nuotrauką.'
                ]
            ]);
        }

        if(empty($lat) || empty($long) || empty($email))
        {
            return $this->render('report/index.html.twig', [
                'errors' => [
                    'Prašome užpildyti visus laukus.'
                ]
            ]);
        }

        if($_FILES['image_file']['size'] > 10485760)
        {
            return $this->render('report/index.html.twig', [
                'errors' => [
                    'Failas dydis per didelis.'
                ]
            ]);
        }

        $file = $_FILES['image_file'];

        // get bytes
        $data = file_get_contents($file['tmp_name']);

        // encode to base64
        $base64 = base64_encode($data);

        $query = "INSERT INTO \"Images\"(data, file_type) VALUES ('$base64', '$file[type]')";

        $this->pdo->query($query);

        $image_id = 'SELECT image_id FROM "Images" ORDER BY image_id DESC LIMIT 1;';

        $image_id = $this->pdo->query($image_id)->fetchColumn();

        $stmt = $this->pdo->prepare(
            "INSERT INTO \"Locations\" (image_id, latlong, email, status)  VALUES (:image_id, ST_POINT(:lat, :lng, 4326), :email, 'Laukia patvirtinimo')");

        $stmt->bindParam(':image_id', $image_id);
        $stmt->bindParam(':lat', $lat);
        $stmt->bindParam(':lng', $long);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $this->render('report/index.html.twig', [
            'messages' => [
                'Dėkojame už pranešimą!'
            ]
        ]);
    }

    /**
     * @Route("/report", name="report")
     * @Route("/pranesti", name="pranesti")
     * @Route("/pranesimas", name="pranesimas")
     */
    public function index(): Response
    {
        return $this->render('report/index.html.twig');
    }
}
