<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Songs;
use App\Entity\Rating;
use App\Entity\User;

use App\Service\AverageRatings;
use Doctrine\Common\Collections\ArrayCollection;


class SongsController extends AbstractController
{
    /**
     * @Route("/", name="songs")
     */
    public function index(AverageRatings $AverageRatingsServ, ManagerRegistry $doctrine): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $entityManager = $doctrine->getManager();

        $currentUser = $this->getUser();

        $songs = $doctrine->getRepository(Songs::class)->findAll();

        $averageRatings = [];
        $currentUserRatings = [];

        foreach ($songs as $song){
            $averageRatings[$song->getId()] = $AverageRatingsServ->getAvgRatingForASong($doctrine, $song, $currentUser);
        }

        $currentUserRatings = $AverageRatingsServ->getRatingForAUser($doctrine, $songs, $currentUser);


        return $this->render('songs/index.html.twig', [
            'controller_name' => 'SongsController',
             'songs' => $songs,
            'averageRatings' => $averageRatings,
            'currentUserRatings' => $currentUserRatings

        ]);
    }


    /**
      * @Route("/songs/show/{id}", name="songs_show" )
      */

      public function show(ManagerRegistry $doctrine, AverageRatings $avgRatingsSrv, int $id){
        $song = $doctrine->getRepository(Songs::class)->find($id);

        $avgRating = $avgRatingsSrv->getAvgRatingForASong($doctrine, $song, $this->getUser());
        
        $countRatings = $song->getRatings()->count();

        $ratings = $song->getRatings();

        return $this->render('songs/show.html.twig', [
            'song' => $song,
            'avgRating' => $avgRating,
            'countRatings' => $countRatings,
            'ratings' => $ratings
        ]);

     }



    /**
     * @Route("/songs/create", name="songs_create")
     */

     public function create(Request $request, ManagerRegistry $doctrine){

            $song = new Songs;

            $form = $this->createFormBuilder($song)
            ->add('Title', TextType::class, ['attr'=> ['class'=>'form-control']])
            ->add('Artist', TextType::class, ['attr' => ['class'=> 'form-control']])
            ->add('save', SubmitType::class, ['label'=>'Create', 'attr'=>['class'=>'btn btn-primary mt-3']])
            ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()){
                $song = $form->getData();

                $entityManager = $doctrine->getManager();

                $entityManager->persist($song);
                $entityManager->flush();
            
                return $this->redirectToRoute('songs');
            }
        
            return $this->renderForm('songs/create.html.twig', [
                'form' => $form
            ]);
     }


     /**
      * Create five songs in a row
      * @Route("/songs/add-five", name="songs_addfive")
      */

      public function addfiveSongs(ManagerRegistry $doctrine){
        
        $tracks = [
            ['title' => 'Shivers', 'artist' => 'Ed Sheeran'],
            ['title' => 'Heat Waves', 'artist' => 'Glass Animals'],
            ['title' => 'Infinity', 'artist' => 'Jaymes Young'],
            ['title' => 'Easy On Me', 'artist' => 'Adele'],
            ['title' => 'Light Switch', 'artist' => 'Charlie Puth']
        ];

        //get the doctrine manager
        $entityManager = $doctrine->getManager();

        foreach ($tracks as $track){
            $song = new Songs();
            $song->setTitle($track['title']);
            $song->setArtist($track['artist']);

            $entityManager->persist($song);
            $entityManager->flush();
        }

        $this->addFlash('notice','Your 5 songs have been added.');

        return $this->redirectToRoute('songs');
             
      }

    /**
     * @Route("/songs/edit/{id}", name="songs_edit")
     * 
     */
     
    public function edit(Request $request, ManagerRegistry $doctrine, int $id){
        
        
        $song = $doctrine->getRepository(Songs::class)->find($id);

        $form = $this->createFormBuilder($song)
        ->add('Title', TextType::class, ['attr'=> ['class'=>'form-control']])
        ->add('Artist', TextType::class, ['attr' => ['class'=> 'form-control']])
        ->add('save', SubmitType::class, ['label'=>'Create', 'attr'=>['class'=>'btn btn-primary mt-3']])
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $song = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($song);
            $entityManager->flush();

            return $this->redirectToRoute('songs');
        }

        return $this->renderForm('songs/edit.html.twig',[
            'form' => $form
        ]);

     }

    /**
     * @Route("/songs/{songId}/rate/{rateVal}", name="songs_rate")
     */

     public function rate(Request $request, ManagerRegistry $doctrine, AverageRatings $AverageRatingsServ, int $songId, int $rateVal) : Response {
        $entityManager = $doctrine->getManager();

        //first, get the current user
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $currentUser = $this->getUser();
        
        //then, find the current song
        $song = $doctrine->getRepository(Songs::class)->find($songId);
      
        if (!$song) return $this->json(['result'=> 'error', 'message'=>'No such a song has been found in the database.']);

        //look for a rating - check if current song and current user both have rating
        $query = $entityManager->createQuery(
            'SELECT r
            FROM App\Entity\Rating r
            WHERE r.user = :user
            AND r.song = :song'
        )->setParameter('user', $currentUser)
        ->setParameter('song', $song);


        $rating = $query->getOneOrNullResult();

        //if rating has been found, update it
        if ($rating) {
            $rating->setRate($rateVal);
            $entityManager->persist($rating);

            $entityManager->flush();

            //get all the ratings of current song
            $ratingsCount = $song->getRatings()->count();

            //get the average rating for the particular song
            $avgRating = $AverageRatingsServ->getAvgRatingForASong($doctrine, $song, $currentUser);

            return $this->json([
                'result' => 'ok',
                'message' => "Your rate for the song '". $song->getTitle() ."' has been updated.",
                'ratingSaved' => $rateVal,
                'ratingsCount' => $ratingsCount,
                'avgRating' => $avgRating
            ]);
        
            
        //if no rating has been found, create new one, attach it to the current song and user
        }else {
            $rating = new Rating;
            $rating->setRate($rateVal);

            $rating->setUser($currentUser);
            $rating->setSong($song);
            
            $entityManager->persist($rating);

            $entityManager->flush();

            //get all the ratings of current song
            $ratingsCount = $song->getRatings()->count();

            //get the average rating for the particular song
            $avgRating = $AverageRatingsServ->getAvgRatingForASong($doctrine, $song, $currentUser);

            return $this->json([
                    'result' => 'ok',
                    'message'=>"New rate added for the song '" . $song->getTitle() . "'! ",
                    'ratingSaved' => $rateVal,
                    'ratingsCount' => $ratingsCount,
                    'avgRating' => $avgRating
                ]
            );

        }

     }

     /**
      * @Route("/songs/delete/{id}}", name="songs_delete")
      */

     public function delete(Request $request, ManagerRegistry $doctrine, int $id) : RedirectResponse {
        $entityManager = $doctrine->getManager();
        $song = $entityManager->getRepository(Songs::class)->find($id);
  

        $entityManager->remove($song);
        $entityManager->flush();

        $this->addFlash(
            'notice',
            "The song has been deleted." 
        );

       return $this->redirectToRoute('songs');
    }
}
