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

#[Route('/api/book', name: 'api_book_')]
class BookController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request, ManagerRegistry $doctrine): JsonResponse
    {

        //wszytkie ksiazki
        $books = $doctrine
            ->getRepository(Book::class)
            ->createQueryBuilder('b')
            ->orderBy('b.name', 'asc')
            ->getQuery()
            ->getResult();

        $response = [];
        foreach ($books as $book) {
            $response[] = $this->formatBookData($book);
        }

        return new JsonResponse($response);
    }

    

    #[Route('/get/{id}', name: 'get', methods: ['GET'])]
    public function get(Request $request, ManagerRegistry $doctrine, int $id): JsonResponse
    {
        //wybrana ksiazka
        $book = $doctrine->getRepository(Book::class)->find($id);
        if(!$book) {
           return $this->json(['message' => 'Ksiązka nie została znaleziona'], Response::HTTP_NOT_FOUND);
        }

        $response = $this->formatBookData($book);
        return new JsonResponse($response);
    }



    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        //przekazane dane
        $data = json_decode($request->getContent(), true);

        $book = new Book();
         
        //nazwa
        if(isset($data['name']))
           $book->setName($data['name']);
        else
           return $this->json(['message' => 'Ksiązka nie została zapisana. Nie przekazano nazwy.'], Response::HTTP_BAD_REQUEST);

        //wydawnictwo
        if(isset($data['publisher']))
            $book->setPublisher($data['publisher']);

        //ilosc stron
        if(isset($data['pageCount']))
            $book->setPublisher($data['pageCount']);


        //zdjecie
        if($request->files->has('image')) {
            $image = $request->files->get('image');
    
            //generuj nazwę
            $imageName = uniqid().'.'.$image->getClientOriginalExtension();
    
            //przenies do /img
            $image->move($this->getParameter('img'), $imageName);
    
            //zapisz nazwe obrazu
            $book->setImage($imageName);
        }
        

        //autor
        if(isset($data['authorId'])){
            $author = $doctrine->getRepository(Author::class)->find($data['authorId']);
            if (!$author) {
               return $this->json(['message' => 'Podany autor nie został znaleziony'], Response::HTTP_NOT_FOUND);
            }
            $book->addAuthor($author);
        }
    
        //data zapisu
        $book->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Warsaw')));
 
        $em = $doctrine->getManager();
        $em->persist($book);
        $em->flush();


        return $this->json(['message' => 'Książka została zapisana',
                            'id' => $book->getId()], Response::HTTP_CREATED);
    }



    #[Route('/edit/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(Request $request, ManagerRegistry $doctrine, int $id): JsonResponse
    {
        //przekazane dane
        $data = json_decode($request->getContent(), true);


        //wybrana ksiazka
        $book = $doctrine->getRepository(Book::class)->find($id);
        if(!$book) {
           return $this->json(['message' => 'Ksiązka nie została znaleziona'], Response::HTTP_NOT_FOUND);
        }


        //nazwa
        if(isset($data['name']))
            $book->setName($data['name']);
         else
            return $this->json(['message' => 'Ksiązka nie została zapisana. Nie przekazano nazwy.'], Response::HTTP_BAD_REQUEST);
     
        //wydawnictwo
        if(isset($data['publisher']))
            $book->setPublisher($data['publisher']);
     
        //ilosc stron
        if(isset($data['pageCount']))
            $book->setPublisher($data['pageCount']);
     
        //zdjecie
        if(isset($data['image']))
            $book->setImage($data['image']);
     
        //autor
        if(isset($data['authorId'])){
            $author = $doctrine->getRepository(Author::class)->find($$data['authorId']);
            if (!$author) {
               return $this->json(['message' => 'Podany autor nie został znaleziony'], Response::HTTP_NOT_FOUND);
            }
            $book->addAuthor($author);
        }

        $em = $doctrine->getManager();
        $em->persist($book);
        $em->flush();

        return $this->json(['message' => 'Książka została edytowana',
                            'id' => $book->getId()], Response::HTTP_OK);
    }



    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, ManagerRegistry $doctrine, int $id): JsonResponse
    {
        //wybrana ksiazka
        $book = $doctrine->getRepository(Book::class)->find($id);
        if(!$book) {
           return $this->json(['message' => 'Ksiązka nie została znaleziona'], Response::HTTP_NOT_FOUND);
        }

        $em = $doctrine->getManager();
        $em->remove($book);
        $em->flush();

        return $this->json(['message' => 'Książka została usunięta'], Response::HTTP_OK);
    }



    #[Route('/search/{query}', name: 'search', methods: ['GET'])]
    public function search(Request $request, ManagerRegistry $doctrine, string $query): JsonResponse
    {
        if(strlen($query) < 3)
            return $this->json(['message' => 'Wyszukiwanie powinno zawierać conajmniej trzy znaki.'], Response::HTTP_BAD_REQUEST);
        

        $bookRepository = $doctrine->getRepository(Book::class);
        $books = $bookRepository->findBySearchQuery($query);

        $response = [];
        foreach ($books as $book) {
            $response[] = $this->formatBookData($book);
        }

        return new JsonResponse($response);
    }


    private function formatBookData(Book $book): array
    {
        $authors = $book->getAuthor();
        $authorData = [];
        foreach ($authors as $author) {
            $authorData[] = [
                'id' => $author->getId(),
                'name' => $author->getName(),
                'country' => $author->getCountry(),
                'createdAt' => $author->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return [
            'id' => $book->getId(),
            'name' => $book->getName(),
            'publisher' => $book->getPublisher(),
            'pageCount' => $book->getPageCount(),
            'image' => $book->getImage(),
            'authors' => $authorData, // Przekazuje tablicę danych autorów
            'createdAt' => $book->getCreatedAt()->format('Y-m-d H:i:s')
        ];
    }
}
