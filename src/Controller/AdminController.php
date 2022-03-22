<?php

namespace App\Controller;

use mysqli;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    private mysqli $conn;

    public function __construct()
    {
        $hostname = "localhost";
        $username = "root";
        $password = "";
        $database = "users";

        $this->conn = new mysqli($hostname, $username, $password, $database);

        if ($this->conn->connect_error)
        {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    /**
     * @Route("/admin/login", name="admin_login", methods={"POST"})
     */
    public function login(Request $request) : Response
    {
        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0)
        {
            return $this->redirectToRoute('admin_main');
        }
        else
        {
            return $this->index();
        }
    }

    /**
     * @Route("/admin/main", name="admin_main")
     */
    public function main(): Response
    {
        return $this->render('admin/main.html.twig', [

        ]);
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [

        ]);
    }
}
