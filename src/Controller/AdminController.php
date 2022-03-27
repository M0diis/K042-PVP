<?php

namespace App\Controller;

use mysqli;
use PDO;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    private PDO $pdo;
    private ParameterBagInterface $params;
    private LoggerInterface $logger;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger)
    {
        $this->params = $params;

        $dsn = "pgsql:host=".$params->get('database_host').";port=5432;dbname=".$params->get('database_name').";";

        $this->pdo = new PDO($dsn, $params->get('database_user'), $params->get('database_password'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    /**
     * @Route("/admin/edit/location/submit", name="submiteditid", methods={"GET", "HEAD"})
     */
    public function submitToList() : Response
    {
        return $this->redirectToRoute("admin_main");
    }

    /**
     * @Route("/admin/edit/location/submit", name="submiteditid", methods={"POST"})
     */
    public function submitEdit(Request $request) : Response
    {
        $id = $request->request->get('id');

        $lat = $request->request->get('lat');
        $lng = $request->request->get('lng');
        $email = $request->request->get('email');

        $stmt = $this->pdo->prepare("UPDATE \"Locations\" SET latlong = ST_POINT(:lat, :lng, 4326), email = :email WHERE location_id = :id");

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':lat', $lat);
        $stmt->bindParam(':lng', $lng);
        $stmt->bindParam(':email', $email);

        $stmt->execute();

        return $this->redirectToRoute("admin_main");
    }

    /**
     * @Route("/admin/edit/location/{id}", name="edit_location", methods={"GET", "HEAD"})
     */
    public function locationEdit(Request $request) : Response
    {
        $id = $request->attributes->get('id');

        $stmt = $this->pdo->prepare('SELECT location_id, ST_X(latlong) AS lat, ST_Y(latlong) AS lng, created_at, email FROM "Locations" WHERE location_id = :id');
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->render("admin/location_edit.html.twig", [
            'location' => $result
        ]);
    }

    /**
     * @Route("/admin/edit/job/{id}", name="edit_job", methods={"GET", "HEAD"})
     */
    public function jobEdit(Request $request) : Response
    {
        $id = $request->attributes->get('id');

        $stmt = $this->pdo->prepare(
            'SELECT wi.location_id AS location_id, wi.work_info_id, ST_X(latlong) AS lat, ST_Y(latlong) AS lng, created_at, status, Wi.price, Wi.work_start, Wi.work_end
                FROM "Locations"
                LEFT JOIN "Work_information" Wi 
                    ON Wi.location_id = "Locations".location_id
                WHERE work_info_id = :id');

        $stmt->bindParam(':id', $id);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->render("admin/job_edit.html.twig", [
            'work' => $result
        ]);
    }

    /**
     * @Route("/admin/delete/location/{id}", name="delete_location", methods={"GET", "HEAD"})
     */
    public function delete(Request $request) : Response
    {
        $id = $request->attributes->get('id');

        $stmt = $this->pdo->prepare("DELETE FROM \"Locations\" WHERE location_id = :id");
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        if($stmt->rowCount() > 0)
        {
            return new Response('Deleted', 200);
        }
        else
        {
            return new Response('Not found', 4040);
        }
    }

    /**
     * @Route("/admin/login", name="admin_login", methods={"POST"})
     */
    public function login(Request $request) : Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $stmt = $this->pdo->prepare('SELECT * FROM "Users" WHERE email = :email AND password = :password');
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        $stmt->execute();

        $user = $stmt->fetch();

        if ($user)
        {
            return $this->redirectToRoute('admin_main');
        }
        else
        {
            return $this->render("admin/index.html.twig", [
                'errors' => [
                    'Neteisingas el. pašto adresas arba slaptažodis.'
                ]
            ]);
        }
    }

    /**
     * @Route("/admin/main", name="admin_main")
     */
    public function main(): Response
    {
        return $this->render('admin/main.html.twig', [
            'locations' => $this->getLocations(),
            'geocode_api_key' => $this->params->get('geocode-api-key')
        ]);
    }
    /**
     * @Route("/admin/locations", name="admin_locations")
     */
    public function locations(): Response
    {
        return $this->render('admin/locations.html.twig', [
            'locations' => $this->getLocations(),
            'geocode_api_key' => $this->params->get('geocode-api-key')
        ]);
    }

    /**
     * @Route("/admin/jobs", name="admin_jobs")
     */
    public function jobs(): Response
    {
        $jobs = $this->pdo->query('
                SELECT wi.location_id AS location_id, wi.work_info_id, ST_X(latlong) AS lat, ST_Y(latlong) AS lng, created_at, status, Wi.price, Wi.work_start, Wi.work_end
                FROM "Locations"
                LEFT JOIN "Work_information" Wi 
                    ON Wi.location_id = "Locations".location_id')->fetchAll();

        return $this->render('admin/jobs.html.twig', [
            'jobs' => $jobs,
            'geocode_api_key' => $this->params->get('geocode-api-key')
        ]);
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/login.html.twig');
    }

    private function getLocations(): array
    {
        return $this->pdo->query("SELECT location_id, ST_X(latlong) AS lat, ST_Y(latlong) AS lng, created_at, email FROM \"Locations\"")->fetchAll();
    }
}
