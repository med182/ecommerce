<?php

namespace App\DataFixtures;

use Faker;
use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordEncoder,
    private SluggerInterface $slugger)
    {
     
    }

    public function load(ObjectManager $manager): void
    {



     $faker = Faker\Factory::create('fr_FR');
     for ($usr=0; $usr <5 ; $usr++) { 
        $user=new User();
        $user->setEmail($faker->email);
        $user->setLastname($faker->lastname);
        $user->setFirstname($faker->firstname);
        $user->setAddress($faker->streetAddress);
        $user->setZipcode(str_replace(' ','',$faker->postcode));
        $user->setCity($faker->city);
        $user->setPassword(
         $this->passwordEncoder->hashPassword($user,'secret')
       
        );
        
        $manager->persist($user);
     }
        $manager->flush();
    }
}
