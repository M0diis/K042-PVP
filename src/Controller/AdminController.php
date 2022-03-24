<?php

namespace App\Controller;

use mysqli;
use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    private PDO $pdo;

    public function __construct(ParameterBagInterface $params)
    {
        $dsn = "pgsql:host=".$params->get('database_host').";port=5432;dbname=".$params->get('database_name').";";

        $this->pdo = new PDO($dsn, $params->get('database_user'), $params->get('database_password'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    /**
     * @Route("/admin/login", name="admin_login", methods={"POST"})
     */
    public function login(Request $request) : Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $stmt = $this->pdo->prepare("SELECT * FROM \"Users\" WHERE email = :email AND password = :password");
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
        return $this->render('admin/main.html.twig');
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }
}
