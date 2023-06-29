<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;



#[Route('/api/author', name: 'api_author_')]
class AuthorController extends AbstractController
{

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        //przekazane dane
        $data = json_decode($request->getContent(), true);

        $author = new Author();
         
        //nazwa
        if(isset($data['name']))
           $author->setName($data['name']);
        else
           return $this->json(['message' => 'Autor nie został zapisany. Nie przekazano nazwy.'], Response::HTTP_BAD_REQUEST);

        //kraj pochodzenia
        if(isset($data['country']))
            $author->setCountry($data['country']);

        //data zapisu
        $author->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Warsaw')));
 
        $em = $doctrine->getManager();
        $em->persist($author);
        $em->flush();


        return $this->json(['message' => 'Autor został zapisany',
                            'id' => $author->getId()], Response::HTTP_CREATED);
    }


    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, ManagerRegistry $doctrine, int $id): JsonResponse
    {
        //wybrany autor
        $author = $doctrine->getRepository(Author::class)->find($id);
        if(!$author) {
           return $this->json(['message' => 'Autor nie został znaleziony'], Response::HTTP_NOT_FOUND);
        }

        $em = $doctrine->getManager();
        $em->remove($author);
        $em->flush();

        return $this->json(['message' => 'Autor został usunięty'], Response::HTTP_OK);
    }
}
