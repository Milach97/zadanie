<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;




class FrontController extends AbstractController
{
    #[Route('/', name: 'front')]
    public function index(Request $request,): Response
    {

        // $url = 'http://0.0.0.0:8000/api/book/list';
        // $response = file_get_contents($url);
        // $books = json_decode($response, true);

        $books = [
            [
                'id' => 1,
                'name' => 'Book 1',
                'author' => 'Author 1',
                'publisher' => 'Publisher 1',
                'pageCount' => 200,
                'image' => '/image1.jpg',
            ],
            [
                'id' => 2,
                'name' => 'Book 2',
                'author' => 'Author 2',
                'publisher' => 'Publisher 2',
                'pageCount' => 250,
                'image' => '/image2.jpg',
            ],
            [
                'id' => 3,
                'name' => 'Book 3',
                'author' => 'Author 2',
                'publisher' => 'Publisher 2',
                'pageCount' => 632,
                'image' => '/image3.jpg',
            ],
            [
                'id' => 4,
                'name' => 'Book 4',
                'author' => 'Author 2',
                'publisher' => 'Publisher 2',
                'pageCount' => 400,
                'image' => '/image4.jpg',
            ],
        ];

        return $this->render('front/index.html.twig', [
            'books' => $books,

        ]);
    }
}
