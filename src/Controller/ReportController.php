<?php

namespace App\Controller;

use mysqli;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    private mysqli $conn;

    public function __construct(ParameterBagInterface $params)
    {
        $this->conn = new mysqli(
            $params->get('database_host'),
            $params->get('database_user'),
            $params->get('database_password'),
            $params->get('database_name')
        );

        if ($this->conn->connect_error)
        {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    /**
     * @Route("/report/submit", name="report_submit", methods={"POST"})
     */
    public function report(Request $request) : Response
    {
        $name = $request->request->get('name');
        $number = $request->request->get('phone');
        $email = $request->request->get('email');
        $address = $request->request->get('address');

        if(empty($name) || empty($number) || empty($email) ||  empty($address))
        {
            return $this->render('report/index.html.twig', [
                'errors' => [
                    'Prašome užpildyti visus laukus.'
                ]
            ]);
        }

        //$sql = "INSERT INTO user_reports (name, number, email, address) VALUES (:name, :number, :email, :address)";

        //$result = $this->conn->query($sql);

        return $this->render('report/index.html.twig', [
            'messages' => [
                'Dėkojame už pranešimą!'
            ]
        ]);
    }

    /**
     * @Route("/report", name="report")
     */
    public function index(): Response
    {
        return $this->render('report/index.html.twig');
    }
}
