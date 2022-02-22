<?php
namespace App\Service;


use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Songs;
use App\Entity\Rating;
use App\Entity\User;



class AverageRatings{

    public function getRatingForAUser(ManagerRegistry $doctrine, $songs, User $user){
        $entityManager = $doctrine->getManager();

        $currentUserRatings = [];

        // -- get the current user rating for each song
        foreach ($songs as $song){
            $query = $entityManager->createQuery(
                'SELECT r
                FROM App\Entity\Rating r
                WHERE r.user = :user
                AND r.song = :song'
            )->setParameter('user', $user)
            ->setParameter('song', $song);

            $rating = $query->getOneOrNullResult();
            $currentUserRatings[$song->getId()] = $rating ? $rating->getRate() :  0;
        }

        return $currentUserRatings;
    }

    public function getAvgRatingForASong($doctrine, $song, User $user){
       
        $ratingsNumber = $song->getRatings()->count();

        $scores = $song->getRatings()->map(function($rating) { 
            return $rating->getRate(); 
        })->getValues();

        
        $ratingSum = array_sum($scores);
         $ratingAvg = $ratingsNumber ? $ratingSum / $ratingsNumber : 0;

        return $ratingAvg;
    }

}