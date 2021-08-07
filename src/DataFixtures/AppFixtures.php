<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Space;
use App\Entity\Answer;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Entity\Question;
use App\Entity\SubComment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr-FR');

        $spaceList = [];
        $userList = [];

        //create fake Spaces
        for ($i = 0; $i < 15; $i++) {

            $space = new Space;
            $space->setName($faker->word);
            $spaceList[] = $space;
            $manager->persist($space);
        }

        //create fake users
        for ($i = 0; $i < 21; $i++) {

            $user = new User;
            $user
                ->setCreatedAt(new DateTimeImmutable())
                ->setUpdatedAt(new DateTimeImmutable())
                ->setEmail($faker->email())
                ->setPassword('demo')
                ->setPseudonym($faker->userName)
                ->setRoles([]);
            $userList[] = $user;
            $manager->persist($user);
        }
        //create fake questions
        for ($a = 0; $a < 21; $a++) {
            $question = new Question;
            $question
                ->setQuestion($faker->sentence . '?')
                ->setAuthor($userList[mt_rand(0, 19)])
                ->setCreatedAt(new DateTimeImmutable())
                ->setUpdatedAt(new DateTimeImmutable());
            for ($b = 0; $b < mt_rand(1, 4); $b++) {
                $question->addSpace($spaceList[mt_rand(0, 10)]);
            }
            $manager->persist($question);

            //create fake Answers
            for ($b = 0; $b < mt_rand(2, 3); $b++) {
                $answer = new Answer;
                $answer->setCreatedAt(new DateTimeImmutable())
                    ->setUpdatedAt(new DateTimeImmutable())
                    ->setViewsNumber(mt_rand(1000, 200000))
                    ->setQuestion($question)
                    ->setAuthor($userList[mt_rand(0, 19)])
                    ->setAnswer(implode('', $faker->sentences));
                $manager->persist($answer);

                //create fake Comments
                for ($c = 0; $c < mt_rand(1, 2); $c++) {
                    $comment = new Comment;
                    $comment->setAnswer($answer)
                        ->setComment($faker->sentence)
                        ->setAuthor($userList[mt_rand(0, 19)])
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setUpdatedAt(new DateTimeImmutable());
                    $manager->persist($comment);

                    //create fake sub_comments
                    for ($d = 0; $d < mt_rand(1, 2); $d++) {
                        $subComment = new SubComment;
                        $subComment->setAuthor($userList[mt_rand(0, 19)])
                            ->setComment($comment)
                            ->setSubComment($faker->sentence)
                            ->setCreatedAt(new DateTimeImmutable())
                            ->setUpdatedAt(new DateTimeImmutable());
                        $manager->persist($subComment);
                    }
                }
            }
        }

        $manager->flush();
    }
}
